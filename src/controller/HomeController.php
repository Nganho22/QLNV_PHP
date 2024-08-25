<?php
require_once __DIR__ . '/../model/UserModel.php';

class HomeController{
    public function login() {
        $title='Login';
        $message='';
        if (isset($_GET['status'])) {
            if ($_GET['status'] === 'logged_out') {
                $message = 'Đăng xuất thành công!';
            }
            elseif ($_GET['status'] === 'needlogin') {
                $message = 'Bạn cần phải đăng nhập!';
            }
        }
        require(__DIR__ . '/../views/pages/login.phtml');
    }

    public function forgotpass() {
        $title='ForgotPass';
        require(__DIR__ . '/../views/pages/forgot_pass.phtml');
    }

    public function home() {
        if (isset($_SESSION['user'])) {
            $Role= $_SESSION['user']['Role'];
            $title = 'Home'; 
            switch ($Role) {
                case 'Nhân viên':
                    $file = "./views/pages/NV/home_NV.phtml";
                    break;
                case 'Quản lý':
                    $file = "./views/pages/QL/home_QL.phtml";
                    break;
                case 'Giám đốc':
                    $file = "./views/pages/GD/home_GD.phtml";

                    break;
                default:
                    $file = null;
                    $title = 'Error';
                    break;

            }
            
            if ($file && file_exists($file)) {
                $message='';
                if (isset($_GET['status'])) {
                    if ($_GET['status'] === 'logged_in') {
                        $message = 'Chúc mừng đăng nhập thành công!';
                    }
                }
                ob_start();
                require($file);
                $content = ob_get_clean();
                require(__DIR__ . '/../views/template.phtml');
            } else {
                echo "Vai trò không hợp lệ.";
            }
        }
        else{
            header('Location: /QLNV_PHP/src/index.php?action=login&status=needlogin');
            exit();
        }
     
    }
    public function GetProfile_page() {
        if (isset($_SESSION['user'])) {
            $title='Profile';
            $user_id = $_SESSION['user']['EmpID'];
            $profile = UserModel::getprofile($user_id);
            $timesheet = UserModel::gettimesheet($user_id);
            $cNghi= UserModel::getCountNghiPhep($user_id);
            $cTre= UserModel::getCountTre($user_id);
            $cPrj = UserModel::getCountPrj_GD();
            $listPrj = UserModel::getListPrj_GD();

            if ($_SESSION['user']['Role'] == 'Nhân viên') {
                $cPrj_NV = UserModel::getCountPrj_NV($user_id);
                $listPrj_NV = UserModel::getListPrj_NV($user_id);
            } elseif ($_SESSION['user']['Role'] == 'Quản lý') {
                $cPrj_QL= UserModel::getCountPrj_QL($user_id);
                $listPrj_QL = UserModel::getListPrj_QL($user_id);
            }

            ob_start();
            require("./views/pages/profile.phtml");
            $content = ob_get_clean();
            require(__DIR__ . '/../views/template.phtml');
        }
        else{
            header('Location: /QLNV_PHP/src/index.php?action=login&status=needlogin');
            exit();
        }
     
    }

    public function GetUpdateprofile_page() {
        if (isset($_SESSION['user'])) {
            ob_start();
            require("./views/pages/update_profile.phtml");
            $content = ob_get_clean();
            require(__DIR__ . '/../views/template.phtml');
        }
        else{
            header('Location: /QLNV_PHP/src/index.php?action=login&status=needlogin');
            exit();
        }
     
    }

    public function Getcheckinout_page() {
        if (isset($_SESSION['user'])) {
            ob_start();
            require("./views/pages/checkin_out.phtml");
            $content = ob_get_clean();
            require(__DIR__ . '/../views/template.phtml');
        }
        else{
            header('Location: /QLNV_PHP/src/index.php?action=login&status=needlogin');
            exit();
        }
     
    }
}
?>