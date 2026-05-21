<?php

class AccountModel
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    public function getAll($filters = [], $limit = 10, $offset = 0)
    {
        $sql = "
            SELECT
                user_id,
                name,
                email,
                phone,
                role,
                status,
                created_at,
                CASE WHEN password IS NULL OR password = '' THEN 0 ELSE 1 END AS has_password
            FROM users
            WHERE 1
        ";

        if (!empty($filters['keyword'])) {
            $sql .= " AND (name LIKE :keyword OR email LIKE :keyword OR phone LIKE :keyword)";
        }

        if (($filters['role'] ?? '') !== '') {
            $sql .= " AND role = :role";
        }

        if (($filters['status'] ?? '') !== '') {
            $sql .= " AND status = :status";
        }

        $sql .= " ORDER BY user_id DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $this->bindFilters($stmt, $filters);
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAll($filters = [])
    {
        $sql = "
            SELECT COUNT(*)
            FROM users
            WHERE 1
        ";

        if (!empty($filters['keyword'])) {
            $sql .= " AND (name LIKE :keyword OR email LIKE :keyword OR phone LIKE :keyword)";
        }

        if (($filters['role'] ?? '') !== '') {
            $sql .= " AND role = :role";
        }

        if (($filters['status'] ?? '') !== '') {
            $sql .= " AND status = :status";
        }

        $stmt = $this->db->prepare($sql);
        $this->bindFilters($stmt, $filters);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("
            SELECT
                user_id,
                name,
                email,
                phone,
                role,
                status,
                CASE WHEN password IS NULL OR password = '' THEN 0 ELSE 1 END AS has_password
            FROM users
            WHERE user_id = ?
        ");
        $stmt->execute([(int) $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO users (name, email, password, role, phone, status)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            trim($data['name'] ?? ''),
            trim($data['email'] ?? ''),
            $data['password'] ?? '',
            $this->normalizeRole($data['role'] ?? 'student'),
            trim($data['phone'] ?? ''),
            (int) ($data['status'] ?? 1)
        ]);
    }

    public function update($data)
    {
        $stmt = $this->db->prepare("
            UPDATE users
            SET name = ?, email = ?, role = ?, phone = ?, status = ?
            WHERE user_id = ?
        ");

        $ok = $stmt->execute([
            trim($data['name'] ?? ''),
            trim($data['email'] ?? ''),
            $this->normalizeRole($data['role'] ?? 'student'),
            trim($data['phone'] ?? ''),
            (int) ($data['status'] ?? 1),
            (int) ($data['user_id'] ?? 0)
        ]);

        if (!empty($data['password'])) {
            $stmtPassword = $this->db->prepare("
                UPDATE users
                SET password = ?
                WHERE user_id = ?
            ");
            $stmtPassword->execute([
                $data['password'],
                (int) ($data['user_id'] ?? 0)
            ]);
        }

        return $ok;
    }

    public function updateStatus($id, $status)
    {
        $stmt = $this->db->prepare("
            UPDATE users
            SET status = ?
            WHERE user_id = ?
        ");

        return $stmt->execute([(int) $status, (int) $id]);
    }

    public function deleteAccount($id)
    {
        $stmt = $this->db->prepare("
            UPDATE users
            SET status = 0,
                password = ''
            WHERE user_id = ?
        ");

        return $stmt->execute([(int) $id]);
    }

    private function bindFilters($stmt, $filters)
    {
        if (!empty($filters['keyword'])) {
            $stmt->bindValue(':keyword', '%' . $filters['keyword'] . '%');
        }

        if (($filters['role'] ?? '') !== '') {
            $stmt->bindValue(':role', $this->normalizeRole($filters['role']));
        }

        if (($filters['status'] ?? '') !== '') {
            $stmt->bindValue(':status', (int) $filters['status'], PDO::PARAM_INT);
        }
    }

    private function normalizeRole($role)
    {
        $allowed = ['admin', 'teacher', 'parent', 'student'];
        return in_array($role, $allowed, true) ? $role : 'student';
    }
}
