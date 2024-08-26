<?php
require_once __DIR__ . '/../model/UserModel.php';
require_once __DIR__ . '/../model/PassModel.php';
require_once __DIR__ . '/../controller/HomeController.php';

class ProjectController {
    public function GetProjectPage() {
        if (isset($_SESSION['user'])) {
            $title='Danh sách Project';
            $user_id = $_SESSION['user']['EmpID'];
            $role = $_SESSION['user']['Role'];
            $profile = UserModel::getprofile($user_id);
            
            ob_start();
            require("./views/pages/project_list.phtml");
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