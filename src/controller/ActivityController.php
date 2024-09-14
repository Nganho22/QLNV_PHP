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

                $countActivityByMonth = ActivityModel::CountActivityByMonth(date('m'), $_SESSION['API']['Activity']);
                $countAllActivity = ActivityModel::CountActivity($_SESSION['API']['Activity']);
                $activities = ActivityModel::GetActivityJoin($empID, $_SESSION['API']['Activity']);
                $allActivitiesCB = ActivityModel::SearchActivitiesCoBan($searchCB, $_SESSION['API']['Activity']);
                $allActivitiesLK = ActivityModel::SearchActivitiesLienKet($searchLK, $_SESSION['API']['Activity']);
                $activitiesTT = ActivityModel::getActivitiesByMonth(date('m'), $_SESSION['API']['Activity']);
                //print_r($activities);
            // Phân trang cho hoạt động tham gia 
            if ($activities !== null) {
                $totalItems = count($activities);
                $totalPages = ceil($totalItems / $itemsPerPage);
                $offset = ($currentPage - 1) * $itemsPerPage;
                $pagedActivities = array_slice($activities, $offset, $itemsPerPage);
            } else {
                $pagedActivities = [];
                $totalPages = 1;
            }

            // Phân trang cho hoạt động trong tháng
            if ($activitiesTT !== null) {
                $totalItemsTT = count($activitiesTT);
                $totalPagesTT = ceil($totalItemsTT / $itemsPerPage);
                $offsetTT = ($currentPageTT - 1) * $itemsPerPage;
                $pagedActivitiesTT = array_slice($activitiesTT, $offsetTT, $itemsPerPage);
            } else {
                $pagedActivitiesTT = [];
                $totalPagesTT = 1;
            }
        
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
                    'activitiesTG' => $pagedActivities,
                    'totalPagesTG' => $totalPages,
                    'activitiesTT' => $pagedActivitiesTT,
                    'totalPagesTT' => $totalPagesTT,
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

    public function GetCreateActivity_page() {
        if (isset($_SESSION['user'])) {
            $Role = $_SESSION['user']['Role'];
            $title = 'Activity Page'; 
            $empID = $_SESSION['user']['EmpID'];
            $apiUrl = 'http://localhost:9002/apiActivity';

            $file = "./views/pages/GD/createActivity.phtml";
              
            
          
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
    


    

    public function JoinActivity() {
        if (isset($_SESSION['user'])) {
            $Role = $_SESSION['user']['Role'];
            $empID = $_SESSION['user']['EmpID'];
            
            if (!isset($_GET['activityId']) || !is_numeric($_GET['activityId'])) {
                header('Location: /QLNV_PHP/src/index.php?action=GetActivityDetail&status=invalid');
                exit();
            }
            
            $ActivityID = intval($_GET['activityId']);
            $point = intval($_GET['point']);
            error_log("ActivityID: " . $ActivityID);
            error_log("EmployeeID: " . $empID);
            $result = ActivityModel::addFelicitation($point, $empID); 
            ActivityModel::updateEmpActivityPoints($empID, $point);
            if (!isset($_SESSION['API']['Activity'])) {
                header('Location: /QLNV_PHP/src/index.php?action=login&status=needlogin');
                exit();
            }
    
            $apiUrl = $_SESSION['API']['Activity'];
            $a = ActivityModel::CreateJoinActivity($ActivityID, $empID, $apiUrl);
            if ($a === 1) {
                header('Location: /QLNV_PHP/src/index.php?action=GetActivityDetail&activityId=' . $ActivityID . '&status=success');
            } else {
                header('Location: /QLNV_PHP/src/index.php?action=GetActivityDetail&activityId=' . $ActivityID . '&status=fail');
            }
            exit();
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
            $ActivityID = isset($_GET['activityId']) ? $_GET['activityId'] : null;
            // print_r($ActivityID);
            //$ActivityID = intval($_GET['activityId']);
            $activity = ActivityModel::GetActivityDetail($ActivityID, $_SESSION['API']['Activity']);
            $checkJoin = ActivityModel::CheckJoin($ActivityID,$empID,$_SESSION['API']['Activity']);

            //print_r($activity);
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
                    } elseif ($_GET['status'] === 'success') {
                        $message = "Success";
                    } elseif ($_GET['status'] === 'fail') {
                        $message = "Fail!";
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


    public function CreateActivity() {
        if (isset($_SESSION['user'])) {
            $Role = $_SESSION['user']['Role'];
            $title = 'Create Activity'; 
            $empID = $_SESSION['user']['EmpID'];
            


                $tenHoatDong = $_POST['tenHoatDong'];
                $loaiHoatDong = $_POST['loaiHoatDong'];
                $noiDung = $_POST['noiDung'];
                $moTa = $_POST['moTa'];
                $ngayBatDau = $_POST['ngayBatDau'];
                $ngayKetThuc = $_POST['ngayKetThuc'];
                $hanCuoiDangKy = $_POST['ngayHanChotDangKy'];
                $nganSach = $_POST['nganSach'];
                $point = $_POST['point'];


                
               $result = ActivityModel::CreateActivity($tenHoatDong, $point, $noiDung, $moTa, 0, $nganSach, $hanCuoiDangKy, $ngayBatDau, $ngayKetThuc, $loaiHoatDong, 1,$_SESSION['API']['Activity']);

                if ($result !== 0) {
                    header('Location: /QLNV_PHP/src/index.php?action=GetActivityDetail&activityId=' . $result . '&status=success');
                } else {
                    header('Location: /QLNV_PHP/src/index.php?action=GetCreateActivityPage&status=fail');
                }
                exit();
            
        } else {
            header('Location: /QLNV_PHP/src/index.php?action=login&status=needlogin');
            exit();
        }
    }
    
    
}
?>