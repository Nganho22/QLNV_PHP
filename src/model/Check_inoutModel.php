<?php
require_once __DIR__ . '/../config/MySQLconnect.php';

class Check_inoutModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();   
    }
    public function __destruct() {
        $this->db->close(); 
    }

    public static function getFelicitationCountsByEmpID($user_id) {
        $db = new Database();
        $conn = $db->connect();
        
        $countLateQuery = "SELECT COUNT(*) AS countLate
                                    FROM Check_inout
                                    WHERE EmpID = ?
                                    AND Late = 1";
        $stmt = $conn->prepare($countLateQuery);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $countLate = $result->fetch_assoc()['countLate'];
    
        $absenceQuery = "SELECT COUNT(*) AS absence
                                    FROM Check_inout
                                    WHERE EmpID = ?
                                    AND Nghi = 1";
        $stmt = $conn->prepare($absenceQuery);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $absence = $result->fetch_assoc()['absence']?? 0;

        $stmt->close();
        $db->close();
        return [
            'countLate' => $countLate,
            'absence' => $absence
        ];
    }


    public static function getDeadlinesTimesheet($user_id) {
        $db = new Database();
        $conn = $db->connect();
    
        $stmt = $conn->prepare("SELECT Time_sheet.TenDuAn, Time_sheet.HanChot 
                                FROM Time_sheet
                                WHERE Time_sheet.EmpID = ?");
        $stmt->bind_param("i", $user_id);
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

    public static function getTimeSheetsByEmpID($user_id) {
        $db = new Database();
        $conn = $db->connect();

        $query = "SELECT * FROM Time_sheet WHERE EmpID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $requests = $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();
        $db->close();
        return $requests;
    }

    public static function getCheckInOutData($user_id) {
        $db = new Database();
        $conn = $db->connect();
        
        $query = "SELECT *
                  FROM Check_inout
                  WHERE EmpID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        $stmt->close();
        $db->close();
        return $data;
    }
    
    
}
?>