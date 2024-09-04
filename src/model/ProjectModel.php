<?php
require_once __DIR__ . '/../config/MySQLconnect.php';

class ProjectModel {
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

    //========= Quản lý + Giám đốc ================
    public static function getProjectCountsByEmpID($user_id) {
        $db = new Database();
        $conn = $db->connect();
    
        // Kiểm tra nếu $user_id là 3 thì bỏ qua điều kiện WHERE
        if ($user_id == 3) {
            $query = "
                SELECT 
                    COUNT(ProjectID) as total, 
                    SUM(CASE WHEN TinhTrang = 'Đã hoàn thành' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN TinhTrang = 'Chưa hoàn thành' THEN 1 ELSE 0 END) as not_completed
                FROM Project
            ";
            $stmt = $conn->prepare($query);
        } else {
            $query = "
                SELECT 
                    COUNT(ProjectID) as total, 
                    SUM(CASE WHEN TinhTrang = 'Đã hoàn thành' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN TinhTrang = 'Chưa hoàn thành' THEN 1 ELSE 0 END) as not_completed
                FROM Project 
                WHERE QuanLy = ?
            ";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('i', $user_id);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $counts = $result->fetch_assoc();
    
        $stmt->close();
        $db->close();
        
        return [
            'total' => $counts['total'],
            'completed' => $counts['completed'],
            'not_completed' => $counts['not_completed']
        ];
    }

    public static function getListProject($user_id, $limit, $offset) {
        $db = new Database();
        $conn = $db->connect();
        
        // Check if the $user_id is 3
        if ($user_id == 3) {
            // Select all projects and order by NgayGiao in descending order
            $listPrj = "SELECT * FROM Project ORDER BY NgayGiao DESC LIMIT ? OFFSET ?";
            $stmt = $conn->prepare($listPrj);
            $stmt->bind_param('ii', $limit, $offset);
        } else {
            // Select projects where QuanLy equals $user_id and order by NgayGiao in descending order
            $listPrj = "SELECT * FROM Project WHERE QuanLy = ? ORDER BY NgayGiao DESC LIMIT ? OFFSET ?";
            $stmt = $conn->prepare($listPrj);
            $stmt->bind_param('iii', $user_id, $limit, $offset);
        }
    
        $stmt->execute();
        $result = $stmt->get_result();
        $list = $result->fetch_all(MYSQLI_ASSOC);
    
        $stmt->close();
        $db->close();
        return $list;
    }
    

    public static function getProjectsAndCount($user_id, $searchTerm = '', $types = [], $statuses = [], $departments = [], $limit, $offset) {
        $db = new Database();
        $conn = $db->connect();

        $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM Project WHERE 1=1";

        if ($user_id != 3) {
            $sql .= " AND QuanLy = ?";
        }
        
        if (!empty($searchTerm)) {
            $sql .= " AND Ten LIKE ?";
            $searchTerm = '%' . $searchTerm . '%';
        }
        
        if (!empty($types)) {
            $typePlaceholders = implode(',', array_fill(0, count($types), '?'));
            $sql .= " AND TienDo IN ($typePlaceholders)";
        }
        
        if (!empty($statuses)) {
            $statusPlaceholders = implode(',', array_fill(0, count($statuses), '?'));
            $sql .= " AND TinhTrang IN ($statusPlaceholders)";
        }

        if (!empty($departments)) {
            $departmentPlaceholders = implode(',', array_fill(0, count($departments), '?'));
            $sql .= " AND PhongID IN ($departmentPlaceholders)";
        }

        $sql .= " ORDER BY NgayGiao DESC LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);

        $params = [];
        if ($user_id != 3) {
            $params[] = $user_id;
        }
        if (!empty($searchTerm)) {
            $params[] = $searchTerm;
        }
        $params = array_merge($params, $types, $statuses, $departments, [$limit, $offset]);
        
        $stmt->bind_param(str_repeat('s', count($params) - 2) . 'ii', ...$params);
        $stmt->execute();

        $result = $stmt->get_result();
        $projects = $result->fetch_all(MYSQLI_ASSOC);

        $stmt = $conn->query("SELECT FOUND_ROWS() as total");
        $totalResult = $stmt->fetch_assoc()['total'];

        $stmt->close();
        $db->close();

        return ['projects' => $projects, 'total' => $totalResult];
    }

    //========= Tạo prj - Giám đốc ================
    public static function getQuanLyList () {
        $db = new Database();
        $conn = $db->connect();

        $query = "SELECT EmpID, HoTen FROM Profile WHERE Role = 'Quản lý'";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $requests = $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();
        $db->close();
        return $requests;
    }

    public static function getProjectIDs() {
        $db = new Database();
        $conn = $db->connect();

        $sql = "SELECT ProjectID FROM Project";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        $result = $stmt->get_result();
        $projectIDs = $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();
        $db->close();
        return $projectIDs;
    }

    public static function getCreateProject($Ten, $NgayGiao, $HanChotDuKien, $HanChot, $QuanLy) {
        $db = new Database();
        $conn = $db->connect();

        $TienDo = '0%';
        $SoGioThucHanh = 0;
        $PhongID = '';
        $TinhTrang = 'Chưa hoàn thành';

        $PhongIDQuery = "SELECT PhongID FROM Profile WHERE EmpID = ?";
        $stmt = $conn->prepare($PhongIDQuery);
        $stmt->bind_param('i', $QuanLy);
        $stmt->execute();
        $stmt->bind_result($PhongID);
        $stmt->fetch();
        $stmt->close();

        $query = "INSERT INTO Project (Ten, NgayGiao, HanChotDuKien, HanChot, TienDo, SoGioThucHanh, PhongID, QuanLy, TinhTrang)
                  VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param('sssssisis', $Ten, $NgayGiao, $HanChotDuKien, $HanChot, $TienDo, $SoGioThucHanh, $PhongID, $QuanLy, $TinhTrang);
        
        $result = $stmt->execute();
        
        $stmt->close();
        $db->close();
        
        return $result;
    }

    //================ Nhân viên ===================
    private static function getProjectIDsByEmpID($user_id) {
        $db = new Database();
        $conn = $db->connect();
    
        $projectQuery = "
            SELECT DISTINCT ProjectID
            FROM Time_sheet
            WHERE EmpID = ?
        ";
        $projectStmt = $conn->prepare($projectQuery);
        $projectStmt->bind_param('i', $user_id);
        $projectStmt->execute();
        $projectResult = $projectStmt->get_result();
    
        $projectIDs = [];
        while ($row = $projectResult->fetch_assoc()) {
            $projectIDs[] = $row['ProjectID'];
        }
    
        $projectStmt->close();
        $db->close();
    
        return $projectIDs;
    }
    
    public static function getProjectCountsByEmpID_NV($user_id) {
        $projectIDs = self::getProjectIDsByEmpID($user_id);
    
        if (empty($projectIDs)) {
            return [
                'total' => 0,
                'completed' => 0,
                'not_completed' => 0
            ];
        }
    
        $db = new Database();
        $conn = $db->connect();
    
        $query = "
            SELECT 
                COUNT(ProjectID) as total, 
                SUM(CASE WHEN TinhTrang = 'Đã hoàn thành' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN TinhTrang = 'Chưa hoàn thành' THEN 1 ELSE 0 END) as not_completed
            FROM Project
            WHERE ProjectID IN (" . implode(',', array_fill(0, count($projectIDs), '?')) . ")
        ";
    
        $stmt = $conn->prepare($query);
        $stmt->bind_param(str_repeat('s', count($projectIDs)), ...$projectIDs);
    
        $stmt->execute();
        $result = $stmt->get_result();
        $counts = $result->fetch_assoc();
    
        $stmt->close();
        $db->close();
    
        return [
            'total' => $counts['total'],
            'completed' => $counts['completed'],
            'not_completed' => $counts['not_completed']
        ];
    }

    public static function getListProject_NV($user_id, $limit, $offset) {
        $projectIDs = self::getProjectIDsByEmpID($user_id);
        $db = new Database();
        $conn = $db->connect();
    
        if (empty($projectIDs)) {
            return []; 
        }
        $placeholders = implode(',', array_fill(0, count($projectIDs), '?'));
    
        $listPrj = "
            SELECT * FROM Project 
            WHERE ProjectID IN ($placeholders)
            LIMIT ? OFFSET ?
        ";
    
        $stmt = $conn->prepare($listPrj);
        $params = array_merge($projectIDs, [$limit, $offset]);
        $types = str_repeat('s', count($projectIDs)) . 'ii';
        $stmt->bind_param($types, ...$params);
    
        $stmt->execute();
        $result = $stmt->get_result();
        $list = $result->fetch_all(MYSQLI_ASSOC);
    
        $stmt->close();
        $db->close();
        return $list;
    }
    
    public static function getProjectsAndCount_NV($user_id, $searchTerm = '', $types = [], $statuses = [], $limit, $offset) {
        $projectIDs = self::getProjectIDsByEmpID($user_id);
        
        if (empty($projectIDs)) {
            return ['projects' => [], 'total' => 0];
        }
    
        $db = new Database();
        $conn = $db->connect();
        
        $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM Project WHERE ProjectID IN (" . implode(',', array_fill(0, count($projectIDs), '?')) . ")";
        
        if (!empty($searchTerm)) {
            $sql .= " AND Ten LIKE ?";
            $searchTerm = '%' . $searchTerm . '%';
        }
        
        if (!empty($types)) {
            $typePlaceholders = implode(',', array_fill(0, count($types), '?'));
            $sql .= " AND TienDo IN ($typePlaceholders)";
        }
        
        if (!empty($statuses)) {
            $statusPlaceholders = implode(',', array_fill(0, count($statuses), '?'));
            $sql .= " AND TinhTrang IN ($statusPlaceholders)";
        }
    
        $sql .= " LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
    
        $params = $projectIDs;
        if (!empty($searchTerm)) {
            $params[] = $searchTerm;
        }
        $params = array_merge($params, $types, $statuses, [$limit, $offset]);
    
        $stmt->bind_param(str_repeat('s', count($projectIDs)) . str_repeat('s', !empty($searchTerm)) . str_repeat('s', count($types)) . str_repeat('s', count($statuses)) . 'ii', ...$params);
        $stmt->execute();
    
        $result = $stmt->get_result();
        $projects = $result->fetch_all(MYSQLI_ASSOC);
    
        $stmt = $conn->query("SELECT FOUND_ROWS() as total");
        $totalResult = $stmt->fetch_assoc()['total'];
    
        $stmt->close();
        $db->close();
    
        return ['projects' => $projects, 'total' => $totalResult];
    }
    
    //================ Detail ======================

    public static function getDetailProject($ProjectID) {
        $db = new Database();
        $conn = $db->connect();
    
        $query = " 
            SELECT 
                p.*, 
                pr.HoTen, 
                pb.TenPhong 
            FROM 
                Project p
            LEFT JOIN 
                Profile pr ON p.QuanLy = pr.EmpID
            LEFT JOIN 
                PhongBan pb ON p.PhongID = pb.PhongID
            WHERE 
                p.ProjectID = ?";

        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $ProjectID);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $requests = $result->fetch_assoc();
    
        $stmt->close();
        $db->close();
        return $requests;
    }

    public static function getEmployeesByUserDepartment($user_id) {
        $db = new Database();
        $conn = $db->connect();
    
        $query = "SELECT p2.EmpID, p2.HoTen 
                  FROM Profile p1
                  LEFT JOIN Profile p2 ON p1.PhongID = p2.PhongID
                  WHERE p1.EmpID = ?";
                  
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $employees = $result->fetch_all(MYSQLI_ASSOC);
    
        $stmt->close();
        $db->close();
        return $employees;
    }

    public static function getTimeSheetsAndCount($user_id, $employeeIDs, $ProjectID, $searchTerm = '', $limit, $offset) {
        $db = new Database();
        $conn = $db->connect();
        
        if ($user_id == 3) {
            $sql = "
                SELECT SQL_CALC_FOUND_ROWS t.*, p.HoTen as AssigneeName, p2.TenPhong 
                FROM Time_sheet t
                LEFT JOIN Profile p ON t.EmpID = p.EmpID
                LEFT JOIN PhongBan p2 ON t.PhongBan = p2.PhongID
                WHERE t.ProjectID = ?
            ";
        } else {
            $sql = "
                SELECT SQL_CALC_FOUND_ROWS t.*, p.HoTen as AssigneeName, p2.TenPhong 
                FROM Time_sheet t
                LEFT JOIN Profile p ON t.EmpID = p.EmpID
                LEFT JOIN PhongBan p2 ON t.PhongBan = p2.PhongID
                WHERE t.EmpID IN (" . implode(',', array_fill(0, count($employeeIDs), '?')) . ")
                AND t.ProjectID = ?
            ";
        }
    
        if (!empty($searchTerm)) {
            $sql .= " AND t.NguoiGui LIKE ?";
            $searchTerm = '%' . $searchTerm . '%';
        }
    
        $sql .= " LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
    
        if ($user_id == 3) {
            $params = [$ProjectID];
            $types = 's';
        } else {
            $params = array_merge($employeeIDs, [$ProjectID]);
            $types = str_repeat('i', count($employeeIDs)) . 's';
        }
    
        if (!empty($searchTerm)) {
            $params[] = $searchTerm;
            $types .= 's';
        }
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';
    
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
    
        $result = $stmt->get_result();
        $timeSheets = $result->fetch_all(MYSQLI_ASSOC);
    
        // Lấy tổng số lượng bản ghi
        $totalResult = $conn->query("SELECT FOUND_ROWS() as total");
        $total = $totalResult->fetch_assoc()['total'];
    
        $stmt->close();
        $db->close();
    
        return ['timeSheets' => $timeSheets, 'total' => $total];
    }
    
    public static function createTimeSheet($user_id, $projectID, $assignee, $PhongBan, $today, $deadline, $reward, $description) {
        $db = new Database();
        $conn = $db->connect();

        $TenDuAn = '';
        $NguoiGui = '';

        $queryTenDuAn = "SELECT Ten FROM Project WHERE ProjectID = ?";
        $stmtTenDuAn = $conn->prepare($queryTenDuAn);
        $stmtTenDuAn->bind_param('s', $projectID);
        $stmtTenDuAn->execute();
        $stmtTenDuAn->bind_result($TenDuAn);
        $stmtTenDuAn->fetch();
        $stmtTenDuAn->close();
    
        // Truy vấn lấy NguoiGui từ bảng Profile
        $queryNguoiGui = "SELECT HoTen FROM Profile WHERE EmpID = ?";
        $stmtNguoiGui = $conn->prepare($queryNguoiGui);
        $stmtNguoiGui->bind_param('i', $assignee);
        $stmtNguoiGui->execute();
        $stmtNguoiGui->bind_result($NguoiGui);
        $stmtNguoiGui->fetch();
        $stmtNguoiGui->close();
        $TienDo = 0;
        $TrangThai = 'Chưa hoàn thành';
        $SoGio = 0;

        $query = "INSERT INTO Time_sheet (ProjectID, EmpID, TenDuAn, NguoiGui, PhongBan, TienDo, TrangThai, SoGioThucHien, NgayGiao, HanChot, DiemThuong, NoiDung)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param('sisssisissis', $projectID, $assignee, $TenDuAn, $NguoiGui, $PhongBan, $TienDo, $TrangThai, $SoGio, $today, $deadline, $reward, $description);
        
        $result = $stmt->execute();
        
        $stmt->close();
        $db->close();
        
        return $result;
    }

    public static function UpdateProjectStatus($projectID_s, $newStatus) {
        $db = new Database();
        $conn = $db->connect();
        // var_dump($projectID_s);
        // var_dump($newStatus);

        $query = "UPDATE Project SET TinhTrang = ?, TienDo = '100%' WHERE ProjectID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ss', $newStatus, $projectID_s);
        
        $result = $stmt->execute();

        $stmt->close();
        $db->close();
        return $result;
    }

    //================ Time Sheet ======================
    public static function getDetailTimeSheet($timeSheetID) {
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
}
?>