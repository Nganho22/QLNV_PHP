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
    
    public static function searchProjectByName($searchTerm, $limit, $offset) {
        $db = new Database();
        $conn = $db->connect();

        $searchTerm = "%$searchTerm%";

        $query = "SELECT * FROM Project WHERE Ten LIKE ? LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('sii', $searchTerm, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $projects = $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();
        $db->close();

        return $projects;
    }

    public static function countSearchProjectByName($searchTerm) {
        $db = new Database();
        $conn = $db->connect();
    
        $countQuery = "SELECT COUNT(*) as total FROM Project WHERE Ten LIKE ?";
        $stmt = $conn->prepare($countQuery);
        $likeName = '%' . $searchTerm . '%';
        $stmt->bind_param('s', $likeName);
        $stmt->execute();
        $result = $stmt->get_result();
        $total = $result->fetch_assoc()['total'];
    
        $stmt->close();
        $db->close();
        return $total;
    }
    
    public static function filterProjects($user_id, $progressFilters, $statusFilters, $classFilters, $limit, $offset) {
        $db = new Database();
        $conn = $db->connect();
        
        $query = "SELECT * FROM Project WHERE 1=1";
    
        if (!empty($progressFilters)) {
            $placeholders = implode(',', array_fill(0, count($progressFilters), '?'));
            $query .= " AND TienDo IN ($placeholders)";
        }
    
        if (!empty($statusFilters)) {
            $placeholders = implode(',', array_fill(0, count($statusFilters), '?'));
            $query .= " AND TinhTrang IN ($placeholders)";
        }
    
        if (!empty($classFilters)) {
            $placeholders = implode(',', array_fill(0, count($classFilters), '?'));
            $query .= " AND PhongID IN ($placeholders)";
        }
    
        if ($user_id != 3) {
            $query .= " AND QuanLy = ?";
        }
    
        $query .= " LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($query);
        $params = array_merge($progressFilters, $statusFilters, $classFilters);
        if ($user_id != 3) {
            $params[] = $user_id;
        }
        $params[] = $limit;
        $params[] = $offset;
    
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $projects = $result->fetch_all(MYSQLI_ASSOC);
    
        $stmt->close();
        $db->close();
    
        return $projects;
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
}
?>