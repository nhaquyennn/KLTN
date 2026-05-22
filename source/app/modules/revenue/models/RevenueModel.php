<?php

class RevenueModel
{
    private $db;

    public function __construct($db = null)
    {
        $this->db = $db ?: (new Database())->connect();
        $this->ensureTuitionPaymentsTable();
    }

    public function getSummary($fromDate, $toDate)
    {
        $range = $this->fetchRangeSummary($fromDate, $toDate);

        return [
            'range_revenue' => (float) ($range['range_revenue'] ?? 0),
            'payment_count' => (int) ($range['payment_count'] ?? 0),
            'paid_enrollments' => (int) ($range['paid_enrollments'] ?? 0),
            'paid_students' => (int) ($range['paid_students'] ?? 0),
            'today_revenue' => (float) $this->fetchColumn("
                SELECT COALESCE(SUM(amount), 0)
                FROM tuition_payments
                WHERE DATE(paid_at) = CURDATE()
            "),
            'month_revenue' => (float) $this->fetchColumn("
                SELECT COALESCE(SUM(amount), 0)
                FROM tuition_payments
                WHERE paid_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
                AND paid_at < DATE_ADD(LAST_DAY(CURDATE()), INTERVAL 1 DAY)
            "),
            'all_time_revenue' => (float) $this->fetchColumn("
                SELECT COALESCE(SUM(amount), 0)
                FROM tuition_payments
            "),
            'tuition_debt' => (float) $this->fetchColumn("
                SELECT COALESCE(SUM(GREATEST(final_fee - paid_amount, 0)), 0)
                FROM enrollments
                WHERE status <> 'dropped'
            "),
            'legacy_untracked' => (float) $this->fetchColumn("
                SELECT COALESCE(SUM(e.paid_amount), 0)
                FROM enrollments e
                WHERE COALESCE(e.paid_amount, 0) > 0
                AND NOT EXISTS (
                    SELECT 1
                    FROM tuition_payments tp
                    WHERE tp.enrollment_id = e.enrollment_id
                )
            "),
        ];
    }

    public function getDailyRevenue($fromDate, $toDate)
    {
        $stmt = $this->db->prepare("
            SELECT DATE(paid_at) AS revenue_date, COALESCE(SUM(amount), 0) AS total_amount
            FROM tuition_payments
            WHERE paid_at BETWEEN :from_time AND :to_time
            GROUP BY DATE(paid_at)
            ORDER BY revenue_date ASC
        ");
        $stmt->execute($this->rangeParams($fromDate, $toDate));

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPaymentMethodTotals($fromDate, $toDate)
    {
        $stmt = $this->db->prepare("
            SELECT payment_method, COUNT(*) AS payment_count, COALESCE(SUM(amount), 0) AS total_amount
            FROM tuition_payments
            WHERE paid_at BETWEEN :from_time AND :to_time
            GROUP BY payment_method
            ORDER BY total_amount DESC, payment_method ASC
        ");
        $stmt->execute($this->rangeParams($fromDate, $toDate));

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecentPayments($fromDate, $toDate, $limit = 20)
    {
        $stmt = $this->db->prepare("
            SELECT
                tp.payment_id,
                tp.amount,
                tp.payment_method,
                tp.transaction_code,
                tp.paid_at,
                e.enrollment_id,
                u.name AS student_name,
                c.class_code,
                co.name AS course_name,
                p.name AS package_name
            FROM tuition_payments tp
            JOIN enrollments e ON e.enrollment_id = tp.enrollment_id
            JOIN students st ON st.student_id = e.student_id
            JOIN users u ON u.user_id = st.user_id
            JOIN classes c ON c.class_id = e.class_id
            LEFT JOIN courses co ON co.course_id = c.course_id
            LEFT JOIN packages p ON p.package_id = c.package_id
            WHERE tp.paid_at BETWEEN :from_time AND :to_time
            ORDER BY tp.paid_at DESC, tp.payment_id DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':from_time', $fromDate . ' 00:00:00');
        $stmt->bindValue(':to_time', $toDate . ' 23:59:59');
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function fetchRangeSummary($fromDate, $toDate)
    {
        $stmt = $this->db->prepare("
            SELECT
                COALESCE(SUM(tp.amount), 0) AS range_revenue,
                COUNT(tp.payment_id) AS payment_count,
                COUNT(DISTINCT tp.enrollment_id) AS paid_enrollments,
                COUNT(DISTINCT e.student_id) AS paid_students
            FROM tuition_payments tp
            JOIN enrollments e ON e.enrollment_id = tp.enrollment_id
            WHERE tp.paid_at BETWEEN :from_time AND :to_time
        ");
        $stmt->execute($this->rangeParams($fromDate, $toDate));

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    private function rangeParams($fromDate, $toDate)
    {
        return [
            ':from_time' => $fromDate . ' 00:00:00',
            ':to_time' => $toDate . ' 23:59:59',
        ];
    }

    private function fetchColumn($sql)
    {
        return $this->db->query($sql)->fetchColumn();
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
}
