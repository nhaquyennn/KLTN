<?php
class PackageModel
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    public function getAll($keyword = '', $status = '', $limit = 5, $offset = 0)
    {
        $sql = "SELECT p.*, c.name AS course_name
                FROM packages p
                LEFT JOIN courses c ON p.course_id = c.course_id";
        $params = [];

        if (!empty($course_id)) {
            $sql .= " AND p.course_id = ?";
            $params[] = $course_id;
        }

        if (!empty($keyword)) {
            $sql .= " AND name LIKE ?";
            $params[] = "%$keyword%";
        }

        if ($status !== '') {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY package_id DESC LIMIT $limit OFFSET $offset";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAll($keyword = '', $status = '')
    {
        $sql = "SELECT COUNT(*) FROM packages WHERE 1=1";
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

    public function getById($id)
    {
        $stmt = $this->db->prepare("
        SELECT p.*, c.name AS course_name
        FROM packages p
        LEFT JOIN courses c ON p.course_id = c.course_id
        WHERE p.package_id = ?
    ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO packages (name, total_sessions, price, status)
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['name'],
            $data['total_sessions'],
            $data['price'],
            $data['status']
        ]);
    }

    public function update($id, $data)
    {
        $stmt = $this->db->prepare("
        UPDATE packages 
        SET name = ?, 
            total_sessions = ?, 
            price = ?, 
            course_id = ?, 
            status = ?
        WHERE package_id = ?
    ");

        return $stmt->execute([
            $data['name'],
            $data['total_sessions'],
            $data['price'],
            $data['course_id'],
            $data['status'],
            $id
        ]);
    }

    public function delete($id)
    {
        return $this->db->prepare("DELETE FROM packages WHERE package_id=?")
            ->execute([$id]);
    }

    public function getByCourse($course_id)
    {
        $stmt = $this->db->prepare("
            SELECT package_id, name, total_sessions  
            FROM packages
            WHERE course_id = ?
        ");
        $stmt->execute([$course_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}