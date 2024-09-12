<?php
require_once __DIR__ . '/../config/MySQLconnect.php';

class UserModel {

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

    public static function clogin($username, $password, $apiUrl) {

        $url = $apiUrl . '/getActiveProfile?tenTaiKhoan=' . urlencode($username) . '&matKhau=' . urlencode($password);

        if (!self::isApiAvailable($url)) {
            return null;
        }

        $response = file_get_contents($url);
        $userData = json_decode($response, true);

        if ($userData) {
            $user = [
                'EmpID' => $userData['empid'],
                'PhongID' => $userData['phongid'],
                'HoTen' => $userData['hoten'],
                'Role' => $userData['role'],
                'Image' => 'public/img/avatar/' . $userData['image'],
                'TenPhong' => $userData['tenphong']
            ];
            return $user;
        }
        return null;

    }

    public static function getprofile($user_id, $apiUrl){
        
        $url = $apiUrl . '/findByID/' . $user_id;

        if (!self::isApiAvailable($url)) {
            return null;
        }


        $response = file_get_contents($url);
        $userData = json_decode($response, true);

        $profile = [
            'TenPhong' => null,
            'Role' => null,
            'HoTen' => null,
            'Email' => null,
            'GioiTinh' => null,
            'SoDienThoai' => null,
            'CCCD' => null,
            'STK' => null,
            'Luong' => null,
            'DiemThuong' => null,
            'DiaChi' => null,
            'Image' => null
        ];

        if ($userData && is_array($userData)) {
            $u = $userData;
            $profile['EmpID'] = $u['empid'];
            $profile['HoTen'] = $u['hoten'];
            $profile['Role'] = $u['role'];
            $profile['Email'] = $u['email'];
            $profile['GioiTinh'] = $u['gioitinh'];
            $profile['SoDienThoai'] = $u['sodienthoai'];
            $profile['CCCD'] = $u['cccd'];
            $profile['STK'] = $u['stk'];
            $profile['Luong'] = $u['luong'];
            $profile['DiemThuong'] = $u['diemthuong'];
            $profile['DiaChi'] = $u['diachi'];

            $profile['Image'] = 'public/img/avatar/'.$u['image'];
            $profile['Image_name'] = $u['image'];
            $profile['TenPhong'] = $u['tenphong'];
        }
        return $profile;
    }





    public static function getCountNghiPhep($user_id, $apiUrl) {
        
        $url = $apiUrl . '/CountNghiByID/' . $user_id;

        if (!self::isApiAvailable($url)) {
            return 0;
        }


        $response = file_get_contents($url);
        $cNghi = json_decode($response, true);
        $Nghi = 0;
        if (isset($cNghi)) {
            $Nghi = $cNghi;
        }
        return $Nghi;
    }

    public static function getCountTre($user_id, $apiUrl) {
        
        $url = $apiUrl . '/CountLateByID/' . $user_id;

        if (!self::isApiAvailable($url)) {
            return 0;
        }


        $response = file_get_contents($url);
        $cLate = json_decode($response, true);
        $Late = 0;
        if (isset($cLate)) {
            $Late = $cLate;
        }
        return $Late;
    }

    // public static function updateProfile ($suser_id, $gioitinh, $cccd, $sdt, $stk, $diachi, $img , $newPass ) {
    //     $db = new Database();
    //     $conn = $db->connect();

    //     if ($newPass) {
    //         $sql = "UPDATE Profile SET gioitinh = ?, cccd = ?, sodienthoai = ?, stk = ?, diachi = ?, image = ?, matkhau = ? WHERE empid = ?";
    //         $stmt = $conn->prepare($sql);
    //         $stmt->bind_param("sssssssi", $gioitinh, $cccd, $sdt, $stk, $diachi, $img, $newPass, $suser_id);
    //     } else {
    //         $sql = "UPDATE Profile SET gioitinh = ?, cccd = ?, sodienthoai = ?, stk = ?, diachi = ?, image = ? WHERE empid = ?";
    //         $stmt = $conn->prepare($sql);
    //         $stmt->bind_param("ssssssi", $gioitinh, $cccd, $sdt, $stk, $diachi, $img, $suser_id);
    //     }
    //     $result = $stmt->execute();
    //     $stmt->close(); // Close statement
    //     return $result;
    // }

    public static function updateProfile($empID, $gioiTinh, $cccd, $sdt, $stk, $diaChi, $image, $newPass = null, $apiUrl) {
        // URL của API
        $url = $apiUrl . '/updateProfile';
    
        // Dữ liệu cần gửi đi
        $data = array(
            'empID' => $empID,
            'gioiTinh' => $gioiTinh,
            'cccd' => $cccd,
            'sdt' => $sdt,
            'stk' => $stk,
            'diaChi' => $diaChi,
            'image' => $image
        );
        
        // Nếu có newPass thì thêm vào dữ liệu
        if ($newPass) {
            $data['newPass'] = $newPass;
        }
    
        // Chuyển dữ liệu thành dạng URL query string
        $postData = http_build_query($data);
    
        // Khởi tạo cURL
        $ch = curl_init($url);
    
        // Cấu hình cURL cho PUT request
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT'); // Sử dụng PUT method
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded'
        ));
    
        // Gửi yêu cầu và nhận phản hồi
        $response = curl_exec($ch);
    
        // Kiểm tra lỗi cURL
        if (curl_errno($ch)) {
            curl_close($ch);
            return "Request Error: " . curl_error($ch); // Xử lý lỗi
        }
    
        // Lấy HTTP status code
        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
        // Đóng cURL
        curl_close($ch);
    
        // Xử lý kết quả
        if ($httpStatusCode == 200) {
            return "Profile updated successfully.";
        } else {
            return "Failed to update profile. HTTP Status Code: " . $httpStatusCode;
        }
    }
    
    

    public static function getPhongIDByEmpID($empID, $apiUrl) {

        
        $url = $apiUrl . '/findByID/' . $empID;

        if (!self::isApiAvailable($url)) {
            return null;
        }


        $response = file_get_contents($url);
        $userData = json_decode($response, true);

       $phongID = null;

        if ($userData && is_array($userData)) {
            $u = $userData;
            $phongID = $u['phongid'];
        }
        return $phongID;
    }




    
    public static function getPoint_Month($empID, $apiUrl) {
        $url = $apiUrl . '/totalPointsByMonth/' . $empID;

        if (!self::isApiAvailable($url)) {
            return array_fill(1, 12, 0);
        }
        
        $response = file_get_contents($url);
        $data = json_decode($response, true);
        $monthlyPoints = array_fill(1, 12, 0);
        if ($data && is_array($data)) {
            foreach ($data as $entry) {
                $month = isset($entry['month']) ? (int)$entry['month'] : 0;
                $totalPoints = isset($entry['total_points']) ? (int)$entry['total_points'] : 0;
                
                if ($month >= 1 && $month <= 12) {
                    $monthlyPoints[$month] = $totalPoints;
                }
            }
        }
        return $monthlyPoints;
    }



    public static function searchProfiles_QL($empID, $searchTerm, $limit, $offset, $apiUrl) {
        $url = $apiUrl . '/getProfileNVByQL?empid=' . $empID . '&hoten=' . urlencode($searchTerm) . '&limit=' . $limit . '&offset=' . $offset;
        
        if (!self::isApiAvailable($url)) {
            return null;
        }
        
        $response = file_get_contents($url);
        if ($response === FALSE) {
            return null;
        }
    
        $Datas = json_decode($response, true);
        if ($Datas === null) {
            echo 'JSON Decode Error: ' . json_last_error_msg();
            return null;
        }
    
        $profiles = [];
        if (is_array($Datas)) {
            foreach ($Datas as $Data) {
                $profile = [
                    'EmpID' => isset($Data['empid']) ? $Data['empid'] : null,
                    'HoTen' => isset($Data['hoten']) ? $Data['hoten'] : null,
                    'Email' => isset($Data['email']) ? $Data['email'] : null
                ];
                $profiles[] = $profile;
            }
        }
        
        return $profiles;
    }
    

     
    public static function countSearchProfiles_QL($empID, $searchTerm, $apiUrl) {
        $searchTerm = urlencode($searchTerm);
        $url = $apiUrl . '/countProfilesBySearch?empID=' . $empID . '&hoTen=' . $searchTerm;
    

        if (!self::isApiAvailable($url)) {
            return 0; 
        }
        
        $response = file_get_contents($url);
        
        
        $data = json_decode($response, true);
        
        
        if (isset($data['total'])) {
            return (int)$data['total'];
        }
        
        return 0;
    }
    
    
    /* public static function countAllEmployees_QL($empID) {
        $db = new Database();
        $conn = $db->connect();
    
        // Lấy PhongID của người dùng hiện tại
        $stmt = $conn->prepare("SELECT PhongID FROM Profile WHERE empid = ?");
        $stmt->bind_param("i", $empID);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $phongID = $row['PhongID'];
        $stmt->close();
    
        $query = "
            SELECT COUNT(*) as total
            FROM Profile
            WHERE PhongID = ? AND empid <> ?";
    
        $stmt = $conn->prepare($query);
        $stmt->bind_param('si', $phongID, $empID);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
    
        $stmt->close();
        $db->close();
    
        return $row['total'];
    }
    */
    public static function countAllEmployees_QL($empID, $apiUrl) {
        $url = $apiUrl . '/countProfilesInSamePhongBan?empID=' . $empID;
        if (!self::isApiAvailable($url)) {
            return 0; 
        }
        
        $response = file_get_contents($url);
        
        
        $data = json_decode($response, true);
        if (isset($data['total'])) {
            return (int)$data['total'];
        }
        
        return 0;
    }
     public static function getPhongBanStatistics($empID) {
        $db = new Database();
        $conn = $db->connect();
        

        $stmt = $conn->prepare("SELECT phongid FROM Profile WHERE empid = ?");
        $stmt->bind_param("i", $empID);
        $stmt->execute();
        $result = $stmt->get_result();
        $phongID = $result->fetch_assoc()['phongid'];
        $stmt->close();
    
        if (!$phongID) {
            $db->close();
            return [];
        }
    
        $stmt = $conn->prepare("SELECT Profile.empid, Profile.hoten
                                FROM Profile
                                WHERE Profile.phongid = ?");
        $stmt->bind_param("s", $phongID);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $employees = [];
        while ($row = $result->fetch_assoc()) {
            
            $employee =[
                'EmpID' => $row['empid'],
                'HoTen' => $row['hoten']
            ];
            $employees[] = $employee;
        }
    
        $stmt->close();
        $db->close();
        return $employees;
    }

    public static function getWorkFromHomeCountByEmpID($empID) {
        $db = new Database();
        $conn = $db->connect();
        
        $stmt = $conn->prepare("SELECT phongid FROM Profile WHERE empid = ?");
        $stmt->bind_param("i", $empID);
        $stmt->execute();
        $result = $stmt->get_result();
        $phongID = $result->fetch_assoc()['phongid'];
        $stmt->close();
        
        if (!$phongID) {
            $db->close();
            return 0;
        }
    
        $stmt = $conn->prepare(
            "SELECT COUNT(DISTINCT Check_inout.empid) AS countWFH
            FROM Check_inout
            INNER JOIN Profile ON Check_inout.empid = Profile.empid
            WHERE Profile.phongid = ? 
            AND Check_inout.workfromhome = 1
            AND DATE(Check_inout.date_checkin) = CURDATE()"
        );
        $stmt->bind_param("s", $phongID);
        $stmt->execute();
        $result = $stmt->get_result();
        $countWFH = $result->fetch_assoc()['countWFH'] ?? 0;
    
        $stmt->close();
        $db->close();
        return $countWFH;
    }
    
    public static function getAbsence($empID) {
        $db = new Database();
        $conn = $db->connect();
        
        $stmt = $conn->prepare("SELECT phongid FROM Profile WHERE empid = ?");
        $stmt->bind_param("i", $empID);
        $stmt->execute();
        $result = $stmt->get_result();
        $phongID = $result->fetch_assoc()['phongid'];
        $stmt->close();
        
        if (!$phongID) {
            $db->close();
            return 0;
        }
    
        $stmt = $conn->prepare(
            "SELECT COUNT(DISTINCT Check_inout.empid) AS absence
            FROM Check_inout
            INNER JOIN Profile ON Check_inout.empid = Profile.empid
            WHERE Profile.phongid = ? 
            AND Check_inout.nghi = 1
            AND DATE(Check_inout.date_checkin) = CURDATE()"
        );
        $stmt->bind_param("s", $phongID);
        $stmt->execute();
        $result = $stmt->get_result();
        $absence = $result->fetch_assoc()['absence'] ?? 0;
    
        $stmt->close();
        $db->close();
        return $absence;
    }

    public static function getHienDien($empID) {
        $db = new Database();
        $conn = $db->connect();
        

        $stmt = $conn->prepare(
            "SELECT phongid 
             FROM Profile 
             WHERE empid = ?"
        );
        $stmt->bind_param("i", $empID);
        $stmt->execute();
        $result = $stmt->get_result();
        $phongID = $result->fetch_assoc()['phongid'];
        $stmt->close();
        
        if (!$phongID) {
            $db->close();
            return []; 
        }  
       
        $stmt = $conn->prepare(
            "SELECT 
                PhongBan.phongid, 
                PhongBan.tenphong, 
                COUNT(DISTINCT Check_inout.empid) AS SoHienDien
            FROM PhongBan
            LEFT JOIN Profile ON PhongBan.phongid = Profile.phongid
            LEFT JOIN Check_inout ON Profile.empid = Check_inout.empid
                AND DATE(Check_inout.date_checkin) = CURDATE()
                AND Check_inout.time_checkout IS NULL
            WHERE PhongBan.phongid = ?
            GROUP BY PhongBan.Phongid, PhongBan.tenphong"
        );
        $stmt->bind_param("s", $phongID);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $hiendiens = [];
        while ($row = $result->fetch_assoc()) {
            $hiendien =[
                'PhongID' => $row['phongid'],
                'TenPhong' => $row['tenphong'],
                'SoHienDien' => $row['SoHienDien']
                ];
            $hiendiens[] = $hiendien;
        }
        $stmt->close();
        $db->close();
        return $hiendiens;
    }

    public static function getPhongBan_Checkinout($empID) {
        $db = new Database();
        $conn = $db->connect();
    
        $stmtPhongID = $conn->prepare(
            "SELECT phongid
             FROM Profile 
             WHERE empid = ?"
        );
        $stmtPhongID->bind_param("i", $empID);
        $stmtPhongID->execute();
        $resultPhongID = $stmtPhongID->get_result();
        $phongID = $resultPhongID->fetch_assoc()['phongid'];
        $stmtPhongID->close();
        
        if (!$phongID) {
            $db->close();
            return [];
        }
    
        $stmt = $conn->prepare(
            "SELECT PhongBan.phongid, PhongBan.tenphong, 
                    COALESCE(COUNT(CASE WHEN Check_inout.time_checkin IS NOT NULL THEN 1 END), 0) AS SoLanCheckin,
                    COALESCE(COUNT(CASE WHEN Check_inout.time_checkout IS NOT NULL THEN 1 END), 0) AS SoLanCheckout
             FROM Profile
             INNER JOIN PhongBan ON Profile.phongid = PhongBan.phongid
             LEFT JOIN Check_inout ON Profile.empid = Check_inout.empid
                AND DATE(Check_inout.date_checkin) = CURDATE()
             WHERE PhongBan.phongid = ?
             GROUP BY PhongBan.phongid, PhongBan.tenphong"
        );
        $stmt->bind_param("s", $phongID);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $checkinouts = [];
        while ($row = $result->fetch_assoc()) {
            $checkinout =[
                'PhongID' => $row['phongid'],
                'TenPhong' => $row['tenphong'],
                'SoLanCheckin' => $row['SoLanCheckin'],
                'SoLanCheckout' => $row['SoLanCheckout']
                ];
            $checkinouts[]=$checkinout;
        }
    
        $stmt->close();
        $db->close();
        return $checkinouts;
    }

    public static function getEmployeesList_QL($empID, $limit, $offset, $apiUrl) {
        $url = $apiUrl . '/getProfileNVByQL?empid=' . $empID . '&hoten=' . '&limit=' . $limit . '&offset=' . $offset;
        
        if (!self::isApiAvailable($url)) {
            return null;
        }
        
        $response = file_get_contents($url);
        if ($response === FALSE) {
            return null;
        }
    
        $Datas = json_decode($response, true);
        if ($Datas === null) {
            echo 'JSON Decode Error: ' . json_last_error_msg();
            return null;
        }
    
        $profiles = [];
        if (is_array($Datas)) {
            foreach ($Datas as $Data) {
                $profile = [
                    'EmpID' => isset($Data['empid']) ? $Data['empid'] : null,
                    'HoTen' => isset($Data['hoten']) ? $Data['hoten'] : null,
                    'Email' => isset($Data['email']) ? $Data['email'] : null
                ];
                $profiles[] = $profile;
            }
        }
        
        return $profiles;
    }

    


    

    
    public static function getEmployeesList_GD($empID, $limit, $offset) {
        $db = new Database();
        $conn = $db->connect();
    
        $stmt = $conn->prepare("SELECT empid, hoten, email
                                FROM Profile
                                WHERE empid <> ?
                                LIMIT ? OFFSET ?");
        $stmt->bind_param("iii", $empID, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $employees =[];
        while ($row = $result->fetch_assoc()) {
            $employee= [
                'EmpID' => $row['empid'],
                'HoTen' => $row['hoten'],
                'Email' => $row['email'],
            ];
            $employees[] = $employee;
        }
        $stmt->close();
        $db->close();
        return $employees;
    }

    public static function countAllEmployees_GD($empID) {
        $db = new Database();
        $conn = $db->connect();
    
        $stmt = $conn->prepare("SELECT COUNT(*) as total
                                FROM Profile
                                WHERE empid <> ?");
        $stmt->bind_param("i", $empID);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
    
        $stmt->close();
        $db->close();
    
        return $row['total'];
    }


    public static function searchProfiles_GD($searchTerm, $limit, $offset) {
        $searchTerm = "%$searchTerm%";
        $query = "
            SELECT empid, hoten, email
            FROM Profile
            WHERE hoten LIKE ?
            LIMIT ? OFFSET ?";

        $db = new Database();
        $conn = $db->connect();
        $stmt = $conn->prepare($query);
        $params = [$searchTerm, $limit, $offset];
        $stmt->bind_param('sii', ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        $employees =[];
        while ($row = $result->fetch_assoc()) {
            $employee = [
                'EmpID' => $row['empid'],
                'HoTen' => $row['hoten'],
                'Email' => $row['email'],
            ];
            $employees[] = $employee;
        }

        $stmt->close();
        $db->close();

        return $employees;
    }

    public static function countSearchProfiles_GD($searchTerm) {
        $searchTerm = "%$searchTerm%";
        $query = "
            SELECT COUNT(empid) as total
            FROM Profile
            WHERE hoten LIKE ?";

        $db = new Database();
        $conn = $db->connect();
        $stmt = $conn->prepare($query);
        $params = [$searchTerm];
        $stmt->bind_param('s', ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $total = $result->fetch_assoc()['total'];

        $stmt->close();
        $db->close();

        return $total;
    }

    public static function getPhongBan_GD($limit, $offset) {
        $db = new Database();
        $conn = $db->connect();
    
        $stmt = $conn->prepare("
            SELECT 
                PhongBan.*, 
                Profile.hoten 
            FROM PhongBan 
            LEFT JOIN Profile 
            ON 
                PhongBan.quanlyid = Profile.empid
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $phongBans = [];    
        while ($row = $result->fetch_assoc()) {
            $phongBan = [
                'PhongID' => $row['phongid'],
                'TenPhong' => $row['tenphong'],
                'QuanLyID' => $row['quanlyid'],
                'SoThanhVien' => $row['sothanhvien'],
                'HoTen' => $row['hoten']
            ];
            
         
            $phongBans[] = $phongBan;
        }

        $stmt->close();
        $db->close();
        return $phongBans;
    }
    
    public static function countAllPhongBan_GD() {
        $db = new Database();
        $conn = $db->connect();
    
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM PhongBan");
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
    
        $stmt->close();
        $db->close();
    
        return $row['total'];
    }

    public static function searchPhongBan_GD($searchTerm_PB, $limit, $offset) {
        $searchTerm = "%$searchTerm_PB%";
        $query = "
            SELECT PhongBan.phongid, PhongBan.tenphong, PhongBan.quanlyid, Profile.hoten 
            FROM PhongBan
            LEFT JOIN Profile 
            ON PhongBan.quanlyid = Profile.empid
            WHERE Profile.hoten LIKE ? OR PhongBan.tenphong LIKE ?
            LIMIT ? OFFSET ?";
    
        $db = new Database();
        $conn = $db->connect();
        $stmt = $conn->prepare($query);
    
        // Truyền các giá trị trực tiếp vào bind_param
        $stmt->bind_param('ssii', $searchTerm, $searchTerm, $limit, $offset);
        
        $stmt->execute();
        $result = $stmt->get_result();
    
        $rooms = [];
        while ($row = $result->fetch_assoc()) {
            $room= [
                'PhongID' => $row['phongid'],
                'TenPhong' => $row['tenphong'],
                'QuanLyID' => $row['quanlyid'],
                'HoTen' => $row['hoten']
            ];
            $rooms[] = $room;
        }
    
        $stmt->close();
        $db->close();
    
        return $rooms;
    }
    
    public static function countSearchPhongBan_GD($searchTerm_PB) {
        $searchTerm = "%$searchTerm_PB%";
        $query = "
            SELECT COUNT(PhongBan.phongid) as total
            FROM PhongBan
            LEFT JOIN Profile 
            ON PhongBan.quanlyid = Profile.empid
            WHERE Profile.hoten LIKE ? OR PhongBan.tenphong LIKE ?";
    
        $db = new Database();
        $conn = $db->connect();
        $stmt = $conn->prepare($query);
    

        $stmt->bind_param('ss', $searchTerm, $searchTerm);
        
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $total = $row['total'];
        
        $stmt->close();
        $db->close();
    
        return $total;
    }

    public static function GetTime_checkInOut($empID, $apiUrl) {
        $url = $apiUrl . '/CurrentTimesheet/' . $empID;

        if (!self::isApiAvailable($url)) {
            return null;
        }
        

        $response = file_get_contents($url);
        $checkinoutData = json_decode($response, true);
        $CheckInOut = [
            'STT' => null,
            'Time_checkin' => null,
            'Time_checkout' => null,
            'WorkFromHome' => 0,
            'Nghi' => 0,
            'Late' => 0,
            'Overtime' => 0,
        ];
        if($checkinoutData){
            $CheckInOut['STT'] =  $checkinoutData['stt'];
            $CheckInOut['Time_checkin'] =  $checkinoutData['timecheckin'];
            $CheckInOut['Time_checkout'] =  $checkinoutData['timecheckout'];
            $CheckInOut['WorkFromHome'] =  $checkinoutData['workfromhome'];
            $CheckInOut['Nghi'] =  $checkinoutData['nghi'];
            $CheckInOut['Late'] =  $checkinoutData['late'];
            $CheckInOut['Overtime'] =  $checkinoutData['overtime'];
        }
        return $CheckInOut;

    }

    /*
    public function UpCheckInOut2($empID) {
        $db = new Database();
        $conn = $db->connect();
        $statusinout = '';
        $url = $this->apiUrl . '/CurrentTimesheet/' . $empID;

        $CheckInOut = $this->GetTime_checkInOut($empID);   
        if ($CheckInOut) {
    
            if (is_null($CheckInOut['Time_checkin'])) {
                $updateStmt = $conn->prepare("
                    UPDATE Check_inout 
                    SET Time_checkin = CURTIME(), 
                        Late = CASE WHEN CURTIME() > '08:00:00' THEN 1 ELSE 0 END 
                    WHERE STT = ?
                ");
                $updateStmt->bind_param("i",  $CheckInOut ['STT']);
                $updateStmt->execute();
                $updateStmt->close();
                $statusinout = 'checked-in';
            } 
            else if (is_null($CheckInOut['Time_checkout'])) {
                $updateStmt = $conn->prepare("
                    UPDATE Check_inout 
                    SET Time_checkout = CURTIME(), 
                        Overtime = CASE WHEN CURTIME() > '17:00:00' THEN 1 ELSE 0 END 
                    WHERE STT = ?
                ");
                $updateStmt->bind_param("i", $CheckInOut['STT']);
                $updateStmt->execute();
                $updateStmt->close();
                $statusinout = 'checked-out';
            } else {
                $statusinout = 'already-checked-out';
            }
        } else {
            $insertStmt = $conn->prepare("
                INSERT INTO Check_inout (EmpID, Date_checkin, Time_checkin, Late) 
                VALUES (?, CURDATE(), CURTIME(), CASE WHEN CURTIME() > '08:00:00' THEN 1 ELSE 0 END)
            ");
            $insertStmt->bind_param("i", $empID);
            $insertStmt->execute();
            $insertStmt->close();
            $statusinout = 'checked-in';
        }
    
        $db->close();
    
        return $statusinout;
    }*/

    public static function UpCheckInOut($empID) {
        $db = new Database();
        $conn = $db->connect();
        

        $stmt = $conn->prepare("SELECT stt, time_checkin, time_checkout FROM Check_inout WHERE empid = ? AND date_checkin = CURDATE();");
        $stmt->bind_param("i", $empID);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $statusinout = '';
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
    
            if (is_null($row['time_checkin'])) {
                $updateStmt = $conn->prepare("
                    UPDATE Check_inout 
                    SET time_checkin = CURTIME(), 
                        late = CASE WHEN CURTIME() > '08:00:00' THEN 1 ELSE 0 END 
                    WHERE stt = ?
                ");
                $updateStmt->bind_param("i", $row['stt']);
                $updateStmt->execute();
                $updateStmt->close();
                $statusinout = 'checked-in';
            } 
            else if (is_null($row['time_checkout'])) {
                $updateStmt = $conn->prepare("
                    UPDATE Check_inout 
                    SET time_checkout = CURTIME(), 
                        overtime = CASE WHEN CURTIME() > '17:00:00' THEN 1 ELSE 0 END 
                    WHERE stt = ?
                ");
                $updateStmt->bind_param("i", $row['stt']);
                $updateStmt->execute();
                $updateStmt->close();
                $statusinout = 'checked-out';
            } else {
                $statusinout = 'already-checked-out';
            }
        } else {
            $insertStmt = $conn->prepare("
                INSERT INTO Check_inout (empid, date_checkin, time_checkin, late, nghi) 
                VALUES (?, CURDATE(), CURTIME(), CASE WHEN CURTIME() > '08:00:00' THEN 1 ELSE 0 END, 0)
            ");
            $insertStmt->bind_param("i", $empID);
            $insertStmt->execute();
            $insertStmt->close();
            $statusinout = 'checked-in';
        }
    
        $stmt->close();
        $db->close();
    
        return $statusinout;
    }

    private function callAPI($method, $url, $data) {
        $curl = curl_init();
    
        switch ($method){
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }
    
        // Các tùy chọn cURL khác
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    
        // Thực hiện request
        $result = curl_exec($curl);
        if(!$result){die("Connection Failure");}
        curl_close($curl);
        
        return json_decode($result, true);  // Trả về dữ liệu dưới dạng mảng
    }
    
}
    

?>
