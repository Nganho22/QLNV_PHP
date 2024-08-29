<?php
require_once __DIR__ . '/../config/connect.php';

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
    
        $approvedRequestsQuery = "SELECT COUNT(RequestID) as approved FROM Request WHERE EmpID = ? AND TrangThai = 1";
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

        $query = "SELECT * FROM Request WHERE EmpID = ? AND TrangThai = 1 ORDER BY NgayGui DESC LIMIT ? OFFSET ?";
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

        $query = "SELECT COUNT(RequestID) as total FROM Request WHERE EmpID = ? AND TrangThai = 1";
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
    
    public static function createRequest($user_id, $nguoiGui, $loai, $tieuDe, $ngayGui, $noiDung) {
        $db = new Database();
        $conn = $db->connect();

        $query = "INSERT INTO Request (EmpID, NguoiGui, Loai, TieuDe, NgayGui, NoiDung) VALUES (?,?,?,?,?,?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('isssss', $user_id, $nguoiGui, $loai, $tieuDe, $ngayGui, $noiDung);
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
    public static function getRequestCountsByEmpID_QL($user_id) {
        $db = new Database();
        $conn = $db->connect();

        // 1. Lấy PhongID từ Profile dựa trên EmpID
        $getPhongIDQuery = "SELECT PhongID FROM Profile WHERE EmpID = ?";
        $stmt = $conn->prepare($getPhongIDQuery);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $phongID = $result->fetch_assoc()['PhongID'];

        if (!$phongID) {
            // Không tìm thấy PhongID, trả về kết quả mặc định
            return [
                'total' => 0,
                'pending' => 0,
                'approved' => 0
            ];
        }

        // 2. Lấy danh sách EmpID từ Profile dựa trên PhongID
        $getEmpIDsQuery = "SELECT EmpID FROM Profile WHERE PhongID = ?";
        $stmt = $conn->prepare($getEmpIDsQuery);
        $stmt->bind_param('s', $phongID);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $empIDs = [];
        while ($row = $result->fetch_assoc()) {
            $empIDs[] = $row['EmpID'];
        }

        if (empty($empIDs)) {
            // Không tìm thấy EmpID nào, trả về kết quả mặc định
            return [
                'total' => 0,
                'pending' => 0,
                'approved' => 0
            ];
        }

        // 3. Tạo danh sách EmpID dạng chuỗi cho truy vấn IN
        $empIDsString = implode(',', array_fill(0, count($empIDs), '?'));

        // 4. Tính tổng số RequestID, RequestID với TrangThai = 0, TrangThai = 1
        $totalRequestsQuery = "
            SELECT
                COUNT(RequestID) as total,
                SUM(CASE WHEN TrangThai = 0 THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN TrangThai = 1 THEN 1 ELSE 0 END) as approved
            FROM Request
            WHERE EmpID IN ($empIDsString)";

        $stmt = $conn->prepare($totalRequestsQuery);
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
        $db = new Database();
        $conn = $db->connect();
    
        // 1. Lấy PhongID từ Profile dựa trên EmpID
        $getPhongIDQuery = "SELECT PhongID FROM Profile WHERE EmpID = ?";
        $stmt = $conn->prepare($getPhongIDQuery);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $phongID = $result->fetch_assoc()['PhongID'];
    
        if (!$phongID) {
            // Không tìm thấy PhongID, trả về một mảng rỗng
            return [];
        }
    
        // 2. Lấy danh sách EmpID từ Profile dựa trên PhongID
        $getEmpIDsQuery = "SELECT EmpID FROM Profile WHERE PhongID = ?";
        $stmt = $conn->prepare($getEmpIDsQuery);
        $stmt->bind_param('s', $phongID);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $empIDs = [];
        while ($row = $result->fetch_assoc()) {
            $empIDs[] = $row['EmpID'];
        }
    
        if (empty($empIDs)) {
            // Không tìm thấy EmpID nào, trả về một mảng rỗng
            return [];
        }
    
        // 3. Tạo danh sách EmpID dạng chuỗi cho truy vấn IN
        $empIDsString = implode(',', array_fill(0, count($empIDs), '?'));
    
        // 4. Lấy danh sách RequestID, EmpID, và TrangThai với điều kiện EmpID trong danh sách EmpID
        $getRequestsQuery = "
            SELECT RequestID, EmpID, TrangThai
            FROM Request
            WHERE EmpID IN ($empIDsString)";
    
        $stmt = $conn->prepare($getRequestsQuery);
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
}
?>
