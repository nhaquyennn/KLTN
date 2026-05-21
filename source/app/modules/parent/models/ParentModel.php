<?php

class ParentModel
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
        $this->ensureReviewTable();
    }

    public function getParentAndStudents()
    {
        $user = $_SESSION['user'] ?? [];
        $userId = $user['id'] ?? 0;
        $role = $user['role'] ?? '';

        if ($role === 'student') {
            return $this->getStudentAccountData($userId);
        }

        $sql = "
            SELECT
                u_p.name AS parent_name,
                p.parent_id,
                u_s.name AS student_name,
                s.student_id
            FROM users u_p
            JOIN parents p ON u_p.user_id = p.user_id
            JOIN parent_student ps ON p.parent_id = ps.parent_id
            JOIN students s ON ps.student_id = s.student_id
            JOIN users u_s ON s.user_id = u_s.user_id
            WHERE u_p.user_id = ?
            AND p.status = 1
            AND s.status = 1
            ORDER BY s.student_id ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'parent_name' => $rows[0]['parent_name'] ?? ($user['name'] ?? 'Phụ huynh'),
            'students' => $rows
        ];
    }

    public function getSelectedStudentId($students)
    {
        $studentId = (int) ($_GET['student_id'] ?? 0);
        $allowedIds = array_map('intval', array_column($students, 'student_id'));

        if ($studentId && in_array($studentId, $allowedIds, true)) {
            return $studentId;
        }

        return $allowedIds[0] ?? 0;
    }

    public function getStudentSummary($studentId)
    {
        $stmt = $this->db->prepare("
            SELECT
                st.student_id,
                u.name AS student_name,
                COALESCE(SUM(GREATEST(e.remaining_sessions, 0)), 0) AS remaining_sessions,
                COALESCE(SUM(e.attended_sessions), 0) AS attended_sessions,
                COUNT(DISTINCT CASE WHEN a.status IN ('present', 'late') THEN a.attendance_id END) AS present_count,
                COUNT(DISTINCT a.attendance_id) AS attendance_count,
                COALESCE(SUM(e.final_fee), 0) AS total_fee,
                COALESCE(SUM(e.paid_amount), 0) AS paid_amount
            FROM students st
            JOIN users u ON u.user_id = st.user_id
            LEFT JOIN enrollments e ON e.student_id = st.student_id AND e.status <> 'dropped'
            LEFT JOIN sessions se ON se.class_id = e.class_id
            LEFT JOIN attendances a ON a.session_id = se.session_id AND a.student_id = st.student_id
            WHERE st.student_id = ?
            GROUP BY st.student_id
        ");
        $stmt->execute([$studentId]);
        $summary = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $nextSession = $this->getUpcomingSessions($studentId, 1)[0] ?? null;
        $latestReview = $this->getLatestReviews($studentId, 1)[0] ?? null;
        $activeEnrollments = $this->getActiveEnrollments($studentId);

        return [
            'student' => $summary,
            'next_session' => $nextSession,
            'latest_review' => $latestReview,
            'active_enrollments' => $activeEnrollments
        ];
    }

    public function getUpcomingSessions($studentId, $limit = 5)
    {
        $stmt = $this->db->prepare("
            SELECT
                s.*,
                c.class_code,
                co.name AS course_name,
                p.name AS package_name,
                sh.name AS shift_name,
                sh.start_time,
                sh.end_time,
                r.name AS room_name,
                GROUP_CONCAT(DISTINCT u.name ORDER BY st.role SEPARATOR ', ') AS teachers
            FROM enrollments e
            JOIN sessions s ON s.class_id = e.class_id
            JOIN classes c ON c.class_id = e.class_id
            JOIN courses co ON co.course_id = c.course_id
            JOIN packages p ON p.package_id = c.package_id
            LEFT JOIN shifts sh ON sh.shift_id = s.shift_id
            LEFT JOIN rooms r ON r.room_id = s.room_id
            LEFT JOIN session_teachers st ON st.session_id = s.session_id
            LEFT JOIN teachers t ON t.teacher_id = st.teacher_id
            LEFT JOIN users u ON u.user_id = t.user_id
            WHERE e.student_id = ?
            AND e.status IN ('studying', 'paused')
            AND s.status <> 'cancelled'
            AND (
                s.session_date > CURDATE()
                OR (s.session_date = CURDATE() AND (sh.start_time IS NULL OR sh.start_time >= CURTIME()))
            )
            GROUP BY s.session_id
            ORDER BY s.session_date ASC, sh.start_time ASC
            LIMIT " . (int) $limit . "
        ");
        $stmt->execute([$studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCalendarSessions($studentId)
    {
        $stmt = $this->db->prepare("
            SELECT
                s.*,
                co.name AS course_name,
                p.name AS package_name,
                sh.start_time,
                sh.end_time,
                r.name AS room_name,
                a.status AS attendance_status,
                GROUP_CONCAT(DISTINCT u.name ORDER BY st.role SEPARATOR ', ') AS teachers
            FROM enrollments e
            JOIN sessions s ON s.class_id = e.class_id
            JOIN classes c ON c.class_id = e.class_id
            JOIN courses co ON co.course_id = c.course_id
            JOIN packages p ON p.package_id = c.package_id
            LEFT JOIN shifts sh ON sh.shift_id = s.shift_id
            LEFT JOIN rooms r ON r.room_id = s.room_id
            LEFT JOIN attendances a ON a.session_id = s.session_id AND a.student_id = e.student_id
            LEFT JOIN session_teachers st ON st.session_id = s.session_id
            LEFT JOIN teachers t ON t.teacher_id = st.teacher_id
            LEFT JOIN users u ON u.user_id = t.user_id
            WHERE e.student_id = ?
            AND e.status <> 'dropped'
            GROUP BY s.session_id
            ORDER BY s.session_date ASC, sh.start_time ASC
        ");
        $stmt->execute([$studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLatestReviews($studentId, $limit = 10)
    {
        $stmt = $this->db->prepare("
            SELECT
                sr.*,
                s.session_date,
                co.name AS course_name,
                c.class_code,
                GROUP_CONCAT(DISTINCT u.name ORDER BY st.role SEPARATOR ', ') AS teachers
            FROM session_reviews sr
            JOIN sessions s ON s.session_id = sr.session_id
            JOIN enrollments e ON e.class_id = s.class_id AND e.student_id = sr.student_id
            JOIN classes c ON c.class_id = s.class_id
            JOIN courses co ON co.course_id = c.course_id
            LEFT JOIN session_teachers st ON st.session_id = s.session_id
            LEFT JOIN teachers t ON t.teacher_id = st.teacher_id
            LEFT JOIN users u ON u.user_id = t.user_id
            WHERE e.student_id = ?
            AND sr.student_id = ?
            GROUP BY sr.review_id
            ORDER BY sr.created_at DESC
            LIMIT " . (int) $limit . "
        ");
        $stmt->execute([$studentId, $studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActiveEnrollments($studentId)
    {
        $stmt = $this->db->prepare("
            SELECT
                e.*,
                c.class_code,
                co.name AS course_name,
                p.name AS package_name,
                p.total_sessions
            FROM enrollments e
            JOIN classes c ON c.class_id = e.class_id
            JOIN courses co ON co.course_id = c.course_id
            JOIN packages p ON p.package_id = c.package_id
            WHERE e.student_id = ?
            AND e.status <> 'dropped'
            ORDER BY e.enrollment_id DESC
        ");
        $stmt->execute([$studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAvailableClassesForStudent($studentId)
    {
        $stmt = $this->db->prepare("
            SELECT
                p.package_id,
                c.class_id,
                c.class_code,
                c.start_date,
                COALESCE(c.max_students, 10) AS max_students,
                co.name AS course_name,
                p.name AS package_name,
                p.price,
                p.total_sessions,
                COUNT(DISTINCT e.enrollment_id) AS student_count,
                COUNT(DISTINCT CASE
                    WHEN s.session_date <= CURDATE()
                    AND s.session_date >= c.start_date
                    AND s.status <> 'cancelled'
                    THEN s.session_id
                END) AS learned_sessions,
                CASE WHEN EXISTS (
                    SELECT 1
                    FROM enrollments current_e
                    JOIN classes current_c ON current_c.class_id = current_e.class_id
                    WHERE current_e.student_id = ?
                    AND current_e.status <> 'dropped'
                    AND current_c.package_id = p.package_id
                ) THEN 1 ELSE 0 END AS already_registered
            FROM packages p
            JOIN courses co ON co.course_id = p.course_id
            JOIN classes c
                ON c.package_id = p.package_id
                AND c.course_id = p.course_id
                AND c.is_active = 1
            LEFT JOIN enrollments e
                ON e.class_id = c.class_id
                AND e.status <> 'dropped'
            LEFT JOIN sessions s
                ON s.class_id = c.class_id
            WHERE p.status = 'active'
            AND co.status = 'active'
            GROUP BY p.package_id, c.class_id
            HAVING student_count < max_students
            AND learned_sessions <= 5
            AND already_registered = 0
            ORDER BY co.name ASC, p.price ASC, c.class_code ASC
        ");
        $stmt->execute([$studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createEnrollment($studentId, $classId)
    {
        if (!$this->isOwnedStudent($studentId)) {
            throw new Exception('Không có quyền đăng ký học viên này');
        }

        $this->assertClassAvailableForRegistration($studentId, $classId);

        $enrollmentModel = new EnrollmentModel();
        return $enrollmentModel->create([
            'student_id' => $studentId,
            'class_id' => $classId,
            'discount_percent' => 0
        ]);
    }

    public function findEnrollmentForCurrentParent($enrollmentId)
    {
        $studentIds = array_map('intval', array_column($this->getParentAndStudents()['students'], 'student_id'));

        if (empty($studentIds)) {
            return null;
        }

        $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
        $stmt = $this->db->prepare("
            SELECT e.*
            FROM enrollments e
            WHERE e.enrollment_id = ?
            AND e.student_id IN ($placeholders)
        ");
        $stmt->execute(array_merge([$enrollmentId], $studentIds));
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function isOwnedStudent($studentId)
    {
        $studentIds = array_map('intval', array_column($this->getParentAndStudents()['students'], 'student_id'));
        return in_array((int) $studentId, $studentIds, true);
    }

    private function assertClassAvailableForRegistration($studentId, $classId)
    {
        $stmt = $this->db->prepare("
            SELECT
                c.class_id,
                COALESCE(c.max_students, 10) AS max_students,
                COUNT(DISTINCT e.enrollment_id) AS student_count,
                COUNT(DISTINCT CASE
                    WHEN s.session_date <= CURDATE()
                    AND s.session_date >= c.start_date
                    AND s.status <> 'cancelled'
                    THEN s.session_id
                END) AS learned_sessions,
                CASE WHEN EXISTS (
                    SELECT 1
                    FROM enrollments current_e
                    JOIN classes current_c ON current_c.class_id = current_e.class_id
                    WHERE current_e.student_id = ?
                    AND current_e.status <> 'dropped'
                    AND current_c.package_id = c.package_id
                ) THEN 1 ELSE 0 END AS already_registered
            FROM classes c
            JOIN courses co ON co.course_id = c.course_id
            JOIN packages p ON p.package_id = c.package_id
            LEFT JOIN enrollments e
                ON e.class_id = c.class_id
                AND e.status <> 'dropped'
            LEFT JOIN sessions s
                ON s.class_id = c.class_id
            WHERE c.class_id = ?
            AND c.is_active = 1
            AND co.status = 'active'
            AND p.status = 'active'
            GROUP BY c.class_id
        ");
        $stmt->execute([$studentId, $classId]);
        $class = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$class) {
            throw new Exception('Lớp học không tồn tại hoặc chưa được mở');
        }

        if ((int) $class['already_registered'] === 1) {
            throw new Exception('Học viên đã đăng ký gói học này');
        }

        if ((int) $class['student_count'] >= (int) $class['max_students']) {
            throw new Exception('Lớp đã đủ ' . (int) $class['max_students'] . ' học viên');
        }

        if ((int) $class['learned_sessions'] > 5) {
            throw new Exception('Lớp đã học quá 5 buổi kể từ ngày mở lớp');
        }
    }

    private function getStudentAccountData($userId)
    {
        $stmt = $this->db->prepare("
            SELECT
                u.name AS parent_name,
                u.name AS student_name,
                st.student_id
            FROM students st
            JOIN users u ON u.user_id = st.user_id
            WHERE u.user_id = ?
            AND st.status = 1
        ");
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'parent_name' => $row['parent_name'] ?? ($_SESSION['user']['name'] ?? 'Học viên'),
            'students' => $row ? [$row] : []
        ];
    }

    private function ensureReviewTable()
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS session_reviews (
                review_id INT AUTO_INCREMENT PRIMARY KEY,
                session_id INT NOT NULL,
                student_id INT NULL,
                review_text TEXT NOT NULL,
                created_at DATETIME NOT NULL,
                UNIQUE KEY uq_session_reviews_session_student (session_id, student_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");

        $columns = $this->db->query("SHOW COLUMNS FROM session_reviews")->fetchAll(PDO::FETCH_COLUMN);

        if (!in_array('student_id', $columns, true)) {
            $this->db->exec("ALTER TABLE session_reviews ADD student_id INT NULL AFTER session_id");
        }

        $indexes = $this->db->query("SHOW INDEX FROM session_reviews")->fetchAll(PDO::FETCH_ASSOC);
        $hasSessionOnlyUnique = false;
        $hasSessionStudentUnique = false;

        foreach ($indexes as $index) {
            if ($index['Key_name'] === 'uq_session_reviews_session') {
                $hasSessionOnlyUnique = true;
            }

            if ($index['Key_name'] === 'uq_session_reviews_session_student') {
                $hasSessionStudentUnique = true;
            }
        }

        if ($hasSessionOnlyUnique) {
            $this->db->exec("ALTER TABLE session_reviews DROP INDEX uq_session_reviews_session");
        }

        if (!$hasSessionStudentUnique) {
            $this->db->exec("ALTER TABLE session_reviews ADD UNIQUE KEY uq_session_reviews_session_student (session_id, student_id)");
        }
    }
}
