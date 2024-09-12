<?php
require_once __DIR__ . '/../model/ActivityModel.php';

class ActivityController{

    
    public function GetActivity_page() {
        if (isset($_SESSION['user'])) {
            $Role = $_SESSION['user']['Role'];
            $title = 'Activity Page'; 
            $empID = $_SESSION['user']['EmpID'];
            $apiUrl = 'http://localhost:9002/apiActivity';
            $model = new ActivityModel($apiUrl);
            
            $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $searchCB = isset($_GET['searchcb']) ? $_GET['searchcb'] : '';
            $currentPageCB = isset($_GET['pageCB']) ? (int)$_GET['pageCB'] : 1;
            $searchLK = isset($_GET['searchlk']) ? $_GET['searchlk'] : '';
            $currentPageLK = isset($_GET['pageLK']) ? (int)$_GET['pageLK'] : 1;
            $itemsPerPage = 1;
            $currentPageTT = isset($_GET['pageTT']) ? (int)$_GET['pageTT'] : 1;


                $countActivityByMonth = $model->CountActivityByMonth(date('m'));
                $countAllActivity = $model->CountActivity();
                $activities = $model->getActivitiesByMonth(date('m'));
                $allActivitiesCB = $model->SearchActivitiesCoBan($searchCB);
                $allActivitiesLK = $model->SearchActivitiesLienKet($searchLK);

            // Phân trang cho hoạt động trong tháng
            if ($activities !== null) {
                $totalItems = count($activities);
                $totalPages = ceil($totalItems / $itemsPerPage);
                $offset = ($currentPage - 1) * $itemsPerPage;
                $pagedActivities = array_slice($activities, $offset, $itemsPerPage);
            } else {
                $pagedActivities = [];
                $totalPages = 1;
            }

            // if ($activities !== null) {
            //     $totalItemsTT = count($activities);
            //     $totalPagesTT = ceil($totalItemsTT / $itemsPerPage);
            //     $offsetTT = ($currentPageTT - 1) * $itemsPerPage;
            //     $pagedActivitiesTT = array_slice($activities, $offsetTT, $itemsPerPage);
            // } else {
            //     $pagedActivitiesTT = [];
            //     $totalPagesTT = 1;
            // }
        
            // Phân trang cho hoạt động cơ bản
            if ($allActivitiesCB !== null) {
                $totalItemsCB = count($allActivitiesCB);
                $totalPagesCB = ceil($totalItemsCB / $itemsPerPage);
                $offsetCB = ($currentPageCB - 1) * $itemsPerPage;
                $pagedActivitiesCB = array_slice($allActivitiesCB, $offsetCB, $itemsPerPage);
            } else {
                $pagedActivitiesCB = [];
                $totalPagesCB = 1;
            }
        
            // Phân trang cho hoạt động liên kết
            if ($allActivitiesLK !== null) {
                $totalItemsLK = count($allActivitiesLK);
                $totalPagesLK = ceil($totalItemsLK / $itemsPerPage);
                $offsetLK = ($currentPageLK - 1) * $itemsPerPage;
                $pagedActivitiesLK = array_slice($allActivitiesLK, $offsetLK, $itemsPerPage);
            } else {
                $pagedActivitiesLK = [];
                $totalPagesLK = 1;
            }

            if (isset($_GET['ajax']) && $_GET['ajax'] === 'true') {
                echo json_encode([
                    'activitesTT' => $pagedActivities,
                    'totalPagesTT' => $totalPages,
                    'activitiesCB' => $pagedActivitiesCB,
                    'totalPagesCB' => $totalPagesCB,
                    'activitiesLK' => $pagedActivitiesLK,
                    'totalPagesLK' => $totalPagesLK
                ]);
                exit();
            }
            
            // Chọn file view dựa trên vai trò
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
            
            // Hiển thị thông báo và view
            if ($file && file_exists($file)) {
                $message = '';
                if (isset($_GET['status'])) {
                    if ($_GET['status'] === 'checked-in') {
                        $message = "Bạn đã check-in thành công.";
                    } elseif ($_GET['status'] === 'checked-out') {
                        $message = "Bạn đã check-out thành công.";
                    } elseif ($_GET['status'] === 'already-checked-out') {
                        $message = "Bạn đã check-out, không thể thực hiện lại.";
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
        } else {
            header('Location: /QLNV_PHP/src/index.php?action=login&status=needlogin');
            exit();
        }
    }
    

    public function GetActivityDetail_page() {
        if (isset($_SESSION['user'])) {
            $Role = $_SESSION['user']['Role'];
            $title = 'Activity Detail'; 
            $empID = $_SESSION['user']['EmpID'];
            $apiUrl = 'http://localhost:9002/apiActivity';
            $model = new ActivityModel($apiUrl);
            $ActivityID = intval($_GET['activityId']);
            $activity = $model->GetActivityDetail($ActivityID);
            
            switch ($Role) {
                case 'Nhân viên':
                    $file = "./views/pages/activity_detail.phtml";
                    break;
                case 'Quản lý':
                    $file = "./views/pages/activity_detail.phtml";
                    break;
                case 'Giám đốc':
                    $file = "./views/pages/activity_detail.phtml";
                    break;
                default:
                    $file = null;
                    $title = 'Error';
                    break;
            }
            
            if ($file && file_exists($file)) {
                $message = '';
                if (isset($_GET['status'])) {
                    if ($_GET['status'] === 'checked-in') {
                        $message = "Bạn đã check-in thành công.";
                    } elseif ($_GET['status'] === 'checked-out') {
                        $message = "Bạn đã check-out thành công.";
                    } elseif ($_GET['status'] === 'already-checked-out') {
                        $message = "Bạn đã check-out, không thể thực hiện lại.";
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
        } else {
            header('Location: /QLNV_PHP/src/index.php?action=login&status=needlogin');
            exit();
        }
    }
    
    /*
    public function GetActivity_page() {
        if (isset($_SESSION['user'])) {
            $Role = $_SESSION['user']['Role'];
            $apiUrl = 'http://localhost:9002/apiActivity';
            $model = new ActivityModel($apiUrl);
    
            // Pagination and search parameters
            $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $currentPageCB = isset($_GET['pageCB']) ? (int)$_GET['pageCB'] : 1;
            $currentPageLK = isset($_GET['pageLK']) ? (int)$_GET['pageLK'] : 1;
            $itemsPerPage = 10; // Adjust as needed
    
            // Fetch and paginate activities for current month
            $activities = $model->getActivitiesByMonth(date('m'));
            $totalItems = count($activities);
            $totalPages = ceil($totalItems / $itemsPerPage);
            $offset = ($currentPage - 1) * $itemsPerPage;
            $pagedActivities = array_slice($activities, $offset, $itemsPerPage);
    
            // Fetch and paginate basic activities
            $searchCB = isset($_GET['searchcb']) ? $_GET['searchcb'] : '';
            $allActivitiesCB = $model->SearchActivitiesCoBan($searchCB);
            $totalItemsCB = count($allActivitiesCB);
            $totalPagesCB = ceil($totalItemsCB / $itemsPerPage);
            $offsetCB = ($currentPageCB - 1) * $itemsPerPage;
            $pagedActivitiesCB = array_slice($allActivitiesCB, $offsetCB, $itemsPerPage);
    
            // Fetch and paginate linked activities
            $searchLK = isset($_GET['searchlk']) ? $_GET['searchlk'] : '';
            $allActivitiesLK = $model->SearchActivitiesLienKet($searchLK);
            $totalItemsLK = count($allActivitiesLK);
            $totalPagesLK = ceil($totalItemsLK / $itemsPerPage);
            $offsetLK = ($currentPageLK - 1) * $itemsPerPage;
            $pagedActivitiesLK = array_slice($allActivitiesLK, $offsetLK, $itemsPerPage);
    
            if (isset($_GET['ajax'])) {
                $response = [
                    'activitiesHtml' => '',
                    'paginationHtml' => '',
                    'activitiesCBHtml' => '',
                    'paginationCBHtml' => '',
                    'activitiesLKHtml' => '',
                    'paginationLKHtml' => ''
                ];
    
                // Generate HTML for activities
                foreach ($pagedActivities as $activity) {
                    $response['activitiesHtml'] .= '<div class="activity-item">'
                        . htmlspecialchars($activity['tenHoatDong']) . '<br>'
                        . '<small>Hạn chót đăng ký: ' . htmlspecialchars($activity['hanCuoiDangKy']) . '</small><br>'
                        . '<small>Ngày bắt đầu: ' . htmlspecialchars($activity['ngayBatDau']) . ' - Ngày kết thúc: ' . htmlspecialchars($activity['ngayKetThuc']) . '</small>'
                        . '<div class="status">' . htmlspecialchars($activity['TinhTrang']) . '</div>'
                        . '</div>';
                }
    
                // Generate pagination HTML for activities
                if ($totalPages > 1) {
                    for ($i = 1; $i <= $totalPages; $i++) {
                        $response['paginationHtml'] .= '<li class="page-item ' . ($i === $currentPage ? 'active' : '') . '">'
                            . '<a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a>'
                            . '</li>';
                    }
                }
    
                // Generate HTML for basic activities
                foreach ($pagedActivitiesCB as $activity) {
                    $response['activitiesCBHtml'] .= '<div class="activity-item">'
                        . htmlspecialchars($activity['tenHoatDong']) . '<br>'
                        . '<small>Hạn chót đăng ký: ' . htmlspecialchars($activity['hanCuoiDangKy']) . '</small><br>'
                        . '<small>Ngày bắt đầu: ' . htmlspecialchars($activity['ngayBatDau']) . ' - Ngày kết thúc: ' . htmlspecialchars($activity['ngayKetThuc']) . '</small>'
                        . '<div class="status">' . htmlspecialchars($activity['TinhTrang']) . '</div>'
                        . '</div>';
                }

                if ($totalPagesCB > 1) {
                    for ($i = 1; $i <= $totalPagesCB; $i++) {
                        $response['paginationCBHtml'] .= '<li class="page-item ' . ($i === $currentPageCB ? 'active' : '') . '">'
                            . '<a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a>'
                            . '</li>';
                    }
                }

                foreach ($pagedActivitiesLK as $activity) {
                    $response['activitiesLKHtml'] .= '<div class="activity-item">'
                        . htmlspecialchars($activity['tenHoatDong']) . '<br>'
                        . '<small>Hạn chót đăng ký: ' . htmlspecialchars($activity['hanCuoiDangKy']) . '</small><br>'
                        . '<small>Ngày bắt đầu: ' . htmlspecialchars($activity['ngayBatDau']) . ' - Ngày kết thúc: ' . htmlspecialchars($activity['ngayKetThuc']) . '</small>'
                        . '<div class="status">' . htmlspecialchars($activity['TinhTrang']) . '</div>'
                        . '</div>';
                }
    

                if ($totalPagesLK > 1) {
                    for ($i = 1; $i <= $totalPagesLK; $i++) {
                        $response['paginationLKHtml'] .= '<li class="page-item ' . ($i === $currentPageLK ? 'active' : '') . '">'
                            . '<a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a>'
                            . '</li>';
                    }
                }
    
                echo json_encode($response);
                exit;
            } else {
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
                    $message = '';
                    if (isset($_GET['status'])) {
                        // Set the message based on the status
                        if ($_GET['status'] === 'checked-in') {
                            $message = "Bạn đã check-in thành công.";
                        } elseif ($_GET['status'] === 'checked-out') {
                            $message = "Bạn đã check-out thành công.";
                        } elseif ($_GET['status'] === 'already-checked-out') {
                            $message = "Bạn đã check-out, không thể thực hiện lại.";
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
        } else {
            header('Location: /QLNV_PHP/src/index.php?action=login&status=needlogin');
            exit();
        }
    }
    */
}
?>