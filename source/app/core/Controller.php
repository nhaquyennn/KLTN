<?php

class Controller {

    protected function view($module, $view, $data = []) {
        extract($data);
        require_once "../modules/$module/views/$view.php";
    }
    protected function auth() {
        if (!isset($_SESSION['user'])) {
            header("Location: ?module=auth&action=login");
            exit;
        }
    }

    protected function role($roles = []) {
        $this->auth();

        if (!in_array($_SESSION['user']['role'], $roles)) {
            echo "🚫 Bạn không có quyền truy cập!";
            exit;
        }
    }
}