<?php
require_once __DIR__ . '/../model/FelicitationModel.php';

class FelicitationController {
    public function GetFelicitationPage() {
        if (isset($_SESSION['user'])) {
            $title = 'Yêu cầu';
            $user_id = $_SESSION['user']['EmpID'];
            $role = $_SESSION['user']['Role'];
    
            $limit = 5;
            $pagePending = isset($_GET['pagePending']) ? (int)$_GET['pagePending'] : 1;
            $offsetPending = ($pagePending - 1) * $limit;
            $timeSheets = FelicitationModel::getTimeSheetsByEmpID($user_id);
    
            switch ($role) {
                case 'Nhân viên':
                    $file = "./views/pages/NV/point_NV.phtml";
                    $creq = FelicitationModel::getFelicitationCountsByEmpID($user_id);
                    $pendingRequests = FelicitationModel::getPendingRequestsByEmpID($user_id, $limit, $offsetPending);
                    $totalPending = FelicitationModel::countPendingRequests($user_id);
                    $points = FelicitationModel::getPoint_Month($user_id);
                    
                    // Thêm dấu "+" hoặc "-" vào dữ liệu
                    foreach ($pendingRequests as &$request) {
                        $point = $request['FelicitationPoint'];
                        $request['FormattedPoint'] = ($point > 0 ? '+' : '') . $point;
                    }
                    unset($request); 

                    if (isset($_GET['ajax'])) {
                        // Trả về dữ liệu dưới dạng JSON cho AJAX
                        $pendingHtml = '';
                        foreach ($pendingRequests as $request) {
                            $pendingHtml .= '<tr>'
                            . '<td>'
                            . htmlspecialchars($request['FormattedPoint'])
                            . '</td>'
                            . '<td>'
                            . htmlspecialchars($request['FelicitationNoiDung'])
                            . '</td>'
                            . '<td>'
                            . htmlspecialchars($request['FelicitationNguoiTang'])
                            . '</td>'
                            . '<td>' . htmlspecialchars($request['Date']) . '</td>'
                            . '</tr>';
                        }  

    
                        $pendingPagination = '';
                        if ($totalPending > $limit) {
                            for ($i = 1; $i <= ceil($totalPending / $limit); $i++) {
                                $pendingPagination .= '<li class="page-item"><a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a></li>';
                            }
                        }

    
                        echo json_encode([
                            'pendingHtml' => $pendingHtml,
                            'pendingPagination' => $pendingPagination,
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

    public function GetTimeSheetDetails() {
        if (isset($_GET['timeSheetID'])) {
            $timeSheetID = $_GET['timeSheetID'];
            $timeSheet = RequestModel::getTimeSheetByID($timeSheetID);
            echo json_encode($timeSheet);
        }
    }
    
}
?>
