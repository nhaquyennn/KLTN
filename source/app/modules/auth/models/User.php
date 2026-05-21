<?php

class User
{
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect(); // PDO
    }

    // Tìm user theo email (PDO chuẩn)
    public function findByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);

        return $stmt->fetch(PDO::FETCH_ASSOC); 
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function verifyPassword($user, $password)
    {
        if (!$user) {
            return false;
        }

        return $password === $user['password'];
    }

    public function updatePassword($userId, $password)
    {
        $stmt = $this->db->prepare("
            UPDATE users
            SET password = ?
            WHERE user_id = ?
        ");

        return $stmt->execute([$password, $userId]);
    }

    // Login
    public function login($email, $password)
    {
        $user = $this->findByEmail($email);

        if (!$user) {
            return "email_not_found";
        }
// Đã hash
        // if (!password_verify($password, $user['password'])) {
        //     return "wrong_password";
        // }
        if ($password !== $user['password']) {
            return "wrong_password";
        }
        return $user;
    }
}
