<?php
session_start();
require_once './controller/HomeController.php';
require_once './controller/UserController.php';
require_once './controller/PassController.php';

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

default:
        $controller = new HomeController();
        $controller->login();
        break;
}
?>
