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
       
    }


    public static function checkpw($user_id, $pw) {
       
    }

}
    

?>
