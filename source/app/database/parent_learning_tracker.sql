-- Add lesson content for parent learning tracker.
-- session_reviews.review_text already exists in the current MVC codebase.

ALTER TABLE sessions
    ADD COLUMN lesson_content TEXT NULL AFTER note;

-- Only run this if your database does not already have session_reviews.review_text.
-- ALTER TABLE session_reviews
--     ADD COLUMN review_text TEXT NOT NULL AFTER student_id;
