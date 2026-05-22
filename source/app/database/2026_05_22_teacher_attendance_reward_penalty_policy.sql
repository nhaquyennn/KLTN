-- Reuse the existing allowances_penalties table as teacher reward/penalty history.
-- Run once on merge_q before deploying the attendance policy code.

ALTER TABLE allowances_penalties
    MODIFY type ENUM('reward', 'bonus', 'penalty') NOT NULL,
    ADD COLUMN session_id INT NULL AFTER teacher_id,
    ADD COLUMN status ENUM('active', 'canceled') NOT NULL DEFAULT 'active' AFTER reason,
    ADD COLUMN canceled_reason TEXT NULL AFTER status,
    ADD COLUMN canceled_by INT NULL AFTER created_by,
    ADD COLUMN updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at,
    ADD COLUMN canceled_at DATETIME NULL AFTER updated_at,
    ADD KEY idx_allowances_session (session_id),
    ADD KEY idx_allowances_status_type (status, type),
    ADD KEY idx_allowances_canceled_by (canceled_by),
    ADD UNIQUE KEY uq_auto_teacher_session_reason (teacher_id, session_id, type, reason(191)),
    ADD CONSTRAINT fk_allowances_session
        FOREIGN KEY (session_id) REFERENCES sessions(session_id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_allowances_canceled_by
        FOREIGN KEY (canceled_by) REFERENCES users(user_id) ON DELETE SET NULL;

ALTER TABLE teacher_attendance
    MODIFY status ENUM('present', 'absent', 'late', 'late_absent') DEFAULT 'present',
    ADD UNIQUE KEY uq_teacher_attendance_teacher_session (teacher_id, session_id);
