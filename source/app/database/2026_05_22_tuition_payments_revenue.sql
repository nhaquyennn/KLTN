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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Legacy enrollments only stored cumulative paid_amount. Backfill one opening row
-- so revenue reports can include old tuition receipts after this migration.
INSERT IGNORE INTO tuition_payments
    (enrollment_id, amount, payment_method, transaction_code, paid_at, created_at)
SELECT
    e.enrollment_id,
    e.paid_amount,
    UPPER(COALESCE(NULLIF(e.payment_method, ''), 'LEGACY')),
    NULLIF(e.transaction_code, ''),
    COALESCE(e.paid_at, e.created_at, CONCAT(e.enroll_date, ' 00:00:00')),
    COALESCE(e.paid_at, e.created_at, CONCAT(e.enroll_date, ' 00:00:00'))
FROM enrollments e
WHERE COALESCE(e.paid_amount, 0) > 0
AND NOT EXISTS (
    SELECT 1
    FROM tuition_payments tp
    WHERE tp.enrollment_id = e.enrollment_id
);
