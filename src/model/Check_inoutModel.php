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
                                    WHERE empid = ?
                                    AND late = 1";
        $stmt = $conn->prepare($countLateQuery);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $countLate = $result->fetch_assoc()['countLate'];
    
        $absenceQuery = "SELECT COUNT(*) AS absence
                                    FROM Check_inout
                                    WHERE empid = ?
                                    AND nghi = 1";
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
    
        $stmt = $conn->prepare("SELECT Time_sheet.tenduan, Time_sheet.hanchot 
                                FROM Time_sheet
                                WHERE Time_sheet.empid = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $deadlines = [];
        while ($row = $result->fetch_assoc()) {
            $deadlines[] = [
                'TenDuAn' => $row['tenduan'],
                'HanChot' => $row['hanchot']
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

    public static function getCheckInOutData($user_id) {
        $db = new Database();
        $conn = $db->connect();
        
        $query = "SELECT *
                  FROM Check_inout
                  WHERE empid = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($checkinoutData = $result->fetch_assoc()) {
            $CheckInOut['STT'] =  $checkinoutData['stt'];
            $CheckInOut['Time_checkin'] =  $checkinoutData['timecheckin'];
            $CheckInOut['Time_checkout'] =  $checkinoutData['timecheckout'];
            $CheckInOut['WorkFromHome'] =  $checkinoutData['workfromhome'];
            $CheckInOut['Nghi'] =  $checkinoutData['nghi'];
            $CheckInOut['Late'] =  $checkinoutData['late'];
            $CheckInOut['Overtime'] =  $checkinoutData['overtime'];

            $data[]=$CheckInOut;
        }
        
        $stmt->close();
        $db->close();
        return $data;
    }
    

    public static function getFelicitationCountsByEmpID($user_id) {
        $db = new Database();
        $conn = $db->connect();
        
        // Lấy PhongID của người dùng hiện tại
        $stmt = $conn->prepare("SELECT phongid FROM Profile WHERE EmpID = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $phongID = $result->fetch_assoc()['phongid'];
        $stmt->close();
        
        if (!$phongID) {
            $db->close();
            return 0;
        }
    
        $stmt = $conn->prepare(
            "SELECT COUNT(*) AS total
            FROM Check_inout
            INNER JOIN Profile ON Check_inout.empid = Profile.empid
            WHERE Profile.phongid = ?"
        );
        $stmt->bind_param("s", $phongID);
        $stmt->execute();
        $result = $stmt->get_result();
        $totalCheckInOut = $result->fetch_assoc()['total'] ?? 0;

        $stmt = $conn->prepare(
            "SELECT COUNT(*) AS ontime
            FROM Check_inout
            INNER JOIN Profile ON Check_inout.empid = Profile.empid
            WHERE Profile.phongid = ?
            AND Check_inout.late = 0"
        );
        $stmt->bind_param("s", $phongID);
        $stmt->execute();
        $result = $stmt->get_result();
        $ontimeCheckInOut = $result->fetch_assoc()['ontime'] ?? 0;

        $stmt = $conn->prepare(
            "SELECT COUNT(*) AS late
            FROM Check_inout
            INNER JOIN Profile ON Check_inout.empid = Profile.empid
            WHERE Profile.phongid = ?
            AND Check_inout.late = 1"
        );
        $stmt->bind_param("s", $phongID);
        $stmt->execute();
        $result = $stmt->get_result();
        $lateCheckInOut = $result->fetch_assoc()['late'] ?? 0;

        $stmt = $conn->prepare(
            "SELECT COUNT(*) AS checkout
            FROM Check_inout
            INNER JOIN Profile ON Check_inout.empid = Profile.empid
            WHERE Profile.phongid = ?
            AND Check_inout.time_checkout IS NOT NULL"
        );
        $stmt->bind_param("s", $phongID);
        $stmt->execute();
        $result = $stmt->get_result();
        $cCheckInOut = $result->fetch_assoc()['checkout'] ?? 0;
    
        $deductedFelicitationQuery = "SELECT SUM(point) AS deducted
                                    FROM Felicitation 
                                    WHERE nguoinhan = ? AND point<0";
        $stmt = $conn->prepare($deductedFelicitationQuery);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $deductedFelicitation = $result->fetch_assoc()['deducted']?? 0;
    
        $availableVoucher = "SELECT COUNT(*) AS cvoucher
                                FROM Felicitation
                                WHERE nguoinhan = ? AND voucherid IS NOT NULL";
        $stmt = $conn->prepare($availableVoucher);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $availableVoucher = $result->fetch_assoc()['cvoucher']?? 0;
    
        $usedVoucher = "SELECT COUNT(*) AS uvoucher
                        FROM Voucher
                        WHERE voucherid IN (
                            SELECT voucherid
                            FROM Felicitation
                            WHERE nguoinhan = ? AND voucherid IS NOT NULL
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
        $stmt = $conn->prepare("SELECT phongid FROM Profile WHERE empid = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $phongID = $result->fetch_assoc()['phongid'];
        $stmt->close();
        
        if (!$phongID) {
            $db->close();
            return 0;
        }
    
        $query = "SELECT c.date_checkin,
                         p.hoten AS NhanVien,
                         c.time_checkin AS GioCheckIn,
                         c.time_checkout AS GioCheckOut,
                         CASE
                             WHEN c.late = 1 THEN 'Trễ'
                             WHEN c.nghi = 1 THEN 'Nghỉ'
                             WHEN c.workfromhome = 1 THEN 'Làm việc tại nhà'
                             WHEN c.late = 0 AND c.nghi = 0 AND c.workfromhome = 0 THEN 'Không có'
                            ELSE ''
                         END AS Note,
                         CASE
                             WHEN c.time_checkin IS NOT NULL AND c.time_checkout IS NULL THEN 'Đã check-in'
                             WHEN c.time_checkin IS NOT NULL AND c.time_checkout IS NOT NULL THEN 'Đã check-out'
                             WHEN c.time_checkin IS NULL AND c.time_checkout IS NULL THEN 'Không có'
                             ELSE 'Chưa check-in'
                         END AS statusCheck
                  FROM Check_inout c
                  JOIN Profile p ON c.empid = p.empid
                  WHERE p.phongid = ?
                  ORDER BY c.date_checkin DESC
                  LIMIT ? OFFSET ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sii", $phongID, $limit, $offset);
        $stmt->execute();
    
        $result = $stmt->get_result();
        $data = [];
        while ($checkinoutData = $result->fetch_assoc()) {
            $CheckInOut['ThoiGian'] =  $checkinoutData['date_checkin'];
            $CheckInOut['NhanVien'] =  $checkinoutData['hoten'];
            $CheckInOut['GioCheckOut'] =  $checkinoutData['timecheckout'];
            $CheckInOut['GioCheckIn'] =  $checkinoutData['time_checkin'];
            $CheckInOut['Note'] =  $checkinoutData['Note'];
            $CheckInOut['statusCheck'] =  $checkinoutData['statusCheck'];

            $data[]=$CheckInOut;
        }

    
        $stmt->close();
        $db->close();
        return $data;
    }
    
    
    public static function countFelicitationRequests($user_id) {
        $db = new Database();
        $conn = $db->connect();

        // Lấy PhongID của người dùng hiện tại
        $stmt = $conn->prepare("SELECT phongid FROM Profile WHERE empid = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $phongID = $result->fetch_assoc()['phongid'];
        $stmt->close();
        
        if (!$phongID) {
            $db->close();
            return 0;
        }
    
        $stmt = $conn->prepare(
            "SELECT COUNT(*) AS total
            FROM Check_inout
            INNER JOIN Profile ON Check_inout.empid = Profile.empid
            WHERE Profile.phongid = ?"
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
    
}
?>