<?php

class TeacherController extends Controller
{
    // ===== DANH SÁCH =====
    public function index()
    {
        $model = new TeacherModel();

        $page = $_GET['page'] ?? 1;

        $limit = 10;

        $offset = ($page - 1) * $limit;

        $filters = [
            'keyword' => $_GET['keyword'] ?? null,
            'specialization' => $_GET['specialization'] ?? null,
            'salary_type' => $_GET['salary_type'] ?? null,
            'status' => $_GET['status'] ?? null,
        ];

        $teachers = $model->getAll($filters, $limit, $offset);

        $total = $model->countAll($filters);

        $totalPages = ceil($total / $limit);

        // specialization list
        $specializationModel = new SpecializationModel();

        $specializations = $specializationModel->getAll('', 'active', 999, 0);

        $view = ROOT_PATH . "/modules/teacher/views/index.php";

        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";

        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    // ===== FORM CREATE =====
    public function create()
    {
        $specializationModel = new SpecializationModel();

        $specializations = $specializationModel->getAll('', 'active', 999, 0);

        $view = ROOT_PATH . "/modules/teacher/views/create.php";

        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";

        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    // ===== STORE =====
    public function store()
    {
        $model = new TeacherModel();

        $data = [
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'specialization_id' => $_POST['specialization_id'],
            'hire_date' => $_POST['hire_date'],
            'salary_type' => $_POST['salary_type'],
            'salary_value' => $_POST['salary_value'],
            'status' => $_POST['status']
        ];

        $model->create($data);

        header("Location: ?module=teacher");
        exit;
    }

    // ===== FORM EDIT =====
    public function edit()
    {
        $id = $_GET['id'] ?? 0;

        $model = new TeacherModel();

        $teacher = $model->findById($id);

        // specialization list
        $specializationModel = new SpecializationModel();

        $specializations = $specializationModel->getAll('', 'active', 999, 0);

        $view = ROOT_PATH . "/modules/teacher/views/edit.php";

        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";

        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    // ===== UPDATE =====
    public function update()
    {
        $model = new TeacherModel();

        $data = [
            'teacher_id' => $_POST['teacher_id'],
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'specialization_id' => $_POST['specialization_id'],
            'hire_date' => $_POST['hire_date'],
            'salary_type' => $_POST['salary_type'],
            'salary_value' => $_POST['salary_value']
        ];

        $model->update($data);

        header("Location: ?module=teacher&action=index");
        exit;
    }

    // ===== DELETE =====
    public function delete()
    {
        $id = $_GET['id'] ?? 0;

        if ($id) {

            $model = new TeacherModel();

            $model->delete($id);
        }

        header("Location: ?module=teacher&action=index");

        exit;
    }

    // ===== RESTORE =====
    public function restore()
    {
        $id = $_GET['id'];

        $model = new TeacherModel();

        $model->restore($id);

        header("Location: ?module=teacher");

        exit;
    }

    public function history()
    {
        $teacherId = $_SESSION['user']['id'];

        $model = new TeacherModel();

        $data = $model->getTeachingHistoryByUserId($teacherId);

        $view = ROOT_PATH . "/modules/teacher/views/history.php";
        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";
        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    
}