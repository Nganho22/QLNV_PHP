<?php

class HomeController{
    public function login() {
        $title='Login';
        require(__DIR__ . '/../views/pages/login.phtml');
    }

    public function home() {
        $title='Home';
        ob_start();
        require("./views/pages/home.phtml");
        $content = ob_get_clean();
        require(__DIR__ . '/../views/template.phtml');
    }
}
?>