<?php
require_once __DIR__ . '/../model/VoucherModel.php';

class VoucherController {
    public function GetVoucher_Page() {
        if (isset($_SESSION['user'])) {
            $title = 'Yêu cầu';
            $user_id = $_SESSION['user']['EmpID'];
    
            $limit = 5;
            $pageHistory = isset($_GET['pageHistory']) ? (int)$_GET['pageHistory'] : 1;
            $pageHistory_QL = isset($_GET['pageHistory_QL']) ? (int)$_GET['pageHistory_QL'] : 1;
            $offsetHistory = ($pageHistory - 1) * $limit;
            $offsetHistory_QL = ($pageHistory_QL - 1) * $limit;
            $timeSheets = VoucherModel::getTimeSheetsByEmpID($user_id);

            $file = "./views/pages/voucher_list.phtml";
            $creq = VoucherModel::getVoucherCountsByEmpID($user_id);
            $historyRequests = VoucherModel::getHistoryRequestsByEmpID($limit, $offsetHistory);
            $totalHistory = VoucherModel::countVoucherRequests();
            $history_QLRequests = VoucherModel::getHistoryRequestsByEmpID_QL($limit, $offsetHistory_QL);
            $totalHistory_QL = VoucherModel::countFelicitationRequests_QL();
            $point = VoucherModel::getPoint_Month($user_id);
            
            // Thêm dấu "+" hoặc "-" vào dữ liệu
            if (isset($_GET['ajax'])) {
                // Trả về dữ liệu dưới dạng JSON cho AJAX
                $historyHtml = '';
                foreach ($historyRequests as $request) {
                    $historyHtml .= '<tr>'
                    . '<td>'. htmlspecialchars($request['Diem']) . '</td>'
                    . '<td>'. htmlspecialchars($request['TenVoucher']) . '</td>'
                    . '<td>'. htmlspecialchars($request['HanSuDung']) . '</td>'
                    . '</tr>';
                }

                $history_QLHtml = '';
                foreach ($history_QLRequests as $request) {
                    $history_QLHtml .= '<tr>'
                    . '<td>' . htmlspecialchars($request['Diem']) . '</td>'
                    . '<td>' . htmlspecialchars($request['TenVoucher']) . '</td>'
                    . '<td>' . htmlspecialchars($request['HanSuDung']) . '</td>'
                    . '</tr>';
                        }

                $historyPagination = '';
                if ($totalHistory > $limit) {
                    for ($i = 1; $i <= ceil($totalHistory / $limit); $i++) {
                        $historyPagination .= '<li class="page-item"><a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a></li>';
                    }
                }

                $history_QLPagination = '';
                if ($totalHistory_QL > $limit) {
                    for ($i = 1; $i <= ceil($totalHistory_QL / $limit); $i++) {
                        $history_QLPagination .= '<li class="page-item"><a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a></li>';
                    }
                }

                echo json_encode([
                    'historyHtml' => $historyHtml,
                    'historyPagination' => $historyPagination,
                    'history_QLHtml' => $history_QLHtml,
                    'history_QLPagination' => $history_QLPagination,
                    'timeSheets' => $timeSheets
                ]);
            } else {
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
            }
        } else {
            header('Location: /QLNV_PHP/src/index.php?action=login&status=needlogin');
            exit();
        }
    }
}
?>
