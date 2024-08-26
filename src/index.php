<?php
session_start();
require_once './controller/HomeController.php';
require_once './controller/UserController.php';
require_once './controller/PassController.php';
require_once './controller/ProjectController.php';

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
        $controller = new ProjectController();
        $controller->GetProjectPage();
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
        break;
    case "GetCheckinoutPage":
        $controller = new HomeController();
        $controller->Getcheckinout_page();
        break;
default:
        $controller = new HomeController();
        $controller->login();
        break;
}
?>
