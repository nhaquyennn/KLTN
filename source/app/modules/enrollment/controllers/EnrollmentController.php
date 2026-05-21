<?php
class EnrollmentController extends Controller
{
    public function index()
    {
        $model = new EnrollmentModel();
        $studentModel = new StudentModel();
        $classModel = new ClassModel();

        // FILTER
        $filters = [
            'keyword' => $_GET['keyword'] ?? '',
            'payment_filter' => $_GET['payment_filter'] ?? ''
        ];

        // PAGINATION
        $limit = 5;
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $offset = ($page - 1) * $limit;

        // DATA
        $enrollments = $model->getAll($filters, $limit, $offset);
        $total = $model->countAll($filters);
        $totalPages = ceil($total / $limit);

        $students = $studentModel->getAll([], 1000, 0);
        $classes = $classModel->getAll([], 1000, 0);
        $classEnrollmentCounts = [];

        foreach ($classes as $class) {
            $classEnrollmentCounts[$class['class_id']] = $model->getClassEnrollmentCount($class['class_id']);
        }

        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";
        $view = ROOT_PATH . "/modules/enrollment/views/index.php";

        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    public function store()
    {
        try {
            $model = new EnrollmentModel();
            $model->create($_POST);
            $_SESSION['success'] = "Ghi danh học viên thành công";

        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        header("Location: ?module=enrollment");
    }

    public function changeStatus()
    {
        $id = $_GET['id'];
        $status = $_GET['status'];

        $model = new EnrollmentModel();
        $model->updateStatus($id, $status);

        header("Location: ?module=enrollment");
    }

    public function getAvailableStudents()
    {
        $class_id = $_GET['class_id'];

        $enrollmentModel = new EnrollmentModel();

        if ($enrollmentModel->isClassFull($class_id)) {
            $capacity = $enrollmentModel->getClassCapacity($class_id);
            echo json_encode([
                'success' => false,
                'full' => true,
                'message' => "Lớp đã đủ " . $capacity . " học viên. Vui lòng mở thêm lớp mới.",
                'students' => []
            ]);
            exit;
        }

        $model = new StudentModel();
        $data = $model->getAvailableByClass($class_id);

        echo json_encode([
            'success' => true,
            'full' => false,
            'students' => $data
        ]);
    }

    public function pay()
    {
        $id = $_GET['id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $amount = (float) ($_POST['amount'] ?? 0);

            $model = new EnrollmentModel();

            $enrollment = $model->findById($id);

            if (!$enrollment) {
                $_SESSION['error'] = "Không tìm thấy đăng ký";
                header("Location: ?module=enrollment");
                exit;
            }

            $remaining = $enrollment['final_fee'] - $enrollment['paid_amount'];

            // Không cho thanh toán vượt số còn thiếu
            if ($amount <= 0) {
                $_SESSION['error'] = "Số tiền không hợp lệ";
                header("Location: ?module=enrollment&action=pay&id=" . $id);
                exit;
            }

            if ($amount > $remaining) {
                $_SESSION['error'] = "Số tiền vượt quá số còn thiếu";
                header("Location: ?module=enrollment&action=pay&id=" . $id);
                exit;
            }

            $model->pay($id, $amount);

            $_SESSION['success'] = "Thanh toán thành công";

            header("Location: ?module=enrollment");
            exit;
        }

        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";
        $view = ROOT_PATH . "/modules/enrollment/views/pay.php";
        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    public function payAjax()
    {
        $id = $_POST['enrollment_id'] ?? 0;
        $amount = (float) ($_POST['amount'] ?? 0);

        $model = new EnrollmentModel();

        $enrollment = $model->findById($id);

        if (!$enrollment) {
            echo json_encode([
                'success' => false,
                'message' => 'Không tìm thấy đăng ký'
            ]);
            exit;
        }

        $remaining = $enrollment['final_fee'] - $enrollment['paid_amount'];

        if ($amount <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Số tiền không hợp lệ'
            ]);
            exit;
        }

        // Chặn thanh toán dư
        if ($amount > $remaining) {
            echo json_encode([
                'success' => false,
                'message' => 'Số tiền vượt quá số còn thiếu'
            ]);
            exit;
        }

        $model->pay($id, $amount);

        echo json_encode([
            'success' => true
        ]);
        exit;
    }

    public function payment()
    {
        $id = $_GET['id'];

        $model = new EnrollmentModel();
        $enrollment = $model->findById($id);

        if (!$enrollment) {
            die("Enrollment not found");
        }

        require ROOT_PATH . "/modules/enrollment/vnpay_php/config.php";

        $vnp_TxnRef = $id . "_" . time();

        // CHỈ THANH TOÁN PHẦN CÒN THIẾU
        $remaining = (float) $enrollment['final_fee'] - (float) $enrollment['paid_amount'];

        if ($remaining <= 0) {
            $_SESSION['error'] = "Đơn này đã thanh toán đủ";
            header("Location: ?module=enrollment");
            exit;
        }

        $vnp_Amount = (int) ($remaining * 100);

        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $_SERVER['REMOTE_ADDR'],
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

        $paymentUrl = $vnp_Url . "?" . $query . "&vnp_SecureHash=" . $secureHash;

        header("Location: " . $paymentUrl);
        exit;
    }

    public function vnpayReturn()
    {
        require ROOT_PATH . "/modules/enrollment/vnpay_php/config.php";
        $vnp_SecureHash = $_GET['vnp_SecureHash'] ?? '';
        $inputData = [];
        foreach ($_GET as $key => $value) {
            if (
                substr($key, 0, 4) === "vnp_" &&
                $key !== "vnp_SecureHash" &&
                $key !== "vnp_SecureHashType"
            ) {
                $inputData[$key] = $value;
            }
        }
        ksort($inputData);
        $hashData = http_build_query($inputData, '', '&');

        $secureHash = hash_hmac(
            "sha512",
            $hashData,
            $vnp_HashSecret
        );
        $success = false;
        $message = '';
        // =============================
        // VERIFY SIGNATURE
        // =============================
        if ($secureHash === $vnp_SecureHash) {
            $txn = explode("_", $_GET['vnp_TxnRef']);
            $enrollment_id = $txn[0];
            // =============================
            // PAYMENT SUCCESS
            // =============================
            if ($_GET['vnp_ResponseCode'] === "00") {
                $model = new EnrollmentModel();
                $model->paymentSuccess(
                    $enrollment_id,
                    $_GET['vnp_TransactionNo']
                );
                $success = true;
                $message = "Thanh toán VNPay thành công";
            } else {
                $message = "Thanh toán thất bại";
            }
        } else {
            $message = "Sai chữ ký VNPay";
        }
        ?>

            <!DOCTYPE html>
            <html lang="vi">

            <head>

                <meta charset="UTF-8">

                <title>VNPay Result</title>

                <link
                    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
                    rel="stylesheet">

                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

            </head>

            <body>

                <script>

                    Swal.fire({

                        icon: '<?= $success ? "success" : "error" ?>',

                        title: '<?= $success ? "Thành công" : "Thất bại" ?>',

                        text: '<?= $message ?>',

                        confirmButtonText: 'OK',

                        allowOutsideClick: false

                    }).then(() => {

                        window.location.href = '?module=enrollment';
                    });

                </script>

            </body>

            </html>

            <?php
            exit;
    }

}
