<?php
require_once __DIR__ . '/../config/MySQLconnect.php';

class FelicitationModel {
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

    //UpdatePoint cho Quản lý
    public static function updateManagerPoints($manager_id, $pointGive) {
        $db = new Database();
        $conn = $db->connect();
        
        // Truy vấn để lấy điểm thưởng hiện tại của quản lý
        $query = "SELECT diemthuong FROM Profile WHERE empid = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $manager_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $currentPoints = $result->fetch_assoc()['diemthuong'] ?? 0;
        
        // Tính điểm thưởng mới
        $newPoints = $currentPoints - $pointGive;

        // Cập nhật điểm thưởng của quản lý
        $updateQuery = "UPDATE Profile SET diemthuong = ? WHERE empid = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param('ii', $newPoints, $manager_id);
        $updateStmt->execute();

        $stmt->close();
        $updateStmt->close();
        $db->close();
    }

    //UpdatePoint cho nhân viên
    public static function updateEmpGivePoints($emp_id, $pointGive) {
        $db = new Database();
        $conn = $db->connect();
        
        // Truy vấn để lấy điểm thưởng hiện tại của quản lý
        $query = "SELECT diemthuong FROM Profile WHERE empid = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $emp_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $currentPoints = $result->fetch_assoc()['diemthuong'] ?? 0;
        
        // Tính điểm thưởng mới
        $newPoints = $currentPoints + $pointGive;

        // Cập nhật điểm thưởng của quản lý
        $updateQuery = "UPDATE Profile SET diemthuong = ? WHERE empid = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param('ii', $newPoints, $emp_id);
        $updateStmt->execute();

        $stmt->close();
        $updateStmt->close();
        $db->close();
    }

    //UpdatePoint cho Felicitation
    public static function addFelicitation($point, $nguoiNhan, $nguoiTang) {
        
        $noiDung = "Nhờ hoàn thành time-sheet";
    
        $db = new Database();
        $conn = $db->connect();
    
        $query = "INSERT INTO Felicitation (point, date, noidung, nguoinhan, nguoitang) 
                  VALUES (?, CURDATE(), ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('isii', $point, $noiDung, $nguoiNhan, $nguoiTang);
    
        $stmt->execute();
        $stmt->close();
        $db->close();
    }

    // Trong FelicitationModel
    public static function getEmployeesByManagerID($user_id) {
        $db = new Database();
        $conn = $db->connect();

        // Truy vấn danh sách nhân viên thuộc phòng ban của người quản lý
        $query = "SELECT empid, hoten FROM Profile WHERE phongid = (
                    SELECT phongid FROM Profile WHERE empid = ?
                )AND empid != ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ii', $user_id,$user_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        //$employees = $result->fetch_all(MYSQLI_ASSOC);

        $employees = [];
        while ($row = $result->fetch_assoc()) {
            $row = [
                'EmpID' => $row['empid'] ?? 'N/A',
                'HoTen' => $row['hoten'] ?? 'N/A',
                'PhongID' => $row['phongid'] ?? 'N/A'
            ];
            $employees[] = $row;
        }

        $stmt->close();
        $db->close();
        return $employees;
    }
    // Trong FelicitationModel
    public static function getEmployeePointsByID($user_id) {
        $db = new Database();
        $conn = $db->connect();
    
        $query = "SELECT diemthuong FROM Profile WHERE empid = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        $points = [];
        if (isset($row['diemthuong'])) {
            $points['DiemThuong'] = $row['diemthuong']; 
        } else {
            $points['DiemThuong'] = 0; 
        }
    
        $stmt->close();
        $db->close();
        
        return $points;
    }
    

    public static function getFelicitationCountsByEmpID($user_id) {
        $db = new Database();
        $conn = $db->connect();
        
        $totalFelicitationQuery = "SELECT SUM(point) as total FROM Felicitation WHERE nguoinhan = ? AND point >0";
        $stmt = $conn->prepare($totalFelicitationQuery);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $totalFelicitation = $result->fetch_assoc()['total'];
    
        $existingFelicitationQuery = "SELECT diemthuong as existing FROM Profile WHERE empid = ?";
        $stmt = $conn->prepare($existingFelicitationQuery);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $existingFelicitation = $result->fetch_assoc()['existing']?? 0;
    
        $exchangeFelicitationQuery = "SELECT SUM(point) AS exchange
                                        FROM Felicitation
                                        WHERE voucherid IS NOT NULL
                                        AND nguoinhan = ?";
        $stmt = $conn->prepare($exchangeFelicitationQuery);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $exchangeFelicitation = $result->fetch_assoc()['exchange']?? 0;

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
                        WHERE tinhtrang = 'Đã dùng'";
        $stmt = $conn->prepare($usedVoucher);
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
        
        $totalFelicitationQuery = "SELECT SUM(point) as total FROM Felicitation WHERE nguoinhan =? AND point>0";
        $stmt = $conn->prepare($totalFelicitationQuery);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $totalFelicitation = $result->fetch_assoc()['total'];
        
    
        $existingFelicitationQuery = "SELECT diemthuong as existing FROM Profile WHERE empid = ?";
        $stmt = $conn->prepare($existingFelicitationQuery);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $existingFelicitation = $result->fetch_assoc()['existing']?? 0;

        $exchange_QLFelicitationQuery = "SELECT SUM(point) AS exchange_ql
                                            FROM Felicitation
                                            WHERE voucherid IS NOT NULL
                                            AND nguoinhan = ?";
        $stmt = $conn->prepare($exchange_QLFelicitationQuery);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $exchange_QLFelicitation = $result->fetch_assoc()['exchange_ql']?? 0;

        $deductedPointsAsGiverQuery = "SELECT SUM(f.point) AS deducted
                                    FROM Felicitation f
                                    WHERE f.nguoitang = ?";

        $stmt = $conn->prepare($deductedPointsAsGiverQuery);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $deductedPointsAsGiver = $result->fetch_assoc()['deducted'] ?? 0;
        $deductedPointsAsReceiverQuery = "SELECT SUM(f.point) AS deducted
                                  FROM Felicitation f
                                  WHERE f.nguoinhan = ? AND f.point < 0";

        $stmt = $conn->prepare($deductedPointsAsReceiverQuery);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $deductedPointsAsReceiver = $result->fetch_assoc()['deducted'] ?? 0;
        // Tổng hợp điểm bị trừ từ cả hai loại giao dịch
        $deductedFelicitation = -($deductedPointsAsGiver ?? 0) + ($deductedPointsAsReceiver ?? 0);
      
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
                        ) AND tinhtrang = 'Đã dùng'";
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
    
        $query = "SELECT f.felicitationid, 
                        f.point, 
                        f.noidung, 
                        f.date,
                        p.hoten AS FelicitationNguoiTang
                    FROM Felicitation f
                    LEFT JOIN Profile p ON f.nguoitang = p.empid  -- Kết nối với Profile để lấy thông tin của NguoiTang
                    WHERE f.nguoinhan = ?
                    ORDER BY f.date DESC
                    LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('iii', $user_id, $limit, $offset);
        $stmt->execute();
        
        $result = $stmt->get_result();
        //$requests = $result->fetch_all(MYSQLI_ASSOC);
    
        $requests = [];
        while ($row = $result->fetch_assoc()) {
            $row = [
                'FelicitationID' => $row['felicitationid'] ?? 'N/A',
                'FelicitationPoint' => $row['point'] ?? 'N/A',
                'FelicitationNoiDung' => $row['noidung'] ?? 'N/A',
                'Date' => $row['date'] ?? 'N/A',
                'FelicitationNguoiTang' => $row['hoten'] ?? 'N/A',
                'NguoiTang' => $row['nguoitang'] ?? 'N/A',
                'EmpID' => $row['empid'] ?? 'N/A',
                'NguoiNhan' => $row['nguoinhan'] ?? 'N/A',
            ];
            $requests[] = $row;
        }

        $stmt->close();
        $db->close();
        return $requests;
    }

    public static function getHistoryRequestsByEmpID_QL($user_id, $limit, $offset) {
        $db = new Database();
        $conn = $db->connect();
    
        $query = "SELECT f.felicitationid, 
                         f.point, 
                         f.noidung, 
                         f.date,
                         CASE 
                            WHEN f.nguoitang IS NULL THEN NULL
                            WHEN f.nguoinhan = ? THEN NULL
                            ELSE p.hoten 
                         END AS FelicitationNguoiNhan,
                         CASE 
                            WHEN f.nguoitang = ? THEN 'donor'
                            ELSE 'receiver'
                         END AS FelicitationRole
                  FROM Felicitation f
                  LEFT JOIN Profile p ON f.nguoinhan = p.empid
                  WHERE f.nguoinhan = ? OR f.nguoitang = ?
                  ORDER BY f.date DESC
                  LIMIT ? OFFSET ?";
    
        $stmt = $conn->prepare($query);
        $stmt->bind_param('iiiiii', $user_id, $user_id, $user_id, $user_id, $limit, $offset);
        $stmt->execute();
    
        $result = $stmt->get_result();
        //$requests = $result->fetch_all(MYSQLI_ASSOC);
        $requests = [];
        while ($row = $result->fetch_assoc()) {
            $row = [
                'FelicitationID' => $row['felicitationid'] ?? 'N/A',
                'FelicitationPoint' => $row['point'] ?? 'N/A',
                'FelicitationNoiDung' => $row['noidung'] ?? 'N/A',
                'Date' => $row['date'] ?? 'N/A',
                'FelicitationNguoiTang' => $row['hoten'] ?? 'N/A',
                'NguoiTang' => $row['nguoitang'] ?? 'N/A',
                'EmpID' => $row['empid'] ?? 'N/A',
                'NguoiNhan' => $row['nguoinhan'] ?? 'N/A',
                'FelicitationRole' => $row['FelicitationRole'] ?? 'N/A',
                'FelicitationNguoiNhan' => $row['FelicitationNguoiNhan'] ?? 'N/A',
            ];
            $requests[] = $row;
        }
    
        $stmt->close();
        $db->close();
        return $requests;
    }
    
    

    public static function countFelicitationRequests($user_id) {
        $db = new Database();
        $conn = $db->connect();

        $query = "SELECT COUNT(felicitationid) as total FROM Felicitation WHERE nguoinhan = ?";
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

        $query = "SELECT COUNT(felicitationid) as total FROM Felicitation WHERE nguoinhan = ? or nguoitang =?";
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
    
    
}
?>
