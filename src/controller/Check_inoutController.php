<?php
require_once __DIR__ . '/../model/Check_inoutModel.php';
require_once __DIR__ . '/../model/UserModel.php';
require_once __DIR__ . '/../model/PassModel.php';
require_once __DIR__ . '/../controller/HomeController.php';


class Check_inoutController {
    public function GetCheck_inoutPage() {
        if (isset($_SESSION['user'])) {
            $title = 'Danh sách Project';
            $user_id = $_SESSION['user']['EmpID'];
            $role = $_SESSION['user']['Role'];
            $timeSheets = Check_inoutModel::getTimeSheetsByEmpID($user_id);
            $message = '';
            $checkInOutData = Check_inoutModel::getCheckInOutData($user_id);

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

            switch ($role) {
                case 'Nhân viên':
                    $file = "./views/pages/NV/check_inout_NV.phtml";
                    $deadlines = Check_inoutModel::getDeadlinesTimesheet($user_id);
                    $creqq = Check_inoutModel::getCheck_inoutByEmpID($user_id);

                    ob_start();
                    require($file);
                    $content = ob_get_clean();
                    require(__DIR__ . '/../views/template.phtml');
                    break;

                case 'Quản lý':
                    $file = "./views/pages/QL/check_inout_QL.phtml";
                    $deadlines = Check_inoutModel::getDeadlinesTimesheet($user_id);
                    $creqq = Check_inoutModel::getCheck_inoutByEmpID($user_id);
                    ob_start();
                    require($file);
                    $content = ob_get_clean();
                    require(__DIR__ . '/../views/template.phtml');
                    break;

                default:
                    $file = null;
                    $title = 'Error';
                    break;
            }

        } else {
            header('Location: /QLNV_PHP/src/index.php?action=login&status=needlogin');
            exit();
        }
    }

    public function GetCheck_inoutList() {
        if (isset($_SESSION['user'])) {
            $title='Trang danh sách nhân viên chấm công';
            $user_id = $_SESSION['user']['EmpID'];
            $role = $_SESSION['user']['Role'];
            $profile = UserModel::getprofile($user_id);

            $limit = 8;
            $pageHistory = isset($_GET['pageHistory']) ? (int)$_GET['pageHistory'] : 1;
            $offsetHistory = ($pageHistory - 1) * $limit;
            $timeSheets = Check_inoutModel::getTimeSheetsByEmpID($user_id);
    
            $message = '';
            if (isset($_GET['status'])) {
                if ($_GET['status'] === 'success') {
                    $message = "Điểm đã được tặng thành công.";
                } elseif ($_GET['status'] === 'error') {
                    $message = "Đã xảy ra lỗi. Vui lòng thử lại.";
                }
            }
            
            $file = "./views/pages/checkin_out_list.phtml";
            $creq = Check_inoutModel::getFelicitationCountsByEmpID($user_id);
            $historyRequests = Check_inoutModel::getHistoryRequestsByEmpID($user_id, $limit, $offsetHistory);
            $totalHistory = Check_inoutModel::countFelicitationRequests($user_id);
            $point = Check_inoutModel::getPoint_Month($user_id);
            
            if (isset($_GET['ajax'])) {
                // Trả về dữ liệu dưới dạng JSON cho AJAX
                $historyHtml = '';
                foreach ($historyRequests as $request) {
                    $statusClass = '';
                    if ($request['statusCheck'] === 'Đã check-in') {
                        $statusClass = 'status-check-in'; // Xanh
                    } elseif ($request['statusCheck'] === 'Đã check-out') {
                        $statusClass = 'status-check-out'; // Đỏ
                    } else {
                        $statusClass = 'status-not-check-in'; // Vàng
                    }
                    
                    $historyHtml .= '<tr>'
                    . '<td>'. htmlspecialchars($request['ThoiGian']) . '</td>'
                    . '<td>'. htmlspecialchars($request['NhanVien']) . '</td>'
                    . '<td>'. htmlspecialchars($request['GioCheckIn']) . '</td>'
                    . '<td>'. htmlspecialchars($request['GioCheckOut']) . '</td>'
                    . '<td>'. htmlspecialchars($request['Note']) . '</td>'
                    . '<td class="' . $statusClass . '">' . htmlspecialchars($request['statusCheck']) . '</td>'
                    . '</tr>';
                }      

                $historyPagination = '';
                if ($totalHistory > $limit) {
                    for ($i = 1; $i <= ceil($totalHistory / $limit); $i++) {
                        $historyPagination .= '<li class="page-item"><a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a></li>';
                    }
                }


                echo json_encode([
                    'historyHtml' => $historyHtml,
                    'historyPagination' => $historyPagination,
                    'timeSheets' => $timeSheets
                ]);
            } else {
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
                require($file);
                $content = ob_get_clean();
                require(__DIR__ . '/../views/template.phtml');
            }
        }
        else{
            header('Location: /QLNV_PHP/src/index.php?action=login&status=needlogin');
            exit();
        }
     
    }
}
?>
