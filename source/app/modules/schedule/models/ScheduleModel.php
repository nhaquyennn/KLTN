<?php
class ScheduleModel
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    // =========================
// GET ALL
// =========================
    public function getAll($filters, $limit, $offset)
    {
        $sql = "SELECT s.*,
                   GROUP_CONCAT(sd.day_of_week ORDER BY sd.day_of_week) AS days
            FROM schedules s
            LEFT JOIN schedule_days sd
                ON s.schedule_id = sd.schedule_id
            WHERE 1";

        // Keyword
        if (!empty($filters['keyword'])) {
            $sql .= " AND s.name LIKE :keyword";
        }

        // Day
        if (!empty($filters['day'])) {
            $sql .= " AND sd.day_of_week = :day";
        }

        // Status
        if (!empty($filters['status'])) {
            $sql .= " AND s.status = :status";
        }

        $sql .= " GROUP BY s.schedule_id
              ORDER BY s.schedule_id DESC
              LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        // Keyword
        if (!empty($filters['keyword'])) {
            $stmt->bindValue(':keyword', '%' . $filters['keyword'] . '%');
        }

        // Day
        if (!empty($filters['day'])) {
            $stmt->bindValue(':day', (int) $filters['day'], PDO::PARAM_INT);
        }

        // Status
        if (!empty($filters['status'])) {
            $stmt->bindValue(':status', $filters['status']);
        }

        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // =========================
// COUNT ALL
// =========================
    public function countAll($filters)
    {
        $sql = "SELECT COUNT(DISTINCT s.schedule_id)
            FROM schedules s
            LEFT JOIN schedule_days sd
                ON s.schedule_id = sd.schedule_id
            WHERE 1";

        // Keyword
        if (!empty($filters['keyword'])) {
            $sql .= " AND s.name LIKE :keyword";
        }

        // Day
        if (!empty($filters['day'])) {
            $sql .= " AND sd.day_of_week = :day";
        }

        // Status
        if (!empty($filters['status'])) {
            $sql .= " AND s.status = :status";
        }

        $stmt = $this->db->prepare($sql);

        // Keyword
        if (!empty($filters['keyword'])) {
            $stmt->bindValue(':keyword', '%' . $filters['keyword'] . '%');
        }

        // Day
        if (!empty($filters['day'])) {
            $stmt->bindValue(':day', (int) $filters['day'], PDO::PARAM_INT);
        }

        // Status
        if (!empty($filters['status'])) {
            $stmt->bindValue(':status', $filters['status']);
        }

        $stmt->execute();

        return $stmt->fetchColumn();
    }

    // =========================
    // CREATE
    // =========================
    public function create($data, $days)
    {
        $this->db->beginTransaction();

        $stmt = $this->db->prepare("
            INSERT INTO schedules (name, code)
            VALUES (?, ?)
        ");
        $stmt->execute([$data['name'], $data['code']]);

        $schedule_id = $this->db->lastInsertId();

        foreach ($days as $day) {
            $stmt = $this->db->prepare("
                INSERT INTO schedule_days (schedule_id, day_of_week)
                VALUES (?, ?)
            ");
            $stmt->execute([$schedule_id, $day]);
        }

        $this->db->commit();
    }

    // =========================
    // GET BY ID
    // =========================
    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM schedules WHERE schedule_id = ?");
        $stmt->execute([$id]);
        $schedule = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $this->db->prepare("SELECT day_of_week FROM schedule_days WHERE schedule_id = ?");
        $stmt->execute([$id]);
        $schedule['days'] = array_column($stmt->fetchAll(), 'day_of_week');

        return $schedule;
    }

    // =========================
    // UPDATE
    // =========================
    public function update($id, $data, $days)
    {
        $this->db->beginTransaction();

        $stmt = $this->db->prepare("
            UPDATE schedules SET name=?, code=? WHERE schedule_id=?
        ");
        $stmt->execute([$data['name'], $data['code'], $id]);

        // Xóa cũ
        $this->db->prepare("DELETE FROM schedule_days WHERE schedule_id=?")
            ->execute([$id]);

        // Thêm lại
        foreach ($days as $day) {
            $this->db->prepare("
                INSERT INTO schedule_days (schedule_id, day_of_week)
                VALUES (?, ?)
            ")->execute([$id, $day]);
        }

        $this->db->commit();
    }

    public function updateStatus($id, $status)
    {
        $sql = "UPDATE schedules
            SET status = :status
            WHERE schedule_id = :id";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }
}