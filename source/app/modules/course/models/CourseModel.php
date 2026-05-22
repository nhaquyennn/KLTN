<?php
class CourseModel
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    // =========================
    // GET ALL
    // =========================
    public function getAll($keyword = '', $status = '', $limit = 10, $offset = 0)
    {
        $sql = "SELECT * FROM courses WHERE 1=1";
        $params = [];

        // FILTER NAME
        if (!empty($keyword)) {
            $sql .= " AND name LIKE ?";
            $params[] = "%$keyword%";
        }

        // FILTER STATUS
        if ($status !== '') {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY course_id DESC LIMIT $limit OFFSET $offset";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =========================
    // GET BY ID
    // =========================
    public function getById($id)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM courses WHERE course_id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // =========================
    // CREATE
    // =========================
    public function create($data)
    {
        $name = trim((string) ($data['name'] ?? ''));

        if ($name === '') {
            throw new Exception('Vui lòng nhập tên khóa học');
        }

        if ($this->nameExists($name)) {
            throw new Exception('Tên khóa học đã tồn tại');
        }

        $code = $this->generateCourseCode($name);

        $stmt = $this->db->prepare("
        INSERT INTO courses (name, description, status, code)
        VALUES (?, ?, ?, ?)
    ");

        return $stmt->execute([
            $name,
            $data['description'],
            $data['status'],
            $code
        ]);
    }

    // =========================
    // UPDATE
    // =========================
    public function update($id, $data)
    {
        $stmt = $this->db->prepare("
            UPDATE courses 
            SET name = ?, description = ?, status = ?
            WHERE course_id = ?
        ");

        return $stmt->execute([
            $data['name'],
            $data['description'],
            $data['status'],
            $id
        ]);
    }

    // =========================
    // DELETE
    // =========================
    public function delete($id)
    {
        $stmt = $this->db->prepare("
            DELETE FROM courses WHERE course_id = ?
        ");
        return $stmt->execute([$id]);
    }

    public function countAll($keyword = '', $status = '')
    {
        $sql = "SELECT COUNT(*) FROM courses WHERE 1=1";
        $params = [];

        if (!empty($keyword)) {
            $sql .= " AND name LIKE ?";
            $params[] = "%$keyword%";
        }

        if ($status !== '') {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchColumn();
    }

    // =============================
    // AUTO GENERATE CODE
    // =============================
    private function generateCourseCode($name)
    {
        $name = strtolower($name);

        if (str_contains($name, 'python')) {
            return 'PYT';
        }

        if (str_contains($name, 'robotics') || str_contains($name, 'robo')) {
            return 'RBT';
        }

        if (str_contains($name, 'scratch')) {
            return 'SCR';
        }

        return 'CRS';
    }

    private function nameExists($name)
    {
        $stmt = $this->db->prepare("
            SELECT 1
            FROM courses
            WHERE LOWER(TRIM(name)) = LOWER(TRIM(?))
            LIMIT 1
        ");
        $stmt->execute([$name]);
        return (bool) $stmt->fetchColumn();
    }
}
