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
            $empID = $_SESSION['user']['EmpID'];
            switch ($Role) {
                case 'Nhân viên':
                    $projects = UserModel::getProjectsForNV($empID);
                    $activities = UserModel::getActivities($empID);
                    $checkInOut = UserModel::getCheckInOut($empID);
                    $file = "./views/pages/NV/home_NV.phtml";
                    break;
                case 'Quản lý':
                    $phongBans = UserModel::getPhongBanStatistics();
                    $employees = UserModel::getEmployeesList($empID);
                    $timesheets = UserModel::getTimesheetList($empID); 
                    $managedProjects = UserModel::getManagedProjects($empID);
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

    public function updateProfile() {
        if (isset($_SESSION['user']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = $_SESSION['user']['EmpID'];
            $gioitinh = $_POST['gender'] ?? '';
            $cccd = $_POST['cccd'] ?? '';
            $sdt = $_POST['phone'] ?? '';
            $stk = $_POST['stk'] ?? '';
            $diachi = $_POST['diachi'] ?? '' ;
            $newPassword = $_POST['newPassword'] ?? '';
            $confirmPassword = $_POST['confirmPassword'] ?? '';
    
            if (!empty($newPassword)) {
                if (empty($confirmPassword)) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Vui lòng nhập lại mật khẩu mới!'
                    ]);
                    exit();
                }
    
                if ($newPassword !== $confirmPassword) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Mật khẩu nhập lại không đúng!'
                    ]);
                    exit();                
                }
            }

            $currentProfile = UserModel::getprofile($user_id);
            $currentImage = $currentProfile['Image_name'];

            // Xử lý ảnh
            $image_path = $currentImage; 

            if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                $target_dir =  __DIR__ . '/../public/img/avatar/';
                $extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
                $target_file = $target_dir . $user_id . "." . $extension;

                if (file_exists($target_file)) {
                    unlink($target_file);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Không xóa được file ảnh cũ!'
                    ]);
                    exit();
                }

                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $image_path = $user_id . "." . $extension;
                    $_SESSION['user']['Image'] = 'public/img/avatar/' . $image_path;
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Lỗi không cập nhật được ảnh!'
                    ]);
                    exit();
                }
            }

            $result = UserModel::updateProfile($user_id, $gioitinh, $cccd, $sdt, $stk, $diachi, $image_path, !empty($newPassword) ? $newPassword : null);

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Cập nhật hồ sơ thành công!'
                ]);
                exit();
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Lỗi không cập nhật được hồ sơ!'
                ]);
                exit();
            }
        }
        else {
            header('Location: /QLNV_PHP/src/index.php?action=login&status=needlogin');
            exit();
        }
    }


    public function Getcheckinout_page() {
        if (isset($_SESSION['user'])) {
            $title='CheckinOut Page';
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

    public function GetActivity_page() {
        if (isset($_SESSION['user'])) {
            $Role= $_SESSION['user']['Role'];
            $title = 'Activity Page'; 
            $empID = $_SESSION['user']['EmpID'];
            switch ($Role) {
                case 'Nhân viên':
                    $file = "./views/pages/NV/activity_NV.phtml";
                    break;
                case 'Quản lý':
                    $file = "./views/pages/QL/activity_QL.phtml";
                    break;
                case 'Giám đốc':
                    $file = "./views/pages/GD/activity_GD.phtml";

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
            header('Location: /QLNV_PHP/src/index.php?action=login&status=needlogin');
            exit();
        }
     
    }
}
?>