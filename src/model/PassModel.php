<?php
require_once __DIR__ . '/../config/connect.php';

class PassModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();   
    }

    public function isEmailExists($email) {
        $sql = "SELECT * FROM Profile WHERE Email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    public function generateResetCode() {
        return random_int(100000, 999999); // Tạo mã xác nhận 6 số ngẫu nhiên
    }

    public function storeResetCode($email, $code) {
        $expiry = gmdate('Y-m-d H:i:s', strtotime('+120 seconds')); // Mã hết hạn sau 120s
        $sql = "INSERT INTO password_resets (Email, Code, Expiry) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sss", $email, $code, $expiry);
        return $stmt->execute();
    }

    public function isValidResetCode($email, $code) {
        $sql = "SELECT * FROM password_resets WHERE Email = ? AND Code = ? AND Expiry > UTC_TIMESTAMP()";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $email, $code);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    public function updatePassword($email, $newPassword) {
        $sql = "UPDATE Profile SET MatKhau = ? WHERE Email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $newPassword, $email);
        return $stmt->execute();
    }

    public function deletePasswordReset($email, $code) {
        $sql = "DELETE FROM password_resets WHERE Email = ? AND Code = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $email, $code);
        return $stmt->execute();
    }

    public function deleteOldResetCode($email) {
        $sql = "DELETE FROM password_resets WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $email);
        return $stmt->execute();
    }
}
?>