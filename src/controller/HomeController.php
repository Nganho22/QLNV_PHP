<?php

class HomeController{
    public function login() {
        $title='Login';
        $message='';
        if (isset($_GET['status'])) {
            if ($_GET['status'] === 'logged_out') {
                $message = 'You have been logged out successfully.';
            }
            elseif ($_GET['status'] === 'needlogin') {
                $message = 'You need to login!';
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