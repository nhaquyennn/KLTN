<?php

class FaceController extends Controller
{
    public function enroll()
    {
        $this->role(['admin', 'teacher']);

        $model = new FaceModel();
        $users = $model->getUsers();

        $view = ROOT_PATH . "/modules/face/views/enroll.php";
        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";

        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    public function attendance()
    {
        $this->role(['admin', 'teacher']);

        $model = new FaceModel();
        $sessions = $model->getTodayTeacherSessions($_SESSION['user']['id'] ?? 0);
        $selectedSessionId = (int) ($_GET['session_id'] ?? 0);
        $selectedSession = null;
        $networkAccess = $this->getAttendanceNetworkAccess();

        foreach ($sessions as $session) {
            if ((int) $session['session_id'] === $selectedSessionId) {
                $selectedSession = $session;
                break;
            }
        }

        $view = ROOT_PATH . "/modules/face/views/teacher_attendance.php";
        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";

        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    public function roster()
    {
        $this->role(['admin', 'teacher']);
        header('Content-Type: application/json');

        try {
            $sessionId = (int) ($_GET['session_id'] ?? 0);

            if (!$sessionId) {
                throw new Exception('Thiếu buổi học');
            }

            $model = new FaceModel();
            echo json_encode([
                'success' => true,
                'data' => $model->getSessionRoster($sessionId, $_SESSION['user']['id'] ?? 0)
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function checkIn()
    {
        $this->role(['admin', 'teacher']);
        header('Content-Type: application/json');

        try {
            $userId = (int) ($_POST['user_id'] ?? ($_POST['teacher_id'] ?? 0));
            $sessionId = (int) ($_POST['session_id'] ?? 0);

            if (!$userId || !$sessionId) {
                throw new Exception('Thiếu người dùng hoặc buổi học');
            }

            $networkAccess = $this->getAttendanceNetworkAccess();
            if (!$networkAccess['allowed']) {
                throw new Exception($networkAccess['message']);
            }

            $imageName = $this->saveAttendanceImage();
            $model = new FaceModel();

            echo json_encode($model->checkIn(
                $userId,
                $sessionId,
                $imageName,
                $_SESSION['user']['id'] ?? 0
            ));
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function checkOut()
    {
        $this->role(['admin', 'teacher']);
        header('Content-Type: application/json');

        $teacher_id = (int) ($_POST['teacher_id'] ?? 0);
        $model = new FaceModel();

        echo json_encode($model->checkOut($teacher_id));
    }

    public function recognizeProxy()
    {
        $this->role(['admin', 'teacher']);
        $this->proxyFaceAiPost('/recognize', [], ['image']);
    }

    public function enrollProxy()
    {
        $this->role(['admin', 'teacher']);
        $this->proxyFaceAiPost('/enroll', [
            'user_id' => $_POST['user_id'] ?? null,
        ], ['image']);
    }

    public function enrollResetProxy()
    {
        $this->role(['admin', 'teacher']);
        $this->proxyFaceAiPost('/enroll/reset', [
            'user_id' => $_POST['user_id'] ?? null,
        ]);
    }

    public function lateReport()
    {
        $filter = $_GET['filter'] ?? 'all';
        $date = $_GET['date'] ?? date('Y-m-d');
        $model = new FaceModel();
        $reports = $model->getAttendanceReport($filter, $date);

        $view = ROOT_PATH . "/modules/face/views/late_report.php";
        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";

        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    public function wifiConfig()
    {
        $this->role(['admin']);

        $config = $this->getAttendanceNetworkConfig();
        $currentAccess = $this->getAttendanceNetworkAccess();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $enabled = isset($_POST['enabled']);
            $cidrText = trim((string) ($_POST['allowed_cidrs'] ?? ''));
            $blockedMessage = trim((string) ($_POST['blocked_message'] ?? ''));
            $cidrs = array_values(array_filter(array_map('trim', preg_split('/\R+/', $cidrText))));
            $errors = [];

            if ($enabled && empty($cidrs)) {
                $errors[] = 'Vui lòng nhập ít nhất một IP hoặc subnet được phép.';
            }

            foreach ($cidrs as $cidr) {
                if (!$this->isValidCidrOrIp($cidr)) {
                    $errors[] = 'IP/subnet không hợp lệ: ' . $cidr;
                }
            }

            if ($blockedMessage === '') {
                $errors[] = 'Vui lòng nhập thông báo khi thiết bị không đúng WiFi.';
            }

            if (empty($errors)) {
                $config = [
                    'enabled' => $enabled,
                    'allowed_cidrs' => $cidrs,
                    'blocked_message' => $blockedMessage,
                ];

                try {
                    $this->saveAttendanceNetworkConfig($config);
                    $_SESSION['success'] = 'Đã lưu cấu hình WiFi điểm danh.';
                    header('Location: ?module=face&action=wifiConfig');
                    exit;
                } catch (Exception $e) {
                    $_SESSION['error'] = $e->getMessage();
                }
            }

            if (!empty($errors)) {
                $_SESSION['error'] = implode('<br>', $errors);
            }

            $config = [
                'enabled' => $enabled,
                'allowed_cidrs' => $cidrs,
                'blocked_message' => $blockedMessage,
            ];
        }

        $view = ROOT_PATH . "/modules/face/views/wifi_config.php";
        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";

        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    private function saveAttendanceImage()
    {
        if (empty($_FILES['image']) || ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }

        $uploadDir = ROOT_PATH . "/public/uploads/attendance/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION) ?: 'jpg';
        $imageName = 'attendance_' . time() . '_' . random_int(1000, 9999) . '.' . $ext;
        $uploadPath = $uploadDir . $imageName;

        move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath);

        return $imageName;
    }

    private function proxyFaceAiPost($path, array $fields = [], array $fileFields = [])
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Method not allowed'
            ]);
            return;
        }

        if (!function_exists('curl_init')) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'PHP cURL extension chưa được bật.'
            ]);
            return;
        }

        $postFields = [];

        foreach ($fields as $key => $value) {
            if ($value === null || $value === '') {
                http_response_code(422);
                echo json_encode([
                    'success' => false,
                    'message' => 'Thiếu dữ liệu: ' . $key
                ]);
                return;
            }

            $postFields[$key] = $value;
        }

        foreach ($fileFields as $field) {
            if (empty($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                http_response_code(422);
                echo json_encode([
                    'success' => false,
                    'message' => 'Thiếu file: ' . $field
                ]);
                return;
            }

            $postFields[$field] = curl_file_create(
                $_FILES[$field]['tmp_name'],
                $_FILES[$field]['type'] ?: 'application/octet-stream',
                $_FILES[$field]['name'] ?: $field
            );
        }

        $ch = curl_init($this->getFaceAiInternalBaseUrl() . $path);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            http_response_code(502);
            echo json_encode([
                'success' => false,
                'message' => 'Không kết nối được FastAPI AI server: ' . $error
            ]);
            return;
        }

        http_response_code($httpCode ?: 200);
        echo $response;
    }

    private function getFaceAiInternalBaseUrl()
    {
        $config = [
            'fastapi_internal_url' => 'http://127.0.0.1:8000',
        ];

        $configPath = ROOT_PATH . '/config/face_ai.php';
        if (is_file($configPath)) {
            $fileConfig = require $configPath;
            if (is_array($fileConfig)) {
                $config = array_merge($config, $fileConfig);
            }
        }

        return rtrim((string) ($config['fastapi_internal_url'] ?? 'http://127.0.0.1:8000'), '/');
    }

    private function getAttendanceNetworkAccess()
    {
        $config = $this->getAttendanceNetworkConfig();

        $clientIp = $this->getClientIp();
        $allowedCidrs = $config['allowed_cidrs'] ?? [];
        $enabled = (bool) ($config['enabled'] ?? true);
        $allowed = !$enabled
            || $this->isLocalhostIp($clientIp)
            || $this->ipMatchesAnyCidr($clientIp, $allowedCidrs);

        return [
            'enabled' => $enabled,
            'allowed' => $allowed,
            'client_ip' => $clientIp,
            'allowed_cidrs' => $allowedCidrs,
            'message' => $allowed
                ? 'Thiết bị đang nằm trong mạng được phép điểm danh.'
                : ($config['blocked_message'] ?? 'Thiết bị không nằm trong WiFi được phép điểm danh.'),
        ];
    }

    private function getClientIp()
    {
        $forwardedFor = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';

        if ($forwardedFor !== '') {
            $ips = array_map('trim', explode(',', $forwardedFor));

            foreach ($ips as $ip) {
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '';
    }

    private function isLocalhostIp($ip)
    {
        return in_array($ip, ['127.0.0.1', '::1'], true);
    }

    private function getAttendanceNetworkConfig()
    {
        $config = [
            'enabled' => true,
            'allowed_cidrs' => ['127.0.0.1/32', '::1/128'],
            'blocked_message' => 'Thiết bị không nằm trong WiFi được phép điểm danh. Vui lòng kết nối WiFi của trung tâm.',
        ];

        $configPath = ROOT_PATH . '/config/attendance_network.php';
        if (is_file($configPath)) {
            $fileConfig = require $configPath;
            if (is_array($fileConfig)) {
                $config = array_merge($config, $fileConfig);
            }
        }

        $config['allowed_cidrs'] = array_values(array_filter(array_map('trim', $config['allowed_cidrs'] ?? [])));

        return $config;
    }

    private function saveAttendanceNetworkConfig(array $config)
    {
        $configPath = ROOT_PATH . '/config/attendance_network.php';
        $content = "<?php\n\nreturn " . var_export($config, true) . ";\n";

        if (file_put_contents($configPath, $content, LOCK_EX) === false) {
            throw new Exception('Không thể lưu cấu hình WiFi điểm danh.');
        }
    }

    private function isValidCidrOrIp($value)
    {
        if (strpos($value, '/') === false) {
            return filter_var($value, FILTER_VALIDATE_IP) !== false;
        }

        [$ip, $prefix] = explode('/', $value, 2);

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return ctype_digit($prefix) && (int) $prefix >= 0 && (int) $prefix <= 32;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return ctype_digit($prefix) && (int) $prefix >= 0 && (int) $prefix <= 128;
        }

        return false;
    }

    private function ipMatchesAnyCidr($ip, array $cidrs)
    {
        foreach ($cidrs as $cidr) {
            if ($this->ipMatchesCidr($ip, trim((string) $cidr))) {
                return true;
            }
        }

        return false;
    }

    private function ipMatchesCidr($ip, $cidr)
    {
        if ($ip === '' || $cidr === '') {
            return false;
        }

        if (strpos($cidr, '/') === false) {
            return $ip === $cidr;
        }

        [$subnet, $prefix] = explode('/', $cidr, 2);
        $prefix = (int) $prefix;

        $ipBin = @inet_pton($ip);
        $subnetBin = @inet_pton($subnet);

        if ($ipBin === false || $subnetBin === false || strlen($ipBin) !== strlen($subnetBin)) {
            return false;
        }

        $maxBits = strlen($ipBin) * 8;
        if ($prefix < 0 || $prefix > $maxBits) {
            return false;
        }

        $fullBytes = intdiv($prefix, 8);
        $remainingBits = $prefix % 8;

        if ($fullBytes > 0 && substr($ipBin, 0, $fullBytes) !== substr($subnetBin, 0, $fullBytes)) {
            return false;
        }

        if ($remainingBits === 0) {
            return true;
        }

        $mask = (0xff << (8 - $remainingBits)) & 0xff;

        return (ord($ipBin[$fullBytes]) & $mask) === (ord($subnetBin[$fullBytes]) & $mask);
    }
}
