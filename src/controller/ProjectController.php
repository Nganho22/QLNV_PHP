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
            $limit = 4;
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $offset = ($page - 1) * $limit;
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

            switch ($role) {
                case 'Quản lý':
                case 'Giám đốc':
                $profile = UserModel::getprofile($user_id);
                $cProject = ProjectModel::getProjectCountsByEmpID($user_id);
                $listProject = ProjectModel::getListProject($user_id, $limit, $offset);
                
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
                            . '<td><a href="index.php?action=GetDetailProjectPage&id=' . htmlspecialchars($project['ProjectID']) . '">' 
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

    public function GetDetailProjectPage($projectId) {
        if (isset($_SESSION['user'])) {
            $title='Project';
            $user_id = $_SESSION['user']['EmpID'];
            $role = $_SESSION['user']['Role'];
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
            
            ob_start();
            require("./views/pages/project_detail.phtml");
            $content = ob_get_clean();
            require(__DIR__ . '/../views/template.phtml');
        }
    }
}
?>