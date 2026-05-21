<?php
session_start();

define('BASE_URL', '/source/dist/app/public/');

/*
|--------------------------------------------------------------------------
| Autoload
|--------------------------------------------------------------------------
*/
spl_autoload_register(function ($class) {
    $paths = [
        "../app/core/$class.php",
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
        }
    }
});

/*
|--------------------------------------------------------------------------
| Helper asset
|--------------------------------------------------------------------------
*/
function asset($path) {
    return BASE_URL . 'assets/' . $path;
}

/*
|--------------------------------------------------------------------------
| Router
|--------------------------------------------------------------------------
*/
$module = $_GET['module'] ?? 'student'; // giữ nguyên viết thường
$action = $_GET['action'] ?? 'index';

// KHÔNG dùng ucfirst nữa → match đúng folder
$controllerPath = "../app/modules/$module/controllers/" . ucfirst($module) . "Controller.php";

if (!file_exists($controllerPath)) {
    die("Module không tồn tại! → " . $controllerPath);
}

require_once $controllerPath;

// class vẫn phải viết hoa
$controllerName = ucfirst($module) . "Controller";

if (!class_exists($controllerName)) {
    die("Controller không tồn tại!");
}

$controller = new $controller();

// check action
if (!method_exists($controller, $action)) {
    die("Action không tồn tại!");
}

// chạy
$controller->$action();