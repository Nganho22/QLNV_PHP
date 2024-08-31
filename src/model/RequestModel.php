<?php
require_once __DIR__ . '/../config/MySQLconnect.php';

class RequestModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();   
    }

    public static function getRequestCountsByEmpID($user_id) {
        $db = new Database();
        $conn = $db->connect();
        
        $totalRequestsQuery = "SELECT COUNT(RequestID) as total FROM Request WHERE EmpID = ?";
        $stmt = $conn->prepare($totalRequestsQuery);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $totalRequests = $result->fetch_assoc()['total'];
    
        $pendingRequestsQuery = "SELECT COUNT(RequestID) as pending FROM Request WHERE EmpID = ? AND TrangThai = 0";
        $stmt = $conn->prepare($pendingRequestsQuery);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $pendingRequests = $result->fetch_assoc()['pending'];
    
        $approvedRequestsQuery = "SELECT COUNT(RequestID) as approved FROM Request WHERE EmpID = ? AND TrangThai != 0";
        $stmt = $conn->prepare($approvedRequestsQuery);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $approvedRequests = $result->fetch_assoc()['approved'];

        $stmt->close();
        $db->close();
        return [
            'total' => $totalRequests,
            'pending' => $pendingRequests,
            'approved' => $approvedRequests
        ];
    }

    public static function getPendingRequestsByEmpID($user_id, $limit, $offset) {
        $db = new Database();
        $conn = $db->connect();

        $query = "SELECT * FROM Request WHERE EmpID = ? AND TrangThai = 0 ORDER BY NgayGui DESC LIMIT ? OFFSET ?";
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

        $query = "SELECT * FROM Request WHERE EmpID = ? AND TrangThai != 0 ORDER BY NgayGui DESC LIMIT ? OFFSET ?";
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

        $query = "SELECT COUNT(RequestID) as total FROM Request WHERE EmpID = ? AND TrangThai = 0";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $total = $result->fetch_assoc()['total'];

        $stmt->close();
        $db->close();
        return $total;
    }

    public static function countApprovedRequests($user_id) {
        $db = new Database();
        $conn = $db->connect();

        $query = "SELECT COUNT(RequestID) as total FROM Request WHERE EmpID = ? AND TrangThai != 0";
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

        $query = "SELECT * FROM Time_sheet WHERE EmpID = ? AND TrangThai = 'Chưa hoàn thành'";
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
    
    public static function createRequest($user_id, $nguoiGui, $loai, $tieuDe, $ngayGui, $ngayChon, $noiDung) {
        $db = new Database();
        $conn = $db->connect();

        $query = "INSERT INTO Request (EmpID, NguoiGui, Loai, TieuDe, NgayGui, NgayChon ,NoiDung) VALUES (?,?,?,?,?,?,?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('issssss', $user_id, $nguoiGui, $loai, $tieuDe, $ngayGui, $ngayChon, $noiDung);
        $result = $stmt->execute();
        
        $stmt->close();
        $db->close();
        return $result;
    }

    public static function createTimeSheetRequest($user_id, $nguoiGui, $loai, $tieuDe, $ngayGui, $noiDung, $timeSheetID, $trangThai, $newUpThoiGianTimesheet) {
        $db = new Database();
        $conn = $db->connect();

        $query = "INSERT INTO Request (EmpID, NguoiGui, Loai, TieuDe, NgayGui, NoiDung, Time_sheetID, Up_TinhTrang_Timesheet, Up_ThoiGian_Timesheet)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('isssssiss', $user_id, $nguoiGui, $loai, $tieuDe, $ngayGui, $noiDung, $timeSheetID, $trangThai, $newUpThoiGianTimesheet);
        $result = $stmt->execute();

        $stmt->close();
        $db->close();
        return $result;
    }

    public static function getDetailRequest($RequestID) {
        $db = new Database();
        $conn = $db->connect();
    
        $query = "SELECT * FROM Request WHERE RequestID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $RequestID);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $requests = $result->fetch_assoc();
    
        $stmt->close();
        $db->close();
        return $requests;
    }

//============== Quản lý =====================    
    private static function getEmpIDsAndPhongID($user_id) {
        $db = new Database();
        $conn = $db->connect();

        // 1. Lấy PhongID và danh sách EmpID từ Profile dựa trên EmpID
        $query = "
            SELECT p1.PhongID, p2.EmpID
            FROM Profile p1
            LEFT JOIN Profile p2 ON p1.PhongID = p2.PhongID
            WHERE p1.EmpID = ?";

        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $phongID = null;
        $empIDs = [];

        while ($row = $result->fetch_assoc()) {
            if ($phongID === null) {
                $phongID = $row['PhongID'];
            }
            $empIDs[] = $row['EmpID'];
        }

        $stmt->close();
        $db->close();

        return ['phongID' => $phongID, 'empIDs' => $empIDs];
    }

    public static function getRequestCountsByEmpID_QL($user_id) {
        $data = self::getEmpIDsAndPhongID($user_id);
        $phongID = $data['phongID'];
        $empIDs = $data['empIDs'];

        if (!$phongID) {
            return ['total' => 0, 'pending' => 0, 'approved' => 0];
        }

        if (empty($empIDs)) {
            return ['total' => 0, 'pending' => 0, 'approved' => 0];
        }

        $empIDsString = implode(',', array_fill(0, count($empIDs), '?'));

        $query = "
            SELECT
                COUNT(RequestID) as total,
                SUM(CASE WHEN TrangThai = 0 THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN TrangThai IN (1, 2) THEN 1 ELSE 0 END) as approved
            FROM Request
            WHERE EmpID IN ($empIDsString)";

        $db = new Database();
        $conn = $db->connect();
        $stmt = $conn->prepare($query);
        $stmt->bind_param(str_repeat('i', count($empIDs)), ...$empIDs);
        $stmt->execute();
        $result = $stmt->get_result();
        $counts = $result->fetch_assoc();

        $stmt->close();
        $db->close();

        return [
            'total' => $counts['total'],
            'pending' => $counts['pending'],
            'approved' => $counts['approved']
        ];
    }

    public static function getRequestsByEmpID_QL($user_id) {
        $data = self::getEmpIDsAndPhongID($user_id);
        $phongID = $data['phongID'];
        $empIDs = $data['empIDs'];

        if (!$phongID) {
            return [];
        }

        if (empty($empIDs)) {
            return [];
        }

        $empIDsString = implode(',', array_fill(0, count($empIDs), '?'));

        $query = "
            SELECT * 
            FROM Request
            WHERE EmpID IN ($empIDsString)";

        $db = new Database();
        $conn = $db->connect();
        $stmt = $conn->prepare($query);
        $stmt->bind_param(str_repeat('i', count($empIDs)), ...$empIDs);
        $stmt->execute();
        $result = $stmt->get_result();

        $requests = [];
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }

        $stmt->close();
        $db->close();

        return $requests;
    }

    public static function searchRequestsByEmpID_QL($user_id, $searchTerm, $limit, $offset) {
        $data = self::getEmpIDsAndPhongID($user_id);
        $phongID = $data['phongID'];
        $empIDs = $data['empIDs'];

        if (!$phongID) {
            return [];
        }

        if (empty($empIDs)) {
            return [];
        }

        $empIDsString = implode(',', array_fill(0, count($empIDs), '?'));

        $searchTerm = "%$searchTerm%";
        $query = "
            SELECT RequestID, EmpID, TieuDe, Loai, NguoiGui, NgayGui, TrangThai
            FROM Request
            WHERE EmpID IN ($empIDsString) AND NguoiGui LIKE ? 
            LIMIT ? OFFSET ?";

        $db = new Database();
        $conn = $db->connect();
        $stmt = $conn->prepare($query);
        $params = array_merge($empIDs, [$searchTerm, $limit, $offset]);
        $stmt->bind_param(str_repeat('i', count($empIDs)) . 'sii', ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        $requests = [];
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }

        $stmt->close();
        $db->close();

        return $requests;
    }

    public static function countSearchRequests_QL($user_id, $searchTerm) {
        $data = self::getEmpIDsAndPhongID($user_id);
        $phongID = $data['phongID'];
        $empIDs = $data['empIDs'];

        if (!$phongID) {
            return 0;
        }

        if (empty($empIDs)) {
            return 0;
        }

        $empIDsString = implode(',', array_fill(0, count($empIDs), '?'));

        $searchTerm = "%$searchTerm%";
        $query = "
            SELECT COUNT(RequestID) as total
            FROM Request
            WHERE EmpID IN ($empIDsString) AND NguoiGui LIKE ?";

        $db = new Database();
        $conn = $db->connect();
        $stmt = $conn->prepare($query);
        $params = array_merge($empIDs, [$searchTerm]);
        $stmt->bind_param(str_repeat('i', count($empIDs)) . 's', ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $total = $result->fetch_assoc()['total'];

        $stmt->close();
        $db->close();

        return $total;
    }

    public static function filterRequestsByEmpID_QL($user_id, $searchTerm, $types, $statuses, $limit, $offset) {
        $data = self::getEmpIDsAndPhongID($user_id);
        $phongID = $data['phongID'];
        $empIDs = $data['empIDs'];
    
        if (!$phongID || empty($empIDs)) {
            return [];
        }
    
        $empIDsString = implode(',', array_fill(0, count($empIDs), '?'));
    
        // Tạo chuỗi placeholders cho các loại và trạng thái
        $typesPlaceholders = count($types) > 0 ? implode(',', array_fill(0, count($types), '?')) : '';
        $statusesPlaceholders = count($statuses) > 0 ? implode(',', array_fill(0, count($statuses), '?')) : '';
        $searchTerm = "%$searchTerm%";
        // Xây dựng câu truy vấn SQL
        $query = "
            SELECT RequestID, EmpID, TieuDe, Loai, NguoiGui, NgayGui, TrangThai
            FROM Request
            WHERE EmpID IN ($empIDsString)
            AND NguoiGui LIKE ?
            " . (count($types) > 0 ? "AND Loai IN ($typesPlaceholders)" : "") . "
            " . (count($statuses) > 0 ? "AND TrangThai IN ($statusesPlaceholders)" : "") . "
            LIMIT ? OFFSET ?";
    
        $db = new Database();
        $conn = $db->connect();
        $stmt = $conn->prepare($query);
    
        // Kết hợp tất cả tham số
        $params = array_merge(
            $empIDs,                    // EmpID
            [$searchTerm],             // searchTerm
            $types,                     // types (Loại)
            $statuses,                  // statuses (TrangThai)
            [$limit, $offset]           // LIMIT và OFFSET
        );
    
        // Tạo chuỗi kiểu cho bind_param
        $typesStr = str_repeat('i', count($empIDs)) // int cho các EmpID
                  . 's'                       // string cho searchTerm
                  . (count($types) > 0 ? str_repeat('s', count($types)) : '') // string cho types
                  . (count($statuses) > 0 ? str_repeat('i', count($statuses)) : '') // int cho statuses
                  . 'ii';                     // int cho LIMIT và OFFSET
    
        $stmt->bind_param($typesStr, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        $requests = [];
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }

        $stmt->close();
        $db->close();
    
        return $requests;
    }
    
    public static function countFilterRequests_QL($user_id, $searchTerm, $types, $statuses) {
        $data = self::getEmpIDsAndPhongID($user_id);
        $phongID = $data['phongID'];
        $empIDs = $data['empIDs'];
    
        if (!$phongID || empty($empIDs)) {
            return 0;
        }
    
        $empIDsString = implode(',', array_fill(0, count($empIDs), '?'));
    
        // Tạo chuỗi placeholders cho các loại và trạng thái
        $typesPlaceholders = count($types) > 0 ? implode(',', array_fill(0, count($types), '?')) : '';
        $statusesPlaceholders = count($statuses) > 0 ? implode(',', array_fill(0, count($statuses), '?')) : '';
        $searchTerm = "%$searchTerm%";

        // Xây dựng câu truy vấn SQL
        $query = "
            SELECT COUNT(RequestID) as total
            FROM Request
            WHERE EmpID IN ($empIDsString)
            AND NguoiGui LIKE ?
            " . (count($types) > 0 ? "AND Loai IN ($typesPlaceholders)" : "") . "
            " . (count($statuses) > 0 ? "AND TrangThai IN ($statusesPlaceholders)" : "") . "";
    
        $db = new Database();
        $conn = $db->connect();
        $stmt = $conn->prepare($query);
    
        // Kết hợp tất cả tham số
        $params = array_merge(
            $empIDs,                    // EmpID
            [$searchTerm],             // searchTerm
            $types,                     // types (Loại)
            $statuses                   // statuses (TrangThai)
        );
    
        // Tạo chuỗi kiểu cho bind_param
        $typesStr = str_repeat('i', count($empIDs)) // int cho các EmpID
                  . 's'                       // string cho searchTerm
                  . (count($types) > 0 ? str_repeat('s', count($types)) : '') // string cho types
                  . (count($statuses) > 0 ? str_repeat('i', count($statuses)) : ''); // int cho statuses
    
        $stmt->bind_param($typesStr, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $total = $result->fetch_assoc()['total'];
    
        $stmt->close();
        $db->close();
    
        return $total;
    }
    
    public static function updateRequest($requestID, $ngayXuLy, $trangThai, $noiDung) {
        $db = new Database();
        $conn = $db->connect();
    
        $query = "UPDATE Request SET NgayXuLy = ?, TrangThai = ?, PhanHoi = ? WHERE RequestID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('sssi', $ngayXuLy, $trangThai, $noiDung, $requestID);
        $result = $stmt->execute();
        
        $stmt->close();
        $db->close();
        return $result;
    }

    public static function insertCheckOK($empID, $ngayChon, $opt_WFH, $opt_Nghi) {
        $db = new Database();
        $conn = $db->connect();

        $query = "INSERT INTO Check_inout (EmpID, Date_checkin, WorkFromHome, Nghi) VALUES (?,?,?,?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('isii', $empID, $ngayChon, $opt_WFH, $opt_Nghi);
        $result = $stmt->execute();
        
        $stmt->close();
        $db->close();
        return $result;
    }

    public static function  updateTimeSheet($timeSheetID, $Up_TinhTrang_TS, $up_ThoiGian_TS) {
        $db = new Database();
        $conn = $db->connect();
    
        $query = "UPDATE Time_sheet SET TrangThai = ?, SoGioThucHien = ? WHERE Time_sheetID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('sii', $Up_TinhTrang_TS, $up_ThoiGian_TS, $timeSheetID);
        $result = $stmt->execute();
        
        $stmt->close();
        $db->close();
        return $result;
    }

    public static function  updateProfile($user_id) {
        $db = new Database();
        $conn = $db->connect();
    
        $query = "UPDATE Profile SET TinhTrang = 0 WHERE EmpID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        $result = $stmt->execute();
        
        $stmt->close();
        $db->close();
        return $result;
    }
}
?>
