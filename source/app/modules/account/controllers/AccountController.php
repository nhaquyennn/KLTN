<?php

class AccountController extends Controller
{
    public function index()
    {
        $this->role(['admin']);

        $model = new AccountModel();

        $filters = [
            'keyword' => trim($_GET['keyword'] ?? ''),
            'role' => $_GET['role'] ?? '',
            'status' => $_GET['status'] ?? '',
            'deleted' => false
        ];

        $limit = 10;
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $offset = ($page - 1) * $limit;

        $accounts = $model->getAll($filters, $limit, $offset);
        $total = $model->countAll($filters);
        $totalPages = (int) ceil($total / $limit);

        $view = ROOT_PATH . "/modules/account/views/index.php";
        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";

        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    public function deleted()
    {
        $this->role(['admin']);

        $model = new AccountModel();

        $filters = [
            'keyword' => trim($_GET['keyword'] ?? ''),
            'role' => $_GET['role'] ?? '',
            'status' => $_GET['status'] ?? '',
            'deleted' => true
        ];

        $limit = 10;
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $offset = ($page - 1) * $limit;

        $accounts = $model->getAll($filters, $limit, $offset);
        $total = $model->countAll($filters);
        $totalPages = (int) ceil($total / $limit);
        $showDeleted = true;

        $view = ROOT_PATH . "/modules/account/views/index.php";
        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";

        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    public function create()
    {
        $this->role(['admin']);

        $view = ROOT_PATH . "/modules/account/views/create.php";
        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";

        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    public function store()
    {
        $this->role(['admin']);

        try {
            (new AccountModel())->create($_POST);
            $_SESSION['success'] = 'Tạo tài khoản thành công';
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        header("Location: ?module=account&action=index");
        exit;
    }

    public function edit()
    {
        $this->role(['admin']);

        $account = (new AccountModel())->findById($_GET['id'] ?? 0);

        if (!$account) {
            $_SESSION['error'] = 'Không tìm thấy tài khoản';
            header("Location: ?module=account&action=index");
            exit;
        }

        $view = ROOT_PATH . "/modules/account/views/edit.php";
        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";

        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    public function update()
    {
        $this->role(['admin']);

        try {
            (new AccountModel())->update($_POST);
            $_SESSION['success'] = 'Cập nhật tài khoản thành công';
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        header("Location: ?module=account&action=index");
        exit;
    }

    public function lock()
    {
        $this->role(['admin']);

        (new AccountModel())->updateStatus($_GET['id'] ?? 0, 0);

        header("Location: ?module=account&action=index");
        exit;
    }

    public function unlock()
    {
        $this->role(['admin']);

        (new AccountModel())->updateStatus($_GET['id'] ?? 0, 1);

        header("Location: ?module=account&action=index");
        exit;
    }

    public function delete()
    {
        $this->role(['admin']);

        $id = (int) ($_GET['id'] ?? 0);

        if ($id === (int) ($_SESSION['user']['id'] ?? 0)) {
            $_SESSION['error'] = 'Không thể xóa tài khoản đang đăng nhập';
            header("Location: ?module=account&action=index");
            exit;
        }

        (new AccountModel())->deleteAccount($id);
        $_SESSION['success'] = 'Đã xóa tài khoản đăng nhập, thông tin người dùng vẫn được giữ nguyên';

        header("Location: ?module=account&action=index");
        exit;
    }

    public function restore()
    {
        $this->role(['admin']);

        (new AccountModel())->restoreAccount($_GET['id'] ?? 0);
        $_SESSION['success'] = 'Đã khôi phục tài khoản. Nếu tài khoản cũ đã mất mật khẩu, hãy cập nhật mật khẩu trước khi sử dụng.';

        header("Location: ?module=account&action=deleted");
        exit;
    }

    public function forceDelete()
    {
        $this->role(['admin']);

        $id = (int) ($_GET['id'] ?? 0);

        if ($id === (int) ($_SESSION['user']['id'] ?? 0)) {
            $_SESSION['error'] = 'Không thể xóa hẳn tài khoản đang đăng nhập';
            header("Location: ?module=account&action=deleted");
            exit;
        }

        try {
            $deleted = (new AccountModel())->permanentlyDeleteAccount($id);
            $_SESSION[$deleted ? 'success' : 'error'] = $deleted
                ? 'Đã xóa hẳn tài khoản'
                : 'Tài khoản không tồn tại hoặc chưa được xóa mềm';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Không thể xóa hẳn vì tài khoản còn dữ liệu học viên hoặc giảng viên liên quan.';
        }

        header("Location: ?module=account&action=deleted");
        exit;
    }
}
