<?php
class StudentModel
{
    protected $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    // =========================
    // GET ALL (FILTER + PAGINATION)
    // =========================
    public function getAll($filters = [], $limit = 10, $offset = 0)
    {
        $sql = "
            SELECT 
                st.student_id,
                st.parent_name,
                st.parent_phone,
                st.date_of_birth,
                st.status,

                u.name AS student_name,
                u.email AS student_email,
                u.phone AS student_phone

            FROM students st
            JOIN users u ON st.user_id = u.user_id

            WHERE 1
        ";

        // FILTER KEYWORD
        if (!empty($filters['keyword'])) {
            $sql .= " AND (
                u.name LIKE :keyword OR
                u.phone LIKE :keyword OR
                st.parent_name LIKE :keyword
            )";
        }

        // FILTER STATUS
        if (isset($filters['status']) && $filters['status'] !== '') {
            $sql .= " AND st.status = :status";
        }

        $sql .= " ORDER BY st.student_id DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        if (!empty($filters['keyword'])) {
            $stmt->bindValue(':keyword', '%' . $filters['keyword'] . '%');
        }
        if (isset($filters['status']) && $filters['status'] !== '') {
            $stmt->bindValue(':status', $filters['status']);
        }

        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =========================
    // COUNT (PHÂN TRANG)
    // =========================
    public function countAll($filters = [])
    {
        $sql = "
            SELECT COUNT(*) 
            FROM students st
            JOIN users u ON st.user_id = u.user_id
            WHERE 1
        ";

        if (!empty($filters['keyword'])) {
            $sql .= " AND (
                u.name LIKE :keyword OR
                u.phone LIKE :keyword OR
                st.parent_name LIKE :keyword
            )";
        }
        if (isset($filters['status']) && $filters['status'] !== '') {
            $sql .= " AND st.status = :status";
        }

        $stmt = $this->db->prepare($sql);

        if (!empty($filters['keyword'])) {
            $stmt->bindValue(':keyword', '%' . $filters['keyword'] . '%');
        }

        if ($filters['status'] !== '' && isset($filters['status'])) {
            $stmt->bindValue(':status', $filters['status']);
        }

        $stmt->execute();
        return $stmt->fetchColumn();
    }

    // =========================
    // GET BY ID
    // =========================
    public function getById($id)
    {
        $stmt = $this->db->prepare("
            SELECT 
                st.*,
                u.name AS student_name,
                u.email AS student_email,
                u.phone AS student_phone
            FROM students st
            JOIN users u ON st.user_id = u.user_id
            WHERE st.student_id = ?
        ");

        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // =========================
    // CREATE
    // =========================
    public function create($data)
    {
        try {
            $this->db->beginTransaction();

            // 1. insert user
            $stmt = $this->db->prepare("
                INSERT INTO users (name, email, password, role, phone, status)
                VALUES (?, ?, ?, 'student', ?, ?)
            ");
            $stmt->execute([
                $data['name'],
                $data['email'] ?? null,
                $data['password'] ?? '',
                $data['phone'],
                $data['status'] ?? 1
            ]);

            $user_id = $this->db->lastInsertId();

            // 2. insert student
            $stmt = $this->db->prepare("
                INSERT INTO students (user_id, parent_name, parent_phone, date_of_birth, status)
                VALUES (?, ?, ?, ?, ?)
            ");

            $ok = $stmt->execute([
                $user_id,
                $data['parent_name'] ?? null,
                $data['parent_phone'] ?? null,
                $data['date_of_birth'] ?? null,
                $data['status'] ?? 1
            ]);

            $this->db->commit();
            return $ok;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // =========================
    // UPDATE
    // =========================
    public function update($data)
    {
        // update user
        $stmt = $this->db->prepare("
            UPDATE users u
            JOIN students st ON u.user_id = st.user_id
            SET u.name = ?, u.email = ?, u.phone = ?, u.status = ?
            WHERE st.student_id = ?
        ");
        $stmt->execute([
            $data['name'],
            $data['email'] ?? null,
            $data['phone'],
            $data['status'] ?? 1,
            $data['student_id']
        ]);

        if (!empty($data['password'])) {
            $stmtPassword = $this->db->prepare("
                UPDATE users u
                JOIN students st ON u.user_id = st.user_id
                SET u.password = ?
                WHERE st.student_id = ?
            ");
            $stmtPassword->execute([$data['password'], $data['student_id']]);
        }

        // update student
        $stmt = $this->db->prepare("
            UPDATE students
            SET parent_name = ?, parent_phone = ?, date_of_birth = ?, status = ?
            WHERE student_id = ?
        ");

        return $stmt->execute([
            $data['parent_name'] ?? null,
            $data['parent_phone'] ?? null,
            $data['date_of_birth'] ?? null,
            $data['status'],
            $data['student_id']
        ]);
    }

    // =========================
    // DELETE (OPTIONAL)
    // =========================
    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM students WHERE student_id = ?");
        return $stmt->execute([$id]);
    }

    public function archive($id)
    {
        $stmt = $this->db->prepare("
            UPDATE students 
            SET status = 0 
            WHERE student_id = ?
        ");

        return $stmt->execute([$id]);
    }

    public function restore($id)
    {
        $stmt = $this->db->prepare("
            UPDATE students 
            SET status = 1 
            WHERE student_id = ?
        ");

        return $stmt->execute([$id]);
    }

    public function getAvailableByClass($class_id)
    {
        $stmt = $this->db->prepare("
            SELECT 
                st.student_id,
                u.name AS student_name

            FROM students st
            JOIN users u ON st.user_id = u.user_id

            WHERE st.student_id NOT IN (
                SELECT student_id 
                FROM enrollments 
                WHERE class_id = ?
            )

            AND st.status = 1
        ");

        $stmt->execute([$class_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
