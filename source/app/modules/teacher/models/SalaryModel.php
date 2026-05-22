<?php

class SalaryModel
{
    private $db;

    public function __construct()
    {
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $database = new Database();
        $this->db = $database->connect();
        $this->ensureAdjustmentHistorySchema();
    }

    public function getSalaryLevels()
    {
        $sql = "
            SELECT 
                sl.*,
                (
                    SELECT COUNT(*)
                    FROM teachers t
                    WHERE t.current_level_id = sl.id
                ) AS teacher_count
            FROM salary_levels sl
            WHERE sl.is_active = 1
            ORDER BY sl.type, sl.level
        ";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateSalaryLevel($data)
    {
        $amount = $this->normalizeMoney($data['amount'] ?? 0);

        $sql = "
            UPDATE salary_levels
            SET
                level_name = :level_name,
                requirement_sessions = :requirement_sessions,
                amount = :amount
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        $ok = $stmt->execute([
            'level_name' => trim($data['level_name'] ?? ''),
            'requirement_sessions' => (int) ($data['requirement_sessions'] ?? 0),
            'amount' => $amount,
            'id' => (int) ($data['id'] ?? 0)
        ]);

        if ($ok) {
            $syncStmt = $this->db->prepare("
                UPDATE teachers
                SET salary_value = ?
                WHERE current_level_id = ?
            ");
            $syncStmt->execute([
                $amount,
                (int) ($data['id'] ?? 0)
            ]);
        }

        return $ok;
    }

    public function autoPromoteTeacherLevels($type = 'per_session')
    {
        $type = $type === 'monthly' ? 'monthly' : 'per_session';
        $salaryType = $type === 'monthly' ? 'fixed' : 'per_session';

        $stmt = $this->db->prepare("
            SELECT
                t.teacher_id,
                t.current_level_id,
                t.salary_value,
                current_level.level AS current_level,
                current_level.type AS current_level_type
            FROM teachers t
            LEFT JOIN salary_levels current_level ON current_level.id = t.current_level_id
            WHERE t.status = 1
            AND t.salary_type = ?
        ");
        $stmt->execute([$salaryType]);
        $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $update = $this->db->prepare("
            UPDATE teachers
            SET current_level_id = ?, salary_value = ?
            WHERE teacher_id = ?
        ");

        $checked = 0;
        $promoted = 0;
        $skipped = 0;

        foreach ($teachers as $teacher) {
            $checked++;

            $totalSessions = $this->countTeachingSessionsUntilNow((int) $teacher['teacher_id']);
            $targetLevel = $this->findLevelForSessions($type, $totalSessions);

            if (!$targetLevel) {
                $skipped++;
                continue;
            }

            $currentLevel = ($teacher['current_level_type'] ?? '') === $type
                ? (int) ($teacher['current_level'] ?? 0)
                : 0;
            $targetLevelNumber = (int) ($targetLevel['level'] ?? 0);

            if ($targetLevelNumber <= $currentLevel && !empty($teacher['current_level_id'])) {
                $skipped++;
                continue;
            }

            $update->execute([
                (int) $targetLevel['id'],
                (float) $targetLevel['amount'],
                (int) $teacher['teacher_id']
            ]);

            $promoted++;
        }

        return [
            'checked' => $checked,
            'promoted' => $promoted,
            'skipped' => $skipped
        ];
    }

    public function calculateAllSalaries($month, $year)
    {
        $month = (int) $month;
        $year = (int) $year;

        $teachers = $this->db->query("
            SELECT t.teacher_id, t.salary_type, t.salary_value, t.current_level_id, u.name
            FROM teachers t
            JOIN users u ON u.user_id = t.user_id
            WHERE t.status = 1
            ORDER BY u.name ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $this->db->beginTransaction();

        try {
            foreach ($teachers as $teacher) {
                $this->calculateTeacherSalary((int) $teacher['teacher_id'], $month, $year, $teacher);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function calculateTeacherSalary($teacherId, $month, $year, $teacher = null)
    {
        $teacherId = (int) $teacherId;
        $month = (int) $month;
        $year = (int) $year;

        if (!$teacher) {
            $stmt = $this->db->prepare("
                SELECT teacher_id, salary_type, salary_value, current_level_id
                FROM teachers
                WHERE teacher_id = ?
            ");
            $stmt->execute([$teacherId]);
            $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if (!$teacher) {
            return false;
        }

        $salaryType = $teacher['salary_type'] === 'fixed' ? 'fixed' : 'per_session';
        $levelType = $salaryType === 'fixed' ? 'monthly' : 'per_session';
        $monthSessions = $this->countTeachingSessions($teacherId, $month, $year, false);
        $totalSessions = $this->countTeachingSessions($teacherId, $month, $year, true);
        $level = $this->findLevelForSessions($levelType, $totalSessions);
        $levelId = $level['id'] ?? ($teacher['current_level_id'] ?: null);
        $levelAmount = isset($level['amount']) ? (float) $level['amount'] : (float) ($teacher['salary_value'] ?? 0);

        $baseSalary = $salaryType === 'fixed'
            ? $levelAmount
            : $monthSessions * $levelAmount;

        $adjustments = $this->getAdjustmentTotals($teacherId, $month, $year);
        $attendance = $this->getTeacherAttendanceStats($teacherId, $month, $year);
        $finalSalary = max(0, $baseSalary + $adjustments['bonus'] - $adjustments['penalty']);

        $stmt = $this->db->prepare("
            SELECT id, status
            FROM teacher_salary_logs
            WHERE teacher_id = ? AND month = ? AND year = ?
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->execute([$teacherId, $month, $year]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $stmt = $this->db->prepare("
                UPDATE teacher_salary_logs
                SET
                    level_id = ?,
                    total_sessions = ?,
                    late_sessions = ?,
                    absent_sessions = ?,
                    base_salary = ?,
                    total_bonus = ?,
                    total_penalty = ?,
                    final_salary = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $levelId,
                $monthSessions,
                $attendance['late'],
                $attendance['absent'],
                $baseSalary,
                $adjustments['bonus'],
                $adjustments['penalty'],
                $finalSalary,
                $existing['id']
            ]);
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO teacher_salary_logs
                (
                    teacher_id, month, year, level_id, total_sessions,
                    late_sessions, absent_sessions, base_salary,
                    total_bonus, total_penalty, final_salary, status
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft')
            ");

            $stmt->execute([
                $teacherId,
                $month,
                $year,
                $levelId,
                $monthSessions,
                $attendance['late'],
                $attendance['absent'],
                $baseSalary,
                $adjustments['bonus'],
                $adjustments['penalty'],
                $finalSalary
            ]);
        }

        if ($levelId) {
            $stmt = $this->db->prepare("
                UPDATE teachers
                SET current_level_id = ?, salary_value = ?
                WHERE teacher_id = ?
            ");
            $stmt->execute([$levelId, $levelAmount, $teacherId]);
        }

        return true;
    }

    public function getPayroll($month, $year)
    {
        $stmt = $this->db->prepare("
            SELECT
                tsl.*,
                u.name AS teacher_name,
                t.salary_type,
                sl.level_name,
                sl.amount AS level_amount
            FROM teacher_salary_logs tsl
            JOIN teachers t ON t.teacher_id = tsl.teacher_id
            JOIN users u ON u.user_id = t.user_id
            LEFT JOIN salary_levels sl ON sl.id = tsl.level_id
            WHERE tsl.month = ? AND tsl.year = ?
            ORDER BY u.name ASC
        ");

        $stmt->execute([(int) $month, (int) $year]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCurrentMonthPayroll()
    {
        return $this->getPayroll((int) date('m'), (int) date('Y'));
    }

    public function getTeacherPayrollByUserId($userId, $month = null, $year = null)
    {
        $month = (int) ($month ?: date('m'));
        $year = (int) ($year ?: date('Y'));

        $teacher = $this->getTeacherByUserId($userId);
        if (!$teacher) {
            return null;
        }

        $this->calculateTeacherSalary((int) $teacher['teacher_id'], $month, $year, $teacher);

        $stmt = $this->db->prepare("
            SELECT
                tsl.*,
                u.name AS teacher_name,
                t.salary_type,
                sl.level_name,
                sl.amount AS level_amount
            FROM teacher_salary_logs tsl
            JOIN teachers t ON t.teacher_id = tsl.teacher_id
            JOIN users u ON u.user_id = t.user_id
            LEFT JOIN salary_levels sl ON sl.id = tsl.level_id
            WHERE tsl.teacher_id = ? AND tsl.month = ? AND tsl.year = ?
            ORDER BY tsl.id DESC
            LIMIT 1
        ");

        $stmt->execute([(int) $teacher['teacher_id'], $month, $year]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getTeacherPayrollHistoryByUserId($userId, $limit = 12)
    {
        $teacher = $this->getTeacherByUserId($userId);
        if (!$teacher) {
            return [];
        }

        $stmt = $this->db->prepare("
            SELECT
                tsl.*,
                sl.level_name,
                sl.amount AS level_amount
            FROM teacher_salary_logs tsl
            LEFT JOIN salary_levels sl ON sl.id = tsl.level_id
            WHERE tsl.teacher_id = ?
            ORDER BY tsl.year DESC, tsl.month DESC, tsl.id DESC
            LIMIT ?
        ");

        $stmt->bindValue(1, (int) $teacher['teacher_id'], PDO::PARAM_INT);
        $stmt->bindValue(2, (int) $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updatePayrollStatus($id, $status)
    {
        $allowedStatuses = ['draft', 'confirmed', 'paid'];

        if (!in_array($status, $allowedStatuses, true)) {
            return false;
        }

        $sql = "
            UPDATE teacher_salary_logs
            SET
                status = ?,
                confirmed_at = CASE WHEN ? IN ('confirmed', 'paid') THEN COALESCE(confirmed_at, NOW()) ELSE NULL END,
                confirmed_by = CASE WHEN ? IN ('confirmed', 'paid') THEN ? ELSE NULL END
            WHERE id = ?
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            $status,
            $status,
            $status,
            (int) ($_SESSION['user']['id'] ?? 1),
            (int) $id
        ]);
    }

    public function saveAdjustment($data)
    {
        $teacherId = $data['teacher_id'] ?? null;

        if ($teacherId === 'all') {
            $teachers = $this->db->query("
                SELECT teacher_id
                FROM teachers
                WHERE status = 1
            ")->fetchAll(PDO::FETCH_COLUMN);

            foreach ($teachers as $id) {
                $data['teacher_id'] = $id;
                $this->insertAdjustment($data);
            }

            return true;
        }

        return $this->insertAdjustment($data);
    }

    public function addBonus($data)
    {
        $data['type'] = 'reward';
        return $this->saveAdjustment($data);
    }

    public function addPenalty($data)
    {
        $data['type'] = 'penalty';
        return $this->saveAdjustment($data);
    }

    public function cancelAdjustment($id, $reason, $userId)
    {
        $adjustment = $this->getAdjustmentById($id);

        if (!$adjustment || ($adjustment['status'] ?? 'active') === 'canceled') {
            return false;
        }

        $stmt = $this->db->prepare("
            UPDATE allowances_penalties
            SET status = 'canceled',
                canceled_reason = ?,
                canceled_by = ?,
                canceled_at = NOW()
            WHERE id = ?
        ");

        return $stmt->execute([
            trim((string) $reason),
            (int) $userId ?: null,
            (int) $id
        ]);
    }

    public function updateAdjustment($id, $data)
    {
        $adjustment = $this->getAdjustmentById($id);

        if (!$adjustment || ($adjustment['status'] ?? 'active') === 'canceled') {
            return false;
        }

        $type = ($data['type'] ?? $adjustment['type']) === 'penalty' ? 'penalty' : 'reward';
        $stmt = $this->db->prepare("
            UPDATE allowances_penalties
            SET teacher_id = ?,
                session_id = ?,
                type = ?,
                amount = ?,
                reason = ?,
                month = ?,
                year = ?
            WHERE id = ?
            AND status = 'active'
        ");

        return $stmt->execute([
            (int) ($data['teacher_id'] ?? $adjustment['teacher_id']),
            !empty($data['session_id']) ? (int) $data['session_id'] : null,
            $type,
            $this->normalizeMoney($data['amount'] ?? $adjustment['amount']),
            trim($data['reason'] ?? $adjustment['reason']),
            (int) ($data['month'] ?? $adjustment['month']),
            (int) ($data['year'] ?? $adjustment['year']),
            (int) $id
        ]);
    }

    public function getAdjustmentById($id)
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM allowances_penalties
            WHERE id = ?
            LIMIT 1
        ");
        $stmt->execute([(int) $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getStats($month, $year)
    {
        $sql = "
            SELECT
                COALESCE(SUM(CASE WHEN type IN ('reward', 'bonus') THEN amount ELSE 0 END), 0) AS total_bonus,
                COALESCE(SUM(CASE WHEN type='penalty' THEN amount ELSE 0 END), 0) AS total_penalty,
                COUNT(CASE WHEN type IN ('reward', 'bonus') THEN 1 END) AS bonus_count,
                COUNT(CASE WHEN type='penalty' THEN 1 END) AS penalty_count
            FROM allowances_penalties
            WHERE month = ?
            AND year = ?
            AND status = 'active'
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int) $month, (int) $year]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getHistory($month, $year, $filters = [])
    {
        $filters = array_merge([
            'teacher_id' => null,
            'kind' => 'all',
            'from_date' => null,
            'to_date' => null,
        ], $filters);

        $sql = "
            SELECT 
                ap.*,
                u.name
            FROM allowances_penalties ap
            JOIN teachers t ON t.teacher_id = ap.teacher_id
            JOIN users u ON u.user_id = t.user_id
            WHERE ap.month = ?
            AND ap.year = ?
        ";
        $params = [(int) $month, (int) $year];

        if (!empty($filters['teacher_id'])) {
            $sql .= " AND ap.teacher_id = ?";
            $params[] = (int) $filters['teacher_id'];
        }

        $kindMap = [
            'reward' => "ap.type IN ('reward', 'bonus') AND ap.status = 'active'",
            'penalty' => "ap.type = 'penalty' AND ap.status = 'active'",
            'canceled_reward' => "ap.type IN ('reward', 'bonus') AND ap.status = 'canceled'",
            'canceled_penalty' => "ap.type = 'penalty' AND ap.status = 'canceled'",
            'canceled' => "ap.status = 'canceled'",
        ];

        if (isset($kindMap[$filters['kind']])) {
            $sql .= ' AND ' . $kindMap[$filters['kind']];
        }

        if (!empty($filters['from_date'])) {
            $sql .= " AND DATE(ap.created_at) >= ?";
            $params[] = $filters['from_date'];
        }

        if (!empty($filters['to_date'])) {
            $sql .= " AND DATE(ap.created_at) <= ?";
            $params[] = $filters['to_date'];
        }

        $sql .= " ORDER BY ap.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTeachingSessionsForAdjustments($month, $year)
    {
        $stmt = $this->db->prepare("
            SELECT
                s.session_id,
                s.session_date,
                c.class_code AS class_name,
                sh.start_time,
                sh.end_time
            FROM sessions s
            LEFT JOIN classes c ON c.class_id = s.class_id
            LEFT JOIN shifts sh ON sh.shift_id = s.shift_id
            WHERE MONTH(s.session_date) = ?
            AND YEAR(s.session_date) = ?
            ORDER BY s.session_date DESC, sh.start_time DESC
        ");

        $stmt->execute([(int) $month, (int) $year]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function insertAdjustment($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO allowances_penalties
            (
                teacher_id,
                type,
                amount,
                reason,
                month,
                year,
                created_by
                , session_id,
                status
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')
        ");

        return $stmt->execute([
            (int) $data['teacher_id'],
            $data['type'] === 'penalty' ? 'penalty' : 'reward',
            $this->normalizeMoney($data['amount'] ?? 0),
            trim($data['reason'] ?? ''),
            (int) ($data['month'] ?? date('m')),
            (int) ($data['year'] ?? date('Y')),
            (int) ($_SESSION['user']['id'] ?? 1),
            !empty($data['session_id']) ? (int) $data['session_id'] : null
        ]);
    }

    private function countTeachingSessions($teacherId, $month, $year, $cumulative)
    {
        $where = $cumulative
            ? "s.session_date <= LAST_DAY(STR_TO_DATE(CONCAT(?, '-', ?, '-01'), '%Y-%m-%d'))"
            : "MONTH(s.session_date) = ? AND YEAR(s.session_date) = ?";

        $params = $cumulative
            ? [(int) $year, (int) $month, (int) $teacherId]
            : [(int) $month, (int) $year, (int) $teacherId];

        $sql = "
            SELECT COUNT(DISTINCT s.session_id)
            FROM sessions s
            JOIN session_teachers st ON st.session_id = s.session_id
            JOIN teacher_attendance ta
                ON ta.session_id = s.session_id
                AND ta.teacher_id = st.teacher_id
                AND ta.status IN ('present', 'late')
            WHERE $where
            AND st.teacher_id = ?
            AND s.status <> 'cancelled'
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    private function countTeachingSessionsUntilNow($teacherId)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT s.session_id)
            FROM sessions s
            JOIN session_teachers st ON st.session_id = s.session_id
            JOIN teacher_attendance ta
                ON ta.session_id = s.session_id
                AND ta.teacher_id = st.teacher_id
                AND ta.status IN ('present', 'late')
            WHERE st.teacher_id = ?
            AND s.status <> 'cancelled'
        ");

        $stmt->execute([(int) $teacherId]);

        return (int) $stmt->fetchColumn();
    }

    private function findLevelForSessions($type, $sessions)
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM salary_levels
            WHERE type = ?
            AND is_active = 1
            AND requirement_sessions <= ?
            ORDER BY requirement_sessions DESC, level DESC
            LIMIT 1
        ");

        $stmt->execute([$type, (int) $sessions]);
        $level = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($level) {
            return $level;
        }

        $stmt = $this->db->prepare("
            SELECT *
            FROM salary_levels
            WHERE type = ?
            AND is_active = 1
            ORDER BY level ASC
            LIMIT 1
        ");
        $stmt->execute([$type]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getAdjustmentTotals($teacherId, $month, $year)
    {
        $stmt = $this->db->prepare("
            SELECT
                COALESCE(SUM(CASE WHEN type IN ('reward', 'bonus') THEN amount ELSE 0 END), 0) AS bonus,
                COALESCE(SUM(CASE WHEN type = 'penalty' THEN amount ELSE 0 END), 0) AS penalty
            FROM allowances_penalties
            WHERE teacher_id = ?
            AND month = ?
            AND year = ?
            AND status = 'active'
        ");

        $stmt->execute([(int) $teacherId, (int) $month, (int) $year]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'bonus' => (float) ($row['bonus'] ?? 0),
            'penalty' => (float) ($row['penalty'] ?? 0)
        ];
    }

    private function getTeacherAttendanceStats($teacherId, $month, $year)
    {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(CASE WHEN status = 'late' THEN 1 END) AS late,
                COUNT(CASE WHEN status IN ('absent', 'late_absent') THEN 1 END) AS absent
            FROM teacher_attendance
            WHERE teacher_id = ?
            AND MONTH(session_date) = ?
            AND YEAR(session_date) = ?
        ");

        $stmt->execute([(int) $teacherId, (int) $month, (int) $year]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'late' => (int) ($row['late'] ?? 0),
            'absent' => (int) ($row['absent'] ?? 0)
        ];
    }

    private function getTeacherByUserId($userId)
    {
        $stmt = $this->db->prepare("
            SELECT teacher_id, salary_type, salary_value, current_level_id
            FROM teachers
            WHERE user_id = ?
            LIMIT 1
        ");

        $stmt->execute([(int) $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function normalizeMoney($value)
    {
        return (float) preg_replace('/[^0-9.]/', '', (string) $value);
    }

    private function ensureAdjustmentHistorySchema()
    {
        $columns = $this->db->query("SHOW COLUMNS FROM allowances_penalties")->fetchAll(PDO::FETCH_COLUMN);
        $typeColumn = $this->db->query("SHOW COLUMNS FROM allowances_penalties LIKE 'type'")->fetch(PDO::FETCH_ASSOC);

        if (strpos((string) ($typeColumn['Type'] ?? ''), "'reward'") === false) {
            $this->db->exec("ALTER TABLE allowances_penalties MODIFY type ENUM('reward', 'bonus', 'penalty') NOT NULL");
        }

        if (!in_array('session_id', $columns, true)) {
            $this->db->exec("ALTER TABLE allowances_penalties ADD session_id INT NULL AFTER teacher_id");
        }

        if (!in_array('status', $columns, true)) {
            $this->db->exec("ALTER TABLE allowances_penalties ADD status ENUM('active', 'canceled') NOT NULL DEFAULT 'active' AFTER reason");
        }

        if (!in_array('canceled_reason', $columns, true)) {
            $this->db->exec("ALTER TABLE allowances_penalties ADD canceled_reason TEXT NULL AFTER status");
        }

        if (!in_array('canceled_by', $columns, true)) {
            $this->db->exec("ALTER TABLE allowances_penalties ADD canceled_by INT NULL AFTER created_by");
        }

        if (!in_array('updated_at', $columns, true)) {
            $this->db->exec("ALTER TABLE allowances_penalties ADD updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
        }

        if (!in_array('canceled_at', $columns, true)) {
            $this->db->exec("ALTER TABLE allowances_penalties ADD canceled_at DATETIME NULL AFTER updated_at");
        }
    }
}
