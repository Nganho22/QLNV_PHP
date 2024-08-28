<?php
require_once __DIR__ . '/../model/RequestModel.php';

class RequestController {
    public function GetRequestPage() {
        if (isset($_SESSION['user'])) {
            $title = 'Yêu cầu';
            $user_id = $_SESSION['user']['EmpID'];
            $role = $_SESSION['user']['Role'];
    
            $limit = 3;
            $pagePending = isset($_GET['pagePending']) ? (int)$_GET['pagePending'] : 1;
            $pageApproved = isset($_GET['pageApproved']) ? (int)$_GET['pageApproved'] : 1;
            $offsetPending = ($pagePending - 1) * $limit;
            $offsetApproved = ($pageApproved - 1) * $limit;
            $timeSheets = RequestModel::getTimeSheetsByEmpID($user_id);
    
            switch ($role) {
                case 'Nhân viên':
                    $file = "./views/pages/NV/request.phtml";
                    $creq = RequestModel::getRequestCountsByEmpID($user_id);
                    $pendingRequests = RequestModel::getPendingRequestsByEmpID($user_id, $limit, $offsetPending);
                    $approvedRequests = RequestModel::getApprovedRequestsByEmpID($user_id, $limit, $offsetApproved);
                    $totalPending = RequestModel::countPendingRequests($user_id);
                    $totalApproved = RequestModel::countApprovedRequests($user_id);
    
                    if (isset($_GET['ajax'])) {
                        // Trả về dữ liệu dưới dạng JSON cho AJAX
                        $pendingHtml = '';
                        foreach ($pendingRequests as $request) {
                            $pendingHtml .= '<tr>'
                            . '<td>' . htmlspecialchars($request['TieuDe']) . '</td>'
                            . '<td>' . ($request['TrangThai'] == 0 ? 'Chưa duyệt' : 'Đã duyệt') . '</td>'
                            . '<td>' . htmlspecialchars($request['NgayGui']) . '</td>'
                            . '</tr>';
                        
                        }
    
                        $approvedHtml = '';
                        foreach ($approvedRequests as $request) {
                            $approvedHtml .= '<tr>'
                                . '<td>' . htmlspecialchars($request['TieuDe']) . '</td>'
                                . '<td>' . ($request['TrangThai'] == 0 ? 'Chưa duyệt' : 'Đã duyệt') . '</td>'
                                . '<td>' . htmlspecialchars($request['NgayXuLy']) . '</td>'
                                . '</tr>';
                        }
    
                        $pendingPagination = '';
                        if ($totalPending > $limit) {
                            for ($i = 1; $i <= ceil($totalPending / $limit); $i++) {
                                $pendingPagination .= '<li class="page-item"><a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a></li>';
                            }
                        }
    
                        $approvedPagination = '';
                        if ($totalApproved > $limit) {
                            for ($i = 1; $i <= ceil($totalApproved / $limit); $i++) {
                                $approvedPagination .= '<li class="page-item"><a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a></li>';
                            }
                        }
    
                        echo json_encode([
                            'pendingHtml' => $pendingHtml,
                            'pendingPagination' => $pendingPagination,
                            'approvedHtml' => $approvedHtml,
                            'approvedPagination' => $approvedPagination,
                            'timeSheets' => $timeSheets
                        ]);
                    } else {
                        ob_start();
                        require($file);
                        $content = ob_get_clean();
                        require(__DIR__ . '/../views/template.phtml');
                    }
                    break;
                case 'Quản lý':
                    $file = "./views/pages/QL/home_QL.phtml";
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
