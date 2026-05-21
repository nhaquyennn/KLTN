<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

// =========================
// CONFIG
// =========================
define('BASE_URL', '/');
define('ROOT_PATH', dirname(__DIR__));

// =========================
// AUTOLOAD
// =========================
spl_autoload_register(function ($class) {

    $paths = [
        ROOT_PATH . "/core/$class.php",
    ];

    // models
    foreach (glob(ROOT_PATH . "/modules/*/models/$class.php") as $file) {
        $paths[] = $file;
    }

    // controllers
    foreach (glob(ROOT_PATH . "/modules/*/controllers/$class.php") as $file) {
        $paths[] = $file;
    }

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// =========================
// HELPER
// =========================
function asset($path)
{
    return BASE_URL . 'assets/' . $path;
}

// =========================
// ROUTING INPUT
// =========================
$module = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['module'] ?? 'dashboard');
$action = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['action'] ?? 'index');

// =========================
// PUBLIC MODULE (KHÔNG CẦN LOGIN)
// =========================
$publicModules = ['auth'];

// =========================
// AUTH GUARD (CHẶN TOÀN HỆ THỐNG)
// =========================
if (!in_array($module, $publicModules)) {

    if (!isset($_SESSION['user'])) {
        header("Location: index.php?module=auth&action=login");
        exit;
    }
}

// =========================
// AUTO CONTROLLER
// =========================
$controllerName = ucfirst($module) . "Controller";
$controllerPath = ROOT_PATH . "/modules/$module/controllers/$controllerName.php";

// check module tồn tại
if (!file_exists($controllerPath)) {
    die("Module không tồn tại: $module");
}

require_once $controllerPath;

// check class
if (!class_exists($controllerName)) {
    die("Controller không tồn tại: $controllerName");
}

// =========================
// INIT CONTROLLER
// =========================
require_once ROOT_PATH . "/core/Database.php";

$db= new PDO(
    "mysql:host=127.0.0.1;dbname=merge_q;charset=utf8",
    "itcenter",
    "123456"
);

$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$controller = new $controllerName($db);

// =========================
// CHECK ACTION
// =========================
if (!method_exists($controller, $action)) {
    die("Action không tồn tại: $action");
}

// =========================
// EXECUTE
// =========================
$controller->$action();
