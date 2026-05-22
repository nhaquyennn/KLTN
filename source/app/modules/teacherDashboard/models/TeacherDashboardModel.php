<?php

class TeacherDashboardModel
{
    private $db;

    public function __construct()
    {
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $this->db = (new Database())->connect();
        $this->ensureDashboardMetadataSchema();
    }

    public function getTeacherByUserId($userId)
    {
        $stmt = $this->db->prepare("
            SELECT t.*, u.user_id, u.name, u.email
            FROM teachers t
            JOIN users u ON u.user_id = t.user_id
            WHERE t.user_id = ?
            AND t.status = 1
            LIMIT 1
        ");
        $stmt->execute([(int) $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getOverviewStats($teacherId)
    {
        $month = (int) date('m');
        $year = (int) date('Y');

        $stmt = $this->db->prepare("
            SELECT
                COUNT(DISTINCT CASE WHEN c.is_active = 1 THEN c.class_id END) AS class_count,
                COUNT(DISTINCT CASE WHEN s.session_date = CURDATE() THEN s.session_id END) AS today_sessions,
                COUNT(DISTINCT CASE WHEN e.status <> 'dropped' THEN e.student_id END) AS student_count,
                COUNT(DISTINCT CASE
                    WHEN MONTH(s.session_date) = ?
                    AND YEAR(s.session_date) = ?
                    AND ta.status IN ('present', 'late')
                    AND s.status <> 'cancelled'
                    THEN s.session_id
                END) AS taught_sessions
            FROM session_teachers st
            JOIN sessions s ON s.session_id = st.session_id
            JOIN classes c ON c.class_id = s.class_id
            LEFT JOIN enrollments e ON e.class_id = c.class_id
            LEFT JOIN teacher_attendance ta
                ON ta.session_id = s.session_id
                AND ta.teacher_id = st.teacher_id
            WHERE st.teacher_id = ?
        ");
        $stmt->execute([$month, $year, (int) $teacherId]);
        $overview = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $adjustmentStmt = $this->db->prepare("
            SELECT
                COALESCE(SUM(CASE WHEN type IN ('reward', 'bonus') AND status = 'active' THEN amount ELSE 0 END), 0) AS reward_total,
                COALESCE(SUM(CASE WHEN type = 'penalty' AND status = 'active' THEN amount ELSE 0 END), 0) AS penalty_total
            FROM allowances_penalties
            WHERE teacher_id = ?
            AND month = ?
            AND year = ?
        ");
        $adjustmentStmt->execute([(int) $teacherId, $month, $year]);
        $adjustments = $adjustmentStmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $attendanceStmt = $this->db->prepare("
            SELECT
                COUNT(CASE WHEN status = 'late' THEN 1 END) AS late_count,
                COUNT(CASE WHEN status IN ('absent', 'late_absent') THEN 1 END) AS absent_count
            FROM teacher_attendance
            WHERE teacher_id = ?
            AND MONTH(session_date) = ?
            AND YEAR(session_date) = ?
        ");
        $attendanceStmt->execute([(int) $teacherId, $month, $year]);
        $attendance = $attendanceStmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $payroll = (new SalaryModel())->getTeacherPayrollByUserId($this->getTeacherUserId($teacherId), $month, $year) ?: [];

        return array_merge($overview, $adjustments, [
            'estimated_salary' => (float) ($payroll['base_salary'] ?? 0),
            'net_salary' => (float) ($payroll['final_salary'] ?? 0),
            'late_absent_count' => (int) ($attendance['late_count'] ?? 0) + (int) ($attendance['absent_count'] ?? 0),
        ]);
    }

    public function getTodaySchedule($teacherId)
    {
        $stmt = $this->db->prepare("
            SELECT
                s.session_id,
                s.class_id,
                s.session_date,
                s.status AS session_status,
                c.class_code,
                co.name AS course_name,
                sh.name AS shift_name,
                sh.start_time,
                sh.end_time,
                r.name AS room_name,
                COUNT(DISTINCT e.student_id) AS student_count,
                ta.attendance_id,
                ta.status AS attendance_status,
                ta.check_in_time,
                ta.method
            FROM session_teachers st
            JOIN sessions s ON s.session_id = st.session_id
            JOIN classes c ON c.class_id = s.class_id
            JOIN courses co ON co.course_id = c.course_id
            LEFT JOIN shifts sh ON sh.shift_id = s.shift_id
            LEFT JOIN rooms r ON r.room_id = s.room_id
            LEFT JOIN enrollments e ON e.class_id = s.class_id AND e.status <> 'dropped'
            LEFT JOIN teacher_attendance ta
                ON ta.session_id = s.session_id
                AND ta.teacher_id = st.teacher_id
            WHERE st.teacher_id = ?
            AND s.session_date = CURDATE()
            GROUP BY s.session_id
            ORDER BY sh.start_time ASC, s.session_id ASC
        ");
        $stmt->execute([(int) $teacherId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $row['display_status'] = $this->getSessionDisplayStatus($row);
        }

        return $rows;
    }

    public function getMyClasses($teacherId)
    {
        $stmt = $this->db->prepare("
            SELECT
                c.class_id,
                c.class_code,
                co.name AS course_name,
                p.total_sessions,
                COUNT(DISTINCT e.student_id) AS student_count,
                COUNT(DISTINCT CASE WHEN s.status = 'done' THEN s.session_id END) AS completed_sessions,
                COALESCE(
                    ROUND(
                        COUNT(DISTINCT CASE WHEN a.status IN ('present', 'late') THEN a.attendance_id END) * 100
                        / NULLIF(COUNT(DISTINCT a.attendance_id), 0),
                        1
                    ),
                    0
                ) AS attendance_rate
            FROM classes c
            JOIN courses co ON co.course_id = c.course_id
            JOIN packages p ON p.package_id = c.package_id
            JOIN sessions s ON s.class_id = c.class_id
            JOIN session_teachers st ON st.session_id = s.session_id
            LEFT JOIN enrollments e ON e.class_id = c.class_id AND e.status <> 'dropped'
            LEFT JOIN attendances a ON a.session_id = s.session_id AND a.student_id = e.student_id
            WHERE st.teacher_id = ?
            GROUP BY c.class_id
            ORDER BY c.class_id DESC
        ");
        $stmt->execute([(int) $teacherId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStudentsByClass($teacherId, $classId)
    {
        if (!$this->teacherOwnsClass($teacherId, $classId)) {
            throw new Exception('Bạn không có quyền xem lớp này');
        }

        $stmt = $this->db->prepare("
            SELECT
                st.student_id,
                su.name AS student_name,
                su.phone AS student_phone,
                COALESCE(NULLIF(st.parent_name, ''), GROUP_CONCAT(DISTINCT pu.name ORDER BY pu.name SEPARATOR ', ')) AS parent_name,
                COALESCE(NULLIF(st.parent_phone, ''), GROUP_CONCAT(DISTINCT pu.phone ORDER BY pu.name SEPARATOR ', ')) AS parent_phone,
                e.payment_status,
                COALESCE(
                    ROUND(
                        COUNT(DISTINCT CASE WHEN a.status IN ('present', 'late') THEN a.attendance_id END) * 100
                        / NULLIF(COUNT(DISTINCT sess.session_id), 0),
                        1
                    ),
                    0
                ) AS attendance_rate,
                SUBSTRING_INDEX(
                    GROUP_CONCAT(COALESCE(a.status, 'chưa điểm danh') ORDER BY sess.session_date DESC, sess.session_id DESC SEPARATOR '|'),
                    '|',
                    1
                ) AS latest_learning_status
            FROM enrollments e
            JOIN students st ON st.student_id = e.student_id
            JOIN users su ON su.user_id = st.user_id
            LEFT JOIN parent_student ps ON ps.student_id = st.student_id
            LEFT JOIN parents pr ON pr.parent_id = ps.parent_id
            LEFT JOIN users pu ON pu.user_id = pr.user_id
            LEFT JOIN sessions sess ON sess.class_id = e.class_id AND sess.status <> 'cancelled'
            LEFT JOIN attendances a ON a.session_id = sess.session_id AND a.student_id = st.student_id
            WHERE e.class_id = ?
            AND e.status <> 'dropped'
            GROUP BY st.student_id, e.enrollment_id
            ORDER BY su.name ASC
        ");
        $stmt->execute([(int) $classId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSalarySummary($teacher)
    {
        return (new SalaryModel())->getTeacherPayrollByUserId((int) $teacher['user_id'], date('m'), date('Y')) ?: [];
    }

    public function getRewardPenaltyHistory($teacherId, $filters = [])
    {
        $month = max(1, min(12, (int) ($filters['month'] ?? date('m'))));
        $year = (int) ($filters['year'] ?? date('Y'));
        $kind = $filters['kind'] ?? 'all';

        $sql = "
            SELECT ap.*, ta.method AS attendance_method
            FROM allowances_penalties ap
            LEFT JOIN teacher_attendance ta ON ta.attendance_id = ap.attendance_id
            WHERE ap.teacher_id = ?
            AND ap.month = ?
            AND ap.year = ?
        ";
        $params = [(int) $teacherId, $month, $year];

        $kindMap = [
            'reward' => "ap.type IN ('reward', 'bonus')",
            'penalty' => "ap.type = 'penalty'",
            'canceled' => "ap.status = 'canceled'",
            'active' => "ap.status = 'active'",
        ];

        if (isset($kindMap[$kind])) {
            $sql .= ' AND ' . $kindMap[$kind];
        }

        $sql .= " ORDER BY ap.created_at DESC LIMIT 30";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNotifications($userId, $limit = 8)
    {
        $stmt = $this->db->prepare("
            SELECT notification_id, title, message, type, is_read, created_at
            FROM notifications
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, (int) $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReportSeries($teacherId)
    {
        $labels = [];
        $sessions = [];
        $income = [];
        $onTimeRate = [];
        $lateAbsent = [];

        for ($offset = 5; $offset >= 0; $offset--) {
            $monthDate = new DateTimeImmutable('first day of -' . $offset . ' month', new DateTimeZone('Asia/Ho_Chi_Minh'));
            $month = (int) $monthDate->format('m');
            $year = (int) $monthDate->format('Y');
            $labels[] = $monthDate->format('m/Y');

            $attendance = $this->monthAttendanceStats($teacherId, $month, $year);
            $sessions[] = (int) $attendance['taught'];
            $onTimeRate[] = (float) $attendance['on_time_rate'];
            $lateAbsent[] = (int) $attendance['late_absent'];

            $salary = $this->db->prepare("
                SELECT final_salary
                FROM teacher_salary_logs
                WHERE teacher_id = ? AND month = ? AND year = ?
                ORDER BY id DESC LIMIT 1
            ");
            $salary->execute([(int) $teacherId, $month, $year]);
            $income[] = (float) ($salary->fetchColumn() ?: 0);
        }

        return compact('labels', 'sessions', 'income', 'onTimeRate', 'lateAbsent');
    }

    private function getTeacherUserId($teacherId)
    {
        $stmt = $this->db->prepare("SELECT user_id FROM teachers WHERE teacher_id = ? LIMIT 1");
        $stmt->execute([(int) $teacherId]);
        return (int) $stmt->fetchColumn();
    }

    private function teacherOwnsClass($teacherId, $classId)
    {
        $stmt = $this->db->prepare("
            SELECT 1
            FROM sessions s
            JOIN session_teachers st ON st.session_id = s.session_id
            WHERE st.teacher_id = ?
            AND s.class_id = ?
            LIMIT 1
        ");
        $stmt->execute([(int) $teacherId, (int) $classId]);
        return (bool) $stmt->fetchColumn();
    }

    private function getSessionDisplayStatus($session)
    {
        if (($session['session_status'] ?? '') === 'cancelled') {
            return 'Đã hủy';
        }

        $now = new DateTimeImmutable('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
        $start = new DateTimeImmutable($session['session_date'] . ' ' . ($session['start_time'] ?: '00:00:00'), new DateTimeZone('Asia/Ho_Chi_Minh'));
        $end = new DateTimeImmutable($session['session_date'] . ' ' . ($session['end_time'] ?: $session['start_time']), new DateTimeZone('Asia/Ho_Chi_Minh'));

        if ($now < $start) {
            return 'Chưa bắt đầu';
        }

        if ($now <= $end) {
            return 'Đang diễn ra';
        }

        return 'Đã kết thúc';
    }

    private function monthAttendanceStats($teacherId, $month, $year)
    {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(CASE WHEN status IN ('present', 'late') THEN 1 END) AS taught,
                COUNT(CASE WHEN status = 'present' THEN 1 END) AS on_time,
                COUNT(CASE WHEN status IN ('late', 'absent', 'late_absent') THEN 1 END) AS late_absent
            FROM teacher_attendance
            WHERE teacher_id = ?
            AND MONTH(session_date) = ?
            AND YEAR(session_date) = ?
        ");
        $stmt->execute([(int) $teacherId, (int) $month, (int) $year]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $taught = (int) ($row['taught'] ?? 0);

        return [
            'taught' => $taught,
            'late_absent' => (int) ($row['late_absent'] ?? 0),
            'on_time_rate' => $taught > 0 ? round((int) $row['on_time'] * 100 / $taught, 1) : 0,
        ];
    }

    private function ensureDashboardMetadataSchema()
    {
        $teacherColumns = $this->db->query("SHOW COLUMNS FROM teacher_attendance")->fetchAll(PDO::FETCH_COLUMN);
        $allowanceColumns = $this->db->query("SHOW COLUMNS FROM allowances_penalties")->fetchAll(PDO::FETCH_COLUMN);

        if (!in_array('confidence_score', $teacherColumns, true)) {
            $this->db->exec("ALTER TABLE teacher_attendance ADD confidence_score DECIMAL(6,4) NULL AFTER method");
        }

        if (!in_array('note', $teacherColumns, true)) {
            $this->db->exec("ALTER TABLE teacher_attendance ADD note TEXT NULL AFTER face_image");
        }

        if (!in_array('attendance_id', $allowanceColumns, true)) {
            $this->db->exec("ALTER TABLE allowances_penalties ADD attendance_id INT NULL AFTER session_id");
        }
    }
}
