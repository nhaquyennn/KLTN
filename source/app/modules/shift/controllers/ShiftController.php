<?php
class ShiftController extends Controller
{

    public function index()
    {
        $model = new ShiftModel();

        $filters = [
            'keyword' => trim($_GET['keyword'] ?? '')
        ];

        $limit = 5;
        $total = $model->countAll($filters);
        $totalPages = ceil($total / $limit);
        $page = (int) ($_GET['page'] ?? 1);
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $offset = ($page - 1) * $limit;
        $shifts = $model->getAll($filters, $limit, $offset);
        $filters['start_time'] = $_GET['start_time'] ?? '';
        $filters['end_time'] = $_GET['end_time'] ?? '';

        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";
        $view = ROOT_PATH . "/modules/shift/views/index.php";
        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    public function create()
    {
        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";
        $view = ROOT_PATH . "/modules/shift/views/create.php";
        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    public function store()
    {
        $model = new ShiftModel();

        $model->create([
            'name' => $_POST['name'],
            'start_time' => $_POST['start_time'],
            'end_time' => $_POST['end_time']
        ]);

        header("Location: ?module=shift");
        exit;
    }

    public function edit()
    {
        $model = new ShiftModel();
        $shift = $model->getById($_GET['id']);

        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";
        $view = ROOT_PATH . "/modules/shift/views/edit.php";
        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    public function update()
    {
        $model = new ShiftModel();

        $model->update($_POST['shift_id'], [
            'name' => $_POST['name'],
            'start_time' => $_POST['start_time'],
            'end_time' => $_POST['end_time']
        ]);

        header("Location: ?module=shift");
        exit;
    }

    public function delete()
    {
        $model = new ShiftModel();
        $model->delete($_GET['id']);

        header("Location: ?module=shift");
        exit;
    }
}