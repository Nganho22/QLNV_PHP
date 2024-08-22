<?php
session_start();
require_once './controller/HomeController.php';
require_once './controller/UserController.php';
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
