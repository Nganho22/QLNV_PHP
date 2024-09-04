<?php
require_once __DIR__ . '/../config/MySQLconnect.php';

class UserModel {
    public $EmpID;
    public $Role;
    public $HoTen;
    public $Email;
    public $TenTaiKhoan;
    public $TinhTrang;
    public $Phong;
    public $Checkin;
    public $Checkout;
    public $workcheck;

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

    public function clogin($username, $password) {

        $url = $this->apiUrl . '/getActiveProfile?tenTaiKhoan=' . urlencode($username) . '&matKhau=' . urlencode($password);

        if (!$this->isApiAvailable($url)) {
            return null;
        }

        $response = file_get_contents($url);
        $userData = json_decode($response, true);

        if ($userData) {
            $user = [
                'EmpID' => $userData['empID'],
                'PhongID' => $userData['phongID'],
                'HoTen' => $userData['hoTen'],
                'Role' => $userData['role'],
                'Image' => 'public/img/avatar/' . $userData['image'],
                'TenPhong' => $userData['tenPhong']
            ];
            return $user;
        }

    }


    public static function getprofile($user_id){
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT PhongID, Role, HoTen, Email, GioiTinh, SoDienThoai, CCCD, STK, Luong, DiemThuong, DiaChi, Image 
                                FROM Profile WHERE EmpID = ?");
        $stmt->bind_param("i", $user_id,);
        $stmt->execute();
        $result = $stmt->get_result();

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

        if($result->num_rows > 0){
            $u = $result->fetch_assoc();

            $profile['HoTen'] = $u['HoTen'];
            $profile['Role'] = $u['Role'];
            $profile['Email'] = $u['Email'];
            $profile['GioiTinh'] = $u['GioiTinh'];
            $profile['SoDienThoai'] = $u['SoDienThoai'];
            $profile['CCCD'] = $u['CCCD'];
            $profile['STK'] = $u['STK'];
            $profile['Luong'] = $u['Luong'];
            $profile['DiemThuong'] = $u['DiemThuong'];
            $profile['DiaChi'] = $u['DiaChi'];

            $profile['Image'] = 'public/img/avatar/'.$u['Image'];
            $profile['Image_name'] = $u['Image'];

            if (!is_null($u['PhongID'])) {
                $phong_stmt = $conn->prepare("SELECT TenPhong FROM PhongBan WHERE PhongID = ?");
                $phong_stmt->bind_param("i", $u['PhongID']);
                $phong_stmt->execute();
                $phong_result = $phong_stmt->get_result();
                if ($phong_result->num_rows > 0) {
                    $phong_row = $phong_result->fetch_assoc();
                    $profile['TenPhong'] = $phong_row['TenPhong'];
                }
                $phong_stmt->close();
            }
        }
        $profile['EmpID'] = $user_id;
        $stmt->close();
        $db->close();
        return $profile;
    }

    public static function gettimesheet($user_id) {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT * 
                                FROM Time_sheet WHERE EmpID = ?");
        $stmt->bind_param("i", $user_id,);
        $stmt->execute();
        $result = $stmt->get_result();

        $timesheet = array();
        while ($row = $result->fetch_assoc()) {
            $timesheet[] = $row;
        }

        $stmt->close();
        $db->close();
        return $timesheet;
    }

    public static function getCountPrj_NV($user_id) {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT COUNT(DISTINCT ProjectID) AS total_projects 
                                    FROM Time_sheet 
                                    WHERE EmpID = ?");
        $stmt->bind_param("i", $user_id,);
        $stmt->execute();
        $result = $stmt->get_result();
        $cPrj_NV = $result->fetch_assoc()['total_projects'];

        $stmt->close();
        $db->close();
        
        return $cPrj_NV;
    }

    public static function getCountPrj_QL($user_id) {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT COUNT(DISTINCT ProjectID) AS total_projects 
                                    FROM Project 
                                    WHERE QuanLy = ?");
        $stmt->bind_param("i", $user_id,);
        $stmt->execute();
        $result = $stmt->get_result();
        $cPrj_QL = $result->fetch_assoc()['total_projects'];

        $stmt->close();
        $db->close();
        
        return $cPrj_QL;
    }

    public static function getCountPrj_GD() {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT COUNT(ProjectID) AS total_projects 
                                    FROM Project");
        $stmt->execute();
        $result = $stmt->get_result();
        $cPrj = $result->fetch_assoc()['total_projects'];

        $stmt->close();
        $db->close();
        
        return $cPrj;
    }

    public static function getCountNghiPhep($user_id) {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT COUNT(Nghi) as total_nghi
                                    FROM Check_inout
                                    WHERE EmpID = ? and Nghi = 1");
        $stmt->bind_param("i", $user_id,);
        $stmt->execute();
        $result = $stmt->get_result();
        $cNghi = $result->fetch_assoc()['total_nghi'];

        $stmt->close();
        $db->close();
        
        return $cNghi;
    }

    public static function getCountTre($user_id) {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT COUNT(Late) as countTre
                                    FROM Check_inout
                                    WHERE EmpID = ? and Late = 1");
        $stmt->bind_param("i", $user_id,);
        $stmt->execute();
        $result = $stmt->get_result();
        $cTre = $result->fetch_assoc()['countTre'];

        $stmt->close();
        $db->close();
        
        return $cTre;
    }

    public static function getListPrj_NV($user_id) {
        $db = new Database();
        $conn = $db->connect();
    
        $stmt = $conn->prepare("SELECT DISTINCT ProjectID FROM Time_sheet WHERE EmpID = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $projects = [];
        while ($row = $result->fetch_assoc()) {
            $project_id = $row['ProjectID'];
    
            $project_stmt = $conn->prepare("SELECT TienDo FROM Project WHERE ProjectID = ?");
            $project_stmt->bind_param("i", $project_id);
            $project_stmt->execute();
            $project_result = $project_stmt->get_result();
    
            if ($project_result->num_rows > 0) {
                $project_data = $project_result->fetch_assoc();
                $tien_do_str = $project_data['TienDo'];
                
                $tien_do_percentage = (float) str_replace('%', '', $tien_do_str);
                
                $projects[] = [
                    'ProjectID' => $project_id,
                    'TienDo' => $tien_do_percentage
                ];
            }
            $project_stmt->close();
        }
        $stmt->close();
        $db->close();
    
        return $projects;
    }
    
    public static function getListPrj_QL($user_id) {
        $db = new Database();
        $conn = $db->connect();
    
        $stmt = $conn->prepare("SELECT ProjectID, TienDo FROM Project WHERE QuanLy = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $projects = [];
        while ($row = $result->fetch_assoc()) {
            $tienDo = str_replace('%', '', $row['TienDo']);
    
            $projects[] = [
                'ProjectID' => $row['ProjectID'],
                'TienDo' => (int)$tienDo
            ];
        }
    
        $stmt->close();
        $db->close();
    
        return $projects;
    }

    public static function getListPrj_GD() {
        $db = new Database();
        $conn = $db->connect();
    
        $stmt = $conn->prepare("SELECT ProjectID, TienDo FROM Project");
        $stmt->execute();
        $result = $stmt->get_result();
    
        $projects = [];
        while ($row = $result->fetch_assoc()) {
            $tienDo = str_replace('%', '', $row['TienDo']);
    
            $projects[] = [
                'ProjectID' => $row['ProjectID'],
                'TienDo' => (int)$tienDo
            ];
        }
    
        $stmt->close();
        $db->close();
    
        return $projects;
    }
    
    public static function updateProfile ($suser_id, $gioitinh, $cccd, $sdt, $stk, $diachi, $img , $newPass ) {
        $db = new Database();
        $conn = $db->connect();

        if ($newPass) {
            $sql = "UPDATE Profile SET GioiTinh = ?, CCCD = ?, SoDienThoai = ?, STK = ?, DiaChi = ?, Image = ?, MatKhau = ? WHERE EmpID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssi", $gioitinh, $cccd, $sdt, $stk, $diachi, $img, $newPass, $suser_id);
        } else {
            $sql = "UPDATE Profile SET GioiTinh = ?, CCCD = ?, SoDienThoai = ?, STK = ?, DiaChi = ?, Image = ? WHERE EmpID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssi", $gioitinh, $cccd, $sdt, $stk, $diachi, $img, $suser_id);
        }
        $result = $stmt->execute();
        $stmt->close(); // Close statement
        return $result;
    }

    public static function checkpw($user_id, $pw) {
       
    }

    //Phần Home

    // Hàm để lấy PhongID của nhân viên dựa trên EmpID

    public static function getPhongIDByEmpID($empID) {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare(
            "SELECT PhongID 
            FROM Profile 
            WHERE EmpID = ?"
        );
        $stmt->bind_param("i", $empID);
        $stmt->execute();
        $result = $stmt->get_result();

        $phongID = $result->fetch_assoc()['PhongID'];

        $stmt->close();
        $db->close();

        return $phongID;
    }

    //=================================Nhân viên=================================
        //Lấy Deadline từ Time-sheet
    public static function getDeadlinesTimesheet($empID) {
            $db = new Database();
            $conn = $db->connect();
        
            $stmt = $conn->prepare("SELECT Time_sheet.TenDuAn, Time_sheet.HanChot 
                                    FROM Time_sheet
                                    WHERE Time_sheet.EmpID = ?");
            $stmt->bind_param("i", $empID);
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
    
    // Lấy danh sách dự án
    public static function getProjects_NV($empID) {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT Project.Ten, Project.HanChotDuKien, Time_sheet.TrangThai
                                FROM Time_sheet
                                JOIN Project ON Time_sheet.ProjectID = Project.ProjectID 
                                WHERE Time_sheet.EmpID = ? AND Time_sheet.TrangThai = 'Chưa hoàn thành' LIMIT 3" );
        $stmt->bind_param("i", $empID);
        $stmt->execute();
        $result = $stmt->get_result();

        $projects = [];
        while ($row = $result->fetch_assoc()) {
            $projects[] = $row;
        }

        $stmt->close();
        $db->close();
        return $projects;
    }

    public static function getCountProjects_NV($empID) {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT Project.Ten, Project.HanChotDuKien, Time_sheet.TrangThai
                                FROM Time_sheet
                                JOIN Project ON Time_sheet.ProjectID = Project.ProjectID 
                                WHERE Time_sheet.EmpID = ? " );
        $stmt->bind_param("i", $empID);
        $stmt->execute();
        $result = $stmt->get_result();

        $cprojects = [];
        while ($row = $result->fetch_assoc()) {
            $cprojects[] = $row;
        }

        $stmt->close();
        $db->close();
        return $cprojects;
    }
    // Lấy danh sách hoạt động   
    public static function getActivities($empID) {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT Activity.TenHoatDong, emp_activity.ThoiGianThucHien, emp_activity.ThanhTich
                                FROM Activity 
                                JOIN emp_activity ON Activity.ActivityID = emp_activity.ActivityID
                                WHERE emp_activity.EmpID = ? LIMIT 3");
        $stmt->bind_param("i", $empID);
        $stmt->execute();
        $result = $stmt->get_result();

        $activities = [];
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }

        $stmt->close();
        $db->close();
        return $activities;
    }

    public static function getCountActivities($empID) {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT Activity.TenHoatDong, emp_activity.ThoiGianThucHien, emp_activity.ThanhTich
                                FROM Activity 
                                JOIN emp_activity ON Activity.ActivityID = emp_activity.ActivityID
                                WHERE emp_activity.EmpID = ?");
        $stmt->bind_param("i", $empID);
        $stmt->execute();
        $result = $stmt->get_result();

        $cactivities = [];
        while ($row = $result->fetch_assoc()) {
            $cactivities[] = $row;
        }

        $stmt->close();
        $db->close();
        return $cactivities;
    }


    public static function getPoint_Month($empID) {
        $db = new Database();
        $conn = $db->connect();
    
        $stmt = $conn->prepare("
            SELECT MONTH(Date) AS month, SUM(Point) AS total_points
            FROM Felicitation
            WHERE NguoiNhan = ?
            GROUP BY MONTH(Date)
            ORDER BY MONTH(Date)
        ");
        $stmt->bind_param("i", $empID);
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

    
     //===================================Quản lý========================================
     public static function getDeadlinesTimesheet_QL($empID) {
        $db = new Database();
        $conn = $db->connect();
    
        $stmt = $conn->prepare("SELECT Project.Ten, Project.HanChot 
                                FROM Project
                                WHERE Project.QuanLy = ?");
        $stmt->bind_param("i", $empID);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $deadlines = [];
        while ($row = $result->fetch_assoc()) {
            $deadlines[] = [
                'TenDuAn' => $row['Ten'],
                'HanChot' => $row['HanChot']
            ];
        }
    
        $stmt->close();
        $db->close();
        return $deadlines;
    }

     public static function getProjects_QL($empID) {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT p.Ten AS ProjectName, 
                                       p.TienDo, p.TinhTrang
                                FROM Project p
                                JOIN Profile prof ON p.QuanLy = prof.EmpID
                                WHERE prof.EmpID = ? AND p.TinhTrang <> 'Đã hoàn thành'
                                ORDER BY p.NgayGiao DESC");
        $stmt->bind_param("i", $empID);
        $stmt->execute();
        $result = $stmt->get_result();

        $projects = [];
        while ($row = $result->fetch_assoc()) {
            $projects[] = $row;
        }

        $stmt->close();
        $db->close();
        return $projects;
    }
    
     public static function getPhongBanStatistics($empID) {
        $db = new Database();
        $conn = $db->connect();
        
        // Lấy PhongID của quản lý
        $stmt = $conn->prepare("SELECT PhongID FROM Profile WHERE EmpID = ?");
        $stmt->bind_param("i", $empID);
        $stmt->execute();
        $result = $stmt->get_result();
        $phongID = $result->fetch_assoc()['PhongID'];
        $stmt->close();
    
        if (!$phongID) {
            $db->close();
            return [];
        }
    
        $stmt = $conn->prepare("SELECT Profile.EmpID, Profile.HoTen
                                FROM Profile
                                WHERE Profile.PhongID = ?");
        $stmt->bind_param("s", $phongID);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $employees = [];
        while ($row = $result->fetch_assoc()) {
            $employees[] = $row;
        }
    
        $stmt->close();
        $db->close();
        return $employees;
    }

    public static function getWorkFromHomeCountByEmpID($empID) {
        $db = new Database();
        $conn = $db->connect();
        
        $stmt = $conn->prepare("SELECT PhongID FROM Profile WHERE EmpID = ?");
        $stmt->bind_param("i", $empID);
        $stmt->execute();
        $result = $stmt->get_result();
        $phongID = $result->fetch_assoc()['PhongID'];
        $stmt->close();
        
        if (!$phongID) {
            $db->close();
            return 0;
        }
    
        $stmt = $conn->prepare(
            "SELECT COUNT(DISTINCT Check_inout.EmpID) AS countWFH
            FROM Check_inout
            INNER JOIN Profile ON Check_inout.EmpID = Profile.EmpID
            WHERE Profile.PhongID = ? 
            AND Check_inout.WorkFromHome = 1
            AND DATE(Check_inout.Date_checkin) = CURDATE()"
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
        
        $stmt = $conn->prepare("SELECT PhongID FROM Profile WHERE EmpID = ?");
        $stmt->bind_param("i", $empID);
        $stmt->execute();
        $result = $stmt->get_result();
        $phongID = $result->fetch_assoc()['PhongID'];
        $stmt->close();
        
        if (!$phongID) {
            $db->close();
            return 0;
        }
    
        $stmt = $conn->prepare(
            "SELECT COUNT(DISTINCT Check_inout.EmpID) AS absence
            FROM Check_inout
            INNER JOIN Profile ON Check_inout.EmpID = Profile.EmpID
            WHERE Profile.PhongID = ? 
            AND Check_inout.Nghi = 1
            AND DATE(Check_inout.Date_checkin) = CURDATE()"
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
            "SELECT PhongID 
             FROM Profile 
             WHERE EmpID = ?"
        );
        $stmt->bind_param("i", $empID);
        $stmt->execute();
        $result = $stmt->get_result();
        $phongID = $result->fetch_assoc()['PhongID'];
        $stmt->close();
        
        if (!$phongID) {
            $db->close();
            return []; 
        }  
       
        $stmt = $conn->prepare(
            "SELECT 
                PhongBan.PhongID, 
                PhongBan.TenPhong, 
                COUNT(DISTINCT Check_inout.EmpID) AS SoHienDien
            FROM PhongBan
            LEFT JOIN Profile ON PhongBan.PhongID = Profile.PhongID
            LEFT JOIN Check_inout ON Profile.EmpID = Check_inout.EmpID
                AND DATE(Check_inout.Date_checkin) = CURDATE()
                AND Check_inout.Time_checkout IS NULL
            WHERE PhongBan.PhongID = ?
            GROUP BY PhongBan.PhongID, PhongBan.TenPhong"
        );
        $stmt->bind_param("s", $phongID);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $hiendien = [];
        while ($row = $result->fetch_assoc()) {
            $hiendien[] = $row;
        }
        
        $stmt->close();
        $db->close();
        return $hiendien;
    }

    public static function getPhongBan_Checkinout($empID) {
        $db = new Database();
        $conn = $db->connect();
    
        $stmtPhongID = $conn->prepare(
            "SELECT PhongID 
             FROM Profile 
             WHERE EmpID = ?"
        );
        $stmtPhongID->bind_param("i", $empID);
        $stmtPhongID->execute();
        $resultPhongID = $stmtPhongID->get_result();
        $phongID = $resultPhongID->fetch_assoc()['PhongID'];
        $stmtPhongID->close();
        
        if (!$phongID) {
            $db->close();
            return [];
        }
    
        $stmt = $conn->prepare(
            "SELECT PhongBan.PhongID, PhongBan.TenPhong, 
                    COALESCE(COUNT(CASE WHEN Check_inout.Time_checkin IS NOT NULL THEN 1 END), 0) AS SoLanCheckin,
                    COALESCE(COUNT(CASE WHEN Check_inout.Time_checkout IS NOT NULL THEN 1 END), 0) AS SoLanCheckout
             FROM Profile
             INNER JOIN PhongBan ON Profile.PhongID = PhongBan.PhongID
             LEFT JOIN Check_inout ON Profile.EmpID = Check_inout.EmpID
                AND DATE(Check_inout.Date_checkin) = CURDATE()
             WHERE PhongBan.PhongID = ?
             GROUP BY PhongBan.PhongID, PhongBan.TenPhong"
        );
        $stmt->bind_param("s", $phongID);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $checkinout = [];
        while ($row = $result->fetch_assoc()) {
            $checkinout[] = $row;
        }
    
        $stmt->close();
        $db->close();
        return $checkinout;
    }

    public static function getEmployeesList_QL($empID) {
        $db = new Database();
        $conn = $db->connect();

        // Lấy PhongID của người dùng hiện tại
        $stmt = $conn->prepare("SELECT PhongID FROM Profile WHERE EmpID = ?");
        $stmt->bind_param("i", $empID);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $phongID = $row['PhongID'];
        $stmt->close();

        // Lấy danh sách nhân viên cùng PhongID, loại bỏ người có EmpID trùng với người quản lý
        $stmt = $conn->prepare("SELECT HoTen, Email FROM Profile WHERE PhongID = ? AND EmpID != ? LIMIT 3");
        $stmt->bind_param("ii", $phongID, $empID);
        $stmt->execute();
        $result = $stmt->get_result();

        $employees = [];
        while ($row = $result->fetch_assoc()) {
            $employees[] = $row;
        }

        $stmt->close();
        $db->close();
        return $employees;
    }

    public static function getTimesheetList($empID) {
        $db = new Database();
        $conn = $db->connect();
    
        // Lấy PhongID của người dùng hiện tại
        $stmt = $conn->prepare("SELECT PhongID FROM Profile WHERE EmpID = ?");
        $stmt->bind_param("i", $empID);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $phongID = $row['PhongID'];
        $stmt->close();
    
        // Lấy danh sách time-sheet của nhân viên cùng PhongID, chỉ lấy 3 người đầu tiên
        $stmt = $conn->prepare("SELECT NgayGiao, NoiDung, NguoiGui 
                                FROM Time_sheet 
                                WHERE EmpID IN (
                                    SELECT EmpID FROM Profile WHERE PhongID = ?
                                ) LIMIT 3");
        $stmt->bind_param("i", $phongID);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $timesheets = [];
        while ($row = $result->fetch_assoc()) {
            $timesheets[] = $row;
        }
    
        $stmt->close();
        $db->close();
        return $timesheets;
    }

    //=================================Giam doc==============================
    public static function getDeadlinesTimesheet_GD() {
        $db = new Database();
        $conn = $db->connect();
    
        $stmt = $conn->prepare(
            "SELECT 
                Project.Ten AS TenDuAn, 
                Project.HanChot AS HanChot,
                Profile.HoTen AS TenQuanLy, 
                Project.PhongID AS PhongID
            FROM 
                Project
            INNER JOIN 
                Profile ON Project.QuanLy = Profile.EmpID
            INNER JOIN 
                PhongBan ON Project.PhongID = PhongBan.PhongID"
        );
        $stmt->execute();
        $result = $stmt->get_result();
    
        $deadlines = [];
        while ($row = $result->fetch_assoc()) {
            $deadlines[] = [
                'TenDuAn' => $row['TenDuAn'],
                'HanChot' => $row['HanChot'],
                'TenQuanLy' => $row['TenQuanLy'],
                'PhongID' => $row['PhongID']
            ];
        }
    
        $stmt->close();
        $db->close();
        return $deadlines;
    }
    

    public static function getProjects_GD($limit, $offset) {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT * FROM Project LIMIT ? OFFSET ?");
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $projects = $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();
        $db->close();
        return $projects;
    }

    public static function countAllProject_GD() {
        $db = new Database();
        $conn = $db->connect();
    
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM Project");
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
    
        $stmt->close();
        $db->close();
    
        return $row['total'];
    }

    public static function searchProject_GD($searchTerm_PJ, $limit_PJ, $offset_PJ) {
            $searchTerm = "%$searchTerm_PJ%";
            $query = "
                SELECT Ten, NgayGiao, TienDo
                FROM Project
                WHERE Ten LIKE ?
                LIMIT ? OFFSET ?";
    
            $db = new Database();
            $conn = $db->connect();
            $stmt = $conn->prepare($query);
            $params = [$searchTerm, $limit_PJ, $offset_PJ];
            $stmt->bind_param('sii', ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
    
            $projects = [];
            while ($row = $result->fetch_assoc()) {
                $projects[] = $row;
            }
    
            $stmt->close();
            $db->close();
    
            return $projects;
        }

    public static function countSearchProject_GD($searchTerm_PJ) {
        $searchTerm = "%$searchTerm_PJ%";
        $query = "
            SELECT COUNT(ProjectID) as total
            FROM Project
            WHERE Ten LIKE ?";

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

    public static function getEmployeesList_GD($limit, $offset) {
        $db = new Database();
        $conn = $db->connect();
    
        $stmt = $conn->prepare("SELECT EmpID, HoTen, Email FROM Profile LIMIT ? OFFSET ?");
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $employees = $result->fetch_all(MYSQLI_ASSOC);
    
        $stmt->close();
        $db->close();
        return $employees;
    }

    public static function countAllEmployees_GD() {
        $db = new Database();
        $conn = $db->connect();
    
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM Profile");
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
            SELECT EmpID, HoTen, Email
            FROM Profile
            WHERE HoTen LIKE ?
            LIMIT ? OFFSET ?";

        $db = new Database();
        $conn = $db->connect();
        $stmt = $conn->prepare($query);
        $params = [$searchTerm, $limit, $offset];
        $stmt->bind_param('sii', ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        $profiles = [];
        while ($row = $result->fetch_assoc()) {
            $profiles[] = $row;
        }

        $stmt->close();
        $db->close();

        return $profiles;
    }

    public static function countSearchProfiles_GD($searchTerm) {
        $searchTerm = "%$searchTerm%";
        $query = "
            SELECT COUNT(EmpID) as total
            FROM Profile
            WHERE HoTen LIKE ?";

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
                Profile.HoTen 
            FROM PhongBan 
            LEFT JOIN Profile 
            ON 
                PhongBan.QuanLyID = Profile.EmpID
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $phongBans = $result->fetch_all(MYSQLI_ASSOC);
    
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
            SELECT PhongBan.PhongID, PhongBan.TenPhong, PhongBan.QuanLyID, Profile.HoTen 
            FROM PhongBan
            LEFT JOIN Profile 
            ON PhongBan.QuanLyID = Profile.EmpID
            WHERE Profile.HoTen LIKE ? OR PhongBan.TenPhong LIKE ?
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
            $rooms[] = $row;
        }
    
        $stmt->close();
        $db->close();
    
        return $rooms;
    }
    
    public static function countSearchPhongBan_GD($searchTerm_PB) {
        $searchTerm = "%$searchTerm_PB%";
        $query = "
            SELECT COUNT(PhongBan.PhongID) as total
            FROM PhongBan
            LEFT JOIN Profile 
            ON PhongBan.QuanLyID = Profile.EmpID
            WHERE Profile.HoTen LIKE ? OR PhongBan.TenPhong LIKE ?";
    
        $db = new Database();
        $conn = $db->connect();
        $stmt = $conn->prepare($query);
    

        $stmt->bind_param('ss', $searchTerm, $searchTerm);
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Lấy giá trị của tổng số lượng
        $row = $result->fetch_assoc();
        $total = $row['total'];
        
        $stmt->close();
        $db->close();
    
        return $total;
    }

    public function GetTime_checkInOut($empID) {
        
        $url = $this->apiUrl . '/CurrentTimesheet/' . $empID;

        if (!$this->isApiAvailable($url)) {
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
            $CheckInOut['Time_checkin'] =  $checkinoutData['timeCheckin'];
            $CheckInOut['Time_checkout'] =  $checkinoutData['timeCheckout'];
            $CheckInOut['WorkFromHome'] =  $checkinoutData['workFromHome'];
            $CheckInOut['Nghi'] =  $checkinoutData['nghi'];
            $CheckInOut['Late'] =  $checkinoutData['late'];
            $CheckInOut['Overtime'] =  $checkinoutData['overtime'];
        }
        return $CheckInOut;

    }

    
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
    }

    public static function UpCheckInOut($empID) {
        $db = new Database();
        $conn = $db->connect();
        

        $stmt = $conn->prepare("SELECT STT, Time_checkin, Time_checkout FROM Check_inout WHERE EmpID = ? AND Date_checkin = CURDATE();");
        $stmt->bind_param("i", $empID);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $statusinout = '';
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
    
            if (is_null($row['Time_checkin'])) {
                $updateStmt = $conn->prepare("
                    UPDATE Check_inout 
                    SET Time_checkin = CURTIME(), 
                        Late = CASE WHEN CURTIME() > '08:00:00' THEN 1 ELSE 0 END 
                    WHERE STT = ?
                ");
                $updateStmt->bind_param("i", $row['STT']);
                $updateStmt->execute();
                $updateStmt->close();
                $statusinout = 'checked-in';
            } 
            else if (is_null($row['Time_checkout'])) {
                $updateStmt = $conn->prepare("
                    UPDATE Check_inout 
                    SET Time_checkout = CURTIME(), 
                        Overtime = CASE WHEN CURTIME() > '17:00:00' THEN 1 ELSE 0 END 
                    WHERE STT = ?
                ");
                $updateStmt->bind_param("i", $row['STT']);
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
