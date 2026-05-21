<?php
$module = $_GET['module'] ?? 'auth';
$action = $_GET['action'] ?? 'login';

if ($module === 'auth') {

    require_once "modules/auth/controllers/AuthController.php";
    $controller = new AuthController($db);

    switch ($action) {

        case 'login':
            $controller->login();
            break;

        case 'logout':
            $controller->logout();
            break;

        case 'forgotPassword':
            $controller->forgotPassword();
            break;

        case 'otp':
            $controller->otp();
            break;

        case 'resetPassword':
            $controller->resetPassword();
            break;
    }
}