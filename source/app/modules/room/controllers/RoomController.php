<?php
class RoomController extends Controller
{
    public function index()
    {
        $model = new RoomModel();

        $keyword = trim($_GET['keyword'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $min_capacity = $_GET['min_capacity'] ?? '';
        $max_capacity = $_GET['max_capacity'] ?? '';

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 5;
        $offset = ($page - 1) * $limit;

        $rooms = $model->getAll($keyword, $status, $min_capacity, $max_capacity, $limit, $offset);
        $total = $model->countAll($keyword, $status, $min_capacity, $max_capacity);
        $totalPages = ceil($total / $limit);

        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";
        $view = ROOT_PATH . "/modules/room/views/index.php";
        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    public function create()
    {
        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";
        $view = ROOT_PATH . "/modules/room/views/create.php";
        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    public function store()
    {
        try {
            $model = new RoomModel();

            $model->create([
                'name' => $_POST['name'],
                'capacity' => $_POST['capacity'],
                'status' => $_POST['status']
            ]);
            $_SESSION['success'] = 'Thêm phòng học thành công';
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header("Location: ?module=room&action=create");
            exit;
        }

        header("Location: ?module=room");
        exit;
    }

    public function edit()
    {
        $model = new RoomModel();
        $room = $model->getById($_GET['id']);

        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";
        $view = ROOT_PATH . "/modules/room/views/edit.php";
        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    public function update()
    {
        $model = new RoomModel();

        $model->update([
            'room_id' => $_POST['room_id'],
            'name' => $_POST['name'],
            'capacity' => $_POST['capacity'],
            'status' => $_POST['status']
        ]);

        header("Location: ?module=room");
        exit;
    }

    public function inactive()
    {
        $model = new RoomModel();

        $id = $_GET['id'] ?? 0;

        // Đang học thật
        if ($model->hasRunningSessions($id)) {

            $_SESSION['modal'] = [
                'type' => 'danger',
                'title' => 'Không thể ngưng hoạt động',
                'message' =>
                    'Phòng hiện đang được sử dụng cho buổi học đang diễn ra.'
            ];

            header("Location: ?module=room");
            exit;
        }

        // Có lịch tương lai -> mở modal xử lý
        if (
            $model->hasFutureSessions($id)
            && !isset($_GET['confirm'])
        ) {

            $_SESSION['modal'] = [
                'type' => 'warning',
                'title' => 'Xác nhận ngưng hoạt động',
                'message' =>
                    'Hiện đang có lịch với phòng này, 
                bạn có chắc chắn muốn ngưng hoạt động phòng không?'
            ];

            header("Location: ?module=room&action=inactive&id=$id&confirm=1");
            exit;
        }

        // Sau confirm -> chuyển qua màn reassign
        if (
            $model->hasFutureSessions($id)
            && isset($_GET['confirm'])
        ) {

            $_SESSION['inactive_room_id'] = $id;

            header("Location: ?module=room&action=reassignRoom");
            exit;
        }

        // Không có lịch -> inactive luôn
        $model->changeStatus($id, 'inactive');

        $_SESSION['modal'] = [
            'type' => 'success',
            'title' => 'Thành công',
            'message' => 'Đã ngưng hoạt động phòng.'
        ];

        header("Location: ?module=room");
        exit;
    }

    public function reassignRoom()
    {
        $roomModel = new RoomModel();

        $roomId = $_SESSION['inactive_room_id'] ?? 0;

        if (!$roomId) {
            header("Location: ?module=room");
            exit;
        }

        $sessions = $roomModel->getFutureSessions($roomId);

        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";
        $view = ROOT_PATH . "/modules/room/views/reassign.php";

        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    public function saveReassign()
    {
        $roomModel = new RoomModel();
        $sessionModel = new SessionModel();

        $oldRoomId = $_SESSION['inactive_room_id'] ?? 0;

        if (!$oldRoomId) {
            header("Location: ?module=room");
            exit;
        }

        $rooms = $_POST['rooms'] ?? [];

        foreach ($rooms as $sessionId => $newRoomId) {

            if ($sessionModel->isRoomBusy($newRoomId, $sessionId)) {

                $_SESSION['modal'] = [
                    'type' => 'danger',
                    'title' => 'Trùng phòng',
                    'message' =>
                        'Một trong các phòng đã chọn đang bận.'
                ];

                header("Location: ?module=room&action=reassignRoom");
                exit;
            }

            $sessionModel->updateRoom($sessionId, $newRoomId);
        }

        // inactive phòng cũ
        $roomModel->changeStatus($oldRoomId, 'inactive');

        unset($_SESSION['inactive_room_id']);

        $_SESSION['modal'] = [
            'type' => 'success',
            'title' => 'Thành công',
            'message' =>
                'Đã cập nhật phòng học và ngưng hoạt động phòng.'
        ];

        header("Location: ?module=room");
        exit;
    }

    public function active()
    {
        $model = new RoomModel();

        $model->changeStatus($_GET['id'], 'active');

        header("Location: ?module=room");
        exit;
    }
}
