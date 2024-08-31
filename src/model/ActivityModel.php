<?php
require_once __DIR__ . '/../config/MySQLconnect.php';

class ActivityModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();   
    }

    public static function getFelicitationCountsByEmpID($user_id) {
        $db = new Database();
        $conn = $db->connect();
        
        $totalFelicitationQuery = "SELECT SUM(Point) as total FROM Felicitation WHERE NguoiNhan = ? AND Point >0";
        $stmt = $conn->prepare($totalFelicitationQuery);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $totalFelicitation = $result->fetch_assoc()['total'];
    
        $existingFelicitationQuery = "SELECT DiemThuong as existing FROM Profile WHERE EmpID = ?";
        $stmt = $conn->prepare($existingFelicitationQuery);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $existingFelicitation = $result->fetch_assoc()['existing']?? 0;
    
        $exchangeFelicitationQuery = "SELECT SUM(Point) AS exchange
                                        FROM Felicitation
                                        WHERE VoucherID IS NOT NULL
                                        AND NguoiNhan = ?";
        $stmt = $conn->prepare($exchangeFelicitationQuery);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $exchangeFelicitation = $result->fetch_assoc()['exchange']?? 0;

        $deductedFelicitationQuery = "SELECT SUM(Point) AS deducted
                                    FROM Felicitation 
                                    WHERE NguoiNhan = ? AND Point<0";
        $stmt = $conn->prepare($deductedFelicitationQuery);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $deductedFelicitation = $result->fetch_assoc()['deducted']?? 0;

        $availableVoucher = "SELECT COUNT(*) AS cvoucher
                                FROM Felicitation
                                WHERE NguoiNhan = ? AND VoucherID IS NOT NULL";
        $stmt = $conn->prepare($availableVoucher);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $availableVoucher = $result->fetch_assoc()['cvoucher']?? 0;

        $usedVoucher = "SELECT COUNT(*) AS uvoucher
                        FROM Voucher
                        WHERE VoucherID IN (
                            SELECT VoucherID
                            FROM Felicitation
                            WHERE NguoiNhan = ? AND VoucherID IS NOT NULL
                        ) AND TinhTrang = 'Đã dùng'";
        $stmt = $conn->prepare($usedVoucher);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $usedVoucher = $result->fetch_assoc()['uvoucher']?? 0;

        $stmt->close();
        $db->close();
        return [
            'total' => $totalFelicitation,
            'existing' => $existingFelicitation,
            'exchange' => $exchangeFelicitation,
            'deducted' => $deductedFelicitation,
            'cvoucher' => $availableVoucher,
            'uvoucher' => $usedVoucher
        ];
    }

    
    public static function getFelicitationCountsByEmpID_QL($user_id) {
        $db = new Database();
        $conn = $db->connect();
        
        $totalFelicitationQuery = "SELECT SUM(Point) as total FROM Felicitation WHERE NguoiNhan =? AND Point>0";
        $stmt = $conn->prepare($totalFelicitationQuery);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $totalFelicitation = $result->fetch_assoc()['total'];
        
    
        $existingFelicitationQuery = "SELECT DiemThuong as existing FROM Profile WHERE EmpID = ?";
        $stmt = $conn->prepare($existingFelicitationQuery);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $existingFelicitation = $result->fetch_assoc()['existing']?? 0;

        $exchange_QLFelicitationQuery = "SELECT SUM(Point) AS exchange_ql
                                            FROM Felicitation
                                            WHERE VoucherID IS NOT NULL
                                            AND NguoiNhan = ?";
        $stmt = $conn->prepare($exchange_QLFelicitationQuery);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $exchange_QLFelicitation = $result->fetch_assoc()['exchange_ql']?? 0;

        $deductedPointsAsGiverQuery = "SELECT SUM(f.Point) AS deducted
                                    FROM Felicitation f
                                    WHERE f.NguoiTang = ?";

        $stmt = $conn->prepare($deductedPointsAsGiverQuery);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $deductedPointsAsGiver = $result->fetch_assoc()['deducted'] ?? 0;
        $deductedPointsAsReceiverQuery = "SELECT SUM(f.Point) AS deducted
                                  FROM Felicitation f
                                  WHERE f.NguoiNhan = ? AND f.Point < 0";

        $stmt = $conn->prepare($deductedPointsAsReceiverQuery);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $deductedPointsAsReceiver = $result->fetch_assoc()['deducted'] ?? 0;
        // Tổng hợp điểm bị trừ từ cả hai loại giao dịch
        $deductedFelicitation = -($deductedPointsAsGiver ?? 0) + ($deductedPointsAsReceiver ?? 0);
      
        $availableVoucher = "SELECT COUNT(*) AS cvoucher
                                FROM Felicitation
                                WHERE NguoiNhan = ? AND VoucherID IS NOT NULL";
        $stmt = $conn->prepare($availableVoucher);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $availableVoucher = $result->fetch_assoc()['cvoucher']?? 0;

        $usedVoucher = "SELECT COUNT(*) AS uvoucher
                        FROM Voucher
                        WHERE VoucherID IN (
                            SELECT VoucherID
                            FROM Felicitation
                            WHERE NguoiNhan = ? AND VoucherID IS NOT NULL
                        ) AND TinhTrang = 'Đã dùng'";
        $stmt = $conn->prepare($usedVoucher);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $usedVoucher = $result->fetch_assoc()['uvoucher']?? 0;
        $stmt->close();
        $db->close();
        return [
            'total' => $totalFelicitation,
            'existing' => $existingFelicitation,
            'exchange_ql' => $exchange_QLFelicitation,
            'deducted' => $deductedFelicitation,
            'cvoucher' => $availableVoucher,
            'uvoucher' => $usedVoucher
        ];
    }

    public static function getHistoryRequestsByEmpID($user_id, $limit, $offset) {
        $db = new Database();
        $conn = $db->connect();
    
        $query = "SELECT f.FelicitationID, 
                        f.Point AS FelicitationPoint, 
                        f.NoiDung AS FelicitationNoiDung, 
                        f.Date,
                        p.HoTen AS FelicitationNguoiTang
                    FROM Felicitation f
                    LEFT JOIN Profile p ON f.NguoiTang = p.EmpID  -- Kết nối với Profile để lấy thông tin của NguoiTang
                    WHERE f.NguoiNhan = ?
                    ORDER BY f.Date DESC
                    LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('iii', $user_id, $limit, $offset);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $requests = $result->fetch_all(MYSQLI_ASSOC);
    
        $stmt->close();
        $db->close();
        return $requests;
    }

    public static function getHistoryRequestsByEmpID_QL($user_id, $limit, $offset) {
        $db = new Database();
        $conn = $db->connect();
    
        $query = "SELECT f.FelicitationID, 
                         f.Point AS FelicitationPoint, 
                         f.NoiDung AS FelicitationNoiDung, 
                         f.Date,
                         CASE 
                            WHEN f.NguoiTang IS NULL THEN NULL
                            WHEN f.NguoiNhan = ? THEN NULL
                            ELSE p.HoTen 
                         END AS FelicitationNguoiNhan,
                         CASE 
                            WHEN f.NguoiTang = ? THEN 'donor'
                            ELSE 'receiver'
                         END AS FelicitationRole
                  FROM Felicitation f
                  LEFT JOIN Profile p ON f.NguoiNhan = p.EmpID
                  WHERE f.NguoiNhan = ? OR f.NguoiTang = ?
                  ORDER BY f.Date DESC
                  LIMIT ? OFFSET ?";
    
        $stmt = $conn->prepare($query);
        $stmt->bind_param('iiiiii', $user_id, $user_id, $user_id, $user_id, $limit, $offset);
        $stmt->execute();
    
        $result = $stmt->get_result();
        $requests = $result->fetch_all(MYSQLI_ASSOC);
    
        $stmt->close();
        $db->close();
        return $requests;
    }
    
    

    public static function countFelicitationRequests($user_id) {
        $db = new Database();
        $conn = $db->connect();

        $query = "SELECT COUNT(FelicitationID) as total FROM Felicitation WHERE NguoiNhan = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $total = $result->fetch_assoc()['total'];

        $stmt->close();
        $db->close();
        return $total;
    }

    public static function countFelicitationRequests_QL($user_id) {
        $db = new Database();
        $conn = $db->connect();

        $query = "SELECT COUNT(FelicitationID) as total FROM Felicitation WHERE NguoiNhan = ? or NguoiTang =?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ii', $user_id, $user_id);
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
    
    
}
?>
