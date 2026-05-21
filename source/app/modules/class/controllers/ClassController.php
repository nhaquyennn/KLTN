<?php
class ClassController extends Controller
{
    public function index()
    {
        $model = new ClassModel();

        $courseModel = new CourseModel();
        $packageModel = new PackageModel();
        $scheduleModel = new ScheduleModel();
        $shiftModel = new ShiftModel();

        $courses = $courseModel->getAll();
        $packages = $packageModel->getAll();
        $schedules = $scheduleModel->getAll([], 1000, 0);
        $shifts = $shiftModel->getAll([], 1000, 0);

        $isTeacher = ($_SESSION['user']['role'] ?? '') === 'teacher';
        $teacherId = $isTeacher
            ? $model->getTeacherIdByUserId($_SESSION['user']['id'] ?? 0)
            : null;

        $filters = [
            'keyword' => trim($_GET['keyword'] ?? ''),
            'course_id' => trim($_GET['course_id'] ?? ''),
            'package_id' => trim($_GET['package_id'] ?? ''),
            'schedule_id' => trim($_GET['schedule_id'] ?? ''),
            'shift_id' => trim($_GET['shift_id'] ?? ''),
            'status' => trim($_GET['status'] ?? ''),

            // thêm dòng này
            'teacher_id' => $isTeacher ? ($teacherId ?: -1) : null
        ];

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 5;
        $offset = ($page - 1) * $limit;

        $classes = $model->getAll($filters, $limit, $offset);
        $total = $model->countAll($filters);
        $totalPages = ceil($total / $limit);

        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";
        $view = ROOT_PATH . "/modules/class/views/index.php";
        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    public function store()
    {
        $model = new ClassModel();

        $model->create([
            'course_id' => $_POST['course_id'],
            'package_id' => $_POST['package_id'],
            'schedule_id' => $_POST['schedule_id'],
            'shift_id' => $_POST['shift_id'],
            'start_date' => $_POST['start_date'],
            'max_students' => max(1, (int) ($_POST['max_students'] ?? 10))
        ]);

        header("Location: ?module=class");
        exit;
    }

    public function edit()
    {
        $model = new ClassModel();

        $courseModel = new CourseModel();
        $packageModel = new PackageModel();
        $scheduleModel = new ScheduleModel();
        $shiftModel = new ShiftModel();

        $class = $model->getById($_GET['id']);

        $courses = $courseModel->getAll();
        $packages = $packageModel->getAll();
        $schedules = $scheduleModel->getAll([], 1000, 0);
        $shifts = $shiftModel->getAll([], 1000, 0);

        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";
        $view = ROOT_PATH . "/modules/class/views/edit.php";
        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    public function update()
    {
        $model = new ClassModel();

        $model->update([
            'class_id' => $_POST['class_id'],
            'course_id' => $_POST['course_id'],
            'package_id' => $_POST['package_id'],
            'schedule_id' => $_POST['schedule_id'],
            'shift_id' => $_POST['shift_id'],
            'start_date' => $_POST['start_date'],
            'max_students' => max(1, (int) ($_POST['max_students'] ?? 10))
        ]);

        header("Location: ?module=class");
        exit;
    }

    public function create()
    {
        $courseModel = new CourseModel();
        $packageModel = new PackageModel();
        $scheduleModel = new ScheduleModel();
        $shiftModel = new ShiftModel();

        $courses = $courseModel->getAll();
        $packages = $packageModel->getAll();
        $schedules = $scheduleModel->getAll([], 1000, 0);
        $shifts = $shiftModel->getAll([], 1000, 0);

        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";
        $view = ROOT_PATH . "/modules/class/views/create.php";

        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    public function deactivate()
    {
        (new ClassModel())->deactivate($_GET['id']);
        header("Location: ?module=class");
        exit;
    }

    public function activate()
    {
        (new ClassModel())->activate($_GET['id']);
        header("Location: ?module=class");
        exit;
    }
}
