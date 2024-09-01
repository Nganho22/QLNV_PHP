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
                    $creq = Check_inoutModel::getFelicitationCountsByEmpID($user_id);

                    ob_start();
                    require($file);
                    $content = ob_get_clean();
                    require(__DIR__ . '/../views/template.phtml');
                    break;

                case 'Quản lý':
                    $file = "./views/pages/QL/check_inout_QL.phtml";
                    $deadlines = Check_inoutModel::getDeadlinesTimesheet($user_id);
                    $creq = Check_inoutModel::getFelicitationCountsByEmpID($user_id);
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
}
?>
