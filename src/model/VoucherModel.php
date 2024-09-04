<?php
require_once __DIR__ . '/../config/MySQLconnect.php';

class VoucherModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();   
    }

    public static function getVoucherCountsByEmpID($user_id) {
        $db = new Database();
        $conn = $db->connect();

        $totalPointQuery = "SELECT DiemThuong as totalPoint FROM Profile WHERE EmpID = ?";
        $stmt = $conn->prepare($totalPointQuery);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $totalPoint = $result->fetch_assoc()['totalPoint']?? 0;

        $totalFelicitationQuery = "SELECT COUNT(*) AS total FROM Voucher WHERE TinhTrang IS NOT NULL";
        $stmt = $conn->prepare($totalFelicitationQuery);
        $stmt->execute();
        $result = $stmt->get_result();
        $totalFelicitation = $result->fetch_assoc()['total'];
    
        $existingFelicitationQuery = "SELECT COUNT(*) AS existing
                                        FROM Voucher
                                        WHERE TinhTrang <> 'Đã dùng'";
        $stmt = $conn->prepare($existingFelicitationQuery);
        $stmt->execute();
        $result = $stmt->get_result();
        $existingFelicitation = $result->fetch_assoc()['existing']?? 0;
    
        $exchangeFelicitationQuery = "SELECT COUNT(*) AS exchange
                                        FROM Voucher
                                        WHERE TinhTrang = 'Đã dùng'";
        $stmt = $conn->prepare($exchangeFelicitationQuery);
        $stmt->execute();
        $result = $stmt->get_result();
        $exchangeFelicitation = $result->fetch_assoc()['exchange']?? 0;

        $expiredVoucherQuery = "SELECT COUNT(*) AS expired FROM Voucher WHERE HanSuDung < CURDATE()";
        $stmt = $conn->prepare($expiredVoucherQuery);
        $stmt->execute();
        $result = $stmt->get_result();
        $expiredVoucher = $result->fetch_assoc()['expired'] ?? 0;

        $stmt->close();
        $db->close();
        return [
            'totalPoint' => $totalPoint,
            'total' => $totalFelicitation,
            'existing' => $existingFelicitation,
            'exchange' => $exchangeFelicitation,
            'expired' => $expiredVoucher

        ];
    }

    public static function getAvailableVoucherRequestsByEmpID( $limit, $offset) {
        $db = new Database();
        $conn = $db->connect();
    
        $query = "SELECT VoucherID, 
                        TenVoucher AS TenVoucher, 
                        HanSuDung AS HanSuDung,
                        TriGia AS TriGia
                    FROM Voucher 
                    WHERE TinhTrang <> 'Đã dùng'
                    LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $requests = $result->fetch_all(MYSQLI_ASSOC);
    
        $stmt->close();
        $db->close();
        return $requests;
    }
    
    public static function getAvailableVoucherRequestsByEmpID_QL( $limit, $offset) {
        $db = new Database();
        $conn = $db->connect();
    
        $query = "SELECT VoucherID, 
                        TenVoucher AS TenVoucher, 
                        HanSuDung AS HanSuDung,
                        TriGia AS TriGia
                    FROM Voucher 
                    WHERE TinhTrang IS NULL
                    LIMIT ? OFFSET ?";
    
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();
    
        $result = $stmt->get_result();
        $requests = $result->fetch_all(MYSQLI_ASSOC);
    
        $stmt->close();
        $db->close();
        return $requests;
    }
    
    public static function countVoucherRequests() {
        $db = new Database();
        $conn = $db->connect();

        $query = "SELECT COUNT(*) AS total
                    FROM Voucher
                    WHERE TinhTrang <> 'Đã dùng'";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $total = $result->fetch_assoc()['total'];

        $stmt->close();
        $db->close();
        return $total;
    }

    public static function countExchangeVoucherRequests() {
        $db = new Database();
        $conn = $db->connect();

        $query = "SELECT COUNT(*) AS total
                    FROM Voucher
                    WHERE TinhTrang IS NULL";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $total = $result->fetch_assoc()['total'];

        $stmt->close();
        $db->close();
        return $total;
    }

    public static function getTimeSheetsByEmpID($user_id) {
        $db = new Database();
        $conn = $db->connect();

        $query = "SELECT * FROM Time_sheet WHERE EmpID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $requests = $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();
        $db->close();
        return $requests;
    }

    public static function getPoint_Month($user_id) {
        $db = new Database();
        $conn = $db->connect();
        
        // Truy vấn tổng điểm hàng tháng, bao gồm cả điểm bị trừ
        $stmt = $conn->prepare("
            SELECT MONTH(Date) AS month, 
                   SUM(CASE WHEN NguoiTang = ? THEN -Point ELSE Point END) AS total_points
            FROM Felicitation
            WHERE NguoiNhan = ? OR NguoiTang = ?
            GROUP BY MONTH(Date)
            ORDER BY MONTH(Date)
        ");
        $stmt->bind_param("iii", $user_id, $user_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        // Khởi tạo mảng 12 tháng với giá trị 0
        $monthlyPoints = array_fill(1, 12, 0);
        while ($row = $result->fetch_assoc()) {
            $monthlyPoints[(int)$row['month']] = $row['total_points'];
        }
    
        $stmt->close();
        $db->close();
        return $monthlyPoints;
    }
    
    public static function getVoucherDetails($voucherID) {
    $db = new Database();
    $conn = $db->connect();

    $query = "SELECT TenVoucher, TriGia, HanSuDung, TinhTrang, ChiTiet, HuongDanSuDung
              FROM Voucher
              WHERE VoucherID = ? AND TinhTrang is not NULL";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $voucherID);
    $stmt->execute();
    $result = $stmt->get_result();
    $voucherDetails = $result->fetch_assoc();

    $stmt->close();
    $db->close();
    return $voucherDetails;
}
    public static function getExVoucherDetails($voucherID) {
        $db = new Database();
        $conn = $db->connect();

        $query = "SELECT TenVoucher, TriGia, HanSuDung, TinhTrang, ChiTiet, HuongDanSuDung
                FROM Voucher
                WHERE VoucherID = ? AND TinhTrang IS NULL";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $voucherID);
        $stmt->execute();
        $result = $stmt->get_result();
        $voucherDetails = $result->fetch_assoc();

        $stmt->close();
        $db->close();
        return $voucherDetails;
    }

    public static function updateVoucherTinhTrangEx($voucherID) {
        $db = new Database();
        $conn = $db->connect();

        $query = "UPDATE Voucher SET TinhTrang = 'Chưa dùng' WHERE VoucherID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $voucherID);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
    public static function updateVoucherTinhTrangUsed($voucherID) {
        $db = new Database();
        $conn = $db->connect();

        $query = "UPDATE Voucher SET TinhTrang = 'Đã dùng' WHERE VoucherID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $voucherID);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public static function getTinhTrangVoucherByID($voucherID) {
        $db = new Database();
        $conn = $db->connect();

        $query = "SELECT TinhTrang
                FROM Voucher
                WHERE VoucherID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $voucherID);
        $stmt->execute();
        $result = $stmt->get_result();
        $tinhtrang = $result->fetch_assoc();

        $stmt->close();
        $db->close();
        
        // Trả về giá trị của TinhTrang nếu có, ngược lại trả về một chuỗi rỗng
        return isset($tinhtrang['TinhTrang']) ? $tinhtrang['TinhTrang'] : '';
    }

    public static function getEmployeePointsByID($user_id) {
        $db = new Database();
        $conn = $db->connect();

        // Truy vấn điểm hiện có của nhân viên
        $query = "SELECT DiemThuong FROM Profile WHERE EmpID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $points = $result->fetch_assoc()['DiemThuong'] ?? 0;

        $stmt->close();
        $db->close();
        return $points;
    }

    public static function updateEmpExPoints($user_id, $pointEx) {
        $db = new Database();
        $conn = $db->connect();
        
        // Truy vấn để lấy điểm thưởng hiện tại của quản lý
        $query = "SELECT DiemThuong FROM Profile WHERE EmpID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $currentPoints = $result->fetch_assoc()['DiemThuong'] ?? 0;
        
        // Tính điểm thưởng mới
        $newPoints = $currentPoints - $pointEx;

        // Cập nhật điểm thưởng của quản lý
        $updateQuery = "UPDATE Profile SET DiemThuong = ? WHERE EmpID = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param('ii', $newPoints, $user_id);
        $updateStmt->execute();

        $stmt->close();
        $updateStmt->close();
        $db->close();
    }
    public static function addFelicitation($point, $noiDung, $nguoiNhan, $voucherID) {
        $point = -abs($point);
        $db = new Database();
        $conn = $db->connect();

        $query = "INSERT INTO Felicitation (Point, Date, NoiDung, NguoiNhan, VoucherID) 
                VALUES (?, CURDATE(), ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('isii', $point, $noiDung, $nguoiNhan, $voucherID);

        $stmt->execute();
        $stmt->close();
        $db->close();
    }
    }
    ?>
