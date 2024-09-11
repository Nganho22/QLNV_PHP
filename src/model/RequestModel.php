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
        $apiUrl='http://localhost:9004/apiRequest';
        $url = $apiUrl . '/counts/' . ($user_id);

        //$url = $this->apiUrl . "/counts/" . $user_id;

        if (!$this->isApiAvailable($url)) {
            return null;
        }

        $response = file_get_contents($url);
        $results = json_decode($response, true);

        return $results;

        // if ($results) {
        //     $user = [
        //         'total' => $results['total'],
        //         'pending' => $results['pending'],
        //         'approved' => $results['approved'],
        //     ];
        //     return $results;
        // }
    }

    public function getPendingRequestsByEmpID($user_id, $limit, $offset) {
        $page = floor($offset / $limit);
        $url = $this->apiUrl . "/pending?empID=$user_id&page=$page&size=$limit";

        if (!$this->isApiAvailable($url)) {
            return null;
        }

        $response = file_get_contents($url);
        $results = json_decode($response, true);
        $formattedResults = [];

        if (is_array($results['content'])) {
            foreach ($results['content'] as $result) {
                $formattedResults[] = [
                    'RequestID' => $result['requestid'] ?? 'N/A',
                    'TieuDe' => $result['tieude'] ?? 'N/A',
                    'Loai' => $result['loai'] ?? 'N/A',
                    'NgayGui' => $result['ngaygui'] ?? 'N/A',
                    'NgayXuLy' => $result['ngayxuly'] ?? 'N/A',
                    'TrangThai' => $result['trangthai'] ?? 'N/A'
                ];
            }
        } 
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
        $page = floor($offset / $limit);
        $url = $this->apiUrl . "/approved?empID=$user_id&page=$page&size=$limit";

        if (!$this->isApiAvailable($url)) {
            return null;
        }

        $response = file_get_contents($url);
        $results = json_decode($response, true);
        $formattedResults = [];
        // print_r($results);
        if (isset($results['content']) && is_array($results['content'])) {
            foreach ($results['content'] as $result) {
                $formattedResults[] = [
                    'RequestID' => $result['requestid'] ?? 'N/A',
                    'TieuDe' => $result['tieude'] ?? 'N/A',
                    'Loai' => $result['loai'] ?? 'N/A',
                    'NgayGui' => $result['ngaygui'] ?? 'N/A',
                    'NgayXuLy' => $result['ngayxuly'] ?? 'N/A',
                    'TrangThai' => $result['trangthai'] ?? 'N/A'
                ];
            }
            return $formattedResults;
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

    // ???
    public static function gettimesheet($user_id) {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT * 
                                FROM time_sheet WHERE empid = ?");
        $stmt->bind_param("i", $user_id,);
        $stmt->execute();
        $result = $stmt->get_result();
        $timesheets = array();
        while ($row = $result->fetch_assoc()) {
            $timesheet =[
                'Time_SheetID' => $row['time_sheetid'],
                'ProjectID' => $row['projectid'],
                'EmpID' => $row['empid'],
                'TenDuAn' => $row['tenduan'],
                'NguoiGui' => $row['nguoigui'],
                'PhongBan' => $row['phongban'],
                'TrangThai' => $row['trangthai'],
                'SoGioThucHien' => $row['sogiothuchien'],
                'NgayGiao' => $row['ngaygiao'],
                'HanChot' => $row['hanchot'],
                'DiemThuong' => $row['diemthuong'],
                'Tre' => $row['tre'],
                'NoiDung' => $row['noidung']

            ];
            $timesheets[]=$timesheet;
            
        }
        $stmt->close();
        $db->close();
        return $timesheets;
    }

    public function getTimeSheetsByEmpID($user_id) {
        $apiUrl='http://localhost:9004/apiRequest';
        $url = $apiUrl . '/timesheets/' . ($user_id);

        if (!$this->isApiAvailable($url)) {
            return null;
        }

        $response = file_get_contents($url);
        $results = json_decode($response, true);
        //print_r($results);
        $formattedResults = [];

        if (is_array($results)) {
            foreach ($results as $result) {
                $formattedResults[] = [
                    'Time_sheetID' => $result['timesheetid'] ?? 'N/A',
                    'ProjectID' => $result['projectid'] ?? 'N/A',
                    'TenDuAn' => $result['tenduan'] ?? 'N/A',
                    'NguoiGui' => $result['nguoigui'] ?? 'N/A',
                    'PhongBan' => $result['phongBan'] ?? 'N/A',
                    'TrangThai' => $result['trangThai'] ?? 'N/A',
                    'SoGioThucHien' => $result['sogiothuchien'] ?? 'N/A',
                    'NgayGiao' => $result['ngaygiao'] ?? 'N/A',
                    'HanChot' => $result['hanchot'] ?? 'N/A',
                    'DiemThuong' => $result['diemthuong'] ?? 'N/A',
                    'Tre' => $result['tre'] ?? 'N/A',
                    'NoiDung' => $result['noidung'] ?? 'N/A'
                ];
            }
        } else {
            return "Phản hồi từ API không phải là mảng hợp lệ.";
        }
    
        return $formattedResults;
    }

    public function getTimeSheetByID($timeSheetID) {
        // $apiUrl='http://localhost:9004/apiRequest';
        // $url = $apiUrl . '/timesheetsID/' . ($timeSheetID);
        $url = $this->apiUrl . "/timesheetsID/" . urlencode($timeSheetID);

        if (!$this->isApiAvailable($url)) {
            return null;
        }

        $response = file_get_contents($url);
        $result = json_decode($response, true);
        $formattedResults = [];

        if (is_array($result)) {
        {
                $formattedResults = [
                    'Time_sheetID' => $result['timesheetid'] ?? 'N/A',
                    'ProjectID' => $result['projectid'] ?? 'N/A',
                    'TenDuAn' => $result['tenduan'] ?? 'N/A',
                    'NguoiGui' => $result['nguoigui'] ?? 'N/A',
                    'PhongBan' => $result['phongBan'] ?? 'N/A',
                    'TrangThai' => $result['trangThai'] ?? 'N/A',
                    'SoGioThucHien' => $result['sogiothuchien'] ?? 'N/A',
                    'NgayGiao' => $result['ngaygiao'] ?? 'N/A',
                    'HanChot' => $result['hanchot'] ?? 'N/A',
                    'DiemThuong' => $result['diemthuong'] ?? 'N/A',
                    'Tre' => $result['tre'] ?? 'N/A',
                    'NoiDung' => $result['noidung'] ?? 'N/A'
                ];
            }
        }
        //print_r($formattedResults);

        return $formattedResults;
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
                'EmpID' => $requests['empid'],
                'Loai' => $requests['loai'],
                'NgayGui' => $requests['ngaygui'],
                'TrangThai' => $requests['trangthai'],
                'NgayXuLy' => $requests['ngayxuly'],
                'NgayChon' => $requests['ngaychon'],
                'Time_sheetID' => $requests['time_sheetid'],
                'TieuDe' => $requests['tieude'],
                'NoiDung' => $requests['noidung'],
                'PhanHoi' => $requests['phanhoi'],
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
            $row = [
                'RequestID' => $row['requestid'] ?? 'N/A',
                'TieuDe' => $row['tieude'] ?? 'N/A',
                'Loai' => $row['loai'] ?? 'N/A',
                'NgayGui' => $row['ngaygui'] ?? 'N/A',
                'NguoiGui' => $row['nguoigui'] ?? 'N/A',
                'NgayXuLy' => $row['ngayxuly'] ?? 'N/A',
                'TrangThai' => $row['trangthai'] ?? 'N/A',
                'NgayChon' => $row['ngaychon'] ?? 'N/A'
            ];
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
            SELECT requestid, empid, tieude, loai, nguoigui, ngaygui, trangthai
            FROM Request
            WHERE empid IN ($empIDsString) AND nguoigui LIKE ? 
            ORDER BY ngaygui DESC
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
            $row = [
                'RequestID' => $row['requestid'] ?? 'N/A',
                'EmpID' => $row['empid'] ?? 'N/A',
                'TieuDe' => $row['tieude'] ?? 'N/A',
                'Loai' => $row['loai'] ?? 'N/A',
                'NgayGui' => $row['ngaygui'] ?? 'N/A',
                'NguoiGui' => $row['nguoigui'] ?? 'N/A',
                'TrangThai' => $row['trangthai'] ?? 'N/A'
            ];
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
            SELECT COUNT(requestid) as total
            FROM Request
            WHERE empid IN ($empIDsString) AND nguoigui LIKE ? ORDER BY ngaygui DESC";

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
            SELECT requestid, empid, tieude, loai, nguoigui, ngaygui, trangthai
            FROM Request
            WHERE empid IN ($empIDsString)
            AND nguoigui LIKE ?
            " . (count($types) > 0 ? "AND loai IN ($typesPlaceholders)" : "") . "
            " . (count($statuses) > 0 ? "AND trangthai IN ($statusesPlaceholders)" : "") . "
            ORDER BY ngaygui DESC
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
            $row = [
                'RequestID' => $row['requestid'] ?? 'N/A',
                'EmpID' => $row['empid'] ?? 'N/A',
                'TieuDe' => $row['tieude'] ?? 'N/A',
                'Loai' => $row['loai'] ?? 'N/A',
                'NgayGui' => $row['ngaygui'] ?? 'N/A',
                'NguoiGui' => $row['nguoigui'] ?? 'N/A',
                'TrangThai' => $row['trangthai'] ?? 'N/A'
            ];
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
            SELECT COUNT(requestid) as total
            FROM Request
            WHERE empid IN ($empIDsString)
            AND nguoigui LIKE ?
            " . (count($types) > 0 ? "AND loai IN ($typesPlaceholders)" : "") . "
            " . (count($statuses) > 0 ? "AND trangthai IN ($statusesPlaceholders)" : "") . "
            ORDER BY ngaygui DESC";
    
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
    
        $query = "UPDATE Request SET ngayxuly = ?, trangthai = ?, phanhoi = ? WHERE requestid = ?";
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

        $query = "INSERT INTO Check_inout (empid, date_checkin, workfromhome, nghi) VALUES (?,?,?,?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('isii', $empID, $ngayChon, $opt_WFH, $opt_Nghi);
        $result = $stmt->execute();
        
        $stmt->close();
        $db->close();
        return $result;
    }

    public static function  updateTimeSheet($timeSheetID, $Up_TinhTrang_TS, $up_ThoiGian_TS, $Tre) {
        $db = new Database();
        $conn = $db->connect();
    
        $query = "UPDATE Time_sheet SET trangthai = ?, sogiothuchien = ?, tre = ? WHERE time_sheetid = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('siii', $Up_TinhTrang_TS, $up_ThoiGian_TS, $Tre, $timeSheetID);
        $result = $stmt->execute();
        
        $stmt->close();
        $db->close();
        return $result;
    }

    public static function updatePointProfile ($EmpID, $point) {
        $db = new Database();
        $conn = $db->connect();

        $query = "UPDATE Profile SET diemthuong = diemthuong + ? WHERE empid = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $point, $EmpID);
        $result = $stmt->execute();

        $stmt->close();
        $db->close();
        return $result;
    }
    
    public static function updatePointFelicitation ($NguoiNhan, $point, $NoiDung, $NguoiTang, $Date) {
        $db = new Database();
        $conn = $db->connect();

        $query = "INSERT INTO Felicitation (point, date, noidung, nguoinhan, nguoitang) VALUES (?,?,?,?,?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("issii", $point, $Date, $NoiDung, $NguoiNhan, $NguoiTang);
        $result = $stmt->execute();

        $stmt->close();
        $db->close();
        return $result;
    } 

    public static function  updateProfile($user_id) {
        $db = new Database();
        $conn = $db->connect();
    
        $query = "UPDATE Profile SET tinhtrang = 0 WHERE empid = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        $result = $stmt->execute();
        
        $stmt->close();
        $db->close();
        return $result;
    }
}
?>
