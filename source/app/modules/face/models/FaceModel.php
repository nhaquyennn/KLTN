<?php

class FaceModel
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
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
                AND ta.check_in_time IS NOT NULL
                AND TIMESTAMPDIFF(MINUTE, TIMESTAMP(ta.session_date, sh.start_time), ta.check_in_time) <= 0
            ";
        }

        if ($filter == 'late') {
            $sql .= "
                AND TIMESTAMPDIFF(MINUTE, TIMESTAMP(ta.session_date, sh.start_time), ta.check_in_time) > 0
                AND TIMESTAMPDIFF(MINUTE, TIMESTAMP(ta.session_date, sh.start_time), ta.check_in_time) < 30
            ";
        }

        if ($filter == 'absent') {
            $sql .= "
                AND (
                    ta.check_in_time IS NULL
                    OR TIMESTAMPDIFF(MINUTE, TIMESTAMP(ta.session_date, sh.start_time), ta.check_in_time) >= 30
                )
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

        $status = $this->calculateStatus($session);

        $stmt = $this->db->prepare("
            SELECT attendance_id
            FROM teacher_attendance
            WHERE teacher_id = ?
            AND session_id = ?
            LIMIT 1
        ");
        $stmt->execute([$teacherId, $session['session_id']]);
        $attendanceId = $stmt->fetchColumn();

        if ($attendanceId) {
            $stmt = $this->db->prepare("
                UPDATE teacher_attendance
                SET check_in_time = COALESCE(check_in_time, NOW()),
                    face_image = COALESCE(?, face_image),
                    status = ?
                WHERE attendance_id = ?
            ");
            $stmt->execute([$imageName, $status, $attendanceId]);
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO teacher_attendance
                    (teacher_id, session_id, class_id, session_date, check_in_time, face_image, status, method)
                VALUES
                    (?, ?, ?, ?, NOW(), ?, ?, 1)
            ");
            $stmt->execute([
                $teacherId,
                $session['session_id'],
                $session['class_id'],
                $session['session_date'],
                $imageName,
                $status
            ]);
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
}
