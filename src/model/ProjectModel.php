<?php
require_once __DIR__ . '/../config/MySQLconnect.php';

class ProjectModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();   
    }

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
    
}
?>