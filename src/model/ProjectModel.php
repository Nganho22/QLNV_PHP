<?php
require_once __DIR__ . '/../config/MySQLconnect.php';

class ProjectModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();   
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
            // Select all projects
            $listPrj = "SELECT * FROM Project LIMIT ? OFFSET ?";
            $stmt = $conn->prepare($listPrj);
            $stmt->bind_param('ii', $limit, $offset);

        } else {
            // Select projects where QuanLy equals $user_id
            $listPrj = "SELECT * FROM Project WHERE QuanLy = ? LIMIT ? OFFSET ?";
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

        $sql .= " LIMIT ? OFFSET ?";
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
    
    //================ Nhân viên ===================

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
    
    public static function createTimeSheet($ProjectID, $EmpID, $HanChot, $DiemThuong, $NoiDung, $TaiLieu) {
        $db = new Database();
        $conn = $db->connect();

        $query = "INSERT INTO Request (EmpID, NguoiGui, Loai, TieuDe, NgayGui, NgayChon ,NoiDung) VALUES (?,?,?,?,?,?,?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i','i');
        $result = $stmt->execute();
        
        $stmt->close();
        $db->close();
        return $result;
    }
}
?>