<?php
require_once __DIR__ . '/../model/FelicitationModel.php';

class FelicitationController {
    public function GetFelicitationPage() {
        if (isset($_SESSION['user'])) {
            $title = 'Yêu cầu';
            $user_id = $_SESSION['user']['EmpID'];
            $role = $_SESSION['user']['Role'];
    
            $limit = 5;
            $pageHistory = isset($_GET['pageHistory']) ? (int)$_GET['pageHistory'] : 1;
            $pageHistory_QL = isset($_GET['pageHistory_QL']) ? (int)$_GET['pageHistory_QL'] : 1;
            $offsetHistory = ($pageHistory - 1) * $limit;
            $offsetHistory_QL = ($pageHistory_QL - 1) * $limit;
            $timeSheets = FelicitationModel::getTimeSheetsByEmpID($user_id);
    
            switch ($role) {
                case 'Nhân viên':
                    $file = "./views/pages/NV/point_NV.phtml";
                    $creq = FelicitationModel::getFelicitationCountsByEmpID($user_id);
                    $historyRequests = FelicitationModel::getHistoryRequestsByEmpID($user_id, $limit, $offsetHistory);
                    $totalHistory = FelicitationModel::countFelicitationRequests($user_id);
                    $point = FelicitationModel::getPoint_Month($user_id);
                    
                    // Thêm dấu "+" hoặc "-" vào dữ liệu
                    foreach ($historyRequests as &$request) {
                        $point = $request['FelicitationPoint'];
                        $request['FormattedPoint'] = ($point > 0 ? '+' : '') . $point;
                    }
                    unset($request); 

                    if (isset($_GET['ajax'])) {
                        // Trả về dữ liệu dưới dạng JSON cho AJAX
                        $historyHtml = '';
                        foreach ($historyRequests as $request) {
                            $historyHtml .= '<tr>'
                            . '<td>'. htmlspecialchars($request['FormattedPoint']) . '</td>'
                            . '<td>'. htmlspecialchars($request['FelicitationNoiDung']) . '</td>'
                            . '<td>'. htmlspecialchars($request['FelicitationNguoiTang']) . '</td>'
                            . '<td>' . htmlspecialchars($request['Date']) . '</td>'
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
                    break;
                case 'Quản lý':
                    $file = "./views/pages/QL/point_QL.phtml";
                    $creq = FelicitationModel::getFelicitationCountsByEmpID_QL($user_id);
                    $history_QLRequests = FelicitationModel::getHistoryRequestsByEmpID_QL($user_id, $limit, $offsetHistory_QL);
                    $totalHistory_QL = FelicitationModel::countFelicitationRequests_QL($user_id);
                    $point = FelicitationModel::getPoint_Month($user_id);

                    foreach ($history_QLRequests as &$request) {
                        $point = $request['FelicitationPoint'];
                        $request['FormattedPoint'] = ($request['FelicitationRole'] === 'donor' ? '-' : ($point > 0 ? '+' : '')) . $point;
                    }
                    unset($request);
                
                    if (isset($_GET['ajax'])) {
                        // Trả về dữ liệu dưới dạng JSON cho AJAX
                        $history_QLHtml = '';
                        foreach ($history_QLRequests as $request) {
                            $history_QLHtml .= '<tr>'
                                . '<td>' . htmlspecialchars($request['FormattedPoint']) . '</td>'
                                . '<td>' . htmlspecialchars($request['FelicitationNoiDung']) . '</td>'
                                . '<td>' . htmlspecialchars($request['FelicitationNguoiNhan']) . '</td>'
                                . '<td>' . htmlspecialchars($request['Date']) . '</td>'
                                . '</tr>';
                        }

    
                        $history_QLPagination = '';
                        if ($totalHistory_QL > $limit) {
                            for ($i = 1; $i <= ceil($totalHistory_QL / $limit); $i++) {
                                $history_QLPagination .= '<li class="page-item"><a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a></li>';
                            }
                        }

    
                        echo json_encode([
                            'history_QLHtml' => $history_QLHtml,
                            'history_QLPagination' => $history_QLPagination,
                            'timeSheets' => $timeSheets
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

        } else {
            header('Location: /QLNV_PHP/src/index.php?action=login&status=needlogin');
            exit();
        }
    }

    public function GetVoucher_page() {
        if (isset($_SESSION['user'])) {
            $title='Trang danh sách Voucher';
            $user_id = $_SESSION['user']['EmpID'];
            $profile = UserModel::getprofile($user_id);
            
            ob_start();
            require("./views/pages/voucher_list.phtml");
            $content = ob_get_clean();
            require(__DIR__ . '/../views/template.phtml');
        }
        else{
            header('Location: /QLNV_PHP/src/index.php?action=login&status=needlogin');
            exit();
        }
     
    }

    public function GetTimeSheetDetails() {
        if (isset($_GET['timeSheetID'])) {
            $timeSheetID = $_GET['timeSheetID'];
            $timeSheet = RequestModel::getTimeSheetByID($timeSheetID);
            echo json_encode($timeSheet);
        }
    }
    
}
?>
