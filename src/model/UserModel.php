<?php
require_once __DIR__ . '/../config/connect.php';

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


    function __construct() {
        $this->EmpID = "";
        $this->Role = "";
        $this->HoTen = "";
        $this->Email = "";
        $this->TenTaiKhoan = "";
        $this->TinhTrang = "";
        $this->Phong = "";
        $this->Checkin = "";
        $this->Checkout = "";
        $this->workcheck = "";
    }

    public static function clogin($username, $password) {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT EmpID, HoTen, Role, PhongID, Image FROM Profile WHERE TenTaiKhoan = ? AND MatKhau = ? AND TinhTrang = '1'");
        
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = [
            'EmpID' => null,
            'TenPhong' => null,
            'HoTen' => null,
            'Role' => null,
            'Image' => null
        ];
        if($result->num_rows > 0){
            $u = $result->fetch_assoc();

            $user['EmpID'] = $u['EmpID'];
            $user['HoTen'] = $u['HoTen'];
            $user['Role'] = $u['Role'];
            $user['Image'] = 'public/img/avatar/'.$u['Image'];

            $phong_stmt = $conn->prepare("SELECT TenPhong FROM PhongBan WHERE PhongID = ?");
            $phong_stmt->bind_param("i", $u['PhongID']);
            $phong_stmt->execute();
            $phong_result = $phong_stmt->get_result();
            $phong_row = $phong_result->fetch_assoc();
            $user['TenPhong'] = $phong_row['TenPhong'];
            $phong_stmt->close();
        }
        $stmt->close();
        $db->close();
        return $user;

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
    // Lấy danh sách dự án
    public static function getProjectsForNV($empID) {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT Project.Ten, Project.HanChotDuKien, Project.TinhTrang 
                                FROM Assignment 
                                JOIN Project ON Assignment.ProjectID = Project.ProjectID 
                                WHERE Assignment.EmpID = ?");
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

    

    // Lấy danh sách hoạt động
    public static function getActivities($empID) {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT TenHoatDong, TrangThai 
                                FROM Activity 
                                WHERE EXISTS (
                                    SELECT * FROM Assignment 
                                    WHERE Assignment.EmpID = ? AND Assignment.ProjectID = Activity.ActivityID
                                )");
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

    // Lấy thông tin chấm công
    public static function getCheckInOut($empID) {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT Date_checkin, Time_checkin, Date_checkout, Time_checkout 
                                FROM Check_inout 
                                WHERE EmpID = ?");
        $stmt->bind_param("i", $empID);
        $stmt->execute();
        $result = $stmt->get_result();

        $checkInOut = [];
        while ($row = $result->fetch_assoc()) {
            $checkInOut[] = $row;
        }

        $stmt->close();
        $db->close();
        return $checkInOut;
    }

    public static function getPhongBanStatistics() {
        $db = new Database();
        $conn = $db->connect();

        // Cập nhật câu lệnh SQL để chỉ rõ bảng
        $stmt = $conn->prepare("SELECT PhongBan.PhongID, PhongBan.TenPhong, COUNT(Profile.EmpID) AS SoThanhVien
                                FROM Profile 
                                LEFT JOIN PhongBan ON Profile.PhongID = PhongBan.PhongID
                                GROUP BY PhongBan.PhongID, PhongBan.TenPhong");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $phongBans = [];
        while ($row = $result->fetch_assoc()) {
            $phongBans[] = $row;
        }

        $stmt->close();
        $db->close();
        return $phongBans;
    }


    public static function getEmployeesList($empID) {
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
    public static function getManagedProjects($empID) {
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


}
    

?>
