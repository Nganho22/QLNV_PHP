<?php
require_once __DIR__ . '/../model/UserModel.php';
require_once __DIR__ . '/../controller/HomeController.php';
class UserController{
    public function checklogin(){
        $username = isset($_POST['username']) ? $_POST['username'] : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $user = UserModel::clogin($username, $password);
        $str = 'Wrong username or password, please check again';
        if (is_null($user['EmpID'])) {
            $title = 'Login';
            $error_message = "Thông tin đăng nhập sai, vui lòng thử lại";
            require("./views/pages/login.phtml");
        } else {
            $_SESSION['logged_in'] = true;
            $_SESSION['user'] = $user;
            if (isset($_SESSION['redirect_url'])) {
                $redirect_url = $_SESSION['redirect_url'];
                unset($_SESSION['redirect_url']);
            } else {
                $redirect_url = '/QLNV_PHP/src/index.php?action=home&status=logged_in';
            }
            if (strpos($redirect_url, 'status=logged_in') === false) {
                if (strpos($redirect_url, '?') !== false) {
                    $redirect_url .= '&status=logged_in';
                } else {
                    $redirect_url .= '?status=logged_in';
                }
            }
            header('Location: ' . $redirect_url);
            exit();
        }
    }


}
?>