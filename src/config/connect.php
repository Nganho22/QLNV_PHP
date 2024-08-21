<?php
class Database {
    private $sever = "localhost";
    private $port = "3307";
    private $user = "root";
    private $pass = "root";
    private $dbs = "QLNV_UDPT";
    private static $conn = null;

    public function connect() {

        if (self::$conn === null) {
            self::$conn = new mysqli($this->sever, $this->user, $this->pass, $this->dbs, $this->port);
            if(self::$conn->connect_error)
            {
                die("ERROR: Could not connect. " . self::$conn->connect_error);
            }
            self::$conn->set_charset("utf8");
        }
        return self::$conn;

    }
    public function close() {
        if (self::$conn != null){
            self::$conn->close();
            self::$conn = null;
        }
    }

    
}
?>
