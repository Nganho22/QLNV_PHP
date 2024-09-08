<?php
require_once __DIR__ . '/../config/MySQLconnect.php';

class PassModel {
    private $db;

    /*
    private $apiUrl;

    public function __construct($apiUrl) {
        $this->apiUrl = $apiUrl;
    }*/

    public static function isEmailExists($email) {
        $db = new Database();
        $conn = $db->connect();

        $sql = "SELECT * FROM Profile WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
    
            $newResult = [
                'Email' => $row['email'],
                'EmpID' => $row['empid']
            ];
            $stmt->close();
            return $newResult;
        }
        $stmt->close();
        $db->close();
        return false;
    }

    public static function generateResetCode() {
        return random_int(100000, 999999); // Tạo mã xác nhận 6 số ngẫu nhiên
    }

    public static function storeResetCode($email, $code) {
        $db = new Database();
        $conn = $db->connect();

        $expiry = gmdate('Y-m-d H:i:s', strtotime('+60 seconds')); // Mã hết hạn sau 60s
        $sql = "INSERT INTO password_resets (email, code, created_at) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $email, $code, $expiry);
        $result = $stmt->execute();
        $stmt->close(); 
        $db->close();
        return $result;
    }

    public static function isValidResetCode($email, $code) {
        $db = new Database();
        $conn = $db->connect();

        $sql = "SELECT * FROM password_resets WHERE email = ? AND code = ? AND created_at > UTC_TIMESTAMP()";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $email, $code);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $db->close();
        return $result->num_rows > 0;
    }

    public static function updatePassword($email, $newPassword) {
        $db = new Database();
        $conn = $db->connect();
        $sql = "UPDATE Profile SET matkhau = ? WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $newPassword, $email);
        $result = $stmt->execute();
        $stmt->close(); 
        $db->close();

        return $result;
    }

    public static function deletePasswordReset($email, $code) {
        $db = new Database();
        $conn = $db->connect();

        $sql = "DELETE FROM password_resets WHERE email = ? AND code = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $email, $code);
        $result = $stmt->execute();
        $stmt->close(); 
        $db->close();

        return $result;
    }

    public static function deleteOldResetCode($email) {
        $db = new Database();
        $conn = $db->connect();
        $sql = "DELETE FROM password_resets WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $result = $stmt->execute();
        $stmt->close(); 
        $db->close();

        return $result;
    }
}
?>