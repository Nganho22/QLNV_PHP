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
    
    public static function getProjects_GD() {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT * FROM Project LIMIT 3");
        $stmt->execute();
        $result = $stmt->get_result();
        $projects = $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();
        $db->close();
        return $projects;
    }


    // Lấy danh sách hoạt động
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

    public static function UpCheckInOut($empID) {
        $db = new Database();
        $conn = $db->connect();
    
        $stmt = $conn->prepare("SELECT STT, Time_checkin, Time_checkout FROM Check_inout WHERE EmpID = ? AND Date_checkin = CURDATE()");
        $stmt->bind_param("i", $empID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            if (is_null($row['Time_checkout'])) {
                $updateStmt = $conn->prepare("UPDATE Check_inout SET Time_checkout = CURTIME() WHERE STT = ?");
                $updateStmt->bind_param("i", $row['STT']);
                $updateStmt->execute();
                $updateStmt->close();
                $statusinout = 'checked-out';
            } else {
                $statusinout = 'already-checked-out';
            }
        } else {
            $insertStmt = $conn->prepare("INSERT INTO Check_inout (EmpID, Date_checkin, Time_checkin ,Late) VALUES (?, CURDATE(), CURTIME(),0);");
            $insertStmt->bind_param("i", $empID);
            $insertStmt->execute();
            $insertStmt->close();
            $statusinout = 'checked-in';
        }
    
        $stmt->close();
        $db->close();
    
        return $statusinout;
    }
    
    // Lấy thông tin chấm công
    public static function getCheckInOut($empID) {
        $db = new Database();
        $conn = $db->connect();
    
        // Lấy ngày hiện tại
        $today = date('Y-m-d');
    
        $stmt = $conn->prepare("SELECT Time_checkin, Time_checkout 
                                FROM Check_inout 
                                WHERE EmpID = ? AND Date_checkin = ?");
        $stmt->bind_param("is", $empID, $today);
        $stmt->execute();
        $result = $stmt->get_result();
        $checkInOut = $result->fetch_assoc();
    
        $stmt->close();
        $db->close();
        return $checkInOut;
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

    public static function getHienDien() {
        $db = new Database();
        $conn = $db->connect();
        
        $stmt = $conn->prepare(
            "SELECT PhongBan.PhongID, PhongBan.TenPhong, COUNT(DISTINCT Check_inout.EmpID) AS SoHienDien
            FROM Profile
            INNER JOIN PhongBan ON Profile.PhongID = PhongBan.PhongID
            LEFT JOIN Check_inout ON Profile.EmpID = Check_inout.EmpID
                AND DATE(Check_inout.Date_checkin) = CURDATE()
            GROUP BY PhongBan.PhongID, PhongBan.TenPhong"
        );
        
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
    public static function getPhongBan_Checkinout() {
        $db = new Database();
        $conn = $db->connect();
    
        // Câu lệnh SQL để đếm số lượt Check-in và Check-out của nhân viên trong ngày hôm nay cho mỗi phòng ban
        $stmt = $conn->prepare(
            "SELECT PhongBan.PhongID, PhongBan.TenPhong, 
                    COALESCE(COUNT(CASE WHEN Check_inout.Time_checkin IS NOT NULL THEN 1 END), 0) AS SoLanCheckin,
                    COALESCE(COUNT(CASE WHEN Check_inout.Time_checkout IS NOT NULL THEN 1 END), 0) AS SoLanCheckout
             FROM Profile
             INNER JOIN PhongBan ON Profile.PhongID = PhongBan.PhongID
             LEFT JOIN Check_inout ON Profile.EmpID = Check_inout.EmpID
                AND DATE(Check_inout.Date_checkin) = CURDATE()
             GROUP BY PhongBan.PhongID, PhongBan.TenPhong"
        );
        
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

    public static function getEmployeesList_GD() {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT EmpID, HoTen, Email FROM Profile LIMIT 5");
        $stmt->execute();
        $result = $stmt->get_result();
        $employees = $result->fetch_all(MYSQLI_ASSOC);

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

    public static function getPhongBan_GD() {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT * FROM PhongBan LIMIT 3");
        $stmt->execute();
        $result = $stmt->get_result();
        $phongBans = $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();
        $db->close();
        return $phongBans;
    }

    public static function GetTime_checkInOut($empID) {
        $db = new Database();
        $conn = $db->connect();
        $stmt = $conn->prepare("SELECT Time_checkin, Time_checkout, WorkFromHome, Nghi FROM Check_inout WHERE EmpID = ? AND Date_checkin =  CURDATE();");
        $stmt->bind_param("i", $empID);
        $stmt->execute();
        $result = $stmt->get_result();
        $CheckInOuts = [
            'Time_checkin' => null,
            'Time_checkout' => null,
            'WorkFromHome' => '0',
            'Nghi' => '0',
        ];

        if($result->num_rows > 0){
            $u = $result->fetch_assoc();

            $CheckInOuts['Time_checkin'] = $u['Time_checkin'];
            $CheckInOuts['Time_checkout'] = $u['Time_checkout'];
            $CheckInOuts['WorkFromHome'] = $u['WorkFromHome'];
            $CheckInOuts['Nghi'] = $u['Nghi'];
        }
        $stmt->close();
        $db->close();
        return $CheckInOuts;
    }


}
    

?>
