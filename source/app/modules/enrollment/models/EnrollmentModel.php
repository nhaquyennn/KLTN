<?php
class EnrollmentModel
{
    const MAX_STUDENTS_PER_CLASS = 10;

    private $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
        $this->ensureTuitionPaymentsTable();
    }

    public function getAll($filters, $limit, $offset)
    {
        $sql = "
            SELECT 
                e.*,
                u.name AS student_name,
                c.class_code,
                co.name AS course_name,
                p.name AS package_name,
                p.total_sessions

            FROM enrollments e
            JOIN students st ON e.student_id = st.student_id
            JOIN users u ON st.user_id = u.user_id
            JOIN classes c ON e.class_id = c.class_id
            JOIN courses co ON c.course_id = co.course_id
            JOIN packages p ON c.package_id = p.package_id

            WHERE 1
        ";

        if (!empty($filters['keyword'])) {
            $sql .= " AND u.name LIKE :keyword";
        }

        if (!empty($filters['payment_filter'])) {
            if ($filters['payment_filter'] === 'not_full') {
                $sql .= " AND (e.payment_status <> 'paid' OR COALESCE(e.paid_amount, 0) < COALESCE(e.final_fee, 0))";
            } elseif (in_array($filters['payment_filter'], ['unpaid', 'partial', 'paid'], true)) {
                $sql .= " AND e.payment_status = :payment_filter";
            }
        }

        $sql .= " ORDER BY e.enrollment_id DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        if (!empty($filters['keyword'])) {
            $stmt->bindValue(':keyword', '%' . $filters['keyword'] . '%');
        }

        if (!empty($filters['payment_filter']) && in_array($filters['payment_filter'], ['unpaid', 'partial', 'paid'], true)) {
            $stmt->bindValue(':payment_filter', $filters['payment_filter']);
        }

        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function countAll($filters)
    {
        $sql = "
            SELECT COUNT(*) 
            FROM enrollments e
            JOIN students st ON e.student_id = st.student_id
            JOIN users u ON st.user_id = u.user_id
            WHERE 1
        ";

        if (!empty($filters['keyword'])) {
            $sql .= " AND u.name LIKE :keyword";
        }

        if (!empty($filters['payment_filter'])) {
            if ($filters['payment_filter'] === 'not_full') {
                $sql .= " AND (e.payment_status <> 'paid' OR COALESCE(e.paid_amount, 0) < COALESCE(e.final_fee, 0))";
            } elseif (in_array($filters['payment_filter'], ['unpaid', 'partial', 'paid'], true)) {
                $sql .= " AND e.payment_status = :payment_filter";
            }
        }

        $stmt = $this->db->prepare($sql);

        if (!empty($filters['keyword'])) {
            $stmt->bindValue(':keyword', '%' . $filters['keyword'] . '%');
        }

        if (!empty($filters['payment_filter']) && in_array($filters['payment_filter'], ['unpaid', 'partial', 'paid'], true)) {
            $stmt->bindValue(':payment_filter', $filters['payment_filter']);
        }

        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function create($data)
    {
        if ($this->isStudentEnrolledInClass($data['student_id'], $data['class_id'])) {
            throw new Exception("Học viên đã được ghi danh vào lớp này");
        }

        if ($this->isClassFull($data['class_id'])) {
            $capacity = $this->getClassCapacity($data['class_id']);
            throw new Exception("Lớp đã đủ " . $capacity . " học viên. Vui lòng mở thêm lớp mới.");
        }

        // 1. Lấy package từ class
        $stmt = $this->db->prepare("
            SELECT p.price, p.total_sessions
            FROM classes c
            JOIN packages p ON c.package_id = p.package_id
            WHERE c.class_id = ?
        ");
        $stmt->execute([$data['class_id']]);
        $pkg = $stmt->fetch(PDO::FETCH_ASSOC);

        $total_fee = $pkg['price'];
        $discount = $data['discount_percent'] ?? 0;

        $final_fee = $total_fee - ($total_fee * $discount / 100);

        // 2. Insert
        $stmt = $this->db->prepare("
            INSERT INTO enrollments
            (student_id, class_id, enroll_date, status,
            total_fee, discount_percent, final_fee,
            paid_amount, payment_status,
            attended_sessions, remaining_sessions)
            VALUES (?, ?, CURDATE(), 'studying',
                    ?, ?, ?, 
                    0, 'unpaid',
                    0, ?)
        ");

        $stmt->execute([
            $data['student_id'],
            $data['class_id'],
            $total_fee,
            $discount,
            $final_fee,
            $pkg['total_sessions']
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function getClassEnrollmentCount($class_id)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM enrollments
            WHERE class_id = ?
            AND status <> 'dropped'
        ");

        $stmt->execute([$class_id]);
        return (int) $stmt->fetchColumn();
    }

    public function isClassFull($class_id)
    {
        return $this->getClassEnrollmentCount($class_id) >= $this->getClassCapacity($class_id);
    }

    public function getClassCapacity($class_id)
    {
        $stmt = $this->db->prepare("
            SELECT COALESCE(max_students, ?) AS max_students
            FROM classes
            WHERE class_id = ?
        ");
        $stmt->execute([self::MAX_STUDENTS_PER_CLASS, $class_id]);
        return max(1, (int) $stmt->fetchColumn());
    }

    private function isStudentEnrolledInClass($student_id, $class_id)
    {
        $stmt = $this->db->prepare("
            SELECT 1
            FROM enrollments
            WHERE student_id = ?
            AND class_id = ?
            LIMIT 1
        ");

        $stmt->execute([$student_id, $class_id]);
        return (bool) $stmt->fetchColumn();
    }

    public function updateStatus($id, $status)
    {
        $stmt = $this->db->prepare("
            UPDATE enrollments 
            SET status = ?
            WHERE enrollment_id = ?
        ");

        return $stmt->execute([$status, $id]);
    }

    public function pay($id, $amount, $paymentMethod = 'CASH')
    {
        // Lấy dữ liệu hiện tại
        $stmt = $this->db->prepare("
        SELECT paid_amount, final_fee
        FROM enrollments
        WHERE enrollment_id = ?
    ");

        $stmt->execute([$id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return false;
        }

        $paidAmount = (float) $row['paid_amount'];
        $finalFee = (float) $row['final_fee'];

        // Số còn thiếu
        $remaining = $finalFee - $paidAmount;

        // Không cho thanh toán <= 0
        if ($amount <= 0) {
            return false;
        }

        // Chống thanh toán dư
        if ($amount > $remaining) {
            $amount = $remaining;
        }

        $newPaid = $paidAmount + $amount;

        // Xác định trạng thái
        if ($newPaid >= $finalFee) {
            $paymentStatus = 'paid';
        } elseif ($newPaid > 0) {
            $paymentStatus = 'partial';
        } else {
            $paymentStatus = 'unpaid';
        }

        $this->db->beginTransaction();

        try {
            // Keep the enrollment aggregate for old screens and add a ledger row for reports.
            $stmt = $this->db->prepare("
                UPDATE enrollments
                SET
                    paid_amount = ?,
                    payment_status = ?,
                    payment_method = ?,
                    paid_at = NOW()
                WHERE enrollment_id = ?
            ");

            $updated = $stmt->execute([
                $newPaid,
                $paymentStatus,
                $paymentMethod,
                $id
            ]);

            if (!$updated) {
                $this->db->rollBack();
                return false;
            }

            $this->recordTuitionPayment($id, $amount, $paymentMethod);
            $this->db->commit();

            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    public function updateStatusIfCompleted($enrollment_id)
    {
        $stmt = $this->db->prepare("
        SELECT attended_sessions, total_sessions, status
        FROM enrollments
        WHERE enrollment_id = ?
    ");

        $stmt->execute([$enrollment_id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row)
            return;

        // Nếu đã completed thì bỏ qua
        if ($row['status'] == 'completed')
            return;

        // Học đủ buổi => completed
        if ($row['attended_sessions'] >= $row['total_sessions']) {

            $this->db->prepare("
            UPDATE enrollments
            SET status = 'completed'
            WHERE enrollment_id = ?
        ")->execute([$enrollment_id]);
        }
    }

    public function getAvailableStudents($class_id)
    {
        if ($this->isClassFull($class_id)) {
            return [];
        }

        $stmt = $this->db->prepare("
        SELECT
            st.student_id,
            u.name AS student_name
        FROM students st
        JOIN users u ON st.user_id = u.user_id

        WHERE st.status = 1

        AND NOT EXISTS (
            SELECT 1
            FROM enrollments e
            WHERE e.student_id = st.student_id
            AND e.class_id = ?
        )
    ");

        $stmt->execute([$class_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function paymentSuccess($enrollment_id, $transaction_code)
    {
        if ($this->hasTuitionPaymentTransaction($transaction_code)) {
            return true;
        }

        // Lấy dữ liệu enrollment hiện tại
        $stmt = $this->db->prepare("
        SELECT paid_amount, final_fee
        FROM enrollments
        WHERE enrollment_id = ?
    ");

        $stmt->execute([$enrollment_id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return false;
        }

        $paidAmount = (float) $row['paid_amount'];
        $finalFee = (float) $row['final_fee'];

        // Số tiền VNPay trả về
        $vnpAmount = ($_GET['vnp_Amount'] ?? 0) / 100;

        // Số còn thiếu
        $remaining = $finalFee - $paidAmount;

        // Chống thanh toán dư
        if ($vnpAmount > $remaining) {
            $vnpAmount = $remaining;
        }

        $newPaid = $paidAmount + $vnpAmount;

        // Trạng thái thanh toán
        if ($newPaid >= $finalFee) {
            $paymentStatus = 'paid';
        } elseif ($newPaid > 0) {
            $paymentStatus = 'partial';
        } else {
            $paymentStatus = 'unpaid';
        }

        if ($vnpAmount <= 0) {
            return true;
        }

        $this->db->beginTransaction();

        try {
            $sql = "
                UPDATE enrollments
                SET
                    payment_status = :payment_status,
                    transaction_code = :transaction_code,
                    payment_method = 'VNPay',
                    paid_at = NOW(),
                    paid_amount = :paid_amount
                WHERE enrollment_id = :id
            ";

            $stmt = $this->db->prepare($sql);
            $updated = $stmt->execute([
                ':payment_status' => $paymentStatus,
                ':transaction_code' => $transaction_code,
                ':paid_amount' => $newPaid,
                ':id' => $enrollment_id
            ]);

            if (!$updated) {
                $this->db->rollBack();
                return false;
            }

            $this->recordTuitionPayment($enrollment_id, $vnpAmount, 'VNPAY', $transaction_code);
            $this->db->commit();

            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("
        SELECT *
        FROM enrollments
        WHERE enrollment_id = ?
        LIMIT 1
    ");

        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function ensureTuitionPaymentsTable()
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS tuition_payments (
                payment_id INT AUTO_INCREMENT PRIMARY KEY,
                enrollment_id INT NOT NULL,
                amount DECIMAL(12,2) NOT NULL,
                payment_method VARCHAR(30) NOT NULL DEFAULT 'CASH',
                transaction_code VARCHAR(100) NULL,
                paid_at DATETIME NOT NULL,
                created_by INT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                KEY idx_tuition_payments_enrollment (enrollment_id),
                KEY idx_tuition_payments_paid_at (paid_at),
                UNIQUE KEY uq_tuition_payments_transaction_code (transaction_code)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");
    }

    private function recordTuitionPayment($enrollmentId, $amount, $paymentMethod, $transactionCode = null)
    {
        $stmt = $this->db->prepare("
            INSERT INTO tuition_payments
                (enrollment_id, amount, payment_method, transaction_code, paid_at, created_by)
            VALUES
                (:enrollment_id, :amount, :payment_method, :transaction_code, NOW(), :created_by)
        ");

        $stmt->execute([
            ':enrollment_id' => (int) $enrollmentId,
            ':amount' => (float) $amount,
            ':payment_method' => strtoupper((string) $paymentMethod),
            ':transaction_code' => $transactionCode ?: null,
            ':created_by' => $_SESSION['user']['user_id'] ?? null,
        ]);
    }

    private function hasTuitionPaymentTransaction($transactionCode)
    {
        if (!$transactionCode) {
            return false;
        }

        $stmt = $this->db->prepare("
            SELECT 1
            FROM tuition_payments
            WHERE transaction_code = ?
            LIMIT 1
        ");
        $stmt->execute([$transactionCode]);

        return (bool) $stmt->fetchColumn();
    }
}
