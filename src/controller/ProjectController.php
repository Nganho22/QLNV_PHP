<?php
require_once __DIR__ . '/../model/UserModel.php';
require_once __DIR__ . '/../model/ProjectModel.php';

require_once __DIR__ . '/../controller/HomeController.php';

class ProjectController {
    public function GetProjectPage() {
        if (isset($_SESSION['user'])) {
            $title='Danh sách Project';
            $user_id = $_SESSION['user']['EmpID'];
            $role = $_SESSION['user']['Role'];
            $apiUrl = 'http://localhost:9004/apiRequest';
            $model = new UserModel($apiUrl);
            $limit = 4;
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $offset = ($page - 1) * $limit;
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

            switch ($role) {
                case 'Quản lý':
                case 'Giám đốc':
                $profile = $model->getprofile($user_id);
                $cProject = ProjectModel::getProjectCountsByEmpID($user_id);
                //$listProject = ProjectModel::getListProject($user_id, $limit, $offset);
                //print_r($listProject);
                $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
                $progressFilters = isset($_GET['processes']) ? $_GET['processes'] : [];
                $statusFilters = isset($_GET['types']) ? $_GET['types'] : [];
                $classFilters = isset($_GET['classes']) ? $_GET['classes'] : [];
            
                $projectData = ProjectModel::getProjectsAndCount($user_id, $searchTerm, $progressFilters, $statusFilters, $classFilters, $limit, $offset);
                $projects = $projectData['projects'];
                $totalProjects = $projectData['total'];
                
                $file = "./views/pages/project_list.phtml";

                if (isset($_GET['ajax'])) {
                    $requestHtml = '';
                    foreach ($projects as $project) {
                        $requestHtml .= '<tr>'
                            . '<td><a href="index.php?action=GetDetailProjectPage&projectID=' . htmlspecialchars($project['ProjectID']) . '">' 
                            . htmlspecialchars($project['ProjectID']) . '</a></td>'
                            . '<td>' . htmlspecialchars($project['Ten']) . '</td>'
                            . '<td>' . htmlspecialchars($project['PhongID']) . '</td>'
                            . '<td>' . htmlspecialchars($project['TienDo']) . '</td>'
                            . '<td>' . htmlspecialchars($project['TinhTrang']) . '</td>'
                            . '</tr>';
                    }

                    $paginationHtml = '';
                    if ($totalProjects > $limit) {
                        for ($i = 1; $i <= ceil($totalProjects / $limit); $i++) {
                            $paginationHtml .= '<li class="page-item"><a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a></li>';
                        }
                    }

                    echo json_encode([
                        'requestHtml' => $requestHtml,
                        'paginationHtml' => $paginationHtml
                    ]);
                } else {
                    ob_start();
                    require($file);
                    $content = ob_get_clean();
                    require(__DIR__ . '/../views/template.phtml');
                }
                break;

                case 'Nhân viên':
                    $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
                    $progressFilters = isset($_GET['processes']) ? $_GET['processes'] : [];
                    $statusFilters = isset($_GET['types']) ? $_GET['types'] : [];
                    $profile = $model->getprofile($user_id);
                    $cProject = ProjectModel::getProjectCountsByEmpID_NV($user_id);
                    $listProject = ProjectModel::getListProject_NV($user_id, $limit, $offset);
                    
                    $projectData = ProjectModel::getProjectsAndCount_NV($user_id, $searchTerm, $progressFilters, $statusFilters, $limit, $offset);
                    $projects = $projectData['projects'];
                    $totalProjects = $projectData['total'];

                    $file = "./views/pages/project_list.phtml";

                    if (isset($_GET['ajax'])) {
                        $requestHtml = '';
                    foreach ($projects as $project) {
                        $requestHtml .= '<tr>'
                            . '<td><a href="index.php?action=GetDetailProjectPage&projectID=' . htmlspecialchars($project['ProjectID']) . '">' 
                            . htmlspecialchars($project['ProjectID']) . '</a></td>'
                            . '<td>' . htmlspecialchars($project['Ten']) . '</td>'
                            . '<td>' . htmlspecialchars($project['PhongID']) . '</td>'
                            . '<td>' . htmlspecialchars($project['TienDo']) . '</td>'
                            . '<td>' . htmlspecialchars($project['TinhTrang']) . '</td>'
                            . '</tr>';
                    }

                    $paginationHtml = '';
                    if ($totalProjects > $limit) {
                        for ($i = 1; $i <= ceil($totalProjects / $limit); $i++) {
                            $paginationHtml .= '<li class="page-item"><a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a></li>';
                        }
                    }

                    echo json_encode([
                        'requestHtml' => $requestHtml,
                        'paginationHtml' => $paginationHtml
                    ]);
                        
                    } else {
                        ob_start();
                        require($file);
                        $content = ob_get_clean();
                        require(__DIR__ . '/../views/template.phtml');
                    }
                    break;
                default:
                    $file = null;
                    $title = 'Error';
                    break;
                }
            }
        else{
            header('Location: /QLNV_PHP/src/index.php?action=login&status=needlogin');
            exit();
        }
     
    }

    public function GetDetailProjectPage() {
        if (isset($_SESSION['user'])) {
            $title='Project';
            $user_id = $_SESSION['user']['EmpID'];
            $role = $_SESSION['user']['Role'];

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

            $limit = 3;
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $offset = ($page - 1) * $limit;
            $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

            $projectId = isset($_GET['projectID']) ? $_GET['projectID'] : null;
            $detail = ProjectModel::getDetailProject($projectId);
            $employees = ProjectModel::getEmployeesByUserDepartment($user_id);
            $employeeIDs = array_column($employees, 'EmpID');
            $result = ProjectModel::getTimeSheetsAndCount($user_id, $employeeIDs, $projectId, $searchTerm, $limit, $offset );

            $timeSheets = $result['timeSheets'];
            $totalTimeSheets = $result['total'];
                    
            $file = "./views/pages/project_detail.phtml";

            if (isset($_GET['ajax'])) {
                $taskHtml = '';
                foreach ($timeSheets as $timeSheet) {
                    $taskHtml .= '<tr>'
                        . '<td><a href="index.php?action=GetDetailTimeSheet&timesheetID=' . htmlspecialchars($timeSheet['Time_sheetID']) . '">' 
                            . htmlspecialchars($timeSheet['Time_sheetID']) . '</a></td>'
                        . '<td>' . htmlspecialchars($timeSheet['ProjectID']) . '</td>'
                        . '<td>' . htmlspecialchars($timeSheet['NoiDung']) . '</td>'
                        . '<td>' . htmlspecialchars($timeSheet['NguoiGui']) . '</td>'
                        . '<td>' . htmlspecialchars($timeSheet['TrangThai']) . '</td>'
                        . '</tr>';
                }
        
                $paginationHtml = '';
                $totalPages = ceil($totalTimeSheets / $limit);
                $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                for ($i = 1; $i <= $totalPages; $i++) {
                    $paginationHtml .= '<li class="page-item ' . ($i == $currentPage ? 'active' : '') . '">'
                        . '<a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a>'
                        . '</li>';
                }

                echo json_encode([
                    'taskHtml' => $taskHtml,
                    'paginationHtml' => $paginationHtml
                ]);
                exit();
            } else {
                ob_start();
                require($file);
                $content = ob_get_clean();
                require(__DIR__ . '/../views/template.phtml');
            }
        } else {
            header('Location: /QLNV_PHP/src/index.php?action=login&status=needlogin');
            exit();
        }
    }

    public function CreateTimeSheet() {
        if (isset($_SESSION['user'])) {
            $title='Project';
            $user_id = $_SESSION['user']['EmpID'];
            $role = $_SESSION['user']['Role'];

            $PhongBan = $_POST['phongID'];
            $projectID = $_POST['projectID'];
            $assignee = $_POST['assignee'];
            $today = $_POST['today'];
            $deadline = $_POST['deadline'];
            $reward = $_POST['reward'];
            $description = $_POST['description'];

            $result = ProjectModel::createTimeSheet($user_id, $projectID, $assignee, $PhongBan, $today, $deadline, $reward, $description);

            echo json_encode([
                'success' => $result, 
                'message' => $result ? 'Nhiệm vụ đã được tạo thành công.' : 'Đã xảy ra lỗi khi tạo nhiệm vụ. Vui lòng thử lại.'
            ]);
        } else {
            header('Location: /QLNV_PHP/src/index.php?action=login&status=needlogin');
            exit();
        }
    }

    public function GetDetailTimeSheet() {
        if (isset($_SESSION['user'])) {
            $title='TimeSheet';
            $user_id = $_SESSION['user']['EmpID'];
            $role = $_SESSION['user']['Role'];

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
            
            $timesheetID = isset($_GET['timesheetID']) ? $_GET['timesheetID'] : null;
            $detail = ProjectModel::getDetailTimeSheet($timesheetID);
                        
            $file = "./views/pages/timesheet_detail.phtml";

            ob_start();
            require($file);
            $content = ob_get_clean();
            require(__DIR__ . '/../views/template.phtml');

        } else {
            header('Location: /QLNV_PHP/src/index.php?action=login&status=needlogin');
            exit();
        }
    }

    public function GetCreateProjectPage() {
        if (isset($_SESSION['user']) && $_SESSION['user']['Role'] == "Giám đốc") {
            $title='Create Project';
            $user_id = $_SESSION['user']['EmpID'];
            $role = $_SESSION['user']['Role'];
            $file = "./views/pages/GD/create_prj.phtml";

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
            
            $QuanLy = ProjectModel::getQuanLyList();
            
            ob_start();
            require($file);
            $content = ob_get_clean();
            require(__DIR__ . '/../views/template.phtml');

        } else {
            header('Location: /QLNV_PHP/src/index.php?action=login&status=needlogin');
            exit();
        }
    }

    public function CreateProject() {
        if (isset($_SESSION['user'])) {
            $title='Create Project';
            $user_id = $_SESSION['user']['EmpID'];
            $role = $_SESSION['user']['Role'];

            $Ten = $_POST['projectName'];
            $NgayGiao = $_POST['startDate'];
            $HanChotDuKien = $_POST['expectedDeadline'];
            $HanChot = $_POST['deadline'];
            $QuanLy = $_POST['manager'];
            
            // $projectIDs = ProjectModel::getProjectIDs();
            // $maxID = 0;
            // foreach ($projectIDs as $project) {
            //     $numberPart = (int)substr($project['ProjectID'], 2);
            
            //     if ($numberPart > $maxID) {
            //         $maxID = $numberPart;
            //     }
            // }
            // $newProjectID = 'PJ' . ($maxID + 1);

            $result = ProjectModel::getCreateProject( $Ten, $NgayGiao, $HanChotDuKien, $HanChot, $QuanLy);

            echo json_encode([
                'success' => $result, 
                'message' => $result ? 'Dự án đã được tạo thành công.' : 'Đã xảy ra lỗi khi tạo dự án. Vui lòng thử lại.'
            ]);
        } else {
            header('Location: /QLNV_PHP/src/index.php?action=login&status=needlogin');
            exit();
        }
    }

    public function UpdateProjectStatus(){
        if (isset($_SESSION['user'])) {
            $title='Project';
            $user_id = $_SESSION['user']['EmpID'];
            $role = $_SESSION['user']['Role'];
            $projectID_s = $_POST['projectID'];
            $newStatus = $_POST['newStatus'];
            $totalTime = 0;
            $employees = ProjectModel::getEmployeesByUserDepartment($user_id);
            $employeeIDs = array_column($employees, 'EmpID');
            if ($newStatus === 'Hoàn thành') {
                $timeSheetIDs = ProjectModel::getTimeSheetID_QL($employeeIDs, $projectID_s);
                $currentDate = date('Y-m-d');
                foreach ($timeSheetIDs as $timeSheet) {
                    $tre = ($timeSheet['hanchot'] < $currentDate) ? 1 : 0;
                    $totalTime = $totalTime + $timeSheet['sogiothuchien'];
                    ProjectModel::UpdateTimeSheetStatus($projectID_s, $newStatus, $timeSheet['time_sheetid'], $tre);
                  
                    if ($tre === 0) {
                        ProjectModel::updatePointProfile($timeSheet['empid'], $timeSheet['diemthuong']);
                        ProjectModel::updatePointFelicitation($timeSheet['empid'], $timeSheet['diemthuong'], $user_id, $currentDate);
                    }
                }
                $result = ProjectModel::UpdateProjectStatus($projectID_s, $newStatus, $totalTime);
            }
            else {
                $tre = 0;
                $result_s = ProjectModel::UpdateTimeSheetStatus($projectID_s, $newStatus, $employeeIDs, $tre);
                $result = ProjectModel::UpdateProjectStatus($projectID_s, $newStatus, $totalTime);
            }
            echo json_encode([
                'success' => $result, 
                'message' => $result ? 'Dự án cập nhật thành công.' : 'Đã xảy ra lỗi khi cập nhật. Vui lòng thử lại.'
            ]);
        } else {
            header('Location: /QLNV_PHP/src/index.php?action=login&status=needlogin');
            exit();
        }
    }
}
?>