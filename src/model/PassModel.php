<?php
require_once __DIR__ . '/../config/MySQLconnect.php';

class PassModel {
    private $db;

    /*
    private $apiUrl;

    public function __construct($apiUrl) {
        $this->apiUrl = $apiUrl;
    }*/

    public function isEmailExists($email) {
        $sql = "SELECT * FROM Profile WHERE Email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
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
        $result = $stmt->execute();
        $stmt->close(); // Close statement
        return $result;
        //return $stmt->execute();
    }

    public function isValidResetCode($email, $code) {
        $sql = "SELECT * FROM password_resets WHERE Email = ? AND Code = ? AND Expiry > UTC_TIMESTAMP()";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $email, $code);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result->num_rows > 0;
    }

    public function updatePassword($email, $newPassword) {
        $sql = "UPDATE Profile SET MatKhau = ? WHERE Email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $newPassword, $email);
        $result = $stmt->execute();
        $stmt->close(); // Close statement
        return $result;
    }

    public function deletePasswordReset($email, $code) {
        $sql = "DELETE FROM password_resets WHERE Email = ? AND Code = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $email, $code);
        $result = $stmt->execute();
        $stmt->close(); // Close statement
        return $result;
    }

    public function deleteOldResetCode($email) {
        $sql = "DELETE FROM password_resets WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $email);
        $result = $stmt->execute();
        $stmt->close(); // Close statement
        return $result;
    }

    public function __destruct() {
        $this->db->close(); // Close database connection when the object is destroyed
    }
}
?>