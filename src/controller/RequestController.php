<?php
require_once __DIR__ . '/../model/UserModel.php';
require_once __DIR__ . '/../model/RequestModel.php';
require_once __DIR__ . '/../controller/HomeController.php';

class RequestController {
    public function GetRequestPage() {
        if (isset($_SESSION['user'])) {
            $title='Yêu cầu';
            $user_id = $_SESSION['user']['EmpID'];
            $role = $_SESSION['user']['Role'];
            switch ($role) {
                case 'Nhân viên':
                    $file = "./views/pages/NV/request.phtml";
                    break;
                case 'Quản lý':
                    $file = "./views/pages/QL/home_QL.phtml";
                    break;
                default:
                    $file = null;
                    $title = 'Error';
                    break;

            }
            
                ob_start();
                require($file);
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