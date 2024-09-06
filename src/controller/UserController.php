<?php
require_once __DIR__ . '/../model/UserModel.php';
require_once __DIR__ . '/../model/PassModel.php';
require_once __DIR__ . '/../controller/HomeController.php';
class UserController{
    public function checklogin(){
        $username = isset($_POST['username']) ? $_POST['username'] : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $apiUrl = 'http://localhost:9003/apiProfile';
        $model = new UserModel($apiUrl);
        $user =  $model->clogin($username, $password);
        $str = 'Wrong username or password, please check again';
        if ($user === null) {
            $title = 'Login';
            $error_message = "Thông tin đăng nhập sai, vui lòng thử lại";
            require("./views/pages/login.phtml");
        } else {
            $_SESSION['logged_in'] = true;
            $_SESSION['user'] = $user;
            $checkInOut =  $model->GetTime_checkInOut($user['EmpID']);

            $_SESSION['CheckInOut']= $checkInOut;
            
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

    public function CheckInOut(){
        if (isset($_SESSION['user'])) {
            $empID = $_SESSION['user']['EmpID'];
            $apiUrl = 'http://localhost:9003/apiProfile';
            $model = new UserModel($apiUrl);

            $statusinout = UserModel::UpCheckInOut($empID);
            $checkInOut =  $model-> GetTime_checkInOut($_SESSION['user']['EmpID']);
            
            
            $_SESSION['CheckInOut']= $checkInOut;
            $redirect_url = isset($_SESSION['redirect_url']) ? $_SESSION['redirect_url'] : '/QLNV_PHP/src/index.php?action=home';
    
            $parsed_url = parse_url($redirect_url);
    
            parse_str($parsed_url['query'] ?? '', $query_params);
    
            $query_params['status'] = $statusinout;
    
            $new_query_string = http_build_query($query_params);
    
            $new_url = $parsed_url['path'];
            if (!empty($new_query_string)) {
                $new_url .= '?' . $new_query_string;
            }

            
            header('Location: ' . $new_url);
            exit();
        } else {
            header('Location: /QLNV_PHP/src/index.php?action=login&status=needlogin');
            exit();
        }
    }
    

    public function logout() {
        $_SESSION['logged_in'] = false;
        session_unset();
        session_destroy();
        
        header('Location: /QLNV_PHP/src/index.php?action=login&status=logged_out');
        exit();
    }
}
?>