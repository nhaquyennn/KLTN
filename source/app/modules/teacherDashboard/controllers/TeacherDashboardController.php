<?php

class TeacherDashboardController extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new TeacherDashboardModel();
    }

    public function index()
    {
        $this->role(['teacher']);
        $teacher = $this->requireTeacher();
        (new FaceModel())->processOverdueAttendancePolicy();

        $overview = $this->model->getOverviewStats($teacher['teacher_id']);
        $todaySchedule = $this->model->getTodaySchedule($teacher['teacher_id']);
        $myClasses = $this->model->getMyClasses($teacher['teacher_id']);
        $salary = $this->model->getSalarySummary($teacher);
        $rewardPenaltyHistory = $this->model->getRewardPenaltyHistory($teacher['teacher_id'], $_GET);
        $notifications = $this->model->getNotifications($teacher['user_id']);
        $reportSeries = $this->model->getReportSeries($teacher['teacher_id']);

        $view = ROOT_PATH . "/modules/teacherDashboard/views/index.php";
        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";
        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    public function getTodaySchedule()
    {
        $this->json(fn($teacher) => $this->model->getTodaySchedule($teacher['teacher_id']));
    }

    public function getOverviewStats()
    {
        $this->json(fn($teacher) => $this->model->getOverviewStats($teacher['teacher_id']));
    }

    public function getMyClasses()
    {
        $this->json(fn($teacher) => $this->model->getMyClasses($teacher['teacher_id']));
    }

    public function getStudentsByClass()
    {
        $this->json(fn($teacher) => $this->model->getStudentsByClass($teacher['teacher_id'], $_GET['class_id'] ?? 0));
    }

    public function getSalarySummary()
    {
        $this->json(fn($teacher) => $this->model->getSalarySummary($teacher));
    }

    public function getRewardPenaltyHistory()
    {
        $this->json(fn($teacher) => $this->model->getRewardPenaltyHistory($teacher['teacher_id'], $_GET));
    }

    public function getNotifications()
    {
        $this->json(fn($teacher) => $this->model->getNotifications($teacher['user_id']));
    }

    private function json($callback)
    {
        $this->role(['teacher']);
        header('Content-Type: application/json');

        try {
            echo json_encode([
                'success' => true,
                'message' => 'OK',
                'data' => $callback($this->requireTeacher())
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], JSON_UNESCAPED_UNICODE);
        }

        exit;
    }

    private function requireTeacher()
    {
        $teacher = $this->model->getTeacherByUserId($_SESSION['user']['id'] ?? 0);

        if (!$teacher) {
            throw new Exception('Không tìm thấy hồ sơ giảng viên đang đăng nhập');
        }

        return $teacher;
    }
}
