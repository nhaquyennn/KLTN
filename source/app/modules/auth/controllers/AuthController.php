<?php

class AuthController extends Controller
{
    public function login()
    {
        $view = ROOT_PATH . "/modules/auth/views/login.php";
        $header = ROOT_PATH . "/modules/layouts/header_auth.php";
        require_once ROOT_PATH . "/modules/layouts/auth_main.php";
    }

    public function handleLogin()
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $userModel = new User();
        $result = $userModel->login($email, $password);

        if ($result === "email_not_found") {
            $_SESSION['error'] = "Email không tồn tại";
            header("Location: ?module=auth&action=login");
            exit;
        }

        if ($result === "wrong_password") {
            $_SESSION['error'] = "Sai mật khẩu";
            header("Location: ?module=auth&action=login");
            exit;
        }

        $user = $result;

        if ($user['status'] == 0) {
            $_SESSION['error'] = "Tài khoản bị khóa";
            header("Location: ?module=auth&action=login");
            exit;
        }

        // =========================
        // SESSION LOGIN
        // =========================
        $_SESSION['user'] = [
            'id' => $user['user_id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role']
        ];

        switch ($user['role']) {

            case 'admin':
                header("Location: ?module=dashboard&action=index");
                break;

            case 'teacher':
                header("Location: ?module=class&action=index");
                // hoặc teacher landing page bạn muốn
                break;

            case 'parent':
                header("Location: ?module=parent&action=dashboard");
                break;

            case 'student':
                header("Location: ?module=parent&action=dashboard");
                break;

            default:
                header("Location: ?module=auth&action=login");
                break;
        }
        exit;

        exit;
    }

    public function logout()
    {
        session_destroy();
        header("Location: ?module=auth&action=login");
        exit;
    }

    public function changePassword()
    {
        $this->auth();

        $view = ROOT_PATH . "/modules/auth/views/change_password.php";
        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";
        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    public function updatePassword()
    {
        $this->auth();

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (strlen($newPassword) < 6) {
            $_SESSION['error'] = "Mật khẩu mới phải có ít nhất 6 ký tự";
            header("Location: ?module=auth&action=changePassword");
            exit;
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['error'] = "Xác nhận mật khẩu không khớp";
            header("Location: ?module=auth&action=changePassword");
            exit;
        }

        $userModel = new User();
        $user = $userModel->findById($_SESSION['user']['id']);

        if (!$userModel->verifyPassword($user, $currentPassword)) {
            $_SESSION['error'] = "Mật khẩu hiện tại không đúng";
            header("Location: ?module=auth&action=changePassword");
            exit;
        }

        $userModel->updatePassword($_SESSION['user']['id'], $newPassword);
        $_SESSION['success'] = "Đổi mật khẩu thành công";

        header("Location: ?module=auth&action=changePassword");
        exit;
    }

    public function forgotPassword()
    {
        $view = ROOT_PATH . "/modules/auth/views/forgot_password.php";
        $header = ROOT_PATH . "/modules/layouts/header_auth.php";
        require_once ROOT_PATH . "/modules/layouts/auth_main.php";
    }

    public function handleForgotPassword()
    {
        $email = trim($_POST['email'] ?? '');

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (!$user) {
            $_SESSION['error'] = "Email không tồn tại";
            header("Location: ?module=auth&action=forgotPassword");
            exit;
        }

        if ((int) $user['status'] === 0) {
            $_SESSION['error'] = "Tài khoản đang bị khóa";
            header("Location: ?module=auth&action=forgotPassword");
            exit;
        }

        $_SESSION['reset_user_id'] = $user['user_id'];
        $_SESSION['reset_email'] = $user['email'];

        header("Location: ?module=auth&action=resetPassword");
        exit;
    }

    public function resetPassword()
    {
        if (empty($_SESSION['reset_user_id'])) {
            header("Location: ?module=auth&action=forgotPassword");
            exit;
        }

        $view = ROOT_PATH . "/modules/auth/views/reset_password.php";
        $header = ROOT_PATH . "/modules/layouts/header_auth.php";
        require_once ROOT_PATH . "/modules/layouts/auth_main.php";
    }

    public function handleResetPassword()
    {
        if (empty($_SESSION['reset_user_id'])) {
            header("Location: ?module=auth&action=forgotPassword");
            exit;
        }

        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (strlen($newPassword) < 6) {
            $_SESSION['error'] = "Mật khẩu mới phải có ít nhất 6 ký tự";
            header("Location: ?module=auth&action=resetPassword");
            exit;
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['error'] = "Xác nhận mật khẩu không khớp";
            header("Location: ?module=auth&action=resetPassword");
            exit;
        }

        (new User())->updatePassword($_SESSION['reset_user_id'], $newPassword);

        unset($_SESSION['reset_user_id'], $_SESSION['reset_email']);
        $_SESSION['success'] = "Đặt lại mật khẩu thành công. Vui lòng đăng nhập.";

        header("Location: ?module=auth&action=login");
        exit;
    }
}
