<?php

class Daily_scheduleController extends Controller
{
    public function index()
    {
        $this->role(['admin']);

        $model = new DailyScheduleModel();

        $filters = [
            'date' => $_GET['date'] ?? date('Y-m-d'),
            'student_id' => $_GET['student_id'] ?? '',
            'teacher_id' => $_GET['teacher_id'] ?? ''
        ];

        $students = $model->getStudents();
        $teachers = $model->getTeachers();
        $studentSchedules = $model->getStudentSchedules($filters);
        $teacherSchedules = $model->getTeacherSchedules($filters);

        $view = ROOT_PATH . "/modules/daily_schedule/views/index.php";
        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";

        require_once ROOT_PATH . "/modules/layouts/main.php";
    }
}
