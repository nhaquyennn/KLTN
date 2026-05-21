<?php

class DashboardModel
{
    private $db;

    public function __construct($db = null)
    {
        $this->db = $db ?: (new Database())->connect();
        $this->ensureReviewTable();
    }

    public function getSummary()
    {
        return [
            'total_students' => (int) $this->fetchColumn("SELECT COUNT(*) FROM students WHERE status = 1"),
            'total_teachers' => (int) $this->fetchColumn("SELECT COUNT(*) FROM teachers WHERE status = 1"),
            'active_classes' => (int) $this->countRows("
                SELECT c.class_id
                FROM classes c
                LEFT JOIN packages p ON p.package_id = c.package_id
                LEFT JOIN sessions s ON s.class_id = c.class_id
                WHERE c.is_active = 1
                GROUP BY c.class_id
                HAVING COALESCE(SUM(CASE WHEN s.status = 'done' THEN 1 ELSE 0 END), 0) < COALESCE(MAX(p.total_sessions), 999999)
            "),
            'today_sessions' => (int) $this->fetchColumn("
                SELECT COUNT(*)
                FROM sessions
                WHERE session_date = CURDATE()
                AND status <> 'cancelled'
            "),
            'tuition_debt' => (float) $this->fetchColumn("
                SELECT COALESCE(SUM(final_fee - paid_amount), 0)
                FROM enrollments
                WHERE payment_status <> 'paid'
                AND status <> 'dropped'
            "),
            'room_conflicts' => (int) $this->fetchColumn("
                SELECT COUNT(*)
                FROM (
                    SELECT s1.session_id
                    FROM sessions s1
                    JOIN sessions s2
                        ON s1.session_id < s2.session_id
                        AND s1.room_id = s2.room_id
                        AND s1.session_date = s2.session_date
                    JOIN shifts sh1 ON sh1.shift_id = s1.shift_id
                    JOIN shifts sh2 ON sh2.shift_id = s2.shift_id
                    WHERE s1.session_date = CURDATE()
                    AND s1.room_id IS NOT NULL
                    AND s1.status <> 'cancelled'
                    AND s2.status <> 'cancelled'
                    AND sh1.start_time < sh2.end_time
                    AND sh1.end_time > sh2.start_time
                    GROUP BY s1.session_id, s2.session_id
                ) conflicts
            "),
            'unreviewed_sessions' => (int) $this->fetchColumn("
                SELECT COUNT(*)
                FROM (
                    SELECT s.session_id
                    FROM sessions s
                    LEFT JOIN enrollments e ON e.class_id = s.class_id AND e.status <> 'dropped'
                    LEFT JOIN session_reviews sr ON sr.session_id = s.session_id AND sr.student_id = e.student_id
                    WHERE s.status = 'done'
                    GROUP BY s.session_id
                    HAVING COUNT(DISTINCT e.student_id) > COUNT(DISTINCT sr.student_id)
                ) missing_reviews
            "),
        ];
    }

    public function getTodaySchedule()
    {
        $stmt = $this->db->query("
            SELECT
                s.session_id,
                s.session_date,
                s.status,
                c.class_id,
                c.class_code,
                co.name AS course_name,
                p.name AS package_name,
                sh.name AS shift_name,
                sh.start_time,
                sh.end_time,
                r.name AS room_name,
                GROUP_CONCAT(DISTINCT u.name ORDER BY st.role SEPARATOR ', ') AS teachers
            FROM sessions s
            JOIN classes c ON c.class_id = s.class_id
            LEFT JOIN courses co ON co.course_id = c.course_id
            LEFT JOIN packages p ON p.package_id = c.package_id
            LEFT JOIN shifts sh ON sh.shift_id = s.shift_id
            LEFT JOIN rooms r ON r.room_id = s.room_id
            LEFT JOIN session_teachers st ON st.session_id = s.session_id
            LEFT JOIN teachers t ON t.teacher_id = st.teacher_id
            LEFT JOIN users u ON u.user_id = t.user_id
            WHERE s.session_date = CURDATE()
            AND s.status <> 'cancelled'
            GROUP BY s.session_id
            ORDER BY sh.start_time ASC, s.session_id ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRoomConflicts()
    {
        $stmt = $this->db->query("
            SELECT
                r.name AS room_name,
                c1.class_id AS first_class_id,
                c2.class_id AS second_class_id,
                c1.class_code AS first_class_code,
                c2.class_code AS second_class_code,
                co1.name AS first_course_name,
                co2.name AS second_course_name,
                sh1.name AS first_shift_name,
                sh2.name AS second_shift_name,
                sh1.start_time AS first_start_time,
                sh1.end_time AS first_end_time,
                sh2.start_time AS second_start_time,
                sh2.end_time AS second_end_time
            FROM sessions s1
            JOIN sessions s2
                ON s1.session_id < s2.session_id
                AND s1.room_id = s2.room_id
                AND s1.session_date = s2.session_date
            JOIN rooms r ON r.room_id = s1.room_id
            JOIN classes c1 ON c1.class_id = s1.class_id
            JOIN classes c2 ON c2.class_id = s2.class_id
            LEFT JOIN courses co1 ON co1.course_id = c1.course_id
            LEFT JOIN courses co2 ON co2.course_id = c2.course_id
            JOIN shifts sh1 ON sh1.shift_id = s1.shift_id
            JOIN shifts sh2 ON sh2.shift_id = s2.shift_id
            WHERE s1.session_date = CURDATE()
            AND s1.status <> 'cancelled'
            AND s2.status <> 'cancelled'
            AND sh1.start_time < sh2.end_time
            AND sh1.end_time > sh2.start_time
            ORDER BY r.name ASC, sh1.start_time ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUnreviewedSessions()
    {
        $stmt = $this->db->query("
            SELECT
                s.session_id,
                s.session_date,
                c.class_id,
                c.class_code,
                co.name AS course_name,
                p.name AS package_name,
                sh.name AS shift_name,
                sh.start_time,
                sh.end_time
            FROM sessions s
            JOIN classes c ON c.class_id = s.class_id
            LEFT JOIN courses co ON co.course_id = c.course_id
            LEFT JOIN packages p ON p.package_id = c.package_id
            LEFT JOIN shifts sh ON sh.shift_id = s.shift_id
            LEFT JOIN enrollments e ON e.class_id = s.class_id AND e.status <> 'dropped'
            LEFT JOIN session_reviews sr ON sr.session_id = s.session_id AND sr.student_id = e.student_id
            WHERE s.status = 'done'
            GROUP BY s.session_id
            HAVING COUNT(DISTINCT e.student_id) > COUNT(DISTINCT sr.student_id)
            ORDER BY s.session_date DESC, sh.start_time DESC
            LIMIT 10
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecentEnrollments($limit = 8)
    {
        $stmt = $this->db->prepare("
            SELECT
                e.enrollment_id,
                e.enroll_date,
                e.created_at,
                e.payment_status,
                e.paid_amount,
                e.final_fee,
                e.status,
                u.name AS student_name,
                c.class_code,
                co.name AS course_name,
                p.name AS package_name
            FROM enrollments e
            JOIN students st ON st.student_id = e.student_id
            JOIN users u ON u.user_id = st.user_id
            JOIN classes c ON c.class_id = e.class_id
            JOIN courses co ON co.course_id = c.course_id
            JOIN packages p ON p.package_id = c.package_id
            ORDER BY e.created_at DESC, e.enrollment_id DESC
            LIMIT ?
        ");

        $stmt->bindValue(1, (int) $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function fetchColumn($sql)
    {
        return $this->db->query($sql)->fetchColumn();
    }

    private function countRows($sql)
    {
        return count($this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC));
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
