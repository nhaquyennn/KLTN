<?php

class DailyScheduleModel
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    public function getStudents()
    {
        return $this->db->query("
            SELECT st.student_id, u.name
            FROM students st
            JOIN users u ON u.user_id = st.user_id
            WHERE st.status = 1
            ORDER BY u.name ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTeachers()
    {
        return $this->db->query("
            SELECT t.teacher_id, u.name
            FROM teachers t
            JOIN users u ON u.user_id = t.user_id
            WHERE t.status = 1
            ORDER BY u.name ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStudentSchedules($filters)
    {
        $params = [
            'date' => $filters['date']
        ];

        $sql = "
            SELECT
                s.session_id,
                s.session_date,
                s.status,
                st.student_id,
                u_student.name AS student_name,
                c.class_id,
                c.class_code,
                co.name AS course_name,
                p.name AS package_name,
                sh.name AS shift_name,
                sh.start_time,
                sh.end_time,
                r.name AS room_name,
                a.status AS attendance_status,
                GROUP_CONCAT(DISTINCT u_teacher.name ORDER BY session_teacher.role SEPARATOR ', ') AS teachers
            FROM sessions s
            JOIN enrollments e ON e.class_id = s.class_id
            JOIN students st ON st.student_id = e.student_id
            JOIN users u_student ON u_student.user_id = st.user_id
            JOIN classes c ON c.class_id = s.class_id
            JOIN courses co ON co.course_id = c.course_id
            JOIN packages p ON p.package_id = c.package_id
            LEFT JOIN shifts sh ON sh.shift_id = s.shift_id
            LEFT JOIN rooms r ON r.room_id = s.room_id
            LEFT JOIN attendances a ON a.session_id = s.session_id AND a.student_id = st.student_id
            LEFT JOIN session_teachers session_teacher ON session_teacher.session_id = s.session_id
            LEFT JOIN teachers teacher ON teacher.teacher_id = session_teacher.teacher_id
            LEFT JOIN users u_teacher ON u_teacher.user_id = teacher.user_id
            WHERE s.session_date = :date
            AND s.status <> 'cancelled'
            AND e.status <> 'dropped'
        ";

        if (!empty($filters['student_id'])) {
            $sql .= " AND st.student_id = :student_id";
            $params['student_id'] = (int) $filters['student_id'];
        }

        if (!empty($filters['teacher_id'])) {
            $sql .= " AND EXISTS (
                SELECT 1
                FROM session_teachers st_filter
                WHERE st_filter.session_id = s.session_id
                AND st_filter.teacher_id = :teacher_id
            )";
            $params['teacher_id'] = (int) $filters['teacher_id'];
        }

        $sql .= "
            GROUP BY s.session_id, st.student_id
            ORDER BY sh.start_time ASC, u_student.name ASC, s.session_id ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTeacherSchedules($filters)
    {
        $params = [
            'date' => $filters['date']
        ];

        $sql = "
            SELECT
                s.session_id,
                s.session_date,
                s.status,
                teacher.teacher_id,
                u_teacher.name AS teacher_name,
                session_teacher.role AS teacher_role,
                c.class_id,
                c.class_code,
                co.name AS course_name,
                p.name AS package_name,
                sh.name AS shift_name,
                sh.start_time,
                sh.end_time,
                r.name AS room_name,
                COUNT(DISTINCT e.student_id) AS student_count
            FROM sessions s
            JOIN session_teachers session_teacher ON session_teacher.session_id = s.session_id
            JOIN teachers teacher ON teacher.teacher_id = session_teacher.teacher_id
            JOIN users u_teacher ON u_teacher.user_id = teacher.user_id
            JOIN classes c ON c.class_id = s.class_id
            JOIN courses co ON co.course_id = c.course_id
            JOIN packages p ON p.package_id = c.package_id
            LEFT JOIN shifts sh ON sh.shift_id = s.shift_id
            LEFT JOIN rooms r ON r.room_id = s.room_id
            LEFT JOIN enrollments e ON e.class_id = s.class_id AND e.status <> 'dropped'
            WHERE s.session_date = :date
            AND s.status <> 'cancelled'
        ";

        if (!empty($filters['teacher_id'])) {
            $sql .= " AND teacher.teacher_id = :teacher_id";
            $params['teacher_id'] = (int) $filters['teacher_id'];
        }

        if (!empty($filters['student_id'])) {
            $sql .= " AND EXISTS (
                SELECT 1
                FROM enrollments e_filter
                WHERE e_filter.class_id = s.class_id
                AND e_filter.student_id = :student_id
                AND e_filter.status <> 'dropped'
            )";
            $params['student_id'] = (int) $filters['student_id'];
        }

        $sql .= "
            GROUP BY s.session_id, teacher.teacher_id, session_teacher.role
            ORDER BY sh.start_time ASC, u_teacher.name ASC, s.session_id ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
