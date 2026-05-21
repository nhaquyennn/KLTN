<?php
class StudentController extends Controller
{
    public function index()
    {
        $model = new StudentModel();

        // FILTER
        $filters = [
            'keyword' => $_GET['keyword'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];

        // PAGINATION
        $limit = 5;
        $page = $_GET['page'] ?? 1;
        $offset = ($page - 1) * $limit;

        // DATA
        $students = $model->getAll($filters, $limit, $offset);
        $total = $model->countAll($filters);

        $totalPages = ceil($total / $limit);

        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";
        $view = ROOT_PATH . "/modules/student/views/index.php";

        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    public function create()
    {
        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";
        $view = ROOT_PATH . "/modules/student/views/create.php";
        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    public function edit()
    {
        $id = $_GET['id'];

        $model = new StudentModel();
        $student = $model->getById($id);

        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";
        $view = ROOT_PATH . "/modules/student/views/edit.php";
        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    public function store()
    {
        $model = new StudentModel();
        $model->create($_POST);

        header("Location: ?module=student");
    }

    public function update()
    {
        $model = new StudentModel();
        $model->update($_POST);

        header("Location: ?module=student");
    }

    public function delete()
    {
        $id = $_GET['id'];

        $model = new StudentModel();
        $model->delete($id);

        header("Location: ?module=student");
    }

    public function archive()
    {
        $id = $_GET['id'];

        $model = new StudentModel();
        $model->archive($id);

        header("Location: ?module=student");
    }

    public function restore()
    {
        $id = $_GET['id'];

        $model = new StudentModel();
        $model->restore($id);

        header("Location: ?module=student");
    }
}
