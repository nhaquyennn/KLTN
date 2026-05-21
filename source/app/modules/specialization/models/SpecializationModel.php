<?php

class SpecializationModel
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    public function getAll($keyword, $status, $limit, $offset)
    {
        $keyword = trim($keyword);
        $status = trim($status);

        $sql = "SELECT * FROM specializations WHERE 1";

        if ($keyword != '') {
            $sql .= " AND name LIKE :keyword";
        }

        if ($status != '') {
            $sql .= " AND status = :status";
        }

        $sql .= " ORDER BY specialization_id DESC
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        if ($keyword != '') {
            $stmt->bindValue(':keyword', "%$keyword%");
        }

        if ($status != '') {
            $stmt->bindValue(':status', $status);
        }

        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAll($keyword, $status)
    {
        $keyword = trim($keyword);
        $status = trim($status);

        $sql = "SELECT COUNT(*) FROM specializations WHERE 1";

        if ($keyword != '') {
            $sql .= " AND name LIKE :keyword";
        }

        if ($status != '') {
            $sql .= " AND status = :status";
        }

        $stmt = $this->db->prepare($sql);

        if ($keyword != '') {
            $stmt->bindValue(':keyword', "%$keyword%");
        }

        if ($status != '') {
            $stmt->bindValue(':status', $status);
        }

        $stmt->execute();

        return $stmt->fetchColumn();
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM specializations
            WHERE specialization_id = ?
        ");

        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO specializations
            (name, description, status)
            VALUES (?, ?, ?)
        ");

        $stmt->execute([
            trim($data['name']),
            trim($data['description']),
            $data['status']
        ]);
    }

    public function update($data)
    {
        $stmt = $this->db->prepare("
            UPDATE specializations
            SET
                name = ?,
                description = ?,
                status = ?
            WHERE specialization_id = ?
        ");

        $stmt->execute([
            trim($data['name']),
            trim($data['description']),
            $data['status'],
            $data['specialization_id']
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("
            DELETE FROM specializations
            WHERE specialization_id = ?
        ");

        $stmt->execute([$id]);
    }
}