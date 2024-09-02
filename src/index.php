<?php
session_start();
require_once './controller/HomeController.php';
require_once './controller/UserController.php';
require_once './controller/PassController.php';
require_once './controller/ProjectController.php';
require_once './controller/RequestController.php';
require_once './controller/FelicitationController.php';
require_once './controller/VoucherController.php';
require_once './controller/Check_inoutController.php';
require_once './controller/ActivityController.php';
$action = "";
if (isset($_REQUEST["action"]))
{    
    $action = $_REQUEST["action"];
}
 
switch ($action)
{
    case "login":      
        $controller = new HomeController();
        $controller->login();
        break;
    case "checklogin":      
        $controller = new UserController();
        $controller->checklogin();
        break;
    case "forgot-password":
        $controller = new HomeController();
        $controller->forgotpass();
        break;
    case "sendcode": 
        $controller = new PasswordController();
        $controller->sendCode();
        break;
    case "resetpass": 
        $controller = new PasswordController();
        $controller->resetPassword();
        break;
    case "home":      
        $controller = new HomeController();
        $controller->Home();
        break;
    case "logout":
        $controller = new UserController();
        $controller->logout();
        break;
    case "GetProfilePage":
        $controller = new HomeController();
        $controller->GetProfile_page();
        break;
    case "GetUpdateProfilePage":
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller = new HomeController();
            $controller->updateProfile();
        } else {
        $controller = new HomeController();
        $controller->GetUpdateprofile_page();}
        break;
    case "GetProjectPage": 
        $controller = new ProjectController();
        $controller->GetProjectPage();
        break;
    case "GetDetailProjectPage": 
        $controller = new ProjectController();
        $projectId_get = isset($_GET['id']) ? $_GET['id'] : null;
        if ($projectId_get !== null && (!isset($_SESSION['projectId']) || $_SESSION['projectId'] !== $projectId_get)) {
            $_SESSION['projectId'] = $projectId_get;
        }
        $projectId = isset($_SESSION['projectId']) ? $_SESSION['projectId'] : null;
        $controller->GetDetailProjectPage($projectId);
        break;
    case "GetCheckinoutPage":
        $controller = new HomeController();
        $controller->Getcheckinout_page();
        break;
    case "GetActivityPage":
        $controller = new ActivityController();
        $controller->GetActivity_page();
        break;
    case "GetActivityDetail":
        $controller = new ActivityController();
        $controller->GetActivityDetail_page();
        break;
    case "GetRequestPage":
        $controller = new RequestController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['formType'])) {
                $formType = $_POST['formType'];
                if ($formType === 'TimeSheet') {
                    $controller->submitTimeSheetRequest(); 
                } elseif ($formType === 'General') {
                    $controller->submitRequest(); 
                } else {
                    echo json_encode(['success' => false, 'message' => 'Loại form không hợp lệ.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Không xác định loại form.']);
            }
        } else {
            $controller->GetRequestPage();
        }
        break;
    case "GetDetailRequestPage":
        $controller = new RequestController();
        $requestId = isset($_GET['id']) ? $_GET['id'] : null;
        $controller->GetDetailPage($requestId);
        break;
    case "GetTimeSheetDetails":
        $controller = new RequestController();
        $controller->GetTimeSheetDetails();
        break;   
    case "CheckInOut":
        $controller = new UserController();
        $controller->CheckInOut();
        break;   
    case "GetFelicitationPage":
        $controller = new FelicitationController();
        $controller->GetFelicitationPage();
        break; 
    case "GetVoucherPage":
        $controller = new VoucherController();
        $controller->GetVoucher_page();
        break;
    case 'GetVoucherDetails':
        $controller = new VoucherController();
        $controller->GetVoucherDetails();
        break;
    case "GetCheck_inoutPage":
        $controller = new Check_inoutController();
        $controller->GetCheck_inoutPage();
        break;
default:
        $controller = new HomeController();
        $controller->login();
        break;
}
?>
