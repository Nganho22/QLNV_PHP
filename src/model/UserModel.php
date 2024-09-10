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

    public static function clogin($username, $password) {
        $apiUrl='http://localhost:9003/apiProfile';

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

    public static function getprofile($user_id){

        $apiUrl='http://localhost:9003/apiProfile';
        
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





    public static function getCountNghiPhep($user_id) {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT COUNT(nghi) as total_nghi
                                    FROM Check_inout
                                    WHERE empid = ? and nghi = 1");
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

        $stmt = $conn->prepare("SELECT COUNT(late) as countTre
                                    FROM Check_inout
                                    WHERE empid = ? and late = 1");
        $stmt->bind_param("i", $user_id,);
        $stmt->execute();
        $result = $stmt->get_result();
        $cTre = $result->fetch_assoc()['countTre'];

        $stmt->close();
        $db->close();
        
        return $cTre;
    }

    public static function updateProfile ($suser_id, $gioitinh, $cccd, $sdt, $stk, $diachi, $img , $newPass ) {
        $db = new Database();
        $conn = $db->connect();

        if ($newPass) {
            $sql = "UPDATE Profile SET gioitinh = ?, cccd = ?, sodienthoai = ?, stk = ?, diachi = ?, image = ?, matkhau = ? WHERE empid = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssi", $gioitinh, $cccd, $sdt, $stk, $diachi, $img, $newPass, $suser_id);
        } else {
            $sql = "UPDATE Profile SET gioitinh = ?, cccd = ?, sodienthoai = ?, stk = ?, diachi = ?, image = ? WHERE empid = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssi", $gioitinh, $cccd, $sdt, $stk, $diachi, $img, $suser_id);
        }
        $result = $stmt->execute();
        $stmt->close(); // Close statement
        return $result;
    }
    //Phần Home

    // Hàm để lấy PhongID của nhân viên dựa trên EmpID

    public static function getPhongIDByEmpID($empID) {
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
        $db->close();

        return $phongID;
    }

    public static function gettimesheet($user_id) {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT * 
                                FROM Time_sheet WHERE empid = ?");
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

    //=================================Nhân viên=================================
    public static function getProjects_NV($empID) {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT Project.ten, Project.hanchotdukien, Time_sheet.trangthai, Project.projectid
                                FROM Time_sheet
                                JOIN Project ON Time_sheet.projectid = Project.projectid 
                                WHERE Time_sheet.empid = ? AND Time_sheet.trangthai = 'Chưa hoàn thành'" );
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
                                WHERE Time_sheet.empid = ? " );
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
    
    public static function getProjectsList_NV($empID, $limit, $offset) {
        $db = new Database();
        $conn = $db->connect();

        $query = "
            SELECT Project.Ten, Project.HanChotDuKien, Time_sheet.TrangThai, Project.ProjectID
                                FROM Time_sheet
                                JOIN Project ON Time_sheet.ProjectID = Project.ProjectID 
                                WHERE Time_sheet.empid = ? AND Time_sheet.TrangThai = 'Chưa hoàn thành'
                                LIMIT ? OFFSET ?";
    
        $stmt = $conn->prepare($query);
        $stmt->bind_param('iii', $empID, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $projects = $result->fetch_all(MYSQLI_ASSOC);
    
        $stmt->close();
        $db->close();
        return $projects;
    }
    
    public static function countProjectsList_NV($empID) {
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
    
        // Đếm số lượng nhân viên trong phòng ban
        $query = "
            SELECT COUNT(*) as total
            FROM Time_sheet
            JOIN Project ON Time_sheet.ProjectID = Project.ProjectID 
            WHERE Time_sheet.empid = ? AND Time_sheet.TrangThai = 'Chưa hoàn thành'";
    
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $empID);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
    
        $stmt->close();
        $db->close();
    
        return $row['total'];
    }
    
    //Lấy Deadline từ Time-sheet
    public static function getDeadlinesTimesheet($empID) {
            $db = new Database();
            $conn = $db->connect();
        
            $stmt = $conn->prepare("SELECT Time_sheet.tenduan, Time_sheet.hanchot 
                                    FROM Time_sheet
                                    WHERE Time_sheet.empid = ?");
            $stmt->bind_param("i", $empID);
            $stmt->execute();
            $result = $stmt->get_result();
        
            $deadlines = [];
            while ($row = $result->fetch_assoc()) {
                $deadlines[] = [
                    'TenDuAn' => $row['tenduan'],
                    'HanChot' => $row['hanchot']
                ];
            }
        
            $stmt->close();
            $db->close();
            return $deadlines;
    }
    
    public static function getPoint_Month($empID) {
        $db = new Database();
        $conn = $db->connect();
    
        $stmt = $conn->prepare("
            SELECT MONTH(date) AS month, SUM(point) AS total_points
            FROM Felicitation
            WHERE nguoinhan = ?
            GROUP BY MONTH(date)
            ORDER BY MONTH(date)
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

    public static function getCountPrj_NV($user_id) {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT COUNT(DISTINCT ProjectID) AS total_projects 
                                    FROM Time_sheet 
                                    WHERE empid = ?");
        $stmt->bind_param("i", $user_id,);
        $stmt->execute();
        $result = $stmt->get_result();
        $cPrj_NV = $result->fetch_assoc()['total_projects'];

        $stmt->close();
        $db->close();
        
        return $cPrj_NV;
    }

    public static function getListPrj_NV($user_id) {
        $db = new Database();
        $conn = $db->connect();
    
        $stmt = $conn->prepare("SELECT DISTINCT ProjectID FROM Time_sheet WHERE empid = ?");
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
     //===================================Quản lý========================================
     public static function searchProfiles_QL($empID, $searchTerm, $limit, $offset) {
        $searchTerm = "%$searchTerm%";
        // Tìm kiếm danh sách nhân viên trong phòng ban với từ khóa tìm kiếm và phân trang
        $query = "
            SELECT empid, HoTen, Email
            FROM Profile
            WHERE PhongID = (
                SELECT PhongID 
                FROM Profile 
                WHERE empid = ?
            ) AND HoTen LIKE ? AND empid <> ?
            LIMIT ? OFFSET ?";
            
        $db = new Database();
        $conn = $db->connect();
        $stmt = $conn->prepare($query);
        $params = [$empID, $searchTerm, $empID, $limit, $offset];
        $stmt->bind_param('isiii', ...$params);
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
     
     public static function countSearchProfiles_QL($empID, $searchTerm) {
        $searchTerm = "%$searchTerm%";
        // Đếm số lượng nhân viên trong phòng ban với từ khóa tìm kiếm
        $query = "
            SELECT COUNT(empid) as total
            FROM Profile
            WHERE PhongID = (
                SELECT PhongID 
                FROM Profile 
                WHERE empid = ?
            ) AND HoTen LIKE ? AND empid <> ?";
        
        $db = new Database();
        $conn = $db->connect();
        $stmt = $conn->prepare($query);
        $params = [$empID, $searchTerm, $empID];
        $stmt->bind_param('isi', ...$params);
        $stmt->execute();

        $result = $stmt->get_result();
        $total = $result->fetch_assoc()['total'];
    
        $stmt->close();
        $db->close();
    
        return $total;
    }
    
     public static function countAllEmployees_QL($empID) {
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
    
        // Đếm số lượng nhân viên trong phòng ban
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
    
     public static function getTimesheetList_QL($empID, $limit, $offset) {
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
    
        // Lấy danh sách time-sheet của các nhân viên thuộc phòng ban với phân trang
        $query = "
            SELECT NgayGiao, NoiDung, NguoiGui, Time_sheetID 
            FROM Time_sheet 
            WHERE empid IN (
                SELECT empid FROM Profile WHERE PhongBan = ? AND empid <> ?
            )
            LIMIT ? OFFSET ?";
    
        $stmt = $conn->prepare($query);
        $stmt->bind_param('siii', $phongID, $empID, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $timesheets = $result->fetch_all(MYSQLI_ASSOC);
    
        $stmt->close();
        $db->close();
        return $timesheets;
    }
    
     public static function countAllTimesheet_QL($empID) {
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
    
        // Đếm số lượng time-sheet của các nhân viên trong phòng ban
        $query = "
            SELECT COUNT(*) as total
            FROM Time_sheet 
            WHERE empid IN (
                SELECT empid FROM Profile WHERE PhongBan = ? AND empid <> ?
            )";
    
        $stmt = $conn->prepare($query);
        $stmt->bind_param('si', $phongID, $empID);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
    
        $stmt->close();
        $db->close();
    
        return $row['total'];
    }

     public static function getProjects_QL($empID) {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT p.Ten AS ProjectName, 
                                       p.TienDo, p.TinhTrang
                                FROM Project p
                                JOIN Profile prof ON p.QuanLy = prof.empid
                                WHERE prof.empid = ? AND p.TinhTrang <> 'Đã hoàn thành'
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
    
     public static function getDeadlinesTimesheet_QL($empID) {
        $db = new Database();
        $conn = $db->connect();
    
        $stmt = $conn->prepare("SELECT Project.ten, Project.hanchot 
                                FROM Project
                                WHERE Project.quanly = ?");
        $stmt->bind_param("i", $empID);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $deadlines = [];
        while ($row = $result->fetch_assoc()) {
            $deadlines[] = [
                'TenDuAn' => $row['ten'],
                'HanChot' => $row['hanchot']
            ];
        }
    
        $stmt->close();
        $db->close();
        return $deadlines;
    }


    
     public static function getPhongBanStatistics($empID) {
        $db = new Database();
        $conn = $db->connect();
        
        // Lấy PhongID của quản lý
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

    public static function getEmployeesList_QL($empID, $limit, $offset) {
        $db = new Database();
        $conn = $db->connect();

        // Lấy PhongID của người dùng hiện tại
        $stmt = $conn->prepare("SELECT phongid FROM Profile WHERE empid = ?");
        $stmt->bind_param("i", $empID);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $phongID = $row['phongid'];
        $stmt->close();

        // Lấy danh sách nhân viên cùng PhongID, loại bỏ người có EmpID trùng với người quản lý
        $stmt = $conn->prepare("SELECT empid, hoten, email
                                FROM Profile
                                WHERE phongid = ? AND empid <> ?
                                LIMIT ? OFFSET ?");
        $stmt->bind_param("siii", $phongID, $empID, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();

        $employees = [];
        while ($row = $result->fetch_assoc()) {
            $employee =[
                'HoTen' => $row['hoten'],
                'Email' => $row['email']
                ];
            $employees[] = $employee;
        }

        $stmt->close();
        $db->close();
        return $employees;
    }

    public static function getTimesheetList($empID) {
        $db = new Database();
        $conn = $db->connect();
    
        $stmt = $conn->prepare("SELECT phongid FROM Profile WHERE empid = ?");
        $stmt->bind_param("i", $empID);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $phongID = $row['phongid'];
        $stmt->close();
    
        $stmt = $conn->prepare("SELECT ngaygiao, noidung, nguoigui 
                                FROM Time_sheet 
                                WHERE empid IN (
                                    SELECT empid FROM Profile WHERE phongid = ?
                                ) LIMIT 3");
        $stmt->bind_param("i", $phongID);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $timesheets = [];
        while ($row = $result->fetch_assoc()) {
            $timesheet =[
                'NgayGiao' => $row['ngaygiao'],
                'NoiDung' => $row['noidung'],
                'NguoiGui' => $row['nguoigui'],
                ];
            $timesheets[] = $timesheet;
        }
    
        $stmt->close();
        $db->close();
        return $timesheets;
    }

    //=================================Giam doc==============================
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
    
    public static function getDeadlinesTimesheet_GD() {
        $db = new Database();
        $conn = $db->connect();
    
        $stmt = $conn->prepare(
            "SELECT 
                Project.ten,
                Project.hanchot,
                Profile.hoten,
                Project.phongid
            FROM 
                Project
            INNER JOIN 
                Profile ON Project.quanly = Profile.empid
            INNER JOIN 
                PhongBan ON Project.phongid = PhongBan.phongid"
        );
        $stmt->execute();
        $result = $stmt->get_result();
    
        $deadlines = [];
        while ($row = $result->fetch_assoc()) {
            $deadline = [
                'TenDuAn' => $row['ten'],
                'HanChot' => $row['hanchot'],
                'TenQuanLy' => $row['hoten'],
                'PhongID' => $row['phongid']
            ];
            $deadlines[] = $deadline;
        }
    
        $stmt->close();
        $db->close();
        return $deadlines;
    }
    
    public static function getEmployeesList_GD($limit, $offset) {
        $db = new Database();
        $conn = $db->connect();
    
        $stmt = $conn->prepare("SELECT empid, hoten, email FROM Profile LIMIT ? OFFSET ?");
        $stmt->bind_param("ii", $limit, $offset);
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
