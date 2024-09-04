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
                    $phongID = UserModel::getPhongIDByEmpID($empID);
                    $projects = UserModel::getProjects_NV($empID);
                    $cprojects = UserModel::getCountProjects_NV($empID);
                    $apiUrlActivity = 'http://localhost:9002/apiActivity';
                    $model = new ActivityModel($apiUrlActivity);
                    $cactivities = $model->getActivitiesByMonth(date('m'));
                    $Activities = $model->getActivitiesByMonth(date('m'));
                    $checkInOut = UserModel::getCheckInOut($empID);
                    $points = UserModel::getPoint_Month($empID);
                    $deadlines = UserModel::getDeadlinesTimesheet($empID);
                    $file = "./views/pages/NV/home_NV.phtml";
                    break;
                case 'Quản lý':
                    $phongID = UserModel::getPhongIDByEmpID($empID);
                    $countWFH = UserModel::getWorkFromHomeCountByEmpID($empID);
                    $absence = UserModel::getAbsence($empID);
                    $phongBans = UserModel::getPhongBanStatistics($empID);
                    $hiendien = UserModel::getHienDien($empID);
                    $checkinout = UserModel::getPhongBan_Checkinout($empID);
                    $employees = UserModel::getEmployeesList_QL($empID);
                    $timesheets = UserModel::getTimesheetList($empID); 
                    $managedProjects = UserModel::getProjects_QL($empID);
                    $deadlines = UserModel::getDeadlinesTimesheet_QL($empID);
                    $file = "./views/pages/QL/home_QL.phtml";
                    break;
                case 'Giám đốc':

                    $limit = 6;
                    $searchTerm_NV = isset($_GET['search_nhanvien']) ? $_GET['search_nhanvien'] : '';
                    $page_NV = isset($_GET['page_nhanvien']) ? (int)$_GET['page_nhanvien'] : 1;
                    $offset_NV = ($page_NV - 1) * $limit;

                    if (!empty($searchTerm_NV)) {
                        $employees = UserModel::searchProfiles_GD($searchTerm_NV, $limit, $offset_NV);
                        $totalEmployees = UserModel::countSearchProfiles_GD($searchTerm_NV);
                    } else {
                        $employees = UserModel::getEmployeesList_GD($limit, $offset_NV);
                        $totalEmployees = UserModel::countAllEmployees_GD();
                    }

                    $limit_PB = 3;
                    $searchTerm_PB = isset($_GET['search_phongban']) ? $_GET['search_phongban'] : '';
                    $page_PB = isset($_GET['page_phongban']) ? (int)$_GET['page_phongban'] : 1;
                    $offset_PB = ($page_PB - 1) * $limit_PB;

                    if (!empty($searchTerm_PB)) {
                        $phongBans = UserModel::searchPhongBan_GD($searchTerm_PB, $limit_PB, $offset_PB);
                        $totalRooms = UserModel::countSearchPhongBan_GD($searchTerm_PB);
                    } else {
                        $phongBans = UserModel::getPhongBan_GD($limit_PB, $offset_PB);
                        $totalRooms = UserModel::countAllPhongBan_GD();
                    }

                    $limit_PJ = 3;
                    $searchTerm_PJ = isset($_GET['search_project']) ? $_GET['search_project'] : '';
                    $page_PJ = isset($_GET['page_project']) ? (int)$_GET['page_project'] : 1;
                    $offset_PJ = ($page_PJ - 1) * $limit_PJ;

                    if (!empty($searchTerm_PJ)) {
                        $projects = UserModel::searchProject_GD($searchTerm_PJ, $limit_PJ, $offset_PJ);
                        $totalProjects = UserModel::countSearchProject_GD($searchTerm_PJ);
                    } else {
                        $projects = UserModel::getProjects_GD($limit_PJ, $offset_PJ);
                        $totalProjects = UserModel::countAllProject_GD();
                    }

                    $deadlines = UserModel::getDeadlinesTimesheet_GD();

                    if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
                        $response = [];

                        if (isset($_GET['search_nhanvien']) || isset($_GET['page_nhanvien'])) {
                            $requestHtml = '';
                            foreach ($employees as $employee) {
                                $requestHtml .= '<li>' . htmlspecialchars($employee['HoTen']) . '<br><span>' . htmlspecialchars($employee['Email']) . '</span></li>';
                            }

                            $paginationHtml = '';
                            if ($totalEmployees > $limit) {
                                for ($i = 1; $i <= ceil($totalEmployees / $limit); $i++) {
                                    $paginationHtml .= '<li class="page-item"><a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a></li>';
                                }
                            }

                            $response['requestHtml'] = $requestHtml;
                            $response['paginationHtml'] = $paginationHtml;
                        }

                        if (isset($_GET['search_phongban']) || isset($_GET['page_phongban'])) {
                            $requestHtml_PB = '';
                            foreach ($phongBans as $phongBan) {
                                $requestHtml_PB .= '<tr>' . '<td>' . htmlspecialchars($phongBan['PhongID']) . '</td>' .
                                                    '<td>' . htmlspecialchars($phongBan['TenPhong']) . '</td>' .
                                                    '<td>' . htmlspecialchars($phongBan['HoTen']) . '</td>' . '</tr>';
                            }

                            $paginationHtml_PB = '';
                            if ($totalRooms > $limit_PB) {
                                for ($i = 1; $i <= ceil($totalRooms / $limit_PB); $i++) {
                                    $paginationHtml_PB .= '<li class="page-item"><a class="page-link-phongban" href="#" data-page="' . $i . '">' . $i . '</a></li>';
                                }
                            }

                            $response['roomHtml_PB'] = $requestHtml_PB;
                            $response['paginationHtml_PB'] = $paginationHtml_PB;
                        }

                        if (isset($_GET['search_project']) || isset($_GET['page_project'])) {
                            $requestHtml_PJ = '';
                            foreach ($projects as $project) {
                                $requestHtml_PJ .= '<li>' .
                                                        '<p>' . htmlspecialchars($project['Ten']) . '</p>' .
                                                        '<span>' . htmlspecialchars($project['NgayGiao']) . '</span>' .
                                                        '<span class="status">' . htmlspecialchars($project['TienDo']) . '</span>' .
                                                    '</li>';
                            }

                            $paginationHtml_PJ = '';
                            if ($totalProjects > $limit_PJ) {
                                for ($i = 1; $i <= ceil($totalProjects / $limit_PJ); $i++) {
                                    $paginationHtml_PJ .= '<li class="page-item"><a class="page-link-project" href="#" data-page="' . $i . '">' . $i . '</a></li>';
                                }
                            }

                            $response['projectHtml_PJ'] = $requestHtml_PJ;
                            $response['paginationHtml_PJ'] = $paginationHtml_PJ;
                        }

                        echo json_encode($response);
                        exit;
                    } else {
                        $file = "./views/pages/GD/home_GD.phtml"; 
                    }

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