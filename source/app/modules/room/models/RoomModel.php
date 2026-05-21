<?php
class RoomModel
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    public function getAll($keyword, $status, $min, $max, $limit, $offset)
    {
        $keyword = trim($keyword);
        $status = trim($status);
        $min = trim($min);
        $max = trim($max);

        $sql = "SELECT * FROM rooms WHERE 1";

        if ($keyword !== '') {
            $sql .= " AND name LIKE :keyword";
        }

        if ($status !== '') {
            $sql .= " AND status = :status";
        }

        if ($min !== '' && is_numeric($min)) {
            $sql .= " AND capacity >= :min";
        }

        if ($max !== '' && is_numeric($max)) {
            $sql .= " AND capacity <= :max";
        }

        $sql .= " ORDER BY room_id DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        if ($keyword !== '') {
            $stmt->bindValue(':keyword', "%$keyword%");
        }

        if ($status !== '') {
            $stmt->bindValue(':status', $status);
        }

        if ($min !== '' && is_numeric($min)) {
            $stmt->bindValue(':min', (int) $min, PDO::PARAM_INT);
        }

        if ($max !== '' && is_numeric($max)) {
            $stmt->bindValue(':max', (int) $max, PDO::PARAM_INT);
        }

        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAll($keyword, $status, $min, $max)
    {
        $keyword = trim($keyword);
        $status = trim($status);
        $min = trim($min);
        $max = trim($max);

        $sql = "SELECT COUNT(*) FROM rooms WHERE 1";

        if ($keyword !== '') {
            $sql .= " AND name LIKE :keyword";
        }

        if ($status !== '') {
            $sql .= " AND status = :status";
        }

        if ($min !== '' && is_numeric($min)) {
            $sql .= " AND capacity >= :min";
        }

        if ($max !== '' && is_numeric($max)) {
            $sql .= " AND capacity <= :max";
        }

        $stmt = $this->db->prepare($sql);

        if ($keyword !== '') {
            $stmt->bindValue(':keyword', "%$keyword%");
        }

        if ($status !== '') {
            $stmt->bindValue(':status', $status);
        }

        if ($min !== '' && is_numeric($min)) {
            $stmt->bindValue(':min', (int) $min, PDO::PARAM_INT);
        }

        if ($max !== '' && is_numeric($max)) {
            $stmt->bindValue(':max', (int) $max, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function create($data)
    {
        $sql = "INSERT INTO rooms (name, capacity, status)
                VALUES (:name, :capacity, :status)";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'name' => trim($data['name']),
            'capacity' => (int) $data['capacity'],
            'status' => $data['status'] ?? 'active'
        ]);
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM rooms WHERE room_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($data)
    {
        $sql = "UPDATE rooms 
                SET name = :name,
                    capacity = :capacity,
                    status = :status
                WHERE room_id = :room_id";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'room_id' => $data['room_id'],
            'name' => trim($data['name']),
            'capacity' => (int) $data['capacity'],
            'status' => $data['status']
        ]);
    }

    public function changeStatus($id, $status)
    {
        $stmt = $this->db->prepare("
        UPDATE rooms
        SET status = ?
        WHERE room_id = ?
    ");

        $stmt->execute([$status, $id]);
    }

    public function hasRunningSessions($roomId)
    {
        $sql = "
        SELECT COUNT(*)
        FROM sessions
        WHERE room_id = ?
        AND session_date = CURDATE()
        AND status = 'ongoing'
    ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$roomId]);

        return $stmt->fetchColumn() > 0;
    }

    public function hasFutureSessions($roomId)
    {
        $sql = "
            SELECT COUNT(*)
            FROM sessions s
            JOIN shifts sh ON s.shift_id = sh.shift_id

            WHERE s.room_id = ?

            AND (
                s.session_date > CURDATE()

                OR (
                    s.session_date = CURDATE()
                    AND CURTIME() < sh.start_time
                )
            )

            AND s.status NOT IN ('done', 'cancelled')
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$roomId]);

        return $stmt->fetchColumn() > 0;
    }

    public function removeFutureRoomSessions($roomId)
    {
        $sql = "
            UPDATE sessions
            SET 
                room_id = NULL,
                status = 'conflict',
                note = 'Room inactive'
            WHERE room_id = ?
            AND session_date > CURDATE()
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$roomId]);
    }

    public function getFutureSessions($roomId)
    {
        $sql = "
            SELECT 
                s.session_id,
                s.session_date,

                c.class_code,

                co.name AS course_name,
                p.name AS package_name,

                sh.name AS shift_name

            FROM sessions s

            JOIN classes c 
                ON s.class_id = c.class_id

            JOIN courses co 
                ON c.course_id = co.course_id

            JOIN packages p 
                ON c.package_id = p.package_id

            JOIN shifts sh 
                ON s.shift_id = sh.shift_id

            WHERE s.room_id = ?

            AND (
                s.session_date > CURDATE()

                OR (
                    s.session_date = CURDATE()
                    AND CURTIME() < sh.start_time
                )
            )

            ORDER BY s.session_date ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$roomId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}