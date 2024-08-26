<?php
require_once __DIR__ . '/../model/PassModel.php';
require_once __DIR__ . '/../controller/HomeController.php';

class ProjectController {
    public function GetProjectPage() {
        if (isset($_SESSION['user'])) {
            $title='Cập nhật Profile';
            $user_id = $_SESSION['user']['EmpID'];
            $profile = UserModel::getprofile($user_id);
            
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
}
?>