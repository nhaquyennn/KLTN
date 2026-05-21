<?php
class AuthModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getUserByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePassword($user_id, $password) {
        $stmt = $this->conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$password, $user_id]);
    }

    public function saveOTP($user_id, $otp, $expire) {
        $stmt = $this->conn->prepare("
            UPDATE users 
            SET otp_code = ?, otp_expire = ? 
            WHERE id = ?
        ");
        return $stmt->execute([$otp, $expire, $user_id]);
    }

    public function verifyOTP($user_id, $otp) {
        $stmt = $this->conn->prepare("
            SELECT * FROM users 
            WHERE id = ? 
              AND otp_code = ? 
              AND otp_expire > NOW()
        ");
        $stmt->execute([$user_id, $otp]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function clearOTP($user_id) {
        $stmt = $this->conn->prepare("
            UPDATE users 
            SET otp_code = NULL, otp_expire = NULL 
            WHERE id = ?
        ");
        return $stmt->execute([$user_id]);
    }
}