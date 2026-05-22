<?php

require_once ROOT_PATH . '/modules/teacherAttendance/services/TeacherAttendanceService.php';

class TeacherAttendanceController extends Controller
{
    private $service;

    public function __construct()
    {
        $this->service = new TeacherAttendanceService();
    }

    public function showAttendancePage()
    {
        $this->role(['teacher']);
        $teacher = $this->requireTeacher();

        try {
            $session = $this->service->validateTeachingSession($teacher['teacher_id'], $_GET['session_id'] ?? 0);
            $attendance = $this->service->getAttendanceStatus($teacher['teacher_id'], $session['session_id']);
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: ?module=teacherDashboard&action=index');
            exit;
        }

        $view = ROOT_PATH . "/modules/teacherAttendance/views/checkin.php";
        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";
        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    public function manualCheckin()
    {
        $this->jsonCheckin(function ($teacher, $session) {
            $timing = $this->service->calculateAttendanceStatus($session);
            $note = trim((string) ($_POST['note'] ?? ''));

            if ($timing['minutes'] > 0 && $note === '') {
                throw new Exception('Vui lòng nhập lý do khi điểm danh thủ công sau giờ bắt đầu');
            }

            return $this->service->createAttendanceRecord($teacher['teacher_id'], $session, 'MANUAL', null, null, $note);
        });
    }

    public function faceCheckin()
    {
        $this->jsonCheckin(function ($teacher, $session) {
            if (empty($_FILES['image']) || ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                throw new Exception('Thiếu ảnh điểm danh');
            }

            $ai = $this->recognizeFace($_FILES['image']);
            $recognizedUserId = (int) ($ai['user_id'] ?? $ai['teacher_id'] ?? 0);

            if (empty($ai['success']) || empty($ai['matched']) || $recognizedUserId !== (int) $teacher['user_id']) {
                throw new Exception('Khuôn mặt không khớp với tài khoản giảng viên');
            }

            $snapshot = $this->saveSnapshot($_FILES['image']);
            $confidence = $ai['confidence'] ?? $ai['score'] ?? null;
            return $this->service->createAttendanceRecord($teacher['teacher_id'], $session, 'FACE', $confidence, $snapshot);
        });
    }

    public function getAttendanceStatus()
    {
        $this->role(['teacher']);
        header('Content-Type: application/json');

        try {
            $teacher = $this->requireTeacher();
            $session = $this->service->validateTeachingSession($teacher['teacher_id'], $_GET['session_id'] ?? 0);
            echo json_encode([
                'success' => true,
                'message' => 'OK',
                'data' => $this->service->getAttendanceStatus($teacher['teacher_id'], $session['session_id'])
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage(), 'data' => []], JSON_UNESCAPED_UNICODE);
        }

        exit;
    }

    private function jsonCheckin($callback)
    {
        $this->role(['teacher']);
        header('Content-Type: application/json');

        try {
            $teacher = $this->requireTeacher();
            $session = $this->service->validateTeachingSession($teacher['teacher_id'], $_POST['session_id'] ?? 0);
            $data = $callback($teacher, $session);
            $messages = [
                'present' => 'Điểm danh thành công: đúng giờ.',
                'late' => 'Điểm danh đi trễ, hệ thống đã tạo phạt 50.000đ.',
                'late_absent' => 'Điểm danh sau 30 phút: xác nhận vắng, phạt 100.000đ và buổi học đã hủy.',
            ];
            echo json_encode([
                'success' => $data['status'] !== 'late_absent',
                'message' => $messages[$data['status']] ?? 'Đã lưu điểm danh.',
                'data' => $data
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage(), 'data' => []], JSON_UNESCAPED_UNICODE);
        }

        exit;
    }

    private function recognizeFace($file)
    {
        if (!function_exists('curl_init')) {
            throw new Exception('PHP cURL extension chưa được bật');
        }

        $config = is_file(ROOT_PATH . '/config/face_ai.php') ? require ROOT_PATH . '/config/face_ai.php' : [];
        $url = rtrim((string) ($config['FACE_API_URL'] ?? $config['fastapi_internal_url'] ?? 'http://127.0.0.1:8000'), '/') . '/recognize';
        $timeout = (int) ($config['FACE_API_TIMEOUT'] ?? 30);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => [
                'image' => curl_file_create($file['tmp_name'], $file['type'] ?: 'image/jpeg', $file['name'] ?: 'face.jpg')
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => max(5, $timeout),
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new Exception('Không kết nối được FastAPI nhận diện: ' . $error);
        }

        $data = json_decode($response, true);
        if (!is_array($data)) {
            throw new Exception('FastAPI trả dữ liệu nhận diện không hợp lệ');
        }

        return $data;
    }

    private function saveSnapshot($file)
    {
        $dir = ROOT_PATH . '/public/uploads/attendance/';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $ext = pathinfo($file['name'] ?? '', PATHINFO_EXTENSION) ?: 'jpg';
        $name = 'teacher_dashboard_' . time() . '_' . random_int(1000, 9999) . '.' . $ext;

        if (!move_uploaded_file($file['tmp_name'], $dir . $name)) {
            throw new Exception('Không lưu được snapshot điểm danh');
        }

        return $name;
    }

    private function requireTeacher()
    {
        $teacher = $this->service->getTeacherByUserId($_SESSION['user']['id'] ?? 0);

        if (!$teacher) {
            throw new Exception('Không tìm thấy hồ sơ giảng viên');
        }

        return $teacher;
    }
}
