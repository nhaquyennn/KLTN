<?php
class PackageController extends Controller
{

    public function index()
    {
        $model = new PackageModel();
        $courseModel = new CourseModel();
        $courses = $courseModel->getAll();

        $keyword = $_GET['keyword'] ?? '';
        $status = $_GET['status'] ?? '';
        $course_id = $_GET['course_id'] ?? '';

        // FILTER (optional)
        $keyword = trim($_GET['keyword'] ?? '');
        $status = trim($_GET['status'] ?? '');

        // PAGINATION
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $page = max(1, $page);
        $limit = 5;
        $offset = ($page - 1) * $limit;

        $packages = $model->getAll($keyword, $status, $limit, $offset);
        $total = $model->countAll($keyword, $status);
        $totalPages = ceil($total / $limit);
        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";

        $view = ROOT_PATH . "/modules/package/views/index.php";
        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    public function create()
    {
        $courseModel = new CourseModel();
        $courses = $courseModel->getAll();
        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";
        $view = ROOT_PATH . "/modules/package/views/create.php";
        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    public function store()
    {
        $model = new PackageModel();

        $model->create([
            'name' => $_POST['name'],
            'total_sessions' => $_POST['total_sessions'],
            'price' => $_POST['price'],
            'course_id' => $_POST['course_id'],
            'status' => $_POST['status']
        ]);

        header("Location: ?module=package");
        exit;
    }

    public function edit()
    {
        $courseModel = new CourseModel();
        $courses = $courseModel->getAll();
        $model = new PackageModel();
        $package = $model->getById($_GET['id']);
        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";

        $view = ROOT_PATH . "/modules/package/views/edit.php";
        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    public function update()
    {
        $model = new PackageModel();

        $model->update($_POST['package_id'], [
            'name' => $_POST['name'],
            'total_sessions' => $_POST['total_sessions'],
            'price' => $_POST['price'],
            'course_id' => $_POST['course_id'],
            'status' => $_POST['status']
        ]);

        header("Location: ?module=package");
        exit;
    }

    public function delete()
    {
        $model = new PackageModel();
        $model->delete($_GET['id']);

        header("Location: ?module=package");
        exit;
    }

    public function getByCourse()
    {
        $course_id = $_GET['course_id'] ?? 0;

        $model = new PackageModel();
        $data = $model->getByCourse($course_id);

        echo json_encode($data);
    }
}