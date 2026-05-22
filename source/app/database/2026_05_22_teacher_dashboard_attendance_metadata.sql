-- Reuse current teacher_attendance, allowances_penalties and notifications tables.

ALTER TABLE teacher_attendance
    MODIFY method VARCHAR(20) NULL,
    ADD COLUMN confidence_score DECIMAL(6,4) NULL AFTER method,
    ADD COLUMN note TEXT NULL AFTER face_image;

UPDATE teacher_attendance
SET method = 'FACE'
WHERE method = '1';

ALTER TABLE allowances_penalties
    ADD COLUMN attendance_id INT NULL AFTER session_id,
    ADD KEY idx_allowances_attendance (attendance_id);
