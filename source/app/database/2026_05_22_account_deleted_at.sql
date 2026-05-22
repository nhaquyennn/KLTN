ALTER TABLE users
    ADD COLUMN deleted_at DATETIME NULL AFTER status;

-- Legacy account deletion locked the user and cleared password.
UPDATE users
SET deleted_at = NOW()
WHERE deleted_at IS NULL
AND status = 0
AND (password IS NULL OR password = '');
