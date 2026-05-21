<?php

class TeacherModel
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    // ===== DROPDOWN =====
    public function getTeachers()
    {
        return $this->db->query("
            SELECT 
                t.teacher_id,
                u.name
            FROM teachers t
            JOIN users u 
                ON t.user_id = u.user_id
            WHERE t.status = 1
            ORDER BY u.name ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    // ===== LIST =====
    public function getAll($filters, $limit, $offset)
    {
        $filters = array_merge([
            'keyword' => null,
            'specialization' => null,
            'salary_type' => null,
            'status' => null
        ], $filters ?? []);

        $sql = "
            SELECT 
                t.*,
                u.name,
                u.email,
                s.name AS specialization_name
            FROM teachers t
            JOIN users u 
                ON t.user_id = u.user_id
            LEFT JOIN specializations s
                ON t.specialization_id = s.specialization_id
            WHERE 1
        ";

        if (!empty($filters['keyword'])) {
            $sql .= " AND (u.name LIKE :kw OR u.email LIKE :kw)";
        }

        if (!empty($filters['specialization'])) {
            $sql .= " AND t.specialization_id = :specialization";
        }

        if ($filters['salary_type'] !== null && $filters['salary_type'] !== '') {
            $sql .= " AND t.salary_type = :salary_type";
        }

        if ($filters['status'] !== null && $filters['status'] !== '') {
            $sql .= " AND t.status = :status";
        }

        $sql .= "
            ORDER BY t.teacher_id DESC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->db->prepare($sql);

        if (!empty($filters['keyword'])) {
            $stmt->bindValue(':kw', "%" . $filters['keyword'] . "%");
        }

        if (!empty($filters['specialization'])) {
            $stmt->bindValue(':specialization', $filters['specialization']);
        }

        if ($filters['salary_type'] !== null && $filters['salary_type'] !== '') {
            $stmt->bindValue(':salary_type', $filters['salary_type']);
        }

        if ($filters['status'] !== null && $filters['status'] !== '') {
            $stmt->bindValue(':status', $filters['status']);
        }

        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ===== COUNT =====
    public function countAll($filters)
    {
        $sql = "
            SELECT COUNT(*)
            FROM teachers t
            JOIN users u 
                ON t.user_id = u.user_id
            WHERE 1
        ";

        if (!empty($filters['keyword'])) {
            $sql .= " AND (u.name LIKE :kw OR u.email LIKE :kw)";
        }

        if (!empty($filters['specialization'])) {
            $sql .= " AND t.specialization_id = :specialization";
        }

        if ($filters['salary_type'] !== null && $filters['salary_type'] !== '') {
            $sql .= " AND t.salary_type = :salary_type";
        }

        if ($filters['status'] !== null && $filters['status'] !== '') {
            $sql .= " AND t.status = :status";
        }

        $stmt = $this->db->prepare($sql);

        if (!empty($filters['keyword'])) {
            $stmt->bindValue(':kw', "%" . $filters['keyword'] . "%");
        }

        if (!empty($filters['specialization'])) {
            $stmt->bindValue(':specialization', $filters['specialization']);
        }

        if ($filters['salary_type'] !== null && $filters['salary_type'] !== '') {
            $stmt->bindValue(':salary_type', $filters['salary_type']);
        }

        if ($filters['status'] !== null && $filters['status'] !== '') {
            $stmt->bindValue(':status', $filters['status']);
        }

        $stmt->execute();

        return $stmt->fetchColumn();
    }

    // ===== FIND =====
    public function findById($id)
    {
        $sql = "
            SELECT 
                t.*,
                u.name,
                u.email,
                s.name AS specialization_name
            FROM teachers t
            JOIN users u 
                ON t.user_id = u.user_id
            LEFT JOIN specializations s
                ON t.specialization_id = s.specialization_id
            WHERE t.teacher_id = :id
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'id' => $id
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ===== CREATE =====
    public function create($data)
    {
        try {

            $this->db->beginTransaction();

            $sqlUser = "
                INSERT INTO users
                (name, email, password, role, status)
                VALUES
                (:name, :email, :password, 'teacher', :status)
            ";

            $stmtUser = $this->db->prepare($sqlUser);

            $stmtUser->execute([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'] ?? '',
                'status' => $data['status'] ?? 1
            ]);

            $user_id = $this->db->lastInsertId();

            $sqlTeacher = "
                INSERT INTO teachers
                (
                    user_id,
                    specialization_id,
                    hire_date,
                    salary_type,
                    salary_value,
                    status
                )
                VALUES
                (
                    :user_id,
                    :specialization_id,
                    :hire_date,
                    :salary_type,
                    :salary_value,
                    :status
                )
            ";

            $stmtTeacher = $this->db->prepare($sqlTeacher);

            $stmtTeacher->execute([
                'user_id' => $user_id,
                'specialization_id' => $data['specialization_id'],
                'hire_date' => $data['hire_date'],
                'salary_type' => $data['salary_type'],
                'salary_value' => $data['salary_value'],
                'status' => $data['status']
            ]);

            $this->db->commit();

        } catch (Exception $e) {

            $this->db->rollBack();

            throw $e;
        }
    }

    // ===== UPDATE =====
    public function update($data)
    {
        $sql = "
            UPDATE users u
            JOIN teachers t 
                ON u.user_id = t.user_id
            SET 
                u.name = :name,
                u.email = :email,
                u.status = :status
            WHERE t.teacher_id = :id
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'status' => $data['status'] ?? 1,
            'id' => $data['teacher_id']
        ]);

        if (!empty($data['password'])) {
            $stmtPassword = $this->db->prepare("
                UPDATE users u
                JOIN teachers t ON u.user_id = t.user_id
                SET u.password = ?
                WHERE t.teacher_id = ?
            ");
            $stmtPassword->execute([$data['password'], $data['teacher_id']]);
        }

        $sql2 = "
            UPDATE teachers
            SET 
                specialization_id = :specialization_id,
                hire_date = :hire_date,
                salary_type = :salary_type,
                salary_value = :salary_value,
                status = :status
            WHERE teacher_id = :id
        ";

        $stmt2 = $this->db->prepare($sql2);

        return $stmt2->execute([
            'specialization_id' => $data['specialization_id'],
            'hire_date' => $data['hire_date'],
            'salary_type' => $data['salary_type'],
            'salary_value' => $data['salary_value'],
            'status' => $data['status'] ?? 1,
            'id' => $data['teacher_id']
        ]);
    }

    // ===== DELETE =====
    public function delete($id)
    {
        $stmt = $this->db->prepare("
            UPDATE teachers
            SET status = 0
            WHERE teacher_id = :id
        ");

        return $stmt->execute(['id' => $id]);
    }

    // ===== RESTORE =====
    public function restore($id)
    {
        $stmt = $this->db->prepare("
            UPDATE teachers
            SET status = 1
            WHERE teacher_id = :id
        ");

        return $stmt->execute(['id' => $id]);
    }

    // ===== AVAILABLE TEACHERS =====
    public function getAvailableTeachers($date, $shift_id)
    {
        $stmt = $this->db->prepare("
            SELECT 
                t.teacher_id,
                u.name
            FROM teachers t
            JOIN users u 
                ON t.user_id = u.user_id
            WHERE t.status = 1
            AND t.teacher_id NOT IN (
                SELECT st.teacher_id
                FROM session_teachers st
                JOIN sessions s 
                    ON st.session_id = s.session_id
                WHERE s.session_date = ?
                AND s.shift_id = ?
            )
        ");

        $stmt->execute([$date, $shift_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ===== TEACHING HISTORY =====
    public function getTeachingHistoryByUserId($userId, $month = null, $year = null)
    {
        $params = [$userId];
        $whereClauses = "WHERE t.user_id = ?";

        if ($month) {
            $whereClauses .= " AND MONTH(s.session_date) = ?";
            $params[] = $month;
        }
        if ($year) {
            $whereClauses .= " AND YEAR(s.session_date) = ?";
            $params[] = $year;
        }

        $sql = "
            SELECT 
                s.session_date,
                c.class_code,
                co.name AS course_name,
                p.name AS package_name,
                sh.name AS shift_name,
                sh.start_time,
                sh.end_time,
                st.role
            FROM teachers t
            JOIN session_teachers st ON t.teacher_id = st.teacher_id
            JOIN sessions s ON st.session_id = s.session_id
            JOIN classes c ON s.class_id = c.class_id
            JOIN courses co ON c.course_id = co.course_id
            JOIN packages p ON c.package_id = p.package_id
            LEFT JOIN shifts sh ON s.shift_id = sh.shift_id
            $whereClauses
            ORDER BY s.session_date DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($data as &$row) {
            // Xử lý tên hiển thị lớp
            $parts = explode('-', $row['class_code'] ?? '');
            $suffix = end($parts);
            $row['class_display_name'] = $row['course_name'] . ' - ' . $row['package_name'] . ' (' . $suffix . ')';

            // Xử lý thời gian
            $row['time_range'] = ($row['start_time'] && $row['end_time'])
                ? date('H:i', strtotime($row['start_time'])) . ' - ' . date('H:i', strtotime($row['end_time']))
                : 'N/A';
        }

        return $data;
    }
}
