<?php

class HomeController{
    public function login() {
        $title='Login';
        require(__DIR__ . '/../views/pages/login.phtml');
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
                ob_start();
                require($file);
                $content = ob_get_clean();
                require(__DIR__ . '/../views/template.phtml');
            } else {
                echo "Vai trò không hợp lệ.";
            }
        }
        else{
            $title='Login';
            require(__DIR__ . '/../views/pages/login.phtml');
        }
     
    }
}
?>