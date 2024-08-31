<?php
require_once __DIR__ . '/../config/MySQLconnect.php';

class ProjectModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();   
    }



    public function __destruct() {
        $this->db->close(); // Close database connection when the object is destroyed
    }
}
?>