<?php
class ScheduleController extends Controller
{

    public function index()
    {
        $model = new ScheduleModel();

        $filters = [
            'keyword' => trim($_GET['keyword'] ?? ''),
            'day' => trim($_GET['day'] ?? ''),
            'status' => trim($_GET['status'] ?? '')
        ];

        $page = max(1, (int) ($_GET['page'] ?? 1));

        $limit = 5;
        $offset = ($page - 1) * $limit;

        $schedules = $model->getAll($filters, $limit, $offset);

        $total = $model->countAll($filters);

        $totalPages = ceil($total / $limit);

        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";
        $view = ROOT_PATH . "/modules/schedule/views/index.php";

        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    public function create()
    {
        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";
        $view = ROOT_PATH . "/modules/schedule/views/create.php";
        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    public function store()
    {
        $model = new ScheduleModel();

        $model->create(
            ['name' => $_POST['name'], 'code' => $_POST['code']],
            $_POST['days'] ?? []
        );

        header("Location: ?module=schedule");
        exit;
    }

    public function edit()
    {
        $model = new ScheduleModel();
        $schedule = $model->getById($_GET['id']);

        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";
        $view = ROOT_PATH . "/modules/schedule/views/edit.php";
        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    public function update()
    {
        $model = new ScheduleModel();

        $model->update(
            $_POST['schedule_id'],
            ['name' => $_POST['name'], 'code' => $_POST['code']],
            $_POST['days'] ?? []
        );

        header("Location: ?module=schedule");
        exit;
    }

    public function inactive()
    {
        $id = $_GET['id'] ?? 0;

        $model = new ScheduleModel();

        $model->updateStatus($id, 'inactive');

        header("Location: ?module=schedule");
        exit;
    }

    public function active()
    {
        $id = $_GET['id'] ?? 0;

        $model = new ScheduleModel();

        $model->updateStatus($id, 'active');

        header("Location: ?module=schedule");
        exit;
    }
}