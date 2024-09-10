<?php
require_once __DIR__ . '/../config/MySQLconnect.php';

class VoucherModel {
    private $db;

    private $apiUrl;

    public function __construct($apiUrl) {
        $this->apiUrl = $apiUrl;
    }

    private function isApiAvailable($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    
        if ($result !== false && $httpCode == 200) {
            return true;
        } else {
            return false;
        }
    }
    public static function getVoucherCountsByEmpID($user_id) {
        $db = new Database();
        $conn = $db->connect();

        $totalPointQuery = "SELECT diemthuong as totalPoint FROM Profile WHERE empid = ?";
        $stmt = $conn->prepare($totalPointQuery);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $totalPoint = $result->fetch_assoc()['totalPoint']?? 0;

        $totalFelicitationQuery = "SELECT COUNT(*) AS total FROM Voucher WHERE tinhtrang IS NOT NULL";
        $stmt = $conn->prepare($totalFelicitationQuery);
        $stmt->execute();
        $result = $stmt->get_result();
        $totalFelicitation = $result->fetch_assoc()['total'];
    
        $existingFelicitationQuery = "SELECT COUNT(*) AS existing
                                        FROM Voucher
                                        WHERE tinhtrang <> 'Đã dùng'";
        $stmt = $conn->prepare($existingFelicitationQuery);
        $stmt->execute();
        $result = $stmt->get_result();
        $existingFelicitation = $result->fetch_assoc()['existing']?? 0;
    
        $exchangeFelicitationQuery = "SELECT COUNT(*) AS exchange
                                        FROM Voucher
                                        WHERE tinhtrang = 'Đã dùng'";
        $stmt = $conn->prepare($exchangeFelicitationQuery);
        $stmt->execute();
        $result = $stmt->get_result();
        $exchangeFelicitation = $result->fetch_assoc()['exchange']?? 0;

        $expiredVoucherQuery = "SELECT COUNT(*) AS expired FROM Voucher WHERE hansudung < CURDATE()";
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
    
        $query = "SELECT voucherid, 
                        tenvoucher, 
                        hansudung,
                        trigia 
                    FROM Voucher 
                    WHERE tinhtrang <> 'Đã dùng'
                    LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();
        
        $result = $stmt->get_result();
        //$requests = $result->fetch_all(MYSQLI_ASSOC);
    
        $requests = [];
        while ($row = $result->fetch_assoc()) {
            $row = [
                'VoucherID' => $row['voucherid'],
                'TenVoucher' => $row['tenvoucher'],
                'HanSuDung' => $row['hansudung'],
                'TriGia' => $row['trigia']
            ];
            $requests[] = $row;
        }

        $stmt->close();
        $db->close();
        return $requests;
    }
    
    public static function getExchangeVoucherRequestsByEmpID( $limit, $offset) {
        $db = new Database();
        $conn = $db->connect();
    
        $query = "SELECT voucherid, 
                        tenvoucher, 
                        hansudung,
                        trigia
                    FROM Voucher 
                    WHERE tinhtrang IS NULL
                    LIMIT ? OFFSET ?";
    
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();
    
        $result = $stmt->get_result();
        //$requests = $result->fetch_all(MYSQLI_ASSOC);
    
        $requests = [];
        while ($row = $result->fetch_assoc()) {
            $row = [
                'VoucherID' => $row['voucherid'],
                'TenVoucher' => $row['tenvoucher'],
                'HanSuDung' => $row['hansudung'],
                'TriGia' => $row['trigia']
            ];
            $requests[] = $row;
        }

        $stmt->close();
        $db->close();
        return $requests;
    }
    
    public static function countAvailableVoucher() {
        $db = new Database();
        $conn = $db->connect();

        $query = "SELECT COUNT(*) AS total
                    FROM Voucher
                    WHERE tinhtrang <> 'Đã dùng'";
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
                    WHERE tinhtrang IS NULL";
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

        $query = "SELECT * FROM Time_sheet WHERE empid = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        //$requests = $result->fetch_all(MYSQLI_ASSOC);
        $requests = [];
        while ($row = $result->fetch_assoc()) {
            $row = [
                'Time_sheetID' => $row['time_sheetid'] ?? 'N/A',
                'ProjectID' => $row['projectid'] ?? 'N/A',
                'TenDuAn' => $row['tenduan'] ?? 'N/A',
                'NguoiGui' => $row['nguoigui'] ?? 'N/A',
                'PhongBan' => $row['phongban'] ?? 'N/A',
                'TrangThai' => $row['trangthai'] ?? 'N/A',
                'SoGioThucHien' => $row['sogiothuchien'] ?? 'N/A',
                'NgayGiao' => $row['ngaygiao'] ?? 'N/A',
                'HanChot' => $row['hanchot'] ?? 'N/A',
                'DiemThuong' => $row['diemthuong'] ?? 'N/A',
                'Tre' => $row['tre'] ?? 'N/A',
                'NoiDung' => $row['noidung'] ?? 'N/A'
            ];
            $requests[] = $row;
        }
        $stmt->close();
        $db->close();
        return $requests;
    }

    public static function getPoint_Month($user_id) {
        $db = new Database();
        $conn = $db->connect();
        
        // Truy vấn tổng điểm hàng tháng, bao gồm cả điểm bị trừ
        $stmt = $conn->prepare("
            SELECT MONTH(date) AS month, 
                   SUM(CASE WHEN nguoitang = ? THEN -point ELSE point END) AS total_points
            FROM Felicitation
            WHERE nguoinhan = ? OR nguoitang = ?
            GROUP BY MONTH(date)
            ORDER BY MONTH(date)
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

        $query = "SELECT *
                FROM Voucher
                WHERE voucherid = ? AND tinhtrang is not NULL";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $voucherID);
        $stmt->execute();
        $result = $stmt->get_result();
        //$voucherDetails = $result->fetch_assoc();
        $voucherDetails = [];
            while ($row = $result->fetch_assoc()) {
                $row = [
                    'voucherID' => $row['voucherid'] ?? 'N/A',
                    'TenVoucher' => $row['tenvoucher'] ?? 'N/A',
                    'TriGia' => $row['trigia'] ?? 'N/A',
                    'HanSuDung' => $row['hansudung'] ?? 'N/A',
                    'TinhTrang' => $row['tinhtrang'] ?? 'N/A',
                    'ChiTiet' => $row['chitiet'] ?? 'N/A',
                    'HuongDanSuDung' => $row['huongdansudung'] ?? 'N/A'
                ];
                $voucherDetails[] = $row;
            }

        $stmt->close();
        $db->close();
        return $voucherDetails;
    }
    
    public static function getExVoucherDetails($voucherID) {
        $db = new Database();
        $conn = $db->connect();

        $query = "SELECT *
                FROM Voucher
                WHERE voucherid = ? AND tinhtrang IS NULL";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $voucherID);
        $stmt->execute();
        $result = $stmt->get_result();
        //$voucherDetails = $result->fetch_assoc();
        $voucherDetails = [];
        while ($row = $result->fetch_assoc()) {
            $row = [
                'voucherID' => $row['voucherid'] ?? 'N/A',
                'TenVoucher' => $row['tenvoucher'] ?? 'N/A',
                'TriGia' => $row['trigia'] ?? 'N/A',
                'HanSuDung' => $row['hansudung'] ?? 'N/A',
                'TinhTrang' => $row['tinhtrang'] ?? 'N/A',
                'ChiTiet' => $row['chitiet'] ?? 'N/A',
                'HuongDanSuDung' => $row['huongdansudung'] ?? 'N/A'
            ];
            $voucherDetails[] = $row;
        }
        $stmt->close();
        $db->close();
        return $voucherDetails;
    }

    public static function updateVoucherTinhTrangEx($voucherID) {
        $db = new Database();
        $conn = $db->connect();

        $query = "UPDATE Voucher SET tinhtrang = 'Chưa dùng' WHERE voucherid = ?";
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

        $query = "UPDATE Voucher SET tinhtrang = 'Đã dùng' WHERE voucherid = ?";
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

        $query = "SELECT tinhtrang
                FROM Voucher
                WHERE voucherid = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $voucherID);
        $stmt->execute();
        $result = $stmt->get_result();
        $tinhtrang = $result->fetch_assoc()['TinhTrang'];

        $stmt->close();
        $db->close();
        
        return isset($tinhtrang['TinhTrang']) ? $tinhtrang['TinhTrang'] : '';
    }

    public static function getEmployeePointsByID($user_id) {
        $db = new Database();
        $conn = $db->connect();

        // Truy vấn điểm hiện có của nhân viên
        $query = "SELECT diemthuong FROM Profile WHERE empid = ?";
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
        $query = "SELECT diemthuong FROM Profile WHERE empid = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $currentPoints = $result->fetch_assoc()['DiemThuong'] ?? 0;
        
        // Tính điểm thưởng mới
        $newPoints = $currentPoints - $pointEx;

        // Cập nhật điểm thưởng của quản lý
        $updateQuery = "UPDATE Profile SET diemthuong = ? WHERE empid = ?";
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

        $query = "INSERT INTO Felicitation (point, date, noidung, nguoinhan, voucherid) 
                VALUES (?, CURDATE(), ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('isii', $point, $noiDung, $nguoiNhan, $voucherID);

        $stmt->execute();
        $stmt->close();
        $db->close();
    }
    }
    ?>
