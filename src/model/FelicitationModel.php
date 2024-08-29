<?php
require_once __DIR__ . '/../config/connect.php';

class FelicitationModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();   
    }

    public static function getFelicitationCountsByEmpID($user_id) {
        $db = new Database();
        $conn = $db->connect();
        
        $totalFelicitationQuery = "SELECT SUM(Point) as total FROM Felicitation WHERE NguoiNhan = ?";
        $stmt = $conn->prepare($totalFelicitationQuery);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $totalFelicitation = $result->fetch_assoc()['total'];
    
        $pendingFelicitationQuery = "SELECT DiemThuong as pending FROM Profile WHERE EmpID = ?";
        $stmt = $conn->prepare($pendingFelicitationQuery);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $pendingFelicitation = $result->fetch_assoc()['pending'];
    
        $approvedFelicitationQuery = "SELECT SUM(Point) AS changed
                                        FROM Felicitation
                                        WHERE VoucherID IS NOT NULL
                                        AND NguoiNhan = ?
                                        GROUP BY VoucherID
                                        ";
        $stmt = $conn->prepare($approvedFelicitationQuery);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $approvedFelicitation = $result->fetch_assoc()['changed'];

        $deductedFelicitationQuery = "SELECT SUM(f.Point) AS deducted
                                    FROM Felicitation f
                                    WHERE f.NguoiNhan = ? AND f.Point<0";
        $stmt = $conn->prepare($deductedFelicitationQuery);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $deductedFelicitation = $result->fetch_assoc()['deducted'];

        $stmt->close();
        $db->close();
        return [
            'total' => $totalFelicitation,
            'pending' => $pendingFelicitation,
            'changed' => $approvedFelicitation,
            'deducted' => $deductedFelicitation
        ];
    }

    public static function getPendingRequestsByEmpID($user_id, $limit, $offset) {
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
    
    
    public static function getApprovedRequestsByEmpID($user_id, $limit, $offset) {
        $db = new Database();
        $conn = $db->connect();

        $query = "SELECT * FROM Request WHERE EmpID = ? AND TrangThai = 1 LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('iii', $user_id, $limit, $offset);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $requests = $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();
        $db->close();
        return $requests;
    }

    public static function countPendingRequests($user_id) {
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

    public static function getTimeSheetByID($timeSheetID) {
        $db = new Database();
        $conn = $db->connect();
    
        $query = "SELECT * FROM Time_sheet WHERE Time_sheetID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $timeSheetID);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $timeSheet = $result->fetch_assoc();
    
        $stmt->close();
        $db->close();
        return $timeSheet;
    }

    public static function getPoint_Month($user_id) {
        $db = new Database();
        $conn = $db->connect();
    
        $stmt = $conn->prepare("
            SELECT MONTH(Date) AS month, SUM(Point) AS total_points
            FROM Felicitation
            WHERE NguoiNhan = ?
            GROUP BY MONTH(Date)
            ORDER BY MONTH(Date)
        ");
        $stmt->bind_param("i", $user_id);
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
