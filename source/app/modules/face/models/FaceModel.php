<?php

class FaceModel
{
    private $db;

    public function __construct()
    {
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $this->db = (new Database())->connect();
        $this->ensureAttendancePolicySchema();
    }

    public function getUsers()
    {
        $stmt = $this->db->prepare("
            SELECT
                u.user_id,
                u.name,
                u.role,
                CASE WHEN fd.face_id IS NULL THEN 0 ELSE 1 END AS has_face
            FROM users u
            LEFT JOIN face_data fd
                ON fd.user_id = u.user_id
                AND COALESCE(fd.is_active, 1) = 1
            WHERE u.status = 1
            AND u.role IN ('teacher', 'student')
            GROUP BY u.user_id
            ORDER BY u.role ASC, u.name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTodayTeacherSessions($userId)
    {
        $this->processOverdueAttendancePolicy();
        $user = $this->getUserById($userId);

        if (($user['role'] ?? '') === 'admin') {
            return $this->getTodaySessionsForAdmin();
        }

        $teacherId = $this->getTeacherIdByUserId($userId);

        if (!$teacherId) {
            return [];
        }

        $stmt = $this->db->prepare("
            SELECT
                s.session_id,
                s.class_id,
                s.session_date,
                s.status AS session_status,
                c.class_code,
                co.name AS course_name,
                p.name AS package_name,
                sh.name AS shift_name,
                sh.start_time,
                sh.end_time,
                r.name AS room_name,
                COUNT(DISTINCT e.student_id) AS student_count,
                COUNT(DISTINCT a.student_id) AS attended_count,
                MAX(CASE WHEN ta.attendance_id IS NULL THEN 0 ELSE 1 END) AS teacher_checked
            FROM session_teachers st
            JOIN sessions s ON s.session_id = st.session_id
            JOIN classes c ON c.class_id = s.class_id
            JOIN courses co ON co.course_id = c.course_id
            JOIN packages p ON p.package_id = c.package_id
            LEFT JOIN shifts sh ON sh.shift_id = s.shift_id
            LEFT JOIN rooms r ON r.room_id = s.room_id
            LEFT JOIN enrollments e
                ON e.class_id = s.class_id
                AND e.status <> 'dropped'
            LEFT JOIN attendances a
                ON a.session_id = s.session_id
                AND a.student_id = e.student_id
                AND a.status IN ('present', 'late')
            LEFT JOIN teacher_attendance ta
                ON ta.session_id = s.session_id
                AND ta.teacher_id = st.teacher_id
            WHERE st.teacher_id = ?
            AND s.session_date = CURDATE()
            AND s.status <> 'cancelled'
            GROUP BY s.session_id
            ORDER BY sh.start_time ASC, s.session_id ASC
        ");
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSessionRoster($sessionId, $currentUserId)
    {
        if (!$this->canUserAccessSession($currentUserId, $sessionId)) {
            throw new Exception('Bạn không có quyền truy cập buổi học này');
        }

        $teacherStmt = $this->db->prepare("
            SELECT
                u.user_id,
                u.name,
                'teacher' AS role,
                ta.status,
                ta.check_in_time AS checked_at
            FROM session_teachers st
            JOIN teachers t ON t.teacher_id = st.teacher_id
            JOIN users u ON u.user_id = t.user_id
            LEFT JOIN teacher_attendance ta
                ON ta.session_id = st.session_id
                AND ta.teacher_id = st.teacher_id
            WHERE st.session_id = ?
            ORDER BY st.role ASC, u.name ASC
        ");
        $teacherStmt->execute([$sessionId]);

        $studentStmt = $this->db->prepare("
            SELECT
                u.user_id,
                u.name,
                'student' AS role,
                a.status,
                a.created_at AS checked_at
            FROM sessions s
            JOIN enrollments e
                ON e.class_id = s.class_id
                AND e.status <> 'dropped'
            JOIN students st ON st.student_id = e.student_id
            JOIN users u ON u.user_id = st.user_id
            LEFT JOIN attendances a
                ON a.session_id = s.session_id
                AND a.student_id = st.student_id
            WHERE s.session_id = ?
            ORDER BY u.name ASC
        ");
        $studentStmt->execute([$sessionId]);

        return [
            'teachers' => $teacherStmt->fetchAll(PDO::FETCH_ASSOC),
            'students' => $studentStmt->fetchAll(PDO::FETCH_ASSOC)
        ];
    }

    public function checkIn($userId, $sessionId, $imageName = null, $currentUserId = null)
    {
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $this->processOverdueAttendancePolicy();

        if (!$this->canUserAccessSession($currentUserId ?: ($_SESSION['user']['id'] ?? 0), $sessionId)) {
            return [
                'success' => false,
                'message' => 'Bạn không có quyền điểm danh buổi học này'
            ];
        }

        $session = $this->getTodaySession($sessionId);

        if (!$session) {
            return [
                'success' => false,
                'message' => 'Buổi học không tồn tại hoặc không phải lịch hôm nay'
            ];
        }

        $user = $this->getUserById($userId);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Không tìm thấy người dùng'
            ];
        }

        if ($user['role'] === 'teacher') {
            return $this->checkInTeacher($user, $session, $imageName);
        }

        if ($user['role'] === 'student') {
            return $this->checkInStudent($user, $session);
        }

        return [
            'success' => false,
            'message' => 'Tài khoản nhận diện không phải giảng viên hoặc học sinh'
        ];
    }

    public function checkOut($teacher_id)
    {
        $stmt = $this->db->prepare("
            SELECT attendance_id
            FROM teacher_attendance
            WHERE teacher_id = ?
            AND DATE(session_date) = CURDATE()
            LIMIT 1
        ");
        $stmt->execute([$teacher_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return [
                'success' => false,
                'message' => 'Chưa check-in'
            ];
        }

        $stmt = $this->db->prepare("
            UPDATE teacher_attendance
            SET check_out_time = NOW()
            WHERE attendance_id = ?
        ");
        $stmt->execute([$row['attendance_id']]);

        return [
            'success' => true,
            'message' => 'Check-out success'
        ];
    }

    public function getAttendanceReport($filter = 'all', $date = null)
    {
        $date = $date ?: date('Y-m-d');

        $sql = "
            SELECT
                ta.attendance_id,
                ta.teacher_id,
                u.name AS teacher_name,
                c.class_code,
                co.name AS course_name,
                ta.session_date,
                sh.start_time,
                sh.end_time,
                ta.check_in_time,
                ta.check_out_time,
                ta.face_image,
                ta.created_at,
                TIMESTAMP(ta.session_date, sh.start_time) AS shift_start_time,
                TIMESTAMPDIFF(
                    MINUTE,
                    TIMESTAMP(ta.session_date, sh.start_time),
                    ta.check_in_time
                ) AS late_minutes
            FROM teacher_attendance ta
            JOIN teachers t ON t.teacher_id = ta.teacher_id
            JOIN users u ON u.user_id = t.user_id
            LEFT JOIN classes c ON c.class_id = ta.class_id
            LEFT JOIN courses co ON co.course_id = c.course_id
            LEFT JOIN sessions s ON s.session_id = ta.session_id
            LEFT JOIN shifts sh ON sh.shift_id = s.shift_id
            WHERE ta.session_date = ?
        ";

        if ($filter == 'present') {
            $sql .= "
                AND ta.status = 'present'
            ";
        }

        if ($filter == 'late') {
            $sql .= "
                AND ta.status = 'late'
            ";
        }

        if ($filter == 'absent') {
            $sql .= "
                AND ta.status IN ('absent', 'late_absent')
            ";
        }

        $sql .= " ORDER BY ta.check_in_time DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function checkInTeacher($user, $session, $imageName)
    {
        $teacherId = $this->getTeacherIdByUserId($user['user_id']);

        if (!$teacherId || !$this->isTeacherAssignedToSession($teacherId, $session['session_id'])) {
            return [
                'success' => false,
                'message' => $user['name'] . ' không phải giảng viên của lớp này'
            ];
        }

        $timing = $this->teacherCheckInTiming($session);

        if ($timing['window'] === 'too_early') {
            return [
                'success' => false,
                'message' => 'Chỉ được điểm danh trước giờ học tối đa 10 phút'
            ];
        }

        if ($timing['window'] === 'late_absent') {
            // A check-in after the 30-minute cutoff is kept as attendance evidence,
            // but the session is canceled and never becomes a payable teaching session.
            $this->saveTeacherAttendance($teacherId, $session, $imageName, 'late_absent');
            $this->cancelSessionForTeacherAbsence($session, $teacherId, 'late_absent');

            return [
                'success' => false,
                'type' => 'teacher',
                'user_id' => (int) $user['user_id'],
                'name' => $user['name'],
                'message' => 'Giảng viên điểm danh sau 30 phút. Buổi học không được tính công và đã bị hủy.',
                'status' => 'late_absent'
            ];
        }

        $status = $timing['status'];

        $this->saveTeacherAttendance($teacherId, $session, $imageName, $status);

        if ($status === 'late') {
            $this->createAutomaticPenalty(
                $teacherId,
                $session['session_id'],
                50000,
                'Giảng viên điểm danh trễ từ 15 đến 30 phút'
            );
        }

        $this->syncSessionAttendanceStatus($session['session_id']);

        return [
            'success' => true,
            'type' => 'teacher',
            'user_id' => (int) $user['user_id'],
            'name' => $user['name'],
            'message' => 'Đã điểm danh giảng viên ' . $user['name'],
            'status' => $status
        ];
    }

    private function checkInStudent($user, $session)
    {
        $studentId = $this->getStudentIdByUserId($user['user_id']);

        if (!$studentId || !$this->isStudentInClass($studentId, $session['class_id'])) {
            return [
                'success' => false,
                'message' => $user['name'] . ' không thuộc lớp này'
            ];
        }

        $status = $this->calculateStatus($session);

        $stmt = $this->db->prepare("
            SELECT attendance_id, status
            FROM attendances
            WHERE session_id = ?
            AND student_id = ?
            LIMIT 1
        ");
        $stmt->execute([$session['session_id'], $studentId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $stmt = $this->db->prepare("
                UPDATE attendances
                SET status = ?
                WHERE attendance_id = ?
            ");
            $stmt->execute([$status, $existing['attendance_id']]);
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO attendances (session_id, student_id, status)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$session['session_id'], $studentId, $status]);
        }

        $oldCounted = $existing && in_array($existing['status'], ['present', 'late'], true);
        $newCounted = in_array($status, ['present', 'late'], true);

        if (!$oldCounted && $newCounted) {
            $stmt = $this->db->prepare("
                UPDATE enrollments
                SET attended_sessions = COALESCE(attended_sessions, 0) + 1,
                    remaining_sessions = GREATEST(COALESCE(remaining_sessions, 0) - 1, 0)
                WHERE student_id = ?
                AND class_id = ?
                AND status <> 'dropped'
            ");
            $stmt->execute([$studentId, $session['class_id']]);
        }

        $this->syncSessionAttendanceStatus($session['session_id']);

        return [
            'success' => true,
            'type' => 'student',
            'user_id' => (int) $user['user_id'],
            'name' => $user['name'],
            'message' => 'Đã điểm danh học sinh ' . $user['name'],
            'status' => $status
        ];
    }

    private function calculateStatus($session)
    {
        if (empty($session['start_time'])) {
            return 'present';
        }

        $startTime = strtotime($session['session_date'] . ' ' . $session['start_time']);
        return time() > $startTime ? 'late' : 'present';
    }

    public function processOverdueAttendancePolicy()
    {
        date_default_timezone_set('Asia/Ho_Chi_Minh');

        $stmt = $this->db->query("
            SELECT DISTINCT
                s.session_id,
                s.class_id,
                s.session_date,
                sh.start_time,
                c.class_code,
                co.name AS course_name
            FROM sessions s
            JOIN shifts sh ON sh.shift_id = s.shift_id
            JOIN session_teachers st ON st.session_id = s.session_id
            LEFT JOIN classes c ON c.class_id = s.class_id
            LEFT JOIN courses co ON co.course_id = c.course_id
            WHERE s.status NOT IN ('cancelled', 'conflict')
            AND TIMESTAMP(s.session_date, sh.start_time) < DATE_SUB(NOW(), INTERVAL 30 MINUTE)
            AND NOT EXISTS (
                SELECT 1
                FROM teacher_attendance ta
                WHERE ta.session_id = s.session_id
                AND ta.status IN ('present', 'late')
            )
        ");

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $session) {
            // No teacher checked in within the paid attendance window.
            // Re-running this from a cron or page reload must keep one absence and one penalty per teacher/session/reason.
            $teacherStmt = $this->db->prepare("
                SELECT teacher_id
                FROM session_teachers
                WHERE session_id = ?
            ");
            $teacherStmt->execute([(int) $session['session_id']]);

            foreach ($teacherStmt->fetchAll(PDO::FETCH_COLUMN) as $teacherId) {
                $this->saveTeacherAttendance((int) $teacherId, $session, null, 'absent', false);
                $this->cancelSessionForTeacherAbsence($session, (int) $teacherId, 'absent');
            }
        }

        $this->notifyOverdueStudentAbsences();
    }

    private function teacherCheckInTiming($session)
    {
        if (empty($session['start_time'])) {
            return ['window' => 'on_time', 'status' => 'present'];
        }

        $start = new DateTimeImmutable($session['session_date'] . ' ' . $session['start_time'], new DateTimeZone('Asia/Ho_Chi_Minh'));
        $now = new DateTimeImmutable('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
        $seconds = $now->getTimestamp() - $start->getTimestamp();

        if ($seconds < -600) {
            return ['window' => 'too_early', 'status' => null];
        }

        if ($seconds <= 900) {
            return ['window' => 'on_time', 'status' => 'present'];
        }

        if ($seconds <= 1800) {
            return ['window' => 'late', 'status' => 'late'];
        }

        return ['window' => 'late_absent', 'status' => 'late_absent'];
    }

    private function saveTeacherAttendance($teacherId, $session, $imageName, $status, $withCheckInTime = true)
    {
        $stmt = $this->db->prepare("
            SELECT attendance_id, status
            FROM teacher_attendance
            WHERE teacher_id = ?
            AND session_id = ?
            LIMIT 1
        ");
        $stmt->execute([(int) $teacherId, (int) $session['session_id']]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            if (in_array($existing['status'], ['present', 'late'], true) && in_array($status, ['absent', 'late_absent'], true)) {
                return (int) $existing['attendance_id'];
            }

            $sql = "
                UPDATE teacher_attendance
                SET check_in_time = " . ($withCheckInTime ? "COALESCE(check_in_time, NOW())" : "check_in_time") . ",
                    face_image = COALESCE(?, face_image),
                    status = ?
                WHERE attendance_id = ?
            ";
            $this->db->prepare($sql)->execute([$imageName, $status, (int) $existing['attendance_id']]);
            return (int) $existing['attendance_id'];
        }

        $sql = "
            INSERT INTO teacher_attendance
                (teacher_id, session_id, class_id, session_date, check_in_time, face_image, status, method)
            VALUES
                (?, ?, ?, ?, " . ($withCheckInTime ? "NOW()" : "NULL") . ", ?, ?, 'FACE')
        ";
        $this->db->prepare($sql)->execute([
            (int) $teacherId,
            (int) $session['session_id'],
            (int) $session['class_id'],
            $session['session_date'],
            $imageName,
            $status
        ]);

        return (int) $this->db->lastInsertId();
    }

    private function cancelSessionForTeacherAbsence($session, $teacherId, $attendanceStatus)
    {
        $this->db->prepare("
            UPDATE sessions
            SET status = 'cancelled',
                note = 'Giảng viên vắng mặt'
            WHERE session_id = ?
            AND status <> 'cancelled'
        ")->execute([(int) $session['session_id']]);

        $this->createAutomaticPenalty(
            (int) $teacherId,
            (int) $session['session_id'],
            100000,
            'Giảng viên vắng mặt hoặc điểm danh sau 30 phút'
        );

        $sessionName = trim(($session['course_name'] ?? '') . ' ' . ($session['class_code'] ?? ''));
        $sessionName = $sessionName !== '' ? $sessionName : ('#' . (int) $session['class_id']);
        $message = 'Buổi học của lớp ' . $sessionName . ' ngày '
            . date('d/m/Y', strtotime($session['session_date']))
            . ' đã bị hủy do giảng viên vắng mặt. Trung tâm sẽ sắp xếp buổi học bù sau.';

        $recipients = $this->getClassStudentAndParentUserIds((int) $session['class_id']);
        foreach ($recipients as $userId) {
            $this->insertNotificationOnce(
                (int) $userId,
                'session_teacher_absent_cancelled',
                'Buổi học đã hủy',
                $message,
                (int) $session['session_id']
            );
        }
    }

    private function createAutomaticPenalty($teacherId, $sessionId, $amount, $reason)
    {
        $stmt = $this->db->prepare("
            SELECT id
            FROM allowances_penalties
            WHERE teacher_id = ?
            AND session_id = ?
            AND type = 'penalty'
            AND reason = ?
            LIMIT 1
        ");
        $stmt->execute([(int) $teacherId, (int) $sessionId, $reason]);

        if ($stmt->fetchColumn()) {
            return false;
        }

        $dateStmt = $this->db->prepare("SELECT MONTH(session_date), YEAR(session_date) FROM sessions WHERE session_id = ?");
        $dateStmt->execute([(int) $sessionId]);
        $period = $dateStmt->fetch(PDO::FETCH_NUM) ?: [date('m'), date('Y')];

        $stmt = $this->db->prepare("
            INSERT IGNORE INTO allowances_penalties
                (teacher_id, session_id, type, amount, reason, month, year, status, created_by)
            VALUES
                (?, ?, 'penalty', ?, ?, ?, ?, 'active', NULL)
        ");

        return $stmt->execute([
            (int) $teacherId,
            (int) $sessionId,
            (float) $amount,
            $reason,
            (int) $period[0],
            (int) $period[1]
        ]);
    }

    private function notifyOverdueStudentAbsences()
    {
        $stmt = $this->db->query("
            SELECT
                s.session_id,
                s.class_id,
                s.session_date,
                c.class_code,
                co.name AS course_name,
                e.student_id,
                su.name AS student_name
            FROM sessions s
            JOIN shifts sh ON sh.shift_id = s.shift_id
            JOIN classes c ON c.class_id = s.class_id
            LEFT JOIN courses co ON co.course_id = c.course_id
            JOIN enrollments e ON e.class_id = s.class_id AND e.status <> 'dropped'
            JOIN students st ON st.student_id = e.student_id
            JOIN users su ON su.user_id = st.user_id
            LEFT JOIN attendances a ON a.session_id = s.session_id AND a.student_id = e.student_id
            WHERE s.status <> 'cancelled'
            AND TIMESTAMP(s.session_date, sh.start_time) < DATE_SUB(NOW(), INTERVAL 30 MINUTE)
            AND a.attendance_id IS NULL
        ");

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $attendanceId = $this->insertStudentAbsenceIfMissing((int) $row['session_id'], (int) $row['student_id']);
            $className = trim(($row['course_name'] ?? '') . ' ' . ($row['class_code'] ?? ''));
            $message = 'Học viên ' . $row['student_name'] . ' đã vắng mặt trong buổi học lớp '
                . ($className !== '' ? $className : ('#' . (int) $row['class_id']))
                . ' ngày ' . date('d/m/Y', strtotime($row['session_date'])) . '.';

            foreach ($this->getParentUserIdsForStudent((int) $row['student_id']) as $parentUserId) {
                $this->insertNotificationOnce(
                    (int) $parentUserId,
                    'student_session_absent',
                    'Học viên vắng học',
                    $message,
                    $attendanceId
                );
            }
        }
    }

    private function getTodaySession($sessionId)
    {
        $stmt = $this->db->prepare("
            SELECT
                s.session_id,
                s.class_id,
                s.session_date,
                s.shift_id,
                sh.start_time,
                sh.end_time
            FROM sessions s
            LEFT JOIN shifts sh ON sh.shift_id = s.shift_id
            WHERE s.session_id = ?
            AND s.session_date = CURDATE()
            AND s.status <> 'cancelled'
            LIMIT 1
        ");
        $stmt->execute([$sessionId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function syncSessionAttendanceStatus($sessionId)
    {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(DISTINCT e.student_id) AS student_count,
                COUNT(DISTINCT CASE
                    WHEN a.status IN ('present', 'late', 'absent')
                    THEN a.student_id
                END) AS attendance_count
            FROM sessions s
            LEFT JOIN enrollments e
                ON e.class_id = s.class_id
                AND e.status <> 'dropped'
            LEFT JOIN attendances a
                ON a.session_id = s.session_id
                AND a.student_id = e.student_id
            WHERE s.session_id = ?
            GROUP BY s.session_id
        ");
        $stmt->execute([$sessionId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return;
        }

        $studentCount = (int) ($row['student_count'] ?? 0);
        $attendanceCount = (int) ($row['attendance_count'] ?? 0);

        if ($studentCount > 0 && $attendanceCount >= $studentCount) {
            $stmt = $this->db->prepare("
                UPDATE sessions
                SET status = 'done'
                WHERE session_id = ?
                AND status <> 'cancelled'
            ");
            $stmt->execute([$sessionId]);
            return;
        }

        if ($attendanceCount > 0) {
            $stmt = $this->db->prepare("
                UPDATE sessions
                SET status = 'scheduled'
                WHERE session_id = ?
                AND status NOT IN ('cancelled', 'conflict')
            ");
            $stmt->execute([$sessionId]);
        }
    }

    private function getTodaySessionsForAdmin()
    {
        $stmt = $this->db->prepare("
            SELECT
                s.session_id,
                s.class_id,
                s.session_date,
                s.status AS session_status,
                c.class_code,
                co.name AS course_name,
                p.name AS package_name,
                sh.name AS shift_name,
                sh.start_time,
                sh.end_time,
                r.name AS room_name,
                COUNT(DISTINCT e.student_id) AS student_count,
                COUNT(DISTINCT a.student_id) AS attended_count,
                COUNT(DISTINCT CASE WHEN ta.attendance_id IS NOT NULL THEN ta.teacher_id END) AS teacher_checked
            FROM sessions s
            JOIN classes c ON c.class_id = s.class_id
            JOIN courses co ON co.course_id = c.course_id
            JOIN packages p ON p.package_id = c.package_id
            LEFT JOIN shifts sh ON sh.shift_id = s.shift_id
            LEFT JOIN rooms r ON r.room_id = s.room_id
            LEFT JOIN session_teachers st ON st.session_id = s.session_id
            LEFT JOIN enrollments e
                ON e.class_id = s.class_id
                AND e.status <> 'dropped'
            LEFT JOIN attendances a
                ON a.session_id = s.session_id
                AND a.student_id = e.student_id
                AND a.status IN ('present', 'late')
            LEFT JOIN teacher_attendance ta
                ON ta.session_id = s.session_id
                AND ta.teacher_id = st.teacher_id
            WHERE s.session_date = CURDATE()
            AND s.status <> 'cancelled'
            GROUP BY s.session_id
            ORDER BY sh.start_time ASC, s.session_id ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getUserById($userId)
    {
        $stmt = $this->db->prepare("
            SELECT user_id, name, role
            FROM users
            WHERE user_id = ?
            AND status = 1
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getTeacherIdByUserId($userId)
    {
        $stmt = $this->db->prepare("SELECT teacher_id FROM teachers WHERE user_id = ? AND status = 1 LIMIT 1");
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    private function getStudentIdByUserId($userId)
    {
        $stmt = $this->db->prepare("SELECT student_id FROM students WHERE user_id = ? AND status = 1 LIMIT 1");
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    private function canUserAccessSession($userId, $sessionId)
    {
        $user = $this->getUserById($userId);

        if (($user['role'] ?? '') === 'admin') {
            return true;
        }

        $teacherId = $this->getTeacherIdByUserId($userId);

        if (!$teacherId) {
            return false;
        }

        return $this->isTeacherAssignedToSession($teacherId, $sessionId);
    }

    private function isTeacherAssignedToSession($teacherId, $sessionId)
    {
        $stmt = $this->db->prepare("
            SELECT 1
            FROM session_teachers
            WHERE teacher_id = ?
            AND session_id = ?
            LIMIT 1
        ");
        $stmt->execute([$teacherId, $sessionId]);
        return (bool) $stmt->fetchColumn();
    }

    private function isStudentInClass($studentId, $classId)
    {
        $stmt = $this->db->prepare("
            SELECT 1
            FROM enrollments
            WHERE student_id = ?
            AND class_id = ?
            AND status <> 'dropped'
            LIMIT 1
        ");
        $stmt->execute([$studentId, $classId]);
        return (bool) $stmt->fetchColumn();
    }

    private function insertStudentAbsenceIfMissing($sessionId, $studentId)
    {
        $stmt = $this->db->prepare("
            SELECT attendance_id
            FROM attendances
            WHERE session_id = ?
            AND student_id = ?
            LIMIT 1
        ");
        $stmt->execute([(int) $sessionId, (int) $studentId]);
        $attendanceId = $stmt->fetchColumn();

        if ($attendanceId) {
            return (int) $attendanceId;
        }

        $this->db->prepare("
            INSERT INTO attendances (session_id, student_id, status)
            VALUES (?, ?, 'absent')
        ")->execute([(int) $sessionId, (int) $studentId]);

        return (int) $this->db->lastInsertId();
    }

    private function getClassStudentAndParentUserIds($classId)
    {
        $stmt = $this->db->prepare("
            SELECT DISTINCT st.user_id
            FROM enrollments e
            JOIN students st ON st.student_id = e.student_id
            WHERE e.class_id = ?
            AND e.status <> 'dropped'
            AND st.user_id IS NOT NULL
            UNION
            SELECT DISTINCT p.user_id
            FROM enrollments e
            JOIN parent_student ps ON ps.student_id = e.student_id
            JOIN parents p ON p.parent_id = ps.parent_id
            WHERE e.class_id = ?
            AND e.status <> 'dropped'
            AND p.user_id IS NOT NULL
        ");
        $stmt->execute([(int) $classId, (int) $classId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function getParentUserIdsForStudent($studentId)
    {
        $stmt = $this->db->prepare("
            SELECT DISTINCT p.user_id
            FROM parent_student ps
            JOIN parents p ON p.parent_id = ps.parent_id
            WHERE ps.student_id = ?
            AND p.user_id IS NOT NULL
        ");
        $stmt->execute([(int) $studentId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function insertNotificationOnce($userId, $type, $title, $message, $referenceId)
    {
        $stmt = $this->db->prepare("
            SELECT notification_id
            FROM notifications
            WHERE user_id = ?
            AND type = ?
            AND reference_id = ?
            AND message = ?
            LIMIT 1
        ");
        $stmt->execute([(int) $userId, $type, (int) $referenceId, $message]);

        if ($stmt->fetchColumn()) {
            return false;
        }

        return $this->db->prepare("
            INSERT INTO notifications (user_id, type, title, message, reference_id)
            VALUES (?, ?, ?, ?, ?)
        ")->execute([(int) $userId, $type, $title, $message, (int) $referenceId]);
    }

    private function ensureAttendancePolicySchema()
    {
        $allowanceColumns = $this->db->query("SHOW COLUMNS FROM allowances_penalties")->fetchAll(PDO::FETCH_COLUMN);

        if (!in_array('session_id', $allowanceColumns, true)) {
            $this->db->exec("ALTER TABLE allowances_penalties ADD session_id INT NULL AFTER teacher_id");
        }

        if (!in_array('status', $allowanceColumns, true)) {
            $this->db->exec("ALTER TABLE allowances_penalties ADD status ENUM('active', 'canceled') NOT NULL DEFAULT 'active' AFTER reason");
        }

        if (!in_array('canceled_reason', $allowanceColumns, true)) {
            $this->db->exec("ALTER TABLE allowances_penalties ADD canceled_reason TEXT NULL AFTER status");
        }

        if (!in_array('canceled_by', $allowanceColumns, true)) {
            $this->db->exec("ALTER TABLE allowances_penalties ADD canceled_by INT NULL AFTER created_by");
        }

        if (!in_array('updated_at', $allowanceColumns, true)) {
            $this->db->exec("ALTER TABLE allowances_penalties ADD updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
        }

        if (!in_array('canceled_at', $allowanceColumns, true)) {
            $this->db->exec("ALTER TABLE allowances_penalties ADD canceled_at DATETIME NULL AFTER updated_at");
        }

        if (!in_array('attendance_id', $allowanceColumns, true)) {
            $this->db->exec("ALTER TABLE allowances_penalties ADD attendance_id INT NULL AFTER session_id");
        }

        $teacherColumns = $this->db->query("SHOW COLUMNS FROM teacher_attendance")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('confidence_score', $teacherColumns, true)) {
            $this->db->exec("ALTER TABLE teacher_attendance ADD confidence_score DECIMAL(6,4) NULL AFTER method");
        }

        if (!in_array('note', $teacherColumns, true)) {
            $this->db->exec("ALTER TABLE teacher_attendance ADD note TEXT NULL AFTER face_image");
        }

        $teacherStatus = $this->db->query("SHOW COLUMNS FROM teacher_attendance LIKE 'status'")->fetch(PDO::FETCH_ASSOC);
        if (strpos((string) ($teacherStatus['Type'] ?? ''), 'late_absent') === false) {
            $this->db->exec("ALTER TABLE teacher_attendance MODIFY status ENUM('present', 'absent', 'late', 'late_absent') DEFAULT 'present'");
        }
    }
}
