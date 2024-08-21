<?php
session_start();
require_once './controller/HomeController.php';
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
default:
        $controller = new HomeController();
        $controller->login();
        break;
}
?>
