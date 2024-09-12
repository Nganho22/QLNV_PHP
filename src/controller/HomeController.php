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
                    $limit = 2;
                    $file = "./views/pages/NV/home_NV.phtml";
                    $phongID = UserModel::getPhongIDByEmpID($empID,  $_SESSION['API']['Profile']);
                    // $projects = UserModel::getProjects_NV($empID);
                    $cprojects = ProjectModel::getCountProjects_NV($empID);
                    $apiUrlActivity = 'http://localhost:9002/apiActivity';
                    $modelActivity = new ActivityModel($apiUrlActivity);
                    $cactivities = $modelActivity->getActivitiesByMonth(date('m'));
                    $Activities = $modelActivity->getActivitiesByMonth(date('m'));
                    $checkInOut = $_SESSION['CheckInOut'];
                    $points = UserModel::getPoint_Month($empID,  $_SESSION['API']['Profile']);
                    $deadlines = ProjectModel::getDeadlinesTimesheet($empID);
                    
                    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                    $offset = ($page - 1) * $limit;


                    $projects = ProjectModel::getProjectsList_NV($empID, $limit, $offset);
                    $totalProjects = ProjectModel::countProjectsList_NV($empID);
                
                    if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
                        $response = [];
                    
                        $projectsHtml = '';
                        foreach ($projects as $project) {
                            $projectsHtml .= '<li>
                                <a href="index.php?action=GetDetailProjectPage&projectID=' . htmlspecialchars($project['ProjectID']) . '">
                                    ' . htmlspecialchars($project['Ten']) . '
                                </a>
                                <span>Hạn chót: ' . htmlspecialchars($project['HanChotDuKien']) . '</span>
                                <span class="status ' . strtolower(htmlspecialchars($project['TrangThai'])) . '">
                                    ' . htmlspecialchars($project['TrangThai']) . '
                                </span>
                            </li>';
                        }
                    
                        $paginationHtml = '';
                        if ($totalProjects > $limit) {
                            for ($i = 1; $i <= ceil($totalProjects / $limit); $i++) {
                                $paginationHtml .= '<li class="page-item' . ($i == $page ? ' active' : '') . '">
                                    <a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a>
                                </li>';
                            }
                        }
                    
                        $response['projectsHtml'] = $projectsHtml;
                        $response['paginationHtml'] = $paginationHtml;
                    
                        echo json_encode($response);
                        exit;
                    }
                     else {
                    $file = "./views/pages/NV/home_NV.phtml";
                }
                    break;
                case 'Quản lý':
                                      
                    $limit = 2;
                    $searchTerm_NV = isset($_GET['search_nhanvien']) ? $_GET['search_nhanvien'] : '';
                    $page_NV = isset($_GET['page_nhanvien']) ? (int)$_GET['page_nhanvien'] : 1;
                    $offset_NV = ($page_NV - 1) * $limit;

                    if (!empty($searchTerm_NV)) {
                        $employees = UserModel::searchProfiles_QL($empID, $searchTerm_NV, $limit, $offset_NV,  $_SESSION['API']['Profile']);
                        $totalEmployees = UserModel::countSearchProfiles_QL($empID, $searchTerm_NV);
                    } else {
                        $employees = UserModel::getEmployeesList_QL($empID, $limit, $offset_NV);
                        $totalEmployees = UserModel::countAllEmployees_QL($empID);
                    }

                    $limit_TS = 5;
                    $page_TS = isset($_GET['page_timesheet']) ? (int)$_GET['page_timesheet'] : 1;
                    $offset_TS = ($page_TS - 1) * $limit_TS;
                    $timesheets = ProjectModel::getTimesheetList_QL($empID, $limit_TS, $offset_TS);
                    $totalTimesheets = ProjectModel::countAllTimesheet_QL($empID);
                    
                    $checkInOut = $_SESSION['CheckInOut'];
                    $phongID = UserModel::getPhongIDByEmpID($empID,  $_SESSION['API']['Profile']);
                    $countWFH = UserModel::getWorkFromHomeCountByEmpID($empID);
                    $absence = UserModel::getAbsence($empID);
                    $phongBans = UserModel::getPhongBanStatistics($empID);
                    $hiendien = UserModel::getHienDien($empID);
                    $checkinout = UserModel::getPhongBan_Checkinout($empID);
                    // $employees = UserModel::getEmployeesList_QL($empID);
                    // $timesheets = UserModel::getTimesheetList($empID); 
                    $managedProjects = ProjectModel::getProjects_QL($empID);
                    $deadlines = ProjectModel::getDeadlinesTimesheet_QL($empID);
                    if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
                        $response = [];

                        if (isset($_GET['search_nhanvien']) || isset($_GET['page_nhanvien'])) {
                            $requestHtml = '';
                            foreach ($employees as $employee) {
                                $requestHtml .= '<li>
                                    <a href="index.php?action=GetProfileDetail&ID=' . htmlspecialchars($employee['EmpID']) . '">
                                        ' . htmlspecialchars($employee['HoTen']) . '
                                    </a><br>
                                    <span>' . htmlspecialchars($employee['Email']) . '</span>
                                </li>';
                            }
                        
                            $paginationHtml = '';
                            if ($totalEmployees > $limit) {
                                for ($i = 1; $i <= ceil($totalEmployees / $limit); $i++) {
                                    $paginationHtml .= '<li class="page-item">
                                        <a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a>
                                    </li>';
                                }
                            }
                        
                            $response['requestHtml'] = $requestHtml;
                            $response['paginationHtml'] = $paginationHtml;
                        }
                        

                        
                        if (isset($_GET['page_timesheet'])) {
                            $timesheetHtml = '';
                            foreach ($timesheets as $timesheet) {
                                $timesheetHtml .= '<li>' . htmlspecialchars($timesheet['NgayGiao']) . '<br>
                                            <a href="index.php?action=GetDetailTimeSheet&timesheetID=' . htmlspecialchars($timesheet['Time_sheetID']) . '">
                                                ' . htmlspecialchars($timesheet['NoiDung']) . '
                                            </a><br>
                                                <span>' . htmlspecialchars($timesheet['NguoiGui']) . '</span></li>';
                            }
                    
                            $timesheetPaginationHtml = '';
                            if ($totalTimesheets > $limit_TS) {
                                for ($i = 1; $i <= ceil($totalTimesheets / $limit_TS); $i++) {
                                    $timesheetPaginationHtml .= '<li class="page-item"><a class="page-link-timesheet" href="#" data-page="' . $i . '">' . $i . '</a></li>';
                                }
                            }
                    
                            $response['timesheetHtml'] = $timesheetHtml;
                            $response['timesheetPaginationHtml'] = $timesheetPaginationHtml;
                        }
                    

                        echo json_encode($response);
                        exit;
                    } else {
                        $file = "./views/pages/QL/home_QL.phtml"; 
                    }

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
                        $employees = UserModel::getEmployeesList_GD($empID, $limit, $offset_NV);
                        $totalEmployees = UserModel::countAllEmployees_GD($empID);
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
                        $projects = ProjectModel::searchProject_GD($searchTerm_PJ, $limit_PJ, $offset_PJ);
                        $totalProjects = ProjectModel::countSearchProject_GD($searchTerm_PJ);
                    } else {
                        $projects = ProjectModel::getProjects_GD($limit_PJ, $offset_PJ);
                        $totalProjects = ProjectModel::countAllProject_GD();
                    }

                    $deadlines = ProjectModel::getDeadlinesTimesheet_GD();

                    if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
                        $response = [];

                        if (isset($_GET['search_nhanvien']) || isset($_GET['page_nhanvien'])) {
                            $requestHtml = '';
                            foreach ($employees as $employee) {
                                $requestHtml .= '<li>
                                    <a href="index.php?action=GetProfileDetail&ID=' . htmlspecialchars($employee['EmpID']) . '">
                                        ' . htmlspecialchars($employee['HoTen']) . '
                                    </a><br>
                                    <span>' . htmlspecialchars($employee['Email']) . '</span>
                                </li>';
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
                                $requestHtml_PB .= '<tr>' .
                                                    '<td>' . htmlspecialchars($phongBan['PhongID']) . '</td>' .
                                                    '<td>' . htmlspecialchars($phongBan['TenPhong']) . '</td>' .
                                                    '<td><a href="index.php?action=GetProfileDetail&ID=' . htmlspecialchars($phongBan['QuanLyID']) . '">' .
                                                    htmlspecialchars($phongBan['HoTen']) . '</a></td>' .
                                                   '</tr>';
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
                                $requestHtml_PJ .= '<li>
                                                        <a href="index.php?action=GetDetailProjectPage&projectID=' . htmlspecialchars($project['ProjectID']) . '">
                                                            ' . htmlspecialchars($project['Ten']) . '
                                                        </a><br>
                                                        <span>' . htmlspecialchars($project['NgayGiao']) . '</span><br>
                                                        <span class="status">' . htmlspecialchars($project['TienDo']) . '</span></li>';
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
            $profile =  UserModel::getprofile($user_id,  $_SESSION['API']['Profile']);
            
            $timesheets = RequestModel::gettimesheet($user_id);
            $cNghi= UserModel::getCountNghiPhep($user_id,  $_SESSION['API']['Profile']);
            $cTre= UserModel::getCountTre($user_id,  $_SESSION['API']['Profile']);
            $cPrj = ProjectModel::getCountPrj_GD();
            $listPrj = ProjectModel::getListPrj_GD();
            if ($_SESSION['user']['Role'] == 'Nhân viên') {
                $cPrj_NV =ProjectModel::getCountPrj_NV($user_id);
                $listPrj_NV = ProjectModel::getListPrj_NV($user_id);
            } elseif ($_SESSION['user']['Role'] == 'Quản lý') {
                $cPrj_QL= ProjectModel::getCountPrj_QL($user_id);
                $listPrj_QL = ProjectModel::getListPrj_QL($user_id);
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

            $profile = UserModel::getprofile($user_id,  $_SESSION['API']['Profile']);
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


            $currentProfile  =  UserModel::getprofile($user_id,  $_SESSION['API']['Profile']);
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

            $result = UserModel::updateProfile($user_id, $gioitinh, $cccd, $sdt, $stk, $diachi, $image_path, !empty($newPassword) ? $newPassword : null,$_SESSION['API']['Profile'] );
            print_r($result);
            /*if ($result) {
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
            }*/
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
    
    public function GetProfileDetail() {
        if (isset($_SESSION['user'])) {
            $title = 'Chi tiết nhân viên';
            $user_id = $_SESSION['user']['EmpID'];
            $ID = isset($_GET['ID']) ? $_GET['ID'] : null;
            $profile = UserModel::getprofile($ID,  $_SESSION['API']['Profile']);
            $role = $profile['Role'];
            $timesheet = ProjectModel::gettimesheet($ID);
            $cNghi = UserModel::getCountNghiPhep($ID,  $_SESSION['API']['Profile']);
            $cTre = UserModel::getCountTre($ID,  $_SESSION['API']['Profile']);
            $cPrj_NV = ProjectModel::getCountPrj_NV($ID);
            $listPrj_NV = ProjectModel::getListPrj_NV($ID);
            $cPrj_QL= ProjectModel::getCountPrj_QL($ID);
            $listPrj_QL = ProjectModel::getListPrj_QL($ID);
    
            $message = '';
            if (isset($_GET['status'])) {
                switch ($_GET['status']) {
                    case 'checked-in':
                        $message = "Bạn đã check-in thành công.";
                        break;
                    case 'checked-out':
                        $message = "Bạn đã check-out thành công.";
                        break;
                    case 'already-checked-out':
                        $message = "Bạn đã check-out, không thể thực hiện lại.";
                        break;
                    case 'success':
                        $message = "Cập nhật thành công!";
                        break;
                    default:
                        $message = "Đã xảy ra lỗi. Vui lòng thử lại.";
                        break;
                }
            }
    
            // Render giao diện profile_detail.phtml cho nhân viên
            ob_start();
            require("./views/pages/profile_detail.phtml");
            $content = ob_get_clean();
            require(__DIR__ . '/../views/template.phtml');
        } else {
    
        }
}

}
?>