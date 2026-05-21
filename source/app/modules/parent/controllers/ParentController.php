<?php

class ParentController extends Controller
{
    private $parentModel;

    public function __construct()
    {
        $this->parentModel = new ParentModel();
    }

    public function dashboard()
    {
        $this->role(['parent', 'student']);

        $data = $this->parentModel->getParentAndStudents();

        $parentName = $data['parent_name'];
        $students = $data['students'];
        $selectedStudentId = $this->parentModel->getSelectedStudentId($students);
        $summary = $selectedStudentId ? $this->parentModel->getStudentSummary($selectedStudentId) : [];
        $learningTracker = $selectedStudentId ? $this->parentModel->getLearningTrackerData($selectedStudentId) : [];
        $upcomingSessions = $selectedStudentId ? $this->parentModel->getUpcomingSessions($selectedStudentId, 3) : [];
        $reviews = $selectedStudentId ? $this->parentModel->getLatestReviews($selectedStudentId, 3) : [];
        $parentInitials = $this->getInitials($parentName);

        $view = ROOT_PATH . "/modules/parent/views/parent_dashboard.php";
        $header = ROOT_PATH . "/modules/layouts/header_parent.php";

        require_once ROOT_PATH . "/modules/layouts/parent_main.php";
    }

    public function parent_dashboard()
    {
        return $this->dashboard();
    }

    public function calendar()
    {
        $this->role(['parent', 'student']);

        $data = $this->parentModel->getParentAndStudents();

        $parentName = $data['parent_name'];
        $students = $data['students'];
        $selectedStudentId = $this->parentModel->getSelectedStudentId($students);
        $calendarSessions = $selectedStudentId ? $this->parentModel->getCalendarSessions($selectedStudentId) : [];
        $parentInitials = $this->getInitials($parentName);

        $view = ROOT_PATH . "/modules/parent/views/calendar.php";
        $header = ROOT_PATH . "/modules/layouts/header_parent.php";

        require_once ROOT_PATH . "/modules/layouts/parent_main.php";
    }

    public function report()
    {
        $this->role(['parent', 'student']);

        $data = $this->parentModel->getParentAndStudents();

        $parentName = $data['parent_name'];
        $students = $data['students'];
        $selectedStudentId = $this->parentModel->getSelectedStudentId($students);
        $reviews = $selectedStudentId ? $this->parentModel->getLatestReviews($selectedStudentId, 20) : [];
        $parentInitials = $this->getInitials($parentName);

        $view = ROOT_PATH . "/modules/parent/views/daily_report.php";
        $header = ROOT_PATH . "/modules/layouts/header_parent.php";

        require_once ROOT_PATH . "/modules/layouts/parent_main.php";
    }

    public function packages()
    {
        $this->role(['parent', 'student']);

        $data = $this->parentModel->getParentAndStudents();
        $parentName = $data['parent_name'];
        $students = $data['students'];
        $selectedStudentId = $this->parentModel->getSelectedStudentId($students);
        $classes = $selectedStudentId ? $this->parentModel->getAvailableClassesForStudent($selectedStudentId) : [];
        $parentInitials = $this->getInitials($parentName);

        $view = ROOT_PATH . "/modules/parent/views/packages.php";
        $header = ROOT_PATH . "/modules/layouts/header_parent.php";

        require_once ROOT_PATH . "/modules/layouts/parent_main.php";
    }

    public function registerPackage()
    {
        $this->role(['parent', 'student']);

        try {
            $studentId = (int) ($_POST['student_id'] ?? 0);
            $classId = (int) ($_POST['class_id'] ?? 0);

            if (!$studentId || !$classId) {
                throw new Exception('Vui lòng chọn học viên và gói học');
            }

            $enrollmentId = $this->parentModel->createEnrollment($studentId, $classId);
            $_SESSION['success'] = 'Đăng ký gói học thành công. Vui lòng thanh toán học phí.';

            header("Location: ?module=parent&action=payment&id=" . $enrollmentId);
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header("Location: ?module=parent&action=packages");
            exit;
        }
    }

    public function payment()
    {
        $this->role(['parent', 'student']);

        $id = (int) ($_GET['id'] ?? 0);
        $enrollment = $this->parentModel->findEnrollmentForCurrentParent($id);

        if (!$enrollment) {
            $_SESSION['error'] = 'Không tìm thấy đơn đăng ký';
            header("Location: ?module=parent&action=packages");
            exit;
        }

        require ROOT_PATH . "/modules/enrollment/vnpay_php/config.php";

        $remaining = (float) $enrollment['final_fee'] - (float) $enrollment['paid_amount'];

        if ($remaining <= 0) {
            $_SESSION['success'] = 'Đơn đăng ký đã thanh toán đủ';
            header("Location: ?module=parent&action=dashboard&student_id=" . $enrollment['student_id']);
            exit;
        }

        $vnp_TxnRef = $id . "_" . time();
        $vnp_Amount = (int) ($remaining * 100);
        $vnp_Returnurl = "https://itcenter.jo3.org/index.php?module=parent&action=vnpayReturn";

        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            "vnp_Locale" => "vn",
            "vnp_OrderInfo" => "Thanh toan enrollment #" . $id,
            "vnp_OrderType" => "billpayment",
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef
        ];

        ksort($inputData);

        $hashData = http_build_query($inputData, '', '&');
        $query = http_build_query($inputData, '', '&');
        $secureHash = hash_hmac("sha512", $hashData, $vnp_HashSecret);

        header("Location: " . $vnp_Url . "?" . $query . "&vnp_SecureHash=" . $secureHash);
        exit;
    }

    public function vnpayReturn()
    {
        $this->role(['parent', 'student']);

        require ROOT_PATH . "/modules/enrollment/vnpay_php/config.php";

        $vnp_SecureHash = $_GET['vnp_SecureHash'] ?? '';
        $inputData = [];

        foreach ($_GET as $key => $value) {
            if (substr($key, 0, 4) === "vnp_" && $key !== "vnp_SecureHash" && $key !== "vnp_SecureHashType") {
                $inputData[$key] = $value;
            }
        }

        ksort($inputData);
        $secureHash = hash_hmac("sha512", http_build_query($inputData, '', '&'), $vnp_HashSecret);

        if ($secureHash === $vnp_SecureHash) {
            $txn = explode("_", $_GET['vnp_TxnRef'] ?? '');
            $enrollmentId = (int) ($txn[0] ?? 0);
            $enrollment = $this->parentModel->findEnrollmentForCurrentParent($enrollmentId);

            if ($enrollment && ($_GET['vnp_ResponseCode'] ?? '') === "00") {
                (new EnrollmentModel())->paymentSuccess($enrollmentId, $_GET['vnp_TransactionNo'] ?? '');
                $_SESSION['success'] = 'Thanh toán VNPay thành công';
                header("Location: ?module=parent&action=dashboard&student_id=" . $enrollment['student_id']);
                exit;
            }
        }

        $_SESSION['error'] = 'Thanh toán VNPay thất bại hoặc chữ ký không hợp lệ';
        header("Location: ?module=parent&action=packages");
        exit;
    }

    public function getInitials($name)
    {
        $name = trim($name);

        if (empty($name)) {
            return "??";
        }

        $words = explode(" ", $name);
        $initials = "";

        if (count($words) >= 2) {
            $initials = mb_substr($words[0], 0, 1) . mb_substr(end($words), 0, 1);
        } else {
            $initials = mb_substr($words[0], 0, 1);
        }

        return mb_strtoupper($initials);
    }
}
