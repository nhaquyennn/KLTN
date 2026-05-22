<?php
class Database {
    private $host = "127.0.0.1";
    private $dbname = "merge_q";
    private $username = "itcenter";
    private $password = "123456";

    public $conn;

    public function connect() {
        $this->conn = null;
        date_default_timezone_set('Asia/Ho_Chi_Minh');

        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8",
                $this->username,
                $this->password
            );

            // set mode lỗi
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("SET time_zone = '+07:00'");

        } catch (PDOException $e) {
            die("Kết nối CSDL thất bại: " . $e->getMessage());
        }

        return $this->conn;
    }
}
