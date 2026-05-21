<?php
class CourseController extends Controller
{

    // =========================
    // LIST
    // =========================

    public function index()
    {
        $model = new CourseModel();

        // FILTER
        $keyword = $_GET['keyword'] ?? '';
        $status = $_GET['status'] ?? '';

        // PAGINATION
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $page = max(1, $page);

        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 5;

        $keyword = $_GET['keyword'] ?? '';
        $status = $_GET['status'] ?? '';

        $total = $model->countAll($keyword, $status);
        $totalPages = ceil($total / $limit);

        if ($page > $totalPages && $totalPages > 0) {
            $page = $totalPages;
        }

        $offset = ($page - 1) * $limit;

        $courses = $model->getAll($keyword, $status, $limit, $offset);

        // DATA
        $courses = $model->getAll($keyword, $status, $limit, $offset);
        $total = $model->countAll($keyword, $status);
        $totalPages = ceil($total / $limit);

        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";
        $view = ROOT_PATH . "/modules/course/views/index.php";
        require_once ROOT_PATH . "/modules/layouts/main.php";
    }
    // =========================
    // CREATE FORM
    // =========================
    public function create()
    {
        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";
        $view = ROOT_PATH . "/modules/course/views/create.php";
        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    // =========================
    // STORE
    // =========================
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $model = new CourseModel();

            $data = [
                'name' => $_POST['name'],
                'description' => $_POST['description'] ?? '',
                'status' => $_POST['status'] ?? 'active'
            ];

            $model->create($data);
        }

        header("Location: ?module=course");
        exit;
    }

    // =========================
    // EDIT FORM
    // =========================
    public function edit()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            header("Location: ?module=course");
            exit;
        }

        $model = new CourseModel();
        $course = $model->getById($id);

        if (!$course) {
            header("Location: ?module=course");
            exit;
        }

        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";
        $view = ROOT_PATH . "/modules/course/views/edit.php";
        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    // =========================
    // UPDATE
    // =========================
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['course_id'] ?? null;

            if ($id) {
                $model = new CourseModel();

                $data = [
                    'name' => $_POST['name'],
                    'description' => $_POST['description'] ?? '',
                    'status' => $_POST['status']
                ];

                $model->update($id, $data);
            }
        }

        header("Location: ?module=course");
        exit;
    }

    // =========================
    // DELETE
    // =========================
    public function delete()
    {
        $id = $_GET['id'] ?? null;

        if ($id) {
            $model = new CourseModel();
            $model->delete($id);
        }

        header("Location: ?module=course");
        exit;
    }
}