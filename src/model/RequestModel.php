<?php
require_once __DIR__ . '/../config/MySQLconnect.php';

class RequestModel {
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

    public function getRequestCountsByEmpID($user_id) {
        $url = $this->apiUrl . "/counts/" . $user_id;

        if ($this->isApiAvailable($url)) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode == 200) {
                $result = json_decode($response, true);
                return [
                    'total' => $result['total'],
                    'pending' => $result['pending'],
                    'approved' => $result['approved']
                ];
            } else {
                return [
                    'total' => 0,
                    'pending' => 0,
                    'approved' => 0
                ];
            }
        } else {
            return [
                'total' => 0,
                'pending' => 0,
                'approved' => 0
            ];
        }
    }

    public function getPendingRequestsByEmpID($user_id, $limit, $offset) {
        $url = $this->apiUrl . "/pending?empID=$user_id&page=$offset&size=$limit";

        if (!$this->isApiAvailable($url)) {
            return null;
        }

        $response = file_get_contents($url);
        $results = json_decode($response, true);
        if (is_array($results['content'])) {
            $formattedResults = [];
            foreach ($results['content'] as $result) {
                $formattedResults[] = [
                    'RequestID' => $result['requestID'] ?? 'N/A',
                    'TieuDe' => $result['tieuDe'] ?? 'N/A',
                    'Loai' => $result['loai'] ?? 'N/A',
                    'NgayGui' => $result['ngayGui'] ?? 'N/A',
                    'NgayXuLy' => $result['ngayXuLy'] ?? 'N/A',
                    'TrangThai' => $result['trangThai'] ?? 'N/A'
                ];
            }
        } else {
            return null;
        }
        //print_r($formattedResults);

        return $formattedResults;
    }

    public function countPendingRequests($user_id) {
        $url = $this->apiUrl . "/count/pending?empID=$user_id";

        if (!$this->isApiAvailable($url)) {
            return null;
        }
        
        $response = file_get_contents($url);
        $total = json_decode($response, true);

        return $total;
    }

    public function getApprovedRequestsByEmpID($user_id, $limit, $offset) {
        $url = $this->apiUrl . "/approved?empID=$user_id&page=$offset&size=$limit";

        if (!$this->isApiAvailable($url)) {
            return null;
        }

        $response = file_get_contents($url);
        $results = json_decode($response, true);
        // print_r($results);
        if (isset($results['content']) && is_array($results['content'])) {
            $formattedResults = [];
            foreach ($results['content'] as $result) {
                $formattedResults[] = [
                    'RequestID' => $result['requestID'] ?? 'N/A',
                    'TieuDe' => $result['tieuDe'] ?? 'N/A',
                    'Loai' => $result['loai'] ?? 'N/A',
                    'NgayGui' => $result['ngayGui'] ?? 'N/A',
                    'NgayXuLy' => $result['ngayXuLy'] ?? 'N/A',
                    'TrangThai' => $result['trangThai'] ?? 'N/A'
                ];
            }
            return $formattedResults;
        } else {
            return [];
        }
    }
    
    public function countApprovedRequests($user_id) {
        $url = $this->apiUrl . "/count/approved?empID=$user_id";

        if (!$this->isApiAvailable($url)) {
            return null;
        }
        
        $response = file_get_contents($url);
        $total = json_decode($response, true);

        return (int)$total;
    }

    public static function getTimeSheetsByEmpID($user_id) {
        $db = new Database();
        $conn = $db->connect();

        $query = "SELECT * FROM Time_sheet WHERE empid = ? AND trangthai = 'Chưa hoàn thành'";
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
    
        $query = "SELECT * FROM Time_sheet WHERE time_sheetid = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $timeSheetID);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $timeSheets = $result->fetch_assoc();
    
        $stmt->close();
        $db->close();
        if ($timeSheets) {
            $timeSheet = [
                'tTime_sheetID' => $timeSheets['time_sheetid'],
                'ProjectID' => $timeSheets['projectid'],
                'NgayGiao' => $timeSheets['ngaygiao'],
                'HanChot' => $timeSheets['hanchot'],
                'TenDuAn' => $timeSheets['tenduan'],
                'DiemThuong' => $timeSheets['diemthuong'],
                'SoGioThucHien' => $timeSheets['sogiothuchien'],
                'TrangThai' => $timeSheets['trangthai'],
                'NoiDung' => $timeSheets['noidung']
            ];
            return $timeSheet;
        }
    }
    
    public static function createRequest($user_id, $nguoiGui, $loai, $tieuDe, $ngayGui, $ngayChon, $noiDung) {
        $db = new Database();
        $conn = $db->connect();

        $query = "INSERT INTO Request (empid, nguoigui, loai, tieude, ngaygui, ngaychon ,noidung) VALUES (?,?,?,?,?,?,?)";
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

        $query = "INSERT INTO Request (empid, nguoigui, loai, tieude, ngaygui, noidung, time_sheetid, up_tinhtrang_timesheet, up_thoigian_timesheet)
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
    
        $query = "SELECT * FROM Request WHERE requestid = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $RequestID);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $requests = $result->fetch_assoc();
    
        $stmt->close();
        $db->close();

        if ($requests) {
            $request = [
                'RequestID' => $requests['requestid'],
                'NguoiGui' => $requests['nguoigui'],
                'Loai' => $requests['loai'],
                'NgayGui' => $requests['ngayxuly'],
                'TrangThai' => $requests['trangthai'],
                'NgayXuLy' => $requests['ngayxuly'],
                'Time_sheetID' => $requests['time_sheetid'],
                'TieuDe' => $requests['tieude'],
                'NoiDung' => $requests['noidung'],
                'Up_TinhTrang_Timesheet' => $requests['up_tinhtrang_timesheet'],
                'Up_ThoiGian_Timesheet' => $requests['up_thoigian_timesheet']
            ];
            return $request;
        }
    }

//============== Quản lý =====================    
    private static function getEmpIDsAndPhongID($user_id) {
        $db = new Database();
        $conn = $db->connect();

        // 1. Lấy PhongID và danh sách EmpID từ Profile dựa trên EmpID
        $query = "
            SELECT p1.phongid, p2.empid
            FROM Profile p1
            LEFT JOIN Profile p2 ON p1.phongid = p2.phongid
            WHERE p1.empid = ?";

        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $phongID = null;
        $empIDs = [];

        while ($row = $result->fetch_assoc()) {
            if ($phongID === null) {
                $phongID = $row['phongid'];
            }
            $empIDs[] = $row['empid'];
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
                COUNT(requestid) as total,
                SUM(CASE WHEN trangthai = 0 THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN trangthai IN (1, 2) THEN 1 ELSE 0 END) as approved
            FROM Request
            WHERE empid IN ($empIDsString)";

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
            WHERE empid IN ($empIDsString) ORDER BY ngaygui DESC";

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

        $formattedResults = [];
        foreach ($requests['content'] as $result) {
            $formattedResults[] = [
                'RequestID' => $result['requestid'] ?? 'N/A',
                'TieuDe' => $result['tieude'] ?? 'N/A',
                'Loai' => $result['loai'] ?? 'N/A',
                'NgayGui' => $result['ngayGui'] ?? 'N/A',
                'NgayXuLy' => $result['ngayXuLy'] ?? 'N/A',
                'TrangThai' => $result['trangThai'] ?? 'N/A'
            ];
        }
        
        return $formattedResults;
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
            ORDER BY NgayGui DESC
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
            WHERE EmpID IN ($empIDsString) AND NguoiGui LIKE ? ORDER BY NgayGui DESC";

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
            ORDER BY NgayGui DESC
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
            " . (count($statuses) > 0 ? "AND TrangThai IN ($statusesPlaceholders)" : "") . "
            ORDER BY NgayGui DESC";
    
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

    public static function updatePointProfile ($EmpID, $point) {
        $db = new Database();
        $conn = $db->connect();

        $query = "UPDATE Profile SET DiemThuong = DiemThuong + ? WHERE EmpID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $point, $EmpID);
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
