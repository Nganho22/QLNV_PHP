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
            $limit = 5;
            $pagePending = isset($_GET['pagePending']) ? (int)$_GET['pagePending'] : 1;
            $offsetPending = ($pagePending - 1) * $limit;
            $timeSheets = FelicitationModel::getTimeSheetsByEmpID($empID);
            switch ($Role) {
                case 'Nhân viên':
                    $projects = UserModel::getProjects_NV($empID);
                    $cprojects = UserModel::getCountProjects_NV($empID);
                    $cactivities = UserModel::getActivities($empID);
                    $activities = UserModel::getActivities($empID);
                    $checkInOut = UserModel::getCheckInOut($empID);
                    $points = UserModel::getPoint_Month($empID);
                    $deadlines = UserModel::getDeadlinesTimesheet($empID);
                    $file = "./views/pages/NV/home_NV.phtml";
                    break;
                case 'Quản lý':
                    $phongBans = UserModel::getPhongBanStatistics();
                    $hiendien = UserModel::getHienDien();
                    $checkinout = UserModel::getPhongBan_Checkinout();
                    $employees = UserModel::getEmployeesList_QL($empID);
                    $timesheets = UserModel::getTimesheetList($empID); 
                    $managedProjects = UserModel::getProjects_QL($empID);
                    $deadlines = UserModel::getDeadlinesTimesheet($empID);
                    $file = "./views/pages/QL/home_QL.phtml";
                    break;
                case 'Giám đốc':

                    $limit = 5;
                    $searchTerm_NV = isset($_GET['search_nhanvien']) ? $_GET['search_nhanvien'] : '';
                    $page_NV = isset($_GET['page_nhanvien']) ? (int)$_GET['page_nhanvien'] : 1;
                    $offset = ($page_NV - 1) * $limit;
                    $projects = UserModel::getProjects_GD();
                    $phongBans = UserModel::getPhongBan_GD();
                    $deadlines = UserModel::getDeadlinesTimesheet($empID);

                    if (!empty($searchTerm_NV)) {
                        $employees = UserModel::searchProfiles($searchTerm_NV, $limit, $offset);
                        $totalEmployees = UserModel::countSearchProfiles($searchTerm_NV);
                    } else {
                        $employees = UserModel::getEmployeesList_GD($limit, $offset);
                        $totalEmployees = UserModel::countAllEmployees();
                    }

                    if (isset($_GET['ajax'])) {
                        $requestHtml = '';
                        foreach ($employees  as $employee) {
                            $requestHtml .= '<li>' . htmlspecialchars($employee['HoTen']) . '<br><span>' . htmlspecialchars($employee['Email']) . '</span></li>';
                        }
                        $paginationHtml = '';
                        if ($totalEmployees > $limit) {
                            for ($i = 1; $i <= ceil($totalEmployees / $limit); $i++) {
                                $paginationHtml .= '<li class="page-item"><a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a></li>';
                            }
                        }
                        echo json_encode([
                            'requestHtml' => $requestHtml,
                            'paginationHtml' => $paginationHtml
                        ]);
                        exit;
                    } else {
                        $file = "./views/pages/GD/home_GD.phtml"; 
                    }
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
                    elseif ($_GET['status']  === 'checked-in') {
                        $message = "Bạn đã check-in thành công.";
                    } elseif ($_GET['status']  === 'checked-out') {
                        $message = "Bạn đã check-out thành công.";
                    } elseif ($_GET['status']  === 'already-checked-out') {
                        $message = "Bạn đã check-out, không thể thực hiện lại.";
                    } elseif ($_GET['status']  === 'Nghi') {
                        $message = "Bạn đã xin nghỉ hôm nay.";
                    } else {
                        $message = "Đã xảy ra lỗi. Vui lòng thử lại.";
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
            $message='';
            if (isset($_GET['status'])) {
                if ($_GET['status']  === 'checked-in') {
                    $message = "Bạn đã check-in thành công.";
                } elseif ($_GET['status']  === 'checked-out') {
                    $message = "Bạn đã check-out thành công.";
                } elseif ($_GET['status']  === 'already-checked-out') {
                    $message = "Bạn đã check-out, không thể thực hiện lại.";
                } elseif ($_GET['status']  === 'success') {
                    $message = "Cập nhật thành công !";
                } else {
                    $message = "Đã xảy ra lỗi. Vui lòng thử lại.";
                }
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
            $message='';
            if (isset($_GET['status'])) {
                if ($_GET['status']  === 'checked-in') {
                    $message = "Bạn đã check-in thành công.";
                } elseif ($_GET['status']  === 'checked-out') {
                    $message = "Bạn đã check-out thành công.";
                } elseif ($_GET['status']  === 'already-checked-out') {
                    $message = "Bạn đã check-out, không thể thực hiện lại.";
                } elseif ($_GET['status']  === 'success') {
                    $message = "Cập nhật thành công!.";
                } else {
                    $message = "Đã xảy ra lỗi. Vui lòng thử lại.";
                }
            }
            
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

                // if (file_exists($target_file)) {
                //     unlink($target_file);
                // } 
                // Xóa tất cả các file với cùng tên nhưng khác phần mở rộng
                foreach (glob($target_dir . $user_id . ".*") as $oldFile) {
                    unlink($oldFile);
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
            $message='';
            if (isset($_GET['status'])) {
                if ($_GET['status']  === 'checked-in') {
                    $message = "Bạn đã check-in thành công.";
                } elseif ($_GET['status']  === 'checked-out') {
                    $message = "Bạn đã check-out thành công.";
                } elseif ($_GET['status']  === 'already-checked-out') {
                    $message = "Bạn đã check-out, không thể thực hiện lại.";
                } else {
                    $message = "Đã xảy ra lỗi. Vui lòng thử lại.";
                }
            }
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

}
?>