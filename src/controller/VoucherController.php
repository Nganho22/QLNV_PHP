<?php
require_once __DIR__ . '/../model/VoucherModel.php';

class VoucherController {
    public function GetVoucher_Page() {
        if (isset($_SESSION['user'])) {
            $title = 'Yêu cầu';
            $user_id = $_SESSION['user']['EmpID'];

        $message = '';
        if (isset($_GET['status'])) {
            if ($_GET['status'] === 'exsuccess') {
                $message = "Đổi Voucher thành công.";
            } elseif ($_GET['status'] === 'usedsuccess') {
                $message = "Dùng Voucher thành công.";
            } elseif ($_GET['status'] === 'error') {
                $message = "Đã xảy ra lỗi. Vui lòng thử lại.";
            }
        }
            $limit = 3;
            $pageAvailableVoucher = isset($_GET['pageAvailableVoucher']) ? (int)$_GET['pageAvailableVoucher'] : 1;
            $pageExchangeVoucher = isset($_GET['pageExchangeVoucher']) ? (int)$_GET['pageExchangeVoucher'] : 1;
            $offsetAvailableVoucher = ($pageAvailableVoucher - 1) * $limit;
            $offsetExchangeVoucher = ($pageExchangeVoucher - 1) * $limit;
            $timeSheets = VoucherModel::getTimeSheetsByEmpID($user_id);

            $file = "./views/pages/voucher_list.phtml";
            $creq = VoucherModel::getVoucherCountsByEmpID($user_id);
            $availableVoucherRequests = VoucherModel::getAvailableVoucherRequestsByEmpID($limit, $offsetAvailableVoucher);
            $totalAvailableVoucher = VoucherModel::countVoucherRequests();
            $exchangeVoucherRequests = VoucherModel::getAvailableVoucherRequestsByEmpID_QL($limit, $offsetExchangeVoucher);
            $totalExchangeVoucher = VoucherModel::countExchangeVoucherRequests();
            $point = VoucherModel::getPoint_Month($user_id);
            
            // Thêm dấu "+" hoặc "-" vào dữ liệu
            if (isset($_GET['ajax'])) {
                // Trả về dữ liệu dưới dạng JSON cho AJAX
                $availableVoucherHtml = '';
                foreach ($availableVoucherRequests as $request) {
                    $availableVoucherHtml .= '<tr data-id="' . htmlspecialchars($request['VoucherID']) . '">'
                    . '<td>'. htmlspecialchars($request['TriGia']) . '</td>'
                    . '<td><a href="#" class="voucher-link" data-id="' . htmlspecialchars($request['VoucherID']) . '">' . htmlspecialchars($request['TenVoucher']) . '</a></td>'
                    . '<td>'. htmlspecialchars($request['HanSuDung']) . '</td>'
                    . '</tr>';
                }

                $exchangeVoucherHtml = '';
                foreach ($exchangeVoucherRequests as $request) {
                    $exchangeVoucherHtml .= '<tr data-id="' . htmlspecialchars($request['VoucherID']) . '">'
                    . '<td>' . htmlspecialchars($request['TriGia']) . '</td>'
                    . '<td><a href="#" class="voucher-link" data-id="' . htmlspecialchars($request['VoucherID']) . '">' . htmlspecialchars($request['TenVoucher']) . '</a></td>'
                    . '<td>' . htmlspecialchars($request['HanSuDung']) . '</td>'
                    . '</tr>';
                        }

                $availableVoucherPagination = '';
                if ($totalAvailableVoucher > $limit) {
                    for ($i = 1; $i <= ceil($totalAvailableVoucher / $limit); $i++) {
                        $availableVoucherPagination .= '<li class="page-item"><a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a></li>';
                    }
                }

                $exchangeVoucherPagination = '';
                if ($totalExchangeVoucher> $limit) {
                    for ($i = 1; $i <= ceil($totalExchangeVoucher / $limit); $i++) {
                        $exchangeVoucherPagination .= '<li class="page-item"><a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a></li>';
                    }
                }

                echo json_encode([
                    'availableVoucherHtml' => $availableVoucherHtml,
                    'availableVoucherPagination' => $availableVoucherPagination,
                    'exchangeVoucherHtml' => $exchangeVoucherHtml,
                    'exchangeVoucherPagination' => $exchangeVoucherPagination,
                    'timeSheets' => $timeSheets
                ]);
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

    public function GetVoucherDetails() {
        if (isset($_SESSION['user'])) {
            if (isset($_GET['voucherID'])) {
                $voucherID = (int)$_GET['voucherID'];
                $voucherDetails = VoucherModel::getVoucherDetails($voucherID);
                echo json_encode($voucherDetails);
                exit();
            }
        } else {
            header('Location: /QLNV_PHP/src/index.php?action=login&status=needlogin');
            exit();
        }
    }

    public function GetExVoucherDetails() {
        if (isset($_SESSION['user'])) {
            if (isset($_GET['voucherID'])) {
                $voucherID = (int)$_GET['voucherID'];
                $voucherDetails = VoucherModel::getExVoucherDetails($voucherID);
                echo json_encode($voucherDetails);
                exit();
            }
        } else {
            header('Location: /QLNV_PHP/src/index.php?action=login&status=needlogin');
            exit();
        }
    }


    public function GetCheck_inoutPage() {
        if (isset($_SESSION['user'])) {
            $title='Trang danh sách Voucher';
            $user_id = $_SESSION['user']['EmpID'];
            $profile = UserModel::getprofile($user_id);
            
            ob_start();
            require("./views/pages/NV/check_inout_NV.phtml");
            $content = ob_get_clean();
            require(__DIR__ . '/../views/template.phtml');
        }
        else{
            header('Location: /QLNV_PHP/src/index.php?action=login&status=needlogin');
            exit();
        }
     
    }

    public function updateVoucherStatus() {
        if (isset($_SESSION['user'])) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $user_id = $_SESSION['user']['EmpID'];
                $voucherID = $_POST['voucherID'];
                $noiDung = $_POST['noidung'];
                $tinhtrang = VoucherModel::getTinhTrangVoucherByID($voucherID);
                $pointHave = VoucherModel::getEmployeePointsByID($user_id);
                $pointEx = isset($_POST['valuePoint']) ? (int)$_POST['valuePoint'] : null;
    
                $status = 'error'; // Mặc định là lỗi nếu không có gì xảy ra
    
                if ($tinhtrang === '' && $pointHave > $pointEx) {
                    // Nếu tình trạng là rỗng, có thể là voucher mới hoặc chưa dùng
                    $result = VoucherModel::updateVoucherTinhTrangEx($voucherID);
                    VoucherModel::updateEmpExPoints($user_id, $pointEx);
                    VoucherModel::addFelicitation($pointEx, $noiDung, $user_id, $voucherID);
                    $status = 'exsuccess';
                } elseif ($tinhtrang === 'Chưa dùng') {
                    // Nếu tình trạng là 'Chưa dùng', cập nhật tình trạng thành đã dùng
                    $result = VoucherModel::updateVoucherTinhTrangUsed($voucherID);
                    $status = 'usedsuccess';
                }
    
                header("Location: index.php?action=GetVoucherPage&status=$status");
                exit();
            }
        } else {
            header('Location: index.php?action=login&status=needlogin');
            exit();
        }
    }
    
}
?>
