<?php

class SpecializationController extends Controller
{
    public function index()
    {
        $model = new SpecializationModel();

        $keyword = trim($_GET['keyword'] ?? '');
        $status = trim($_GET['status'] ?? '');

        $page = max(1, (int)($_GET['page'] ?? 1));

        $limit = 5;

        $offset = ($page - 1) * $limit;

        $specializations = $model->getAll(
            $keyword,
            $status,
            $limit,
            $offset
        );

        $total = $model->countAll(
            $keyword,
            $status
        );

        $totalPages = ceil($total / $limit);

        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";

        $view = ROOT_PATH . "/modules/specialization/views/index.php";

        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    public function create()
    {
        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";

        $view = ROOT_PATH . "/modules/specialization/views/create.php";

        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    public function store()
    {
        $model = new SpecializationModel();

        $model->create([
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'status' => $_POST['status']
        ]);

        header("Location: ?module=specialization");

        exit;
    }

    public function edit()
    {
        $model = new SpecializationModel();

        $specialization = $model->getById($_GET['id']);

        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";

        $view = ROOT_PATH . "/modules/specialization/views/edit.php";

        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    public function update()
    {
        $model = new SpecializationModel();

        $model->update([
            'specialization_id' => $_POST['specialization_id'],
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'status' => $_POST['status']
        ]);

        header("Location: ?module=specialization");

        exit;
    }

    public function delete()
    {
        $model = new SpecializationModel();

        $model->delete($_GET['id']);

        header("Location: ?module=specialization");

        exit;
    }
}