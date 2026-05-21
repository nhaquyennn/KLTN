<?php
class ShiftModel
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    // =========================
    // GET ALL + FILTER + PAGINATION
    // =========================
    public function getAll($filters, $limit, $offset)
    {
        $sql = "SELECT * FROM shifts WHERE 1";

        // KEYWORD
        if (!empty($filters['keyword'])) {
            $sql .= " AND name LIKE :keyword";
        }

        // START TIME
        if (!empty($filters['start_time'])) {
            $sql .= " AND start_time >= :start_time";
        }

        // END TIME
        if (!empty($filters['end_time'])) {
            $sql .= " AND end_time <= :end_time";
        }

        $sql .= " ORDER BY shift_id DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        // BIND KEYWORD
        if (!empty($filters['keyword'])) {
            $stmt->bindValue(
                ':keyword',
                '%' . $filters['keyword'] . '%'
            );
        }

        // BIND START TIME
        if (!empty($filters['start_time'])) {
            $stmt->bindValue(
                ':start_time',
                $filters['start_time'] . ':00'
            );
        }

        // BIND END TIME
        if (!empty($filters['end_time'])) {
            $stmt->bindValue(
                ':end_time',
                $filters['end_time'] . ':00'
            );
        }

        $stmt->bindValue(
            ':limit',
            (int) $limit,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':offset',
            (int) $offset,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAll($filters)
    {
        $sql = "SELECT COUNT(*) FROM shifts WHERE 1";

        // KEYWORD
        if (!empty($filters['keyword'])) {
            $sql .= " AND name LIKE :keyword";
        }

        // START TIME
        if (!empty($filters['start_time'])) {
            $sql .= " AND start_time >= :start_time";
        }

        // END TIME
        if (!empty($filters['end_time'])) {
            $sql .= " AND end_time <= :end_time";
        }

        $stmt = $this->db->prepare($sql);

        // BIND KEYWORD
        if (!empty($filters['keyword'])) {
            $stmt->bindValue(
                ':keyword',
                '%' . $filters['keyword'] . '%'
            );
        }

        // BIND START TIME
        if (!empty($filters['start_time'])) {
            $stmt->bindValue(
                ':start_time',
                $filters['start_time'] . ':00'
            );
        }

        // BIND END TIME
        if (!empty($filters['end_time'])) {
            $stmt->bindValue(
                ':end_time',
                $filters['end_time'] . ':00'
            );
        }

        $stmt->execute();

        return $stmt->fetchColumn();
    }

    // =========================
    // CRUD
    // =========================
    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM shifts WHERE shift_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO shifts (name, start_time, end_time)
            VALUES (:name, :start_time, :end_time)
        ");
        return $stmt->execute($data);
    }

    public function update($id, $data)
    {
        $stmt = $this->db->prepare("
            UPDATE shifts
            SET name = :name,
                start_time = :start_time,
                end_time = :end_time
            WHERE shift_id = :id
        ");

        $data['id'] = $id;
        return $stmt->execute($data);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM shifts WHERE shift_id = ?");
        return $stmt->execute([$id]);
    }
}