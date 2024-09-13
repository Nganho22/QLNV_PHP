<?php
require_once __DIR__ . '/../config/MySQLconnect.php';

class RequestModel {
    private $apiUrl;

    public function __construct($apiUrl) {
        $this->apiUrl = $apiUrl;
    }

    private static function isApiAvailable($url) {
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

    public static function getRequestCountsByEmpID($user_id, $apiUrl) {
        $url = $apiUrl . '/counts/' . ($user_id);

        //$url = $this->apiUrl . "/counts/" . $user_id;

        if (!self::isApiAvailable($url)) {
            return null;
        }

        $response = file_get_contents($url);
        $results = json_decode($response, true);

        return $results;
    }

    public static function getPendingRequestsByEmpID($user_id, $limit, $offset, $apiUrl) {
        $page = floor($offset / $limit);
        $url = $apiUrl . "/pending?empID=$user_id&page=$page&size=$limit";

        if (!self::isApiAvailable($url)) {
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

    public static function countPendingRequests($user_id, $apiUrl) {
        $url = $apiUrl . "/count/pending?empID=$user_id";

        if (!self::isApiAvailable($url)) {
            return null;
        }
        
        $response = file_get_contents($url);
        $total = json_decode($response, true);

        return $total;
    }

    public static function getApprovedRequestsByEmpID($user_id, $limit, $offset, $apiUrl) {
        $page = floor($offset / $limit);
        $url = $apiUrl . "/approved?empID=$user_id&page=$page&size=$limit";

        if (!self::isApiAvailable($url)) {
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
    
    public static function countApprovedRequests($user_id, $apiUrl) {
        $url = $apiUrl . "/count/approved?empID=$user_id";

        if (!self::isApiAvailable($url)) {
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

    public static function getTimeSheetsByEmpID($user_id, $apiUrl) {
        $url = $apiUrl . '/timesheets/' . ($user_id);

        if (!self::isApiAvailable($url)) {
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

    public static function getTimeSheetByID($timeSheetID, $apiUrl) {
        // $apiUrl='http://localhost:9004/apiRequest';
        // $url = $apiUrl . '/timesheetsID/' . ($timeSheetID);
        $url = $apiUrl . "/timesheetsID/" . urlencode($timeSheetID);

        if (!self::isApiAvailable($url)) {
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
    
    public static function createRequest($user_id, $nguoiGui, $loai, $tieuDe, $ngayGui, $ngayChon, $noiDung, $url) {
        $apiUrl = $url . "/createRequest";
        // $apiUrl = 'http://localhost:9004/apiRequest/createRequest';
        
        $data = array(
            'empID' => $user_id,
            'nguoiGui' => $nguoiGui,
            'loai' => $loai,
            'tieuDe' => $tieuDe,
            'ngayGui' => $ngayGui,
            'ngayChon' => $ngayChon,
            'noiDung' => $noiDung
        );
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        $response = curl_exec($ch);
    
        if (curl_errno($ch)) {
            echo 'Error: ' . curl_error($ch);
            curl_close($ch);
            return false;
        }
    
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    
        if ($httpCode == 200) {
            return true;
        } else {
            return false;
        }
    }

    public static function createTimeSheetRequest($user_id, $nguoiGui, $loai, $tieuDe, $ngayGui, $noiDung, $timeSheetID, $trangThai, $newUpThoiGianTimesheet, $url) {
        //$apiUrl = 'http://localhost:9004/apiRequest/createTimeSheetRequest'; 
        $apiUrl = $url . "/createTimeSheetRequest";

        $data = array(
            'empId' => $user_id,
            'nguoiGui' => $nguoiGui,
            'loai' => $loai,
            'tieuDe' => $tieuDe,
            'ngayGui' => $ngayGui,
            'noiDung' => $noiDung,
            'timeSheetID' => $timeSheetID,
            'trangThai' => $trangThai,
            'newUpThoiGianTimesheet' => $newUpThoiGianTimesheet
        );
    
        // Thiết lập cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
    
        // Thực thi cURL và lấy kết quả
        curl_exec($ch);
    
        // Lấy mã trạng thái HTTP
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    
        // Kiểm tra mã trạng thái HTTP
        if ($httpCode == 200) {
            return true;
        } else {
            return false;
        }
    }
    
    public static function getDetailRequest($RequestID, $url) {
        //$apiUrl = 'http://localhost:9004/apiRequest/getDetailRequest/' . $RequestID;  
        $apiUrl = $url . "/getDetailRequest/" . $RequestID;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        $response = curl_exec($ch);
    
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
            curl_close($ch);
            return false;
        }
    
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    
        if ($httpCode == 200) {
            $result = json_decode($response, true);
            //print_r($result);

            if (isset($result['requestid'])) {
                $request = [
                    'RequestID' => $result['requestid'],
                    'NguoiGui' => $result['nguoigui'],
                    'EmpID' => $result['empid'],
                    'Loai' => $result['loai'],
                    'NgayGui' => $result['ngaygui'],
                    'TrangThai' => $result['trangthai'],
                    'NgayXuLy' => $result['ngayxuly'],
                    'NgayChon' => $result['ngaychon'],
                    'Time_sheetID' => $result['time_sheetid'],
                    'TieuDe' => $result['tieude'],
                    'NoiDung' => $result['noidung'],
                    'PhanHoi' => $result['phanhoi'],
                    'Up_TinhTrang_Timesheet' => $result['up_tinhtrang_timesheet'],
                    'Up_ThoiGian_Timesheet' => $result['up_thoigian_timesheet']
                ];
                return $request;
            } else {
                return null; 
            }
        } else {
            return false; 
        }
    }
    

//============== Quản lý =====================    

    private static function getEmpIDsAndPhongID($user_id) {
        $url = $_SESSION['API']['Request'];
        $apiUrl = $url . "/profileInfo/" . $user_id;

        //$apiUrl = 'http://localhost:9004/apiRequest/profileInfo/' . $user_id;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode == 200) {
            $result = json_decode($response, true);
            
            if ($result && isset($result['phongID']) && isset($result['empIDs'])) {
                return [
                    'phongID' => $result['phongID'],
                    'empIDs' => $result['empIDs']
                ];
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    public static function getRequestCountsByEmpID_QL($user_id, $apiUrl) {
        $data = self::getEmpIDsAndPhongID($user_id);
        $phongID = $data['phongID'];
        $empIDs = $data['empIDs'];
    
        //print_r($data);
        if (!$phongID || empty($empIDs)) {
            return ['total' => 0, 'pending' => 0, 'approved' => 0];
        }
    
        $empIDsString = implode(',', $empIDs);
        $url = $apiUrl . '/counts?empIDs=' . urlencode($empIDsString);
        //$url = 'http://localhost:9004/apiRequest/counts?empIDs=' . urlencode($empIDsString);
    
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200) {
            $result = json_decode($response, true);
            return $result;
        } else {
            return ['total' => 0, 'pending' => 0, 'approved' => 0];
        }
    }
    

    public static function getRequestsByEmpID_QL($user_id, $apiUrl) {
        $data = self::getEmpIDsAndPhongID($user_id);
        $phongID = $data['phongID'];
        $empIDs = $data['empIDs'];
    
        if (!$phongID || empty($empIDs)) {
            return [];
        }
    
        $empIDsString = implode(',', $empIDs);
    
        //$url = 'http://localhost:9004/apiRequest/by-emp-ids?empIDs=' . urlencode($empIDsString);
        $url = $apiUrl . '/by-emp-ids?empIDs=' . urlencode($empIDsString);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    
        if ($httpCode == 200) {
            $requests = json_decode($response, true);
    
            $formattedRequests = [];
            foreach ($requests as $request) {
                $formattedRequests[] = [
                    'RequestID' => $request['requestid'] ?? 'N/A',
                    'TieuDe' => $request['tieude'] ?? 'N/A',
                    'Loai' => $request['loai'] ?? 'N/A',
                    'NgayGui' => $request['ngaygui'] ?? 'N/A',
                    'NguoiGui' => $request['nguoigui'] ?? 'N/A',
                    'NgayXuLy' => $request['ngayxuly'] ?? 'N/A',
                    'TrangThai' => $request['trangthai'] ?? 'N/A',
                    'NgayChon' => $request['ngaychon'] ?? 'N/A'
                ];
            }
    
            return $formattedRequests;
        } else {
            return [];
        }
    }
    
    public static function searchRequestsByEmpID_QL($user_id, $searchTerm, $limit, $offset, $apiUrl) {
        $data = self::getEmpIDsAndPhongID($user_id);
        $phongID = $data['phongID'];
        $empIDs = $data['empIDs'];
    
        if (!$phongID || empty($empIDs)) {
            return [];
        }
    
        $empIDsString = implode(',', $empIDs);
    
        // $url = 'http://localhost:9004/apiRequest/search?empIDs=' . urlencode($empIDsString) . 
        //        '&searchTerm=' . urlencode($searchTerm) . 
        //        '&limit=' . $limit . 
        //        '&offset=' . $offset;
        
        $url = $apiUrl . '/search?empIDs=' . urlencode($empIDsString) . 
                '&searchTerm=' . urlencode($searchTerm) . 
                '&limit=' . $limit . 
                '&offset=' . $offset;

    
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    
        if ($httpCode == 200) {
            $result = json_decode($response, true);
            
            $requests = [];
            foreach ($result as $row) {
                $requests[] = [
                    'RequestID' => $row['requestid'] ?? 'N/A',
                    'EmpID' => $row['empid'] ?? 'N/A',
                    'TieuDe' => $row['tieude'] ?? 'N/A',
                    'Loai' => $row['loai'] ?? 'N/A',
                    'NgayGui' => $row['ngaygui'] ?? 'N/A',
                    'NguoiGui' => $row['nguoigui'] ?? 'N/A',
                    'TrangThai' => $row['trangthai'] ?? 'N/A'
                ];
            }
            
            return $requests;
        } else {
            return [];
        }
    }

    public static function countSearchRequests_QL($user_id, $searchTerm, $apiUrl) {
        $data = self::getEmpIDsAndPhongID($user_id);
        $phongID = $data['phongID'];
        $empIDs = $data['empIDs'];
    
        if (!$phongID || empty($empIDs)) {
            return 0;
        }
    
        $empIDsString = implode(',', $empIDs);
    
        // $url = 'http://localhost:9004/apiRequest/countSearchRequests?empIDs=' . urlencode($empIDsString) . 
        //        '&searchTerm=' . urlencode($searchTerm);
    
        $url = $apiUrl . '/countSearchRequests?empIDs=' . urlencode($empIDsString).
                '&searchTerm=' . urlencode($searchTerm);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    
        if ($httpCode == 200) {
            return (int) $response;
        } else {
            return 0;
        }
    }
    
    public static function filterRequestsByEmpID_QL($user_id, $searchTerm, $types, $statuses, $limit, $offset, $apiUrl) {
        $data = self::getEmpIDsAndPhongID($user_id);
        $phongID = $data['phongID'];
        $empIDs = $data['empIDs'];
    
        if (!$phongID || empty($empIDs)) {
            return [];
        }
    
        $empIDsString = implode(',', $empIDs);
        $typesString = implode(',', $types);
        $statusesString = implode(',', $statuses);
        $searchTerm = urlencode("%$searchTerm%");
    
        // Tạo URL cho API với các tham số
        $url = $apiUrl . '/filter?' .
               'empIDs=' . urlencode($empIDsString) . 
               '&searchTerm=' . $searchTerm . 
               '&types=' . urlencode($typesString) . 
               '&statuses=' . urlencode($statusesString) . 
               '&limit=' . $limit . 
               '&offset=' . $offset;
               
        // Khởi tạo cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        // Thực thi cURL và lấy phản hồi
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    
        // Kiểm tra nếu API trả về thành công (HTTP 200)
        if ($httpCode == 200) {
            // Giải mã JSON response thành mảng
            $result = json_decode($response, true);
            return $result;
        } else {
            // Nếu có lỗi hoặc không thành công, trả về mảng trống
            return [];
        }
    }

    public static function countFilterRequests_QL($user_id, $searchTerm, $types, $statuses, $apiUrl) {
        $data = self::getEmpIDsAndPhongID($user_id);
        $phongID = $data['phongID'];
        $empIDs = $data['empIDs'];
    
        if (!$phongID || empty($empIDs)) {
            return 0;
        }
    
        $empIDsString = implode(',', $empIDs);
        $typesString = implode(',', $types);
        $statusesString = implode(',', $statuses);
        $searchTerm = urlencode("%$searchTerm%");
    
        $url = $apiUrl . '/countFilter?' .
               'empIDs=' . urlencode($empIDsString) . 
               '&searchTerm=' . $searchTerm . 
               '&types=' . urlencode($typesString) . 
               '&statuses=' . urlencode($statusesString);
    
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    
        //print_r($response);

        if ($httpCode == 200) {
            $result = json_decode($response, true);
            // print_r($result);
            return $result ?? 0; 
        } else {
            return 0;
        }
    }
    
    public static function updateRequest($requestID, $ngayXuLy, $trangThai, $noiDung, $apiUrl) {
        $url = $apiUrl . '/updateReq/' . urlencode($requestID) . 
               '?ngayXuLy=' . urlencode($ngayXuLy) . 
               '&trangThai=' . urlencode($trangThai) . 
               '&noiDung=' . urlencode($noiDung);
    
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    
        if ($httpCode == 200) {
            return true;
        } else {
            return false;
        }
    }
    

    public static function insertCheckOK($empID, $ngayChon, $opt_WFH, $opt_Nghi, $apiUrl) {
        $url = $apiUrl . '/checkinout';
        
        $data = [
            'empid' => $empID,
            'datecheckin' => $ngayChon,
            'workfromhome' => $opt_WFH,
            'nghi' => $opt_Nghi
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode == 200 && json_decode($response, true);
    }
    
    // public static function updateTimeSheet($timeSheetID, $Up_TinhTrang_TS, $up_ThoiGian_TS, $Tre, $apiUrl) {
    //     $data = array(
    //         'timeSheetID' => $timeSheetID,
    //         'trangThai' => $Up_TinhTrang_TS,
    //         'soGioThucHien' => $up_ThoiGian_TS,
    //         'tre' => $Tre
    //     );
    
    //     // Khởi tạo cURL
    //     $ch = curl_init($apiUrl . '/updateTimesheet'); // API endpoint
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //     curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    //     curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // Dữ liệu gửi đi
    //     curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    //         'Content-Type: application/json',
    //         'Content-Length: ' . strlen(json_encode($data))
    //     ));
        
    //     // Thực thi cURL và lấy phản hồi
    //     $response = curl_exec($ch);
    //     $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //     curl_close($ch);
    
    //     // Kiểm tra mã trạng thái HTTP
    //     if ($httpCode == 200) {
    //         // Nếu phản hồi thành công
    //         return true;
    //     } else {
    //         // Nếu có lỗi
    //         return false;
    //     }
    // }
    
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
