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
    public static function getCountPrj_NV($user_id) {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT COUNT(DISTINCT projectid) AS total_projects 
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

    public static function getCountPrj_QL($user_id) {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT COUNT(DISTINCT projectid) AS total_projects 
                                    FROM Project 
                                    WHERE quanly = ?");
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

        $stmt = $conn->prepare("SELECT COUNT(projectid) AS total_projects 
                                    FROM project");
        $stmt->execute();
        $result = $stmt->get_result();
        $cPrj = $result->fetch_assoc()['total_projects'];

        $stmt->close();
        $db->close();
        
        return $cPrj;
    }

    
    public static function getListPrj_NV($user_id) {
        $db = new Database();
        $conn = $db->connect();
    
        $stmt = $conn->prepare("SELECT DISTINCT projectid FROM Time_sheet WHERE empid = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $projects = [];
        while ($row = $result->fetch_assoc()) {
            $project_id = $row['projectid'];
    
            $project_stmt = $conn->prepare("SELECT tiendo FROM Project WHERE projectid = ?");
            $project_stmt->bind_param("i", $project_id);
            $project_stmt->execute();
            $project_result = $project_stmt->get_result();
    
            if ($project_result->num_rows > 0) {
                $project_data = $project_result->fetch_assoc();
                $tien_do_str = $project_data['tiendo'];
                
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

    public static function getProjects_NV($empID) {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT Project.ten, Project.hanchotdukien, Time_sheet.trangthai
                                FROM Time_sheet
                                JOIN Project ON Time_sheet.projectid = Project.projectid 
                                WHERE Time_sheet.empid = ? AND Time_sheet.trangthai = 'Chưa hoàn thành' LIMIT 3" );
        $stmt->bind_param("i", $empID);
        $stmt->execute();
        $result = $stmt->get_result();

        $projects = [];
        while ($row = $result->fetch_assoc()) {
            $project =[
                'Ten' => $row['ten'],
                'HanChotDuKien' => $row['hanchotdukien'],
                'TrangThai' => $row['trangthai']

            ];
            $projects[]=$project;
        }

        $stmt->close();
        $db->close();
        return $projects;
    }

    public static function getCountProjects_NV($empID) {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT Project.ten, Project.hanchotdukien, Time_sheet.trangthai
                                FROM Time_sheet
                                JOIN Project ON Time_sheet.ProjectID = Project.ProjectID 
                                WHERE Time_sheet.empid = ? " );
        $stmt->bind_param("i", $empID);
        $stmt->execute();
        $result = $stmt->get_result();

        $projects = [];
        while ($row = $result->fetch_assoc()) {
            $project =[
                'Ten' => $row['ten'],
                'HanChotDuKien' => $row['hanchotdukien'],
                'TrangThai' => $row['trangthai']

            ];
            $projects[]=$project;
        }
        $stmt->close();
        $db->close();
        return $projects;
    }

    public static function getProjects_QL($empID) {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT p.ten AS ProjectName, 
                                       p.tiendo, p.tinhtrang
                                FROM Project p
                                JOIN Profile prof ON p.quanly = prof.empid
                                WHERE prof.empid = ? AND p.tinhtrang <> 'Đã hoàn thành'
                                ORDER BY p.ngaygiao DESC");
        $stmt->bind_param("i", $empID);
        $stmt->execute();
        $result = $stmt->get_result();

        $projects = [];
        while ($row = $result->fetch_assoc()) {
            $project=[
            'ProjectName' => $row['ProjectName'],
            'TienDo' => $row['tiendo'],
            'TinhTrang' => $row['tinhtrang']
            ];

            $projects[] = $project;
        }

        $stmt->close();
        $db->close();
        return $projects;
    }

    public static function getProjects_GD($limit, $offset) {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT * FROM Project LIMIT ? OFFSET ?");
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $projects = [];
        while ($row = $result->fetch_assoc()) {
            $project = [
                'ProjectID' => $row['projectid'],
                'Ten' => $row['ten'],
                'NgayGiao' => $row['ngaygiao'],
                'HanChotDuKien' => $row['hanchotdukien'],
                'HanChot' => $row['hanchot'],
                'TienDo' => $row['tiendo'],
                'SoGioThucHanh' => $row['sogiothuchanh'],
                'PhongID' => $row['phongid'],
                'QuanLy' => $row['quanly'],
                'TinhTrang' => $row['tinhtrang'],
            ];
            $projects[]= $project;
        }

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
                SELECT ten, ngaygiao, tiendo
                FROM Project
                WHERE ten LIKE ?
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
                $project = [
                    'Ten' => $row['ten'],
                    'NgayGiao' => $row['ngaygiao'],
                    'TienDo' => $row['tiendo'],
                ];
                $projects[] = $project;
            }
    
            $stmt->close();
            $db->close();
    
            return $projects;
        }

    public static function countSearchProject_GD($searchTerm_PJ) {
        $searchTerm = "%$searchTerm_PJ%";
        $query = "
            SELECT COUNT(projectid) as total
            FROM Project
            WHERE ten LIKE ?";

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


    
    public static function getListPrj_QL($user_id) {
        $db = new Database();
        $conn = $db->connect();
    
        $stmt = $conn->prepare("SELECT projectid, tiendo FROM Project WHERE quanly = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $projects = [];
        while ($row = $result->fetch_assoc()) {
            $tienDo = str_replace('%', '', $row['tiendo']);
    
            $projects[] = [
                'ProjectID' => $row['projectid'],
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
    
        $stmt = $conn->prepare("SELECT projectid, tiendo FROM Project");
        $stmt->execute();
        $result = $stmt->get_result();
    
        $projects = [];
        while ($row = $result->fetch_assoc()) {
            $tienDo = str_replace('%', '', $row['tiendo']);
    
            $projects[] = [
                'ProjectID' => $row['projectid'],
                'TienDo' => (int)$tienDo
            ];
        }
    
        $stmt->close();
        $db->close();
    
        return $projects;
    }
    



    //========= Quản lý + Giám đốc ================
    public static function getProjectCountsByEmpID($user_id) {
        $db = new Database();
        $conn = $db->connect();
    
        // Kiểm tra nếu $user_id là 3 thì bỏ qua điều kiện WHERE
        if ($user_id == 3) {
            $query = "
                SELECT 
                    COUNT(projectid) as total, 
                    SUM(CASE WHEN tinhtrang = 'Đã hoàn thành' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN tinhtrang = 'Chưa hoàn thành' THEN 1 ELSE 0 END) as not_completed
                FROM Project
            ";
            $stmt = $conn->prepare($query);
        } else {
            $query = "
                SELECT 
                    COUNT(projectid) as total, 
                    SUM(CASE WHEN tinhtrang = 'Đã hoàn thành' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN tinhtrang = 'Chưa hoàn thành' THEN 1 ELSE 0 END) as not_completed
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
            $listPrj = "SELECT projectid, ten, ngaygiao, hanchotdukien, hanchot, tiendo, sogiothuchanh, phongid, quanly, tinhtrang  FROM Project ORDER BY ngaygiao DESC LIMIT ? OFFSET ?";
            $stmt = $conn->prepare($listPrj);
            $stmt->bind_param('ii', $limit, $offset);
        } else {
            // Select projects where QuanLy equals $user_id and order by NgayGiao in descending order
            $listPrj = "SELECT projectid, ten, ngaygiao, hanchotdukien, hanchot, tiendo, sogiothuchanh, phongid, quanly, tinhtrang  FROM Project WHERE quanly = ? ORDER BY ngaygiao DESC LIMIT ? OFFSET ?";
            $stmt = $conn->prepare($listPrj);
            $stmt->bind_param('iii', $user_id, $limit, $offset);
        }
    
        $stmt->execute();
        $result = $stmt->get_result();
        //$list = $result->fetch_all(MYSQLI_ASSOC);

        $list = [];
        while ($row = $result->fetch_assoc()) {
            $row = [
                'ProjectID' => $row['projectid'] ?? 'N/A',
                'Ten' => $row['ten'] ?? 'N/A',
                'NgayGiao' => $row['ngaygiao'] ?? 'N/A',
                'HanChotDuKien' => $row['hanchotdukien'] ?? 'N/A',
                'HanChot' => $row['hanchot'] ?? 'N/A',
                'TienDo' => $row['tiendo'] ?? 'N/A',
                'SoGioThucHanh' => $row['sogiothuchanh'] ?? 'N/A',
                'PhongID' => $row['phongid'] ?? 'N/A',
                'QuanLy' => $row['quanly'] ?? 'N/A',
                'TinhTrang' => $row['tinhtrang'] ?? 'N/A'
            ];
            $list[] = $row;
        }
    
        $stmt->close();
        $db->close();
        return $list;
    }
    

    public static function getProjectsAndCount($user_id, $searchTerm = '', $types = [], $statuses = [], $departments = [], $limit, $offset) {
        $db = new Database();
        $conn = $db->connect();

        $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM Project WHERE 1=1";

        if ($user_id != 3) {
            $sql .= " AND quanly = ?";
        }
        
        if (!empty($searchTerm)) {
            $sql .= " AND ten LIKE ?";
            $searchTerm = '%' . $searchTerm . '%';
        }
        
        if (!empty($types)) {
            $typePlaceholders = implode(',', array_fill(0, count($types), '?'));
            $sql .= " AND tiendo IN ($typePlaceholders)";
        }
        
        if (!empty($statuses)) {
            $statusPlaceholders = implode(',', array_fill(0, count($statuses), '?'));
            $sql .= " AND tinhtrang IN ($statusPlaceholders)";
        }

        if (!empty($departments)) {
            $departmentPlaceholders = implode(',', array_fill(0, count($departments), '?'));
            $sql .= " AND phongid IN ($departmentPlaceholders)";
        }

        $sql .= " ORDER BY ngaygiao DESC LIMIT ? OFFSET ?";
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
        //$projects = $result->fetch_all(MYSQLI_ASSOC);

        $projects = [];
        while ($row = $result->fetch_assoc()) {
            $row = [
                'ProjectID' => $row['projectid'] ?? 'N/A',
                'Ten' => $row['ten'] ?? 'N/A',
                'NgayGiao' => $row['ngaygiao'] ?? 'N/A',
                'HanChotDuKien' => $row['hanchotdukien'] ?? 'N/A',
                'HanChot' => $row['hanchot'] ?? 'N/A',
                'TienDo' => $row['tiendo'] ?? 'N/A',
                'SoGioThucHanh' => $row['sogiothuchanh'] ?? 'N/A',
                'PhongID' => $row['phongid'] ?? 'N/A',
                'QuanLy' => $row['quanly'] ?? 'N/A',
                'TinhTrang' => $row['tinhtrang'] ?? 'N/A'
            ];
            $projects[] = $row;
        }

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

        $query = "SELECT empid, hoten FROM Profile WHERE role = 'Quản lý'";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        
        $result = $stmt->get_result();
        //$requests = $result->fetch_all(MYSQLI_ASSOC);

        $requests = [];
        while ($row = $result->fetch_assoc()) {
            $row = [
                'EmpID' => $row['empid'] ?? 'N/A',
                'HoTen' => $row['hoten'] ?? 'N/A'
            ];
            $requests[] = $row;
        }

        $stmt->close();
        $db->close();
        return $requests;
    }

    public static function getProjectIDs() {
        $db = new Database();
        $conn = $db->connect();

        $sql = "SELECT projectid FROM Project";
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

        $PhongIDQuery = "SELECT phongid FROM Profile WHERE EmpID = ?";
        $stmt = $conn->prepare($PhongIDQuery);
        $stmt->bind_param('i', $QuanLy);
        $stmt->execute();
        $stmt->bind_result($PhongID);
        $stmt->fetch();
        $stmt->close();

        $query = "INSERT INTO Project (ten, ngaygiao, hanchotdukien, hanchot, tiendo, sogiothuchanh, phongid, quanly, tinhtrang)
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
            SELECT DISTINCT projectid
            FROM Time_sheet
            WHERE empid = ?
        ";
        $projectStmt = $conn->prepare($projectQuery);
        $projectStmt->bind_param('i', $user_id);
        $projectStmt->execute();
        $projectResult = $projectStmt->get_result();
    
        $projectIDs = [];
        while ($row = $projectResult->fetch_assoc()) {
            $projectIDs[] = $row['projectid'];
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
                COUNT(projectid) as total, 
                SUM(CASE WHEN tinhtrang = 'Đã hoàn thành' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN tinhtrang = 'Chưa hoàn thành' THEN 1 ELSE 0 END) as not_completed
            FROM Project
            WHERE projectid IN (" . implode(',', array_fill(0, count($projectIDs), '?')) . ")
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
            WHERE projectid IN ($placeholders)
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
        
        $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM Project WHERE projectid IN (" . implode(',', array_fill(0, count($projectIDs), '?')) . ")";
        
        if (!empty($searchTerm)) {
            $sql .= " AND ten LIKE ?";
            $searchTerm = '%' . $searchTerm . '%';
        }
        
        if (!empty($types)) {
            $typePlaceholders = implode(',', array_fill(0, count($types), '?'));
            $sql .= " AND tiendo IN ($typePlaceholders)";
        }
        
        if (!empty($statuses)) {
            $statusPlaceholders = implode(',', array_fill(0, count($statuses), '?'));
            $sql .= " AND tinhtrang IN ($statusPlaceholders)";
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
        //$projects = $result->fetch_all(MYSQLI_ASSOC);

        $projects = [];
        while ($row = $result->fetch_assoc()) {
            $row = [
                'ProjectID' => $row['projectid'] ?? 'N/A',
                'Ten' => $row['ten'] ?? 'N/A',
                'NgayGiao' => $row['ngaygiao'] ?? 'N/A',
                'HanChotDuKien' => $row['hanchotdukien'] ?? 'N/A',
                'HanChot' => $row['hanchot'] ?? 'N/A',
                'TienDo' => $row['tiendo'] ?? 'N/A',
                'SoGioThucHanh' => $row['sogiothuchanh'] ?? 'N/A',
                'PhongID' => $row['phongid'] ?? 'N/A',
                'QuanLy' => $row['quanly'] ?? 'N/A',
                'TinhTrang' => $row['tinhtrang'] ?? 'N/A'
            ];
            $projects[] = $row;
        }
    
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
                pr.hoten, 
                pb.tenphong 
            FROM 
                Project p
            LEFT JOIN 
                Profile pr ON p.quanly = pr.empid
            LEFT JOIN 
                PhongBan pb ON p.phongid = pb.phongid
            WHERE 
                p.projectid = ?";

        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $ProjectID);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $request = $result->fetch_assoc();
        $stmt->close();
        $db->close();
        if ($request) {
            $requests = [
                'ProjectID' => $request['projectid'] ?? 'N/A',
                'Ten' => $request['ten'] ?? 'N/A',
                'NgayGiao' => $request['ngaygiao'] ?? 'N/A',
                'HanChotDuKien' => $request['hanchotdukien'] ?? 'N/A',
                'HanChot' => $request['hanchot'] ?? 'N/A',
                'TienDo' => $request['tiendo'] ?? 'N/A',
                'SoGioThucHanh' => $request['sogiothuchanh'] ?? 'N/A',
                'PhongID' => $request['phongid'] ?? 'N/A',
                'QuanLy' => $request['quanly'] ?? 'N/A',
                'TinhTrang' => $request['tinhtrang'] ?? 'N/A',
                'HoTen' => $request['hoten'] ?? 'N/A',
                'TenPhong' => $request['tenphong'] ?? 'N/A'
            ];
            return $requests;
        }
    }

    public static function getEmployeesByUserDepartment($user_id) {
        $db = new Database();
        $conn = $db->connect();
    
        $query = "SELECT p2.empid, p2.hoten 
                  FROM Profile p1
                  LEFT JOIN Profile p2 ON p1.phongid = p2.phongid
                  WHERE p1.empid = ?";
                  
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        
        $result = $stmt->get_result();

        $employees = [];
        while ($row = $result->fetch_assoc()) {
            $row = [
                'EmpID' => $row['empid'] ?? 'N/A',
                'HoTen' => $row['hoten'] ?? 'N/A'
            ];
            $employees[] = $row;
        }

        $stmt->close();
        $db->close();
        return $employees;
    }

    public static function getTimeSheetsAndCount($user_id, $employeeIDs, $ProjectID, $searchTerm = '', $limit, $offset) {
        $db = new Database();
        $conn = $db->connect();
        
        if ($user_id == 3) {
            $sql = "
                SELECT SQL_CALC_FOUND_ROWS t.*, p.hoten as AssigneeName, p2.tenphong 
                FROM Time_sheet t
                LEFT JOIN Profile p ON t.empid = p.empid
                LEFT JOIN PhongBan p2 ON t.phongban = p2.phongid
                WHERE t.projectid = ?
            ";
        } else {
            $sql = "
                SELECT SQL_CALC_FOUND_ROWS t.*, p.hoten as AssigneeName, p2.tenphong 
                FROM Time_sheet t
                LEFT JOIN Profile p ON t.empid = p.empid
                LEFT JOIN PhongBan p2 ON t.phongban = p2.phongid
                WHERE t.empid IN (" . implode(',', array_fill(0, count($employeeIDs), '?')) . ")
                AND t.projectid = ?
            ";
        }
    
        if (!empty($searchTerm)) {
            $sql .= " AND t.nguoigui LIKE ?";
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

        foreach ($timeSheets as &$timeSheet) {
            $timeSheet = [
                'TenPhong' => $timeSheet['tenphong'] ?? 'N/A',
                'ProjectID' => $timeSheet['projectid'] ?? 'N/A',
                'Time_sheetID' => $timeSheet['time_sheetid'] ?? 'N/A',
                'EmpID' => $timeSheet['empid'] ?? 'N/A',
                'TenDuAn' => $timeSheet['tenduan'] ?? 'N/A',
                'NguoiGui' => $timeSheet['nguoigui'] ?? 'N/A',
                'PhongBan' => $timeSheet['phongban'] ?? 'N/A',
                'TrangThai' => $timeSheet['trangthai'] ?? 'N/A',
                'SoGioThucHien' => $timeSheet['sogiothuchien'] ?? 'N/A',
                'NgayGiao' => $timeSheet['ngaygiao'] ?? 'N/A',
                'HanChot' => $timeSheet['hanchot'] ?? 'N/A',
                'DiemThuong' => $timeSheet['diemthuong'] ?? 'N/A',
                'Tre' => $timeSheet['tre'] ?? 'N/A',
                'NoiDung' => $timeSheet['noidung'] ?? 'N/A'
            ];
        }
    
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

        $queryTenDuAn = "SELECT ten FROM Project WHERE projectid = ?";
        $stmtTenDuAn = $conn->prepare($queryTenDuAn);
        $stmtTenDuAn->bind_param('s', $projectID);
        $stmtTenDuAn->execute();
        $stmtTenDuAn->bind_result($TenDuAn);
        $stmtTenDuAn->fetch();
        $stmtTenDuAn->close();
    
        // Truy vấn lấy NguoiGui từ bảng Profile
        $queryNguoiGui = "SELECT hoten FROM Profile WHERE empid = ?";
        $stmtNguoiGui = $conn->prepare($queryNguoiGui);
        $stmtNguoiGui->bind_param('i', $assignee);
        $stmtNguoiGui->execute();
        $stmtNguoiGui->bind_result($NguoiGui);
        $stmtNguoiGui->fetch();
        $stmtNguoiGui->close();
        $TrangThai = 'Chưa hoàn thành';
        $SoGio = 0;

        $query = "INSERT INTO Time_sheet (projectid, empid, tenduan, nguoigui, phongban, trangthai, sogiothuchien, ngaygiao, hanchot, diemthuong, noidung)
                  VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param('sissssissis', $projectID, $assignee, $TenDuAn, $NguoiGui, $PhongBan, $TrangThai, $SoGio, $today, $deadline, $reward, $description);
        
        $result = $stmt->execute();
        
        $stmt->close();
        $db->close();
        
        return $result;
    }

    public static function getTimeSheetID_QL($employeeIDs, $projectID) {
        $db = new Database();
        $conn = $db->connect();
    
        $sql = "
            SELECT time_sheetid, empid, diemthuong, hanchot, sogiothuchien
            FROM Time_sheet 
            WHERE empid IN (" . implode(',', array_fill(0, count($employeeIDs), '?')) . ") 
            AND projectid = ?
        ";
    
        $params = array_merge($employeeIDs, [$projectID]);
        $types = str_repeat('i', count($employeeIDs)) . 's';
    
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
    
        $result = $stmt->get_result();
        $timeSheetIDs = $result->fetch_all(MYSQLI_ASSOC); 
        
        $stmt->close();
        $db->close();
        
        //return array_column($timeSheetIDs, 'time_sheetid'); 
        return $timeSheetIDs;
    }
    
    public static function UpdateProjectStatus($projectID_s, $newStatus, $totalTime) {
        $db = new Database();
        $conn_s = $db->connect();
    
        if ($newStatus === 'Hoàn thành') {
            $tiendo = '100%';
        } else {
            $tiendo = '0%';
        }
    
        $query = "UPDATE Project SET tinhtrang = ?, tiendo = ?, sogiothuchanh = ? WHERE projectid = ?";
        $stmt = $conn_s->prepare($query);
        
        if ($stmt === false) {
            echo "Lỗi khi chuẩn bị statement cho Project update: " . $conn_s->error;
            return false;
        }
    
        $stmt->bind_param('ssii', $newStatus, $tiendo, $totalTime, $projectID_s);
        $result = $stmt->execute();
        
        $stmt->close();
        $db->close();
    
        return $result;
    }

    public static function UpdateTimeSheetStatus($projectID_s, $timeSheetStatus, $employeeIDs, $tre) {    
        //$timeSheetIDs = self::getTimeSheetID_QL($employeeIDs, $projectID_s);
        if (1) {
            $db = new Database();
            $conn_s = $db->connect();
            $queryUpdateTimeSheet = "
                UPDATE Time_sheet 
                SET trangthai = ?, tre = ?
                WHERE time_sheetid = ?
            ";
    
            $stmtUpdateTimeSheet = $conn_s->prepare($queryUpdateTimeSheet);
            $stmtUpdateTimeSheet->bind_param("sii", $timeSheetStatus, $tre ,$employeeIDs);
            $stmtUpdateTimeSheet->execute();
    
            // foreach ($employeeIDs as $time_sheetid) {
            //     $stmtUpdateTimeSheet->bind_param("iii", $timeSheetStatus, $tre ,$time_sheetid);
            //     $stmtUpdateTimeSheet->execute();
            // }
            $stmtUpdateTimeSheet->close();
            $db->close();
        }
    
        return true;
    }
    

    
    public static function updatePointProfile($employeeIDs, $point) {
        //$timeSheetIDs = self::getTimeSheetID_QL($employeeIDs, $projectID_s);
    
        $db = new Database();
        $conn = $db->connect();
    
        $query = "UPDATE Profile SET diemthuong = diemthuong + ? WHERE empid = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $employeeIDs, $point);
        $stmt->execute();
    
        // foreach ($employeeIDs as $timeSheet) {
        //     $stmt->bind_param("ii", $timeSheet, $point);
        //     $stmt->execute();
        // }
    
        $stmt->close();
        $db->close();
        
        return true;
    }
    
    
    public static function updatePointFelicitation ($employeeIDs, $point, $user_id, $currentDate) {
        //$timeSheetIDs = self::getTimeSheetID_QL($employeeIDs, $projectID_s);
        $NoiDung = 'Nhờ hoàn thành Time-sheet';

        $db = new Database();
        $conn = $db->connect();

        $query = "INSERT INTO Felicitation (point, date, noidung, nguoinhan, nguoitang) VALUES (?,?,?,?,?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("issii", $point, $currentDate, $NoiDung, $employeeIDs, $user_id);
        $result = $stmt->execute();

        $stmt->close();
        $db->close();
        return $result;
    } 
    
    

    //================ Time Sheet ======================
    public static function getDetailTimeSheet($timeSheetID) {
        $db = new Database();
        $conn = $db->connect();
    
        $query = "SELECT * FROM Time_sheet WHERE time_sheetid = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $timeSheetID);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $timeSheets = $result->fetch_assoc();
    
        $stmt->close();
        $db->close();

        if ($timeSheets) {
            $timeSheet = [
                'Time_sheetID' => $timeSheets['time_sheetid'] ?? 'N/A',
                'ProjectID' => $timeSheets['projectid'] ?? 'N/A',
                'EmpID' => $timeSheets['empid'] ?? 'N/A',
                'TenDuAn' => $timeSheets['tenduan'] ?? 'N/A',
                'NguoiGui' => $timeSheets['nguoigui'] ?? 'N/A',
                'PhongBan' => $timeSheets['phongban'] ?? 'N/A',
                'TrangThai' => $timeSheets['trangthai'] ?? 'N/A',
                'SoGioThucHien' => $timeSheets['sogiothuchien'] ?? 'N/A',
                'NgayGiao' => $timeSheets['ngaygiao'] ?? 'N/A',
                'HanChot' => $timeSheets['hanchot'] ?? 'N/A',
                'DiemThuong' => $timeSheets['diemthuong'] ?? 'N/A',
                'Tre' => $timeSheets['tre'] ?? 'N/A',
                'NoiDung' => $timeSheets['noidung'] ?? 'N/A'
            ];
            return $timeSheet;
        }

    }
}
?>