TYPE=TRIGGERS
triggers='CREATE DEFINER=`root`@`localhost` TRIGGER before_insert_class\nBEFORE INSERT ON classes\nFOR EACH ROW\nBEGIN\n    DECLARE prefix VARCHAR(10);\n    DECLARE max_code VARCHAR(20);\n    DECLARE next_number INT;\n\n    -- lấy prefix từ courses\n    SELECT code INTO prefix\n    FROM courses\n    WHERE course_id = NEW.course_id;\n\n    -- lấy mã lớn nhất hiện tại\n    SELECT MAX(class_code) INTO max_code\n    FROM classes\n    WHERE course_id = NEW.course_id;\n\n    -- tính số tiếp theo\n    IF max_code IS NULL THEN\n        SET next_number = 1;\n    ELSE\n        SET next_number = CAST(SUBSTRING(max_code, -3) AS UNSIGNED) + 1;\n    END IF;\n\n    -- gán class_code\n    SET NEW.class_code = CONCAT(prefix, \'-\', LPAD(next_number, 3, \'0\'));\n\nEND'
sql_modes=1098907648
definers='root@localhost'
client_cs_names='utf8mb4'
connection_cl_names='utf8mb4_unicode_ci'
db_cl_names='utf8mb4_general_ci'
created=1776685108287522
