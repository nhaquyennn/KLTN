<?php

class AccountModel
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
        $this->ensureDeletedAccountColumn();
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
                deleted_at,
                created_at,
                CASE WHEN password IS NULL OR password = '' THEN 0 ELSE 1 END AS has_password
            FROM users
            WHERE 1
        ";

        $sql .= !empty($filters['deleted'])
            ? " AND deleted_at IS NOT NULL"
            : " AND deleted_at IS NULL";

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

        $sql .= !empty($filters['deleted'])
            ? " AND deleted_at IS NOT NULL"
            : " AND deleted_at IS NULL";

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
                deleted_at,
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
            AND deleted_at IS NULL
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
                AND deleted_at IS NULL
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
            AND deleted_at IS NULL
        ");

        return $stmt->execute([(int) $status, (int) $id]);
    }

    public function deleteAccount($id)
    {
        $stmt = $this->db->prepare("
            UPDATE users
            SET status = 0,
                deleted_at = NOW()
            WHERE user_id = ?
            AND deleted_at IS NULL
        ");

        return $stmt->execute([(int) $id]);
    }

    public function restoreAccount($id)
    {
        $stmt = $this->db->prepare("
            UPDATE users
            SET deleted_at = NULL,
                status = CASE
                    WHEN password IS NULL OR password = '' THEN 0
                    ELSE 1
                END
            WHERE user_id = ?
            AND deleted_at IS NOT NULL
        ");

        return $stmt->execute([(int) $id]);
    }

    public function permanentlyDeleteAccount($id)
    {
        $stmt = $this->db->prepare("
            DELETE FROM users
            WHERE user_id = ?
            AND deleted_at IS NOT NULL
        ");

        $stmt->execute([(int) $id]);
        return $stmt->rowCount() > 0;
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

    private function ensureDeletedAccountColumn()
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'users'
            AND COLUMN_NAME = 'deleted_at'
        ");
        $stmt->execute();

        if ((int) $stmt->fetchColumn() === 0) {
            $this->db->exec("ALTER TABLE users ADD COLUMN deleted_at DATETIME NULL AFTER status");
        }

        // Legacy account deletion cleared password and locked the row.
        $this->db->exec("
            UPDATE users
            SET deleted_at = NOW()
            WHERE deleted_at IS NULL
            AND status = 0
            AND (password IS NULL OR password = '')
        ");
    }
}
