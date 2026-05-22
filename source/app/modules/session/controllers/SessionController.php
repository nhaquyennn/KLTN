<?php
class SessionController extends Controller
{
    public function index()
    {
        if (empty($_GET['class_id'])) {
            header("Location: ?module=class&action=index");
            exit;
        }

        $class_id = $_GET['class_id'];

        $model = new SessionModel();
        $roomModel = new RoomModel();
        $shiftModel = new ShiftModel();
        $teacherModel = new TeacherModel();

        $classModel = new ClassModel();
        $class = $classModel->getById($class_id);

        $isTeacher = ($_SESSION['user']['role'] ?? '') === 'teacher';
        $teacherId = $isTeacher
            ? $classModel->getTeacherIdByUserId($_SESSION['user']['id'] ?? 0)
            : null;

        $sessions = $model->getByClass($class_id, $isTeacher ? ($teacherId ?: -1) : null);
        $rooms = $model->getRoomsAvailableForClass($class_id);
        $shifts = $shiftModel->getAll([], 1000, 0);
        $teachers = $teacherModel->getAll([], 1000, 0);

        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";
        $view = ROOT_PATH . "/modules/session/views/index.php";
        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    public function store()
    {
        if (empty($_POST['class_id']) || empty($_POST['session_date']) || empty($_POST['shift_id'])) {
            header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '?module=class&action=index'));
            exit;
        }

        (new SessionModel())->create([
            'class_id' => $_POST['class_id'],
            'session_date' => $_POST['session_date'],
            'shift_id' => $_POST['shift_id'],
            'room_id' => $_POST['room_id'] ?? null
        ]);

        header("Location: ?module=session&action=index&class_id=" . $_POST['class_id']);
        exit;
    }

    public function generate()
    {
        $model = new SessionModel();

        $model->deleteSessions($_POST['class_id']);

        $model->generateSessionsCustom(
            $_POST['class_id'],
            $_POST['start_date'],
            $_POST['total_sessions']
        );

        header("Location: ?module=session&action=index&class_id=" . $_POST['class_id']);
        exit;
    }

    public function assignRoom()
    {
        (new SessionModel())->updateRoom(
            $_POST['session_id'],
            $_POST['room_id']
        );

        header("Location: " . $_SERVER['HTTP_REFERER']);
    }

    public function assignBulkRoom()
    {
        $result = (new SessionModel())->assignRoomToSessions(
            $_POST['session_ids'] ?? [],
            $_POST['room_id'] ?? null
        );

        $redirect = $_SERVER['HTTP_REFERER'] ?? '?module=class&action=index';
        $separator = strpos($redirect, '?') === false ? '?' : '&';

        header("Location: " . $redirect . $separator . http_build_query([
            'bulk_room_updated' => $result['updated'],
            'bulk_room_skipped' => $result['skipped']
        ]));
        exit;
    }

    public function assignTime()
    {
        (new SessionModel())->updateShift(
            $_POST['session_id'],
            $_POST['shift_id']
        );

        header("Location: " . $_SERVER['HTTP_REFERER']);
    }

    public function assignTeacher()
    {
        $model = new SessionModel();

        $model->saveTeachers(
            $_POST['session_id'],
            $_POST['main_teacher_id'] ?? null,
            $_POST['assistant_ids'] ?? []
        );

        header("Location: " . $_SERVER['HTTP_REFERER']);
    }

    public function assignBulkTeacher()
    {
        $result = (new SessionModel())->assignTeachersToSessions(
            $_POST['session_ids'] ?? [],
            $_POST['main_teacher_id'] ?? null,
            $_POST['assistant_ids'] ?? []
        );

        $redirect = $_SERVER['HTTP_REFERER'] ?? '?module=class&action=index';
        $separator = strpos($redirect, '?') === false ? '?' : '&';

        header("Location: " . $redirect . $separator . http_build_query([
            'bulk_teacher_updated' => $result['updated'],
            'bulk_teacher_skipped' => $result['skipped']
        ]));
        exit;
    }

    public function takeAttendance()
    {
        $model = new SessionModel();

        $model->takeAttendance($_GET['id']);
        $model->updateStatus($_GET['id'], 'done');

        header("Location: " . $_SERVER['HTTP_REFERER']);
    }

    public function cancel()
    {
        (new SessionModel())->updateStatus($_GET['id'], 'cancelled');

        header("Location: " . $_SERVER['HTTP_REFERER']);
    }

    public function getTeachersWithStatus()
    {
        $session_id = $_GET['session_id'];

        $model = new SessionModel();
        $teachers = $model->getTeachersWithStatus($session_id);

        echo json_encode($teachers);
    }

    public function getRoomsWithStatus()
    {
        $session_id = $_GET['session_id'];

        $model = new SessionModel();
        $rooms = $model->getRoomsWithStatus($session_id);

        echo json_encode($rooms);
    }

    public function getStudentsForAttendance()
    {
        $session_id = $_GET['session_id'];

        $model = new SessionModel();
        $data = $model->getStudentsForAttendance($session_id);

        echo json_encode($data);
    }

    public function getStudentsForReview()
    {
        $session_id = $_GET['session_id'];

        $model = new SessionModel();
        $data = $model->getStudentsForReview($session_id);

        header('Content-Type: application/json');
        echo json_encode($data);
    }

    public function saveAttendance()
    {
        $model = new SessionModel();

        $model->saveAttendance(
            $_POST['session_id'],
            $_POST['status']
        );

        header("Location: " . $_SERVER['HTTP_REFERER']);
    }

    public function assignBulkTime()
    {
        $result = (new SessionModel())->assignShiftToSessions(
            $_POST['session_ids'] ?? [],
            $_POST['shift_id'] ?? null
        );

        $redirect = $_SERVER['HTTP_REFERER'] ?? '?module=class&action=index';
        $separator = strpos($redirect, '?') === false ? '?' : '&';

        header("Location: " . $redirect . $separator . http_build_query([
            'bulk_time_updated' => $result['updated'],
            'bulk_time_skipped' => $result['skipped']
        ]));
        exit;
    }

    public function saveReview()
    {
        if (empty($_POST['session_id']) || empty($_POST['review_text']) || !is_array($_POST['review_text'])) {
            header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '?module=class&action=index'));
            exit;
        }

        (new SessionModel())->saveReviews(
            $_POST['session_id'],
            $_POST['review_text']
        );

        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '?module=class&action=index'));
        exit;
    }
}
