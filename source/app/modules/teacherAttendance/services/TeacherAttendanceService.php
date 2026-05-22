<?php

class TeacherAttendanceService
{
    private $db;

    public function __construct()
    {
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $this->db = (new Database())->connect();
        $this->ensureSchema();
    }

    public function getTeacherByUserId($userId)
    {
        $stmt = $this->db->prepare("
            SELECT t.teacher_id, t.user_id, u.name, u.email
            FROM teachers t
            JOIN users u ON u.user_id = t.user_id
            WHERE t.user_id = ?
            AND t.status = 1
            LIMIT 1
        ");
        $stmt->execute([(int) $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function validateTeachingSession($teacherId, $sessionId)
    {
        $stmt = $this->db->prepare("
            SELECT
                s.session_id,
                s.class_id,
                s.session_date,
                s.status AS session_status,
                s.note,
                c.class_code,
                co.name AS course_name,
                sh.name AS shift_name,
                sh.start_time,
                sh.end_time,
                r.name AS room_name
            FROM session_teachers st
            JOIN sessions s ON s.session_id = st.session_id
            JOIN classes c ON c.class_id = s.class_id
            JOIN courses co ON co.course_id = c.course_id
            LEFT JOIN shifts sh ON sh.shift_id = s.shift_id
            LEFT JOIN rooms r ON r.room_id = s.room_id
            WHERE st.teacher_id = ?
            AND s.session_id = ?
            LIMIT 1
        ");
        $stmt->execute([(int) $teacherId, (int) $sessionId]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$session) {
            throw new Exception('Không có lịch dạy hợp lệ cho buổi học này');
        }

        if (($session['session_status'] ?? '') === 'cancelled') {
            throw new Exception('Buổi học đã bị hủy');
        }

        return $session;
    }

    public function validateCheckinTime($session)
    {
        $timing = $this->calculateAttendanceStatus($session);

        if ($timing['window'] === 'too_early') {
            throw new Exception('Chỉ được điểm danh trước giờ học tối đa 10 phút');
        }

        return $timing;
    }

    public function calculateAttendanceStatus($session)
    {
        if (empty($session['start_time'])) {
            return ['window' => 'on_time', 'status' => 'present', 'minutes' => 0];
        }

        $start = new DateTimeImmutable($session['session_date'] . ' ' . $session['start_time'], new DateTimeZone('Asia/Ho_Chi_Minh'));
        $now = new DateTimeImmutable('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
        $seconds = $now->getTimestamp() - $start->getTimestamp();

        if ($seconds < -600) {
            return ['window' => 'too_early', 'status' => null, 'minutes' => (int) floor($seconds / 60)];
        }

        if ($seconds < 900) {
            return ['window' => 'on_time', 'status' => 'present', 'minutes' => max(0, (int) floor($seconds / 60))];
        }

        if ($seconds <= 1800) {
            return ['window' => 'late', 'status' => 'late', 'minutes' => (int) floor($seconds / 60)];
        }

        return ['window' => 'absent', 'status' => 'late_absent', 'minutes' => (int) floor($seconds / 60)];
    }

    public function createAttendanceRecord($teacherId, $session, $method, $confidence = null, $snapshot = null, $note = '')
    {
        $timing = $this->validateCheckinTime($session);
        $status = $timing['status'];

        $existing = $this->getAttendanceRow($teacherId, $session['session_id']);
        if ($existing) {
            throw new Exception('Buổi học này đã có điểm danh giảng viên');
        }

        $stmt = $this->db->prepare("
            INSERT INTO teacher_attendance
                (teacher_id, session_id, class_id, session_date, check_in_time, status, method, confidence_score, face_image, note)
            VALUES
                (?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            (int) $teacherId,
            (int) $session['session_id'],
            (int) $session['class_id'],
            $session['session_date'],
            $status,
            $method === 'MANUAL' ? 'MANUAL' : 'FACE',
            $confidence !== null ? (float) $confidence : null,
            $snapshot,
            trim((string) $note) ?: null
        ]);

        $attendanceId = (int) $this->db->lastInsertId();
        $this->createPenaltyIfNeeded($teacherId, $session, $status, $attendanceId);

        if ($status === 'late_absent') {
            $this->cancelClassIfAbsent($session, $teacherId, $attendanceId);
        }

        return [
            'attendance_id' => $attendanceId,
            'status' => $status,
            'method' => $method === 'MANUAL' ? 'MANUAL' : 'FACE',
            'confidence_score' => $confidence !== null ? (float) $confidence : null,
            'checkin_time' => date('Y-m-d H:i:s'),
            'timing' => $timing
        ];
    }

    public function createPenaltyIfNeeded($teacherId, $session, $status, $attendanceId = null)
    {
        if ($status === 'late') {
            return $this->insertPenaltyOnce($teacherId, $session, 50000, 'Giảng viên điểm danh trễ từ 15 đến 30 phút', $attendanceId);
        }

        if (in_array($status, ['absent', 'late_absent'], true)) {
            return $this->insertPenaltyOnce($teacherId, $session, 100000, 'Giảng viên vắng mặt hoặc điểm danh sau 30 phút', $attendanceId);
        }

        return false;
    }

    public function cancelClassIfAbsent($session, $teacherId, $attendanceId = null)
    {
        $this->db->prepare("
            UPDATE sessions
            SET status = 'cancelled',
                note = 'Giảng viên vắng mặt'
            WHERE session_id = ?
            AND status <> 'cancelled'
        ")->execute([(int) $session['session_id']]);

        $this->createPenaltyIfNeeded($teacherId, $session, 'absent', $attendanceId);
        $this->sendCancelClassNotifications($session);
        $this->sendAbsentNotifications($session);
    }

    public function sendCancelClassNotifications($session)
    {
        $className = trim(($session['course_name'] ?? '') . ' ' . ($session['class_code'] ?? ''));
        $message = 'Buổi học của lớp ' . ($className ?: ('#' . (int) $session['class_id']))
            . ' ngày ' . date('d/m/Y', strtotime($session['session_date']))
            . ' đã bị hủy do giảng viên vắng mặt. Trung tâm sẽ sắp xếp buổi học bù sau.';

        foreach ($this->getClassRecipientIds($session['class_id']) as $userId) {
            $this->notifyOnce($userId, 'session_teacher_absent_cancelled', 'Buổi học đã hủy', $message, $session['session_id']);
        }
    }

    public function sendAbsentNotifications($session)
    {
        $className = trim(($session['course_name'] ?? '') . ' ' . ($session['class_code'] ?? ''));
        $message = 'Giảng viên vắng mặt trong buổi học lớp ' . ($className ?: ('#' . (int) $session['class_id']))
            . ' ngày ' . date('d/m/Y', strtotime($session['session_date'])) . '.';

        foreach ($this->getClassRecipientIds($session['class_id']) as $userId) {
            $this->notifyOnce($userId, 'teacher_session_absent', 'Cảnh báo vắng buổi học', $message, $session['session_id']);
        }
    }

    public function getAttendanceStatus($teacherId, $sessionId)
    {
        return $this->getAttendanceRow($teacherId, $sessionId);
    }

    public function markOverdueAbsences()
    {
        $stmt = $this->db->query("
            SELECT DISTINCT
                st.teacher_id,
                s.session_id,
                s.class_id,
                s.session_date,
                s.status AS session_status,
                c.class_code,
                co.name AS course_name,
                sh.name AS shift_name,
                sh.start_time,
                sh.end_time
            FROM session_teachers st
            JOIN sessions s ON s.session_id = st.session_id
            JOIN shifts sh ON sh.shift_id = s.shift_id
            JOIN classes c ON c.class_id = s.class_id
            JOIN courses co ON co.course_id = c.course_id
            LEFT JOIN teacher_attendance ta
                ON ta.session_id = s.session_id
                AND ta.teacher_id = st.teacher_id
            WHERE s.status NOT IN ('cancelled', 'conflict')
            AND ta.attendance_id IS NULL
            AND TIMESTAMP(s.session_date, sh.start_time) < DATE_SUB(NOW(), INTERVAL 30 MINUTE)
        ");

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $session) {
            $insert = $this->db->prepare("
                INSERT IGNORE INTO teacher_attendance
                    (teacher_id, session_id, class_id, session_date, status, method, note)
                VALUES
                    (?, ?, ?, ?, 'absent', 'AUTO', 'Tự động xác nhận vắng sau 30 phút')
            ");
            $insert->execute([
                (int) $session['teacher_id'],
                (int) $session['session_id'],
                (int) $session['class_id'],
                $session['session_date']
            ]);

            $attendance = $this->getAttendanceRow($session['teacher_id'], $session['session_id']);
            $this->cancelClassIfAbsent($session, $session['teacher_id'], $attendance['attendance_id'] ?? null);
        }
    }

    private function getAttendanceRow($teacherId, $sessionId)
    {
        $stmt = $this->db->prepare("
            SELECT attendance_id, teacher_id, session_id, check_in_time, status, method, confidence_score, face_image, note
            FROM teacher_attendance
            WHERE teacher_id = ?
            AND session_id = ?
            LIMIT 1
        ");
        $stmt->execute([(int) $teacherId, (int) $sessionId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function insertPenaltyOnce($teacherId, $session, $amount, $reason, $attendanceId)
    {
        $exists = $this->db->prepare("
            SELECT id
            FROM allowances_penalties
            WHERE teacher_id = ?
            AND session_id = ?
            AND type = 'penalty'
            AND reason = ?
            LIMIT 1
        ");
        $exists->execute([(int) $teacherId, (int) $session['session_id'], $reason]);

        if ($exists->fetchColumn()) {
            return false;
        }

        $stmt = $this->db->prepare("
            INSERT IGNORE INTO allowances_penalties
                (teacher_id, session_id, attendance_id, type, amount, reason, month, year, status, created_by)
            VALUES
                (?, ?, ?, 'penalty', ?, ?, ?, ?, 'active', NULL)
        ");

        return $stmt->execute([
            (int) $teacherId,
            (int) $session['session_id'],
            $attendanceId ? (int) $attendanceId : null,
            (float) $amount,
            $reason,
            (int) date('m', strtotime($session['session_date'])),
            (int) date('Y', strtotime($session['session_date']))
        ]);
    }

    private function getClassRecipientIds($classId)
    {
        $stmt = $this->db->prepare("
            SELECT DISTINCT st.user_id
            FROM enrollments e
            JOIN students st ON st.student_id = e.student_id
            WHERE e.class_id = ?
            AND e.status <> 'dropped'
            AND st.user_id IS NOT NULL
            UNION
            SELECT DISTINCT pr.user_id
            FROM enrollments e
            JOIN parent_student ps ON ps.student_id = e.student_id
            JOIN parents pr ON pr.parent_id = ps.parent_id
            WHERE e.class_id = ?
            AND e.status <> 'dropped'
            AND pr.user_id IS NOT NULL
        ");
        $stmt->execute([(int) $classId, (int) $classId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function notifyOnce($userId, $type, $title, $message, $referenceId)
    {
        $exists = $this->db->prepare("
            SELECT notification_id
            FROM notifications
            WHERE user_id = ?
            AND type = ?
            AND reference_id = ?
            LIMIT 1
        ");
        $exists->execute([(int) $userId, $type, (int) $referenceId]);

        if ($exists->fetchColumn()) {
            return false;
        }

        return $this->db->prepare("
            INSERT INTO notifications (user_id, title, message, type, reference_id, is_read)
            VALUES (?, ?, ?, ?, ?, 0)
        ")->execute([(int) $userId, $title, $message, $type, (int) $referenceId]);
    }

    private function ensureSchema()
    {
        $teacherColumns = $this->db->query("SHOW COLUMNS FROM teacher_attendance")->fetchAll(PDO::FETCH_COLUMN);
        $allowanceColumns = $this->db->query("SHOW COLUMNS FROM allowances_penalties")->fetchAll(PDO::FETCH_COLUMN);

        if (!in_array('confidence_score', $teacherColumns, true)) {
            $this->db->exec("ALTER TABLE teacher_attendance ADD confidence_score DECIMAL(6,4) NULL AFTER method");
        }

        if (!in_array('note', $teacherColumns, true)) {
            $this->db->exec("ALTER TABLE teacher_attendance ADD note TEXT NULL AFTER face_image");
        }

        $method = $this->db->query("SHOW COLUMNS FROM teacher_attendance LIKE 'method'")->fetch(PDO::FETCH_ASSOC);
        if (stripos((string) ($method['Type'] ?? ''), 'varchar') === false) {
            $this->db->exec("ALTER TABLE teacher_attendance MODIFY method VARCHAR(20) NULL");
            $this->db->exec("UPDATE teacher_attendance SET method = 'FACE' WHERE method = '1'");
        }

        $status = $this->db->query("SHOW COLUMNS FROM teacher_attendance LIKE 'status'")->fetch(PDO::FETCH_ASSOC);
        if (strpos((string) ($status['Type'] ?? ''), 'late_absent') === false) {
            $this->db->exec("ALTER TABLE teacher_attendance MODIFY status ENUM('present', 'late', 'absent', 'late_absent') DEFAULT 'present'");
        }

        if (!in_array('attendance_id', $allowanceColumns, true)) {
            $this->db->exec("ALTER TABLE allowances_penalties ADD attendance_id INT NULL AFTER session_id");
        }
    }
}
