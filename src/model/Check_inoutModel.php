<?php
require_once __DIR__ . '/../config/MySQLconnect.php';

class Check_inoutModel {
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
    public static function getCheck_inoutByEmpID($user_id) {
        $db = new Database();
        $conn = $db->connect();
        
        $countLateQuery = "SELECT COUNT(*) AS countLate
                                    FROM Check_inout
                                    WHERE EmpID = ?
                                    AND Late = 1";
        $stmt = $conn->prepare($countLateQuery);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $countLate = $result->fetch_assoc()['countLate'];
    
        $absenceQuery = "SELECT COUNT(*) AS absence
                                    FROM Check_inout
                                    WHERE EmpID = ?
                                    AND Nghi = 1";
        $stmt = $conn->prepare($absenceQuery);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $absence = $result->fetch_assoc()['absence']?? 0;

        $stmt->close();
        $db->close();
        return [
            'countLate' => $countLate,
            'absence' => $absence
        ];
    }


    public static function getDeadlinesTimesheet($user_id) {
        $db = new Database();
        $conn = $db->connect();
    
        $stmt = $conn->prepare("SELECT Time_sheet.TenDuAn, Time_sheet.HanChot 
                                FROM Time_sheet
                                WHERE Time_sheet.EmpID = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $deadlines = [];
        while ($row = $result->fetch_assoc()) {
            $deadlines[] = [
                'TenDuAn' => $row['TenDuAn'],
                'HanChot' => $row['HanChot']
            ];
        }
    
        $stmt->close();
        $db->close();
        return $deadlines;
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

    public static function getCheckInOutData($user_id) {
        $db = new Database();
        $conn = $db->connect();
        
        $query = "SELECT *
                  FROM Check_inout
                  WHERE EmpID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        $stmt->close();
        $db->close();
        return $data;
    }
    

    public static function getFelicitationCountsByEmpID($user_id) {
        $db = new Database();
        $conn = $db->connect();
        
        // Lấy PhongID của người dùng hiện tại
        $stmt = $conn->prepare("SELECT PhongID FROM Profile WHERE EmpID = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $phongID = $result->fetch_assoc()['PhongID'];
        $stmt->close();
        
        if (!$phongID) {
            $db->close();
            return 0;
        }
    
        $stmt = $conn->prepare(
            "SELECT COUNT(*) AS total
            FROM Check_inout
            INNER JOIN Profile ON Check_inout.EmpID = Profile.EmpID
            WHERE Profile.PhongID = ?"
        );
        $stmt->bind_param("s", $phongID);
        $stmt->execute();
        $result = $stmt->get_result();
        $totalCheckInOut = $result->fetch_assoc()['total'] ?? 0;

        $stmt = $conn->prepare(
            "SELECT COUNT(*) AS ontime
            FROM Check_inout
            INNER JOIN Profile ON Check_inout.EmpID = Profile.EmpID
            WHERE Profile.PhongID = ?
            AND Check_inout.Late = 0"
        );
        $stmt->bind_param("s", $phongID);
        $stmt->execute();
        $result = $stmt->get_result();
        $ontimeCheckInOut = $result->fetch_assoc()['ontime'] ?? 0;

        $stmt = $conn->prepare(
            "SELECT COUNT(*) AS late
            FROM Check_inout
            INNER JOIN Profile ON Check_inout.EmpID = Profile.EmpID
            WHERE Profile.PhongID = ?
            AND Check_inout.Late = 1"
        );
        $stmt->bind_param("s", $phongID);
        $stmt->execute();
        $result = $stmt->get_result();
        $lateCheckInOut = $result->fetch_assoc()['late'] ?? 0;

        $stmt = $conn->prepare(
            "SELECT COUNT(*) AS checkout
            FROM Check_inout
            INNER JOIN Profile ON Check_inout.EmpID = Profile.EmpID
            WHERE Profile.PhongID = ?
            AND Check_inout.Time_checkout IS NOT NULL"
        );
        $stmt->bind_param("s", $phongID);
        $stmt->execute();
        $result = $stmt->get_result();
        $cCheckInOut = $result->fetch_assoc()['checkout'] ?? 0;
    
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
            'total' => $totalCheckInOut,  // Số lần check-in/out
            'ontime' => $ontimeCheckInOut,
            'late' => $lateCheckInOut,
            'checkout' =>$cCheckInOut,
            'deducted' => $deductedFelicitation,
            'cvoucher' => $availableVoucher,
            'uvoucher' => $usedVoucher
        ];
    }
    
    public static function getHistoryRequestsByEmpID($user_id, $limit, $offset) {
        $db = new Database();
        $conn = $db->connect();
    
        // Lấy PhongID của người dùng hiện tại
        $stmt = $conn->prepare("SELECT PhongID FROM Profile WHERE EmpID = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $phongID = $result->fetch_assoc()['PhongID'];
        $stmt->close();
        
        if (!$phongID) {
            $db->close();
            return 0;
        }
    
        $query = "SELECT c.Date_checkin AS ThoiGian,
                         p.HoTen AS NhanVien,
                         c.Time_checkin AS GioCheckIn,
                         c.Time_checkout AS GioCheckOut,
                         CASE
                             WHEN c.Late = 1 THEN 'Trễ'
                             WHEN c.Nghi = 1 THEN 'Nghỉ'
                             WHEN c.WorkFromHome = 1 THEN 'Làm việc tại nhà'
                             WHEN c.Late = 0 AND c.Nghi = 0 AND c.WorkFromHome = 0 THEN 'Không có'
                            ELSE ''
                         END AS Note,
                         CASE
                             WHEN c.Time_checkin IS NOT NULL AND c.Time_checkout IS NULL THEN 'Đã check-in'
                             WHEN c.Time_checkin IS NOT NULL AND c.Time_checkout IS NOT NULL THEN 'Đã check-out'
                             WHEN c.Time_checkin IS NULL AND c.Time_checkout IS NULL THEN 'Không có'
                             ELSE 'Chưa check-in'
                         END AS statusCheck
                  FROM Check_inout c
                  JOIN Profile p ON c.EmpID = p.EmpID
                  WHERE p.PhongID = ?
                  ORDER BY c.Date_checkin DESC
                  LIMIT ? OFFSET ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sii", $phongID, $limit, $offset);
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

        // Lấy PhongID của người dùng hiện tại
        $stmt = $conn->prepare("SELECT PhongID FROM Profile WHERE EmpID = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $phongID = $result->fetch_assoc()['PhongID'];
        $stmt->close();
        
        if (!$phongID) {
            $db->close();
            return 0;
        }
    
        $stmt = $conn->prepare(
            "SELECT COUNT(*) AS total
            FROM Check_inout
            INNER JOIN Profile ON Check_inout.EmpID = Profile.EmpID
            WHERE Profile.PhongID = ?"
        );
        $stmt->bind_param("s", $phongID);
        $stmt->execute();
        $result = $stmt->get_result();
        $total = $result->fetch_assoc()['total'] ?? 0;

        $stmt->close();
        $db->close();
        return $total;

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