<?php

class SalaryModel
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
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
        $sql = "
            UPDATE salary_levels
            SET
                level_name = :level_name,
                requirement_sessions = :requirement_sessions,
                amount = :amount
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'level_name' => trim($data['level_name'] ?? ''),
            'requirement_sessions' => (int) ($data['requirement_sessions'] ?? 0),
            'amount' => $this->normalizeMoney($data['amount'] ?? 0),
            'id' => (int) ($data['id'] ?? 0)
        ]);
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
        $data['type'] = 'bonus';
        return $this->saveAdjustment($data);
    }

    public function addPenalty($data)
    {
        $data['type'] = 'penalty';
        return $this->saveAdjustment($data);
    }

    public function deletePenaltyById($id)
    {
        $stmt = $this->db->prepare("
            DELETE FROM allowances_penalties
            WHERE id = ?
        ");

        return $stmt->execute([(int) $id]);
    }

    public function getStats($month, $year)
    {
        $sql = "
            SELECT
                COALESCE(SUM(CASE WHEN type='bonus' THEN amount ELSE 0 END), 0) AS total_bonus,
                COALESCE(SUM(CASE WHEN type='penalty' THEN amount ELSE 0 END), 0) AS total_penalty,
                COUNT(CASE WHEN type='bonus' THEN 1 END) AS bonus_count,
                COUNT(CASE WHEN type='penalty' THEN 1 END) AS penalty_count
            FROM allowances_penalties
            WHERE month = ?
            AND year = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int) $month, (int) $year]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getHistory($month, $year)
    {
        $sql = "
            SELECT 
                ap.*,
                u.name
            FROM allowances_penalties ap
            JOIN teachers t ON t.teacher_id = ap.teacher_id
            JOIN users u ON u.user_id = t.user_id
            WHERE ap.month = ?
            AND ap.year = ?
            ORDER BY ap.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int) $month, (int) $year]);

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
            )
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            (int) $data['teacher_id'],
            $data['type'] === 'penalty' ? 'penalty' : 'bonus',
            $this->normalizeMoney($data['amount'] ?? 0),
            trim($data['reason'] ?? ''),
            (int) ($data['month'] ?? date('m')),
            (int) ($data['year'] ?? date('Y')),
            (int) ($_SESSION['user']['id'] ?? 1)
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
            WHERE $where
            AND st.teacher_id = ?
            AND (s.status = 'done' OR s.session_date <= CURDATE())
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

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
                COALESCE(SUM(CASE WHEN type = 'bonus' THEN amount ELSE 0 END), 0) AS bonus,
                COALESCE(SUM(CASE WHEN type = 'penalty' THEN amount ELSE 0 END), 0) AS penalty
            FROM allowances_penalties
            WHERE teacher_id = ?
            AND month = ?
            AND year = ?
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
                COUNT(CASE WHEN status = 'absent' THEN 1 END) AS absent
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
}
