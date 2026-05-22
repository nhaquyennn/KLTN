<?php
class SessionModel
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
        $this->ensureReviewTable();
        $this->ensureTeacherSpecializationsTable();
    }

    public function getByClass($class_id, $teacher_id = null)
    {
        $sql = "
            SELECT 
                s.*,
                sh.name AS shift_name,
                sh.start_time,
                sh.end_time,
                r.name AS room_name,

                COUNT(DISTINCT a.attendance_id) AS attendance_count,

                GROUP_CONCAT(DISTINCT 
                    CASE WHEN st.role = 'main' THEN u.name END
                ) AS teacher_main,

                GROUP_CONCAT(DISTINCT 
                    CASE WHEN st.role = 'assistant' THEN u.name END
                ) AS teacher_assistant,

                COUNT(DISTINCT e.student_id) AS student_count,
                COUNT(DISTINCT sr.student_id) AS reviewed_student_count

            FROM sessions s
            LEFT JOIN enrollments e ON e.class_id = s.class_id AND e.status <> 'dropped'
            LEFT JOIN shifts sh ON s.shift_id = sh.shift_id
            LEFT JOIN rooms r ON s.room_id = r.room_id
            LEFT JOIN attendances a ON a.session_id = s.session_id
            LEFT JOIN session_teachers st ON st.session_id = s.session_id
            LEFT JOIN teachers t ON st.teacher_id = t.teacher_id
            LEFT JOIN users u ON t.user_id = u.user_id
            LEFT JOIN session_reviews sr ON sr.session_id = s.session_id AND sr.student_id = e.student_id
            WHERE s.class_id = ?
        ";

        $params = [$class_id];

        if (!empty($teacher_id)) {
            $sql .= "
                AND EXISTS (
                    SELECT 1
                    FROM session_teachers teacher_filter
                    WHERE teacher_filter.session_id = s.session_id
                    AND teacher_filter.teacher_id = ?
                )
            ";
            $params[] = (int) $teacher_id;
        }

        $sql .= "
            GROUP BY s.session_id
            ORDER BY s.session_date ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteSessions($class_id)
    {
        $this->db->prepare("DELETE FROM sessions WHERE class_id = ?")
            ->execute([$class_id]);
    }

    public function create($data)
    {
        $status = 'scheduled';
        $note = null;

        if (!empty($data['room_id']) && $this->isRoomBusyByDateShift($data['room_id'], $data['session_date'], $data['shift_id'])) {
            $status = 'conflict';
            $note = 'Room conflict';
        }

        $stmt = $this->db->prepare("
            INSERT INTO sessions (class_id, session_date, shift_id, room_id, status, note)
            VALUES (:class_id, :session_date, :shift_id, :room_id, :status, :note)
        ");

        return $stmt->execute([
            'class_id' => $data['class_id'],
            'session_date' => $data['session_date'],
            'shift_id' => $data['shift_id'],
            'room_id' => $data['room_id'] ?: null,
            'status' => $status,
            'note' => $note
        ]);
    }

    public function generateSessionsCustom($class_id, $start_date, $total_sessions)
    {
        $stmt = $this->db->prepare("SELECT * FROM classes WHERE class_id = ?");
        $stmt->execute([$class_id]);
        $class = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $this->db->prepare("
            SELECT day_of_week FROM schedule_days WHERE schedule_id = ?
        ");
        $stmt->execute([$class['schedule_id']]);
        $days = array_column($stmt->fetchAll(), 'day_of_week');

        sort($days);

        $date = new DateTime($start_date);
        $count = 0;

        while ($count < $total_sessions) {

            $phpDay = $date->format('N');
            $dbDay = ($phpDay == 7) ? 1 : $phpDay + 1;

            if (in_array($dbDay, $days)) {

                $shift_id = $class['shift_id'];

                $stmt = $this->db->prepare("
                    SELECT COUNT(*) FROM sessions 
                    WHERE session_date = ? AND shift_id = ?
                ");
                $stmt->execute([$date->format('Y-m-d'), $shift_id]);

                $isConflict = $stmt->fetchColumn() > 0 ? 1 : 0;

                $this->db->prepare("
                    INSERT INTO sessions 
                    (class_id, session_date, shift_id, status, note)
                    VALUES (?, ?, ?, 'scheduled', ?)
                ")->execute([
                            $class_id,
                            $date->format('Y-m-d'),
                            $shift_id,
                            $isConflict ? 'conflict' : null
                        ]);

                $count++;
            }

            $date->modify('+1 day');
        }
    }

    public function updateRoom($session_id, $room_id)
    {
        $this->db->prepare("UPDATE sessions SET room_id = ? WHERE session_id = ?")
            ->execute([$room_id, $session_id]);
    }

    public function assignRoomToSessions($sessionIds, $roomId)
    {
        $sessionIds = $this->normalizeIds($sessionIds);
        $roomId = (int) $roomId;
        $updated = 0;
        $skipped = 0;

        if (empty($sessionIds) || !$roomId) {
            return ['updated' => 0, 'skipped' => count($sessionIds)];
        }

        $stmt = $this->db->prepare("
            UPDATE sessions
            SET room_id = ?
            WHERE session_id = ?
        ");

        foreach ($sessionIds as $sessionId) {
            if (!$this->isSessionAssignable($sessionId) || $this->isRoomBusy($roomId, $sessionId)) {
                $skipped++;
                continue;
            }

            $stmt->execute([$roomId, $sessionId]);
            $updated++;
        }

        return ['updated' => $updated, 'skipped' => $skipped];
    }

    public function updateShift($session_id, $shift_id)
    {
        $this->db->prepare("
            UPDATE sessions 
            SET shift_id = ?, status = 'scheduled', note = NULL
            WHERE session_id = ?
        ")->execute([$shift_id, $session_id]);
    }

    public function assignShiftToSessions($sessionIds, $shiftId)
    {
        $sessionIds = $this->normalizeIds($sessionIds);
        $shiftId = (int) $shiftId;
        $updated = 0;
        $skipped = 0;

        if (empty($sessionIds) || !$shiftId) {
            return ['updated' => 0, 'skipped' => count($sessionIds)];
        }

        foreach ($sessionIds as $sessionId) {
            if (
                !$this->isSessionAssignable($sessionId)
                || $this->willShiftConflictWithRoom($sessionId, $shiftId)
                || $this->willShiftConflictWithAssignedTeachers($sessionId, $shiftId)
            ) {
                $skipped++;
                continue;
            }

            $this->updateShift($sessionId, $shiftId);
            $updated++;
        }

        return ['updated' => $updated, 'skipped' => $skipped];
    }

    public function updateStatus($session_id, $status)
    {
        $this->db->prepare("UPDATE sessions SET status = ? WHERE session_id = ?")
            ->execute([$status, $session_id]);
    }

    public function takeAttendance($session_id)
    {
        $stmt = $this->db->prepare("SELECT class_id FROM sessions WHERE session_id = ?");
        $stmt->execute([$session_id]);
        $class_id = $stmt->fetchColumn();

        $stmt = $this->db->prepare("SELECT student_id FROM enrollments WHERE class_id = ?");
        $stmt->execute([$class_id]);
        $students = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($students as $student_id) {
            $this->db->prepare("
                INSERT IGNORE INTO attendances (session_id, student_id, status)
                VALUES (?, ?, 'present')
            ")->execute([$session_id, $student_id]);
        }
    }

    public function saveTeachers($session_id, $main, $assistants)
    {
        // CHECK MAIN
        if ($main && $this->isTeacherBusy($main, $session_id)) {
            header("Location: " . $_SERVER['HTTP_REFERER'] . "&error=main_conflict");
            exit;
        }

        // CHECK ASSISTANT
        if ($assistants) {
            foreach ($assistants as $t) {
                if ($this->isTeacherBusy($t, $session_id)) {
                    header("Location: " . $_SERVER['HTTP_REFERER'] . "&error=assistant_conflict");
                    exit;
                }
            }
        }

        // XÓA CŨ
        $this->db->prepare("DELETE FROM session_teachers WHERE session_id = ?")
            ->execute([$session_id]);

        // MAIN
        if ($main) {
            $this->db->prepare("
                INSERT INTO session_teachers (session_id, teacher_id, role)
                VALUES (?, ?, 'main')
            ")->execute([$session_id, $main]);
        }

        // ASSISTANT
        if ($assistants) {
            $stmt = $this->db->prepare("
            INSERT INTO session_teachers (session_id, teacher_id, role)
            VALUES (?, ?, 'assistant')
        ");

            foreach ($assistants as $t) {
                if ($t == $main)
                    continue;
                $stmt->execute([$session_id, $t]);
            }
        }
    }

    public function isTeacherBusy($teacher_id, $session_id)
    {
        $stmt = $this->db->prepare("
            SELECT 1
            FROM sessions s
            JOIN session_teachers st ON s.session_id = st.session_id
            JOIN shifts sh ON s.shift_id = sh.shift_id

            WHERE st.teacher_id = ?
            AND s.session_date = (
                SELECT session_date FROM sessions WHERE session_id = ?
            )
            AND s.session_id != ?

            AND (
                sh.start_time < (
                    SELECT sh2.end_time 
                    FROM sessions s2
                    JOIN shifts sh2 ON s2.shift_id = sh2.shift_id
                    WHERE s2.session_id = ?
                )
                AND
                sh.end_time > (
                    SELECT sh2.start_time 
                    FROM sessions s2
                    JOIN shifts sh2 ON s2.shift_id = sh2.shift_id
                    WHERE s2.session_id = ?
                )
            )

            LIMIT 1
        ");

        $stmt->execute([
            $teacher_id,
            $session_id,
            $session_id,
            $session_id,
            $session_id
        ]);

        return $stmt->fetch() ? true : false;
    }

    public function getTeachersWithStatus($session_id)
    {
        $stmt = $this->db->prepare("
            SELECT 
                t.teacher_id,
                u.name,
                COALESCE(GROUP_CONCAT(DISTINCT sp_all.name ORDER BY sp_all.name SEPARATOR ', '), '') AS specialization_names,

                CASE 
                    WHEN EXISTS (
                        SELECT 1
                        FROM sessions s
                        JOIN session_teachers st ON s.session_id = st.session_id
                        JOIN shifts sh ON s.shift_id = sh.shift_id

                        WHERE st.teacher_id = t.teacher_id
                        AND s.session_date = (
                            SELECT session_date FROM sessions WHERE session_id = ?
                        )
                        AND s.session_id != ?

                        AND (
                            sh.start_time < (
                                SELECT sh2.end_time 
                                FROM sessions s2
                                JOIN shifts sh2 ON s2.shift_id = sh2.shift_id
                                WHERE s2.session_id = ?
                            )
                            AND
                            sh.end_time > (
                                SELECT sh2.start_time 
                                FROM sessions s2
                                JOIN shifts sh2 ON s2.shift_id = sh2.shift_id
                                WHERE s2.session_id = ?
                            )
                        )
                    )
                    THEN 1 ELSE 0
                END AS is_busy,

                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM sessions s_match
                        JOIN classes c_match ON c_match.class_id = s_match.class_id
                        JOIN courses co_match ON co_match.course_id = c_match.course_id
                        JOIN specializations sp_match ON sp_match.status = 'active'
                        LEFT JOIN teacher_specializations ts_match
                            ON ts_match.specialization_id = sp_match.specialization_id
                            AND ts_match.teacher_id = t.teacher_id
                        WHERE s_match.session_id = ?
                        AND (
                            co_match.name LIKE CONCAT('%', sp_match.name, '%')
                            OR sp_match.name LIKE CONCAT('%', co_match.name, '%')
                        )
                        AND (
                            ts_match.teacher_id IS NOT NULL
                            OR t.specialization_id = sp_match.specialization_id
                        )
                    )
                    THEN 1 ELSE 0
                END AS is_recommended

            FROM teachers t
            JOIN users u ON t.user_id = u.user_id
            LEFT JOIN teacher_specializations ts_all
                ON ts_all.teacher_id = t.teacher_id
            LEFT JOIN specializations sp_all
                ON sp_all.specialization_id = ts_all.specialization_id
            WHERE t.status = 1
            GROUP BY t.teacher_id
            ORDER BY is_recommended DESC, u.name ASC
        ");

        $stmt->execute([
            $session_id,
            $session_id,
            $session_id,
            $session_id,
            $session_id
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function isRoomBusy($room_id, $session_id)
    {
        $stmt = $this->db->prepare("
            SELECT 1
            FROM sessions s
            JOIN shifts sh ON s.shift_id = sh.shift_id

            WHERE s.room_id = ?
            AND s.session_date = (
                SELECT session_date FROM sessions WHERE session_id = ?
            )

            AND s.session_id != ?

            AND (
                sh.start_time < (
                    SELECT sh2.end_time 
                    FROM sessions s2
                    JOIN shifts sh2 ON s2.shift_id = sh2.shift_id
                    WHERE s2.session_id = ?
                )
                AND
                sh.end_time > (
                    SELECT sh2.start_time 
                    FROM sessions s2
                    JOIN shifts sh2 ON s2.shift_id = sh2.shift_id
                    WHERE s2.session_id = ?
                )
            )

            LIMIT 1
        ");

        $stmt->execute([
            $room_id,
            $session_id,
            $session_id,
            $session_id,
            $session_id
        ]);

        return $stmt->fetch() ? true : false;
    }

    public function isRoomBusyByDateShift($room_id, $session_date, $shift_id)
    {
        $stmt = $this->db->prepare("
            SELECT 1
            FROM sessions s
            JOIN shifts sh ON s.shift_id = sh.shift_id
            JOIN shifts target_shift ON target_shift.shift_id = ?
            WHERE s.room_id = ?
            AND s.session_date = ?
            AND s.status <> 'cancelled'
            AND sh.start_time < target_shift.end_time
            AND sh.end_time > target_shift.start_time
            LIMIT 1
        ");

        $stmt->execute([
            $shift_id,
            $room_id,
            $session_date
        ]);

        return $stmt->fetch() ? true : false;
    }

    private function willShiftConflictWithRoom($sessionId, $shiftId)
    {
        $stmt = $this->db->prepare("
            SELECT 1
            FROM sessions target
            JOIN sessions other
                ON other.room_id = target.room_id
                AND other.session_date = target.session_date
                AND other.session_id <> target.session_id
                AND other.status <> 'cancelled'
            JOIN shifts other_shift ON other_shift.shift_id = other.shift_id
            JOIN shifts target_shift ON target_shift.shift_id = ?
            WHERE target.session_id = ?
            AND target.room_id IS NOT NULL
            AND other_shift.start_time < target_shift.end_time
            AND other_shift.end_time > target_shift.start_time
            LIMIT 1
        ");
        $stmt->execute([(int) $shiftId, (int) $sessionId]);
        return (bool) $stmt->fetchColumn();
    }

    private function willShiftConflictWithAssignedTeachers($sessionId, $shiftId)
    {
        $stmt = $this->db->prepare("
            SELECT 1
            FROM session_teachers target_teacher
            JOIN sessions target ON target.session_id = target_teacher.session_id
            JOIN session_teachers other_teacher
                ON other_teacher.teacher_id = target_teacher.teacher_id
                AND other_teacher.session_id <> target_teacher.session_id
            JOIN sessions other
                ON other.session_id = other_teacher.session_id
                AND other.session_date = target.session_date
                AND other.status <> 'cancelled'
            JOIN shifts other_shift ON other_shift.shift_id = other.shift_id
            JOIN shifts target_shift ON target_shift.shift_id = ?
            WHERE target.session_id = ?
            AND other_shift.start_time < target_shift.end_time
            AND other_shift.end_time > target_shift.start_time
            LIMIT 1
        ");
        $stmt->execute([(int) $shiftId, (int) $sessionId]);
        return (bool) $stmt->fetchColumn();
    }

    public function getRoomsWithStatus($session_id)
    {
        $stmt = $this->db->prepare("
            SELECT 
                r.room_id,
                r.name,
                r.capacity,

                CASE 
                    WHEN EXISTS (
                        SELECT 1 
                        FROM sessions s
                        JOIN shifts sh ON s.shift_id = sh.shift_id

                        WHERE s.room_id = r.room_id
                        AND s.session_date = (
                            SELECT session_date 
                            FROM sessions 
                            WHERE session_id = ?
                        )
                        AND s.session_id != ?

                        AND (
                            sh.start_time < (
                                SELECT sh2.end_time 
                                FROM sessions s2
                                JOIN shifts sh2 
                                    ON s2.shift_id = sh2.shift_id
                                WHERE s2.session_id = ?
                            )
                            AND
                            sh.end_time > (
                                SELECT sh2.start_time 
                                FROM sessions s2
                                JOIN shifts sh2 
                                    ON s2.shift_id = sh2.shift_id
                                WHERE s2.session_id = ?
                            )
                        )
                    )
                    THEN 1 ELSE 0
                END AS is_busy

            FROM rooms r
            WHERE r.status = 'active'
            AND r.capacity >= (
                SELECT COUNT(*)
                FROM sessions ss
                JOIN enrollments e ON e.class_id = ss.class_id
                WHERE ss.session_id = ?
                AND e.status <> 'dropped'
            )
        ");

        $stmt->execute([
            $session_id,
            $session_id,
            $session_id,
            $session_id,
            $session_id
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRoomsAvailableForClass($class_id)
    {
        $stmt = $this->db->prepare("
            SELECT r.*
            FROM rooms r
            WHERE r.status = 'active'
            AND r.capacity >= (
                SELECT COUNT(*)
                FROM enrollments e
                WHERE e.class_id = ?
                AND e.status <> 'dropped'
            )
            ORDER BY r.capacity ASC, r.name ASC
        ");

        $stmt->execute([$class_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStudentsForAttendance($session_id)
    {
        $stmt = $this->db->prepare("
            SELECT 
                st.student_id,
                u.name,
                a.status

            FROM sessions s
            JOIN enrollments e ON s.class_id = e.class_id
            JOIN students st ON e.student_id = st.student_id
            JOIN users u ON st.user_id = u.user_id

            LEFT JOIN attendances a 
                ON a.student_id = st.student_id 
                AND a.session_id = s.session_id

            WHERE s.session_id = ?
        ");

        $stmt->execute([$session_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStudentsForReview($session_id)
    {
        $stmt = $this->db->prepare("
            SELECT 
                st.student_id,
                u.name,
                a.status AS attendance_status,
                sr.review_text
            FROM sessions s
            JOIN enrollments e ON s.class_id = e.class_id
            JOIN students st ON e.student_id = st.student_id
            JOIN users u ON st.user_id = u.user_id
            LEFT JOIN attendances a
                ON a.student_id = st.student_id
                AND a.session_id = s.session_id
            LEFT JOIN session_reviews sr
                ON sr.student_id = st.student_id
                AND sr.session_id = s.session_id
            WHERE s.session_id = ?
            AND e.status <> 'dropped'
            ORDER BY u.name ASC
        ");

        $stmt->execute([$session_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveAttendance($session_id, $data)
    {
        foreach ($data as $student_id => $status) {

            // Lưu trạng thái điểm danh
            $this->db->prepare("
                INSERT INTO attendances (session_id, student_id, status)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE status = VALUES(status)
            ")->execute([$session_id, $student_id, $status]);

            // Nếu có mặt hoặc trễ → tính là đã học
            if ($status == 'present' || $status == 'late') {

                $this->db->prepare("
                    UPDATE enrollments 
                    SET attended_sessions = attended_sessions + 1
                    WHERE student_id = ? 
                    AND class_id = (SELECT class_id FROM sessions WHERE session_id = ?)
                ")->execute([$student_id, $session_id]);
            }
        }

        // cập nhật trạng thái buổi học
        $this->updateStatus($session_id, 'done');
    }

    public function saveReviews($session_id, $reviews)
    {
        $stmt = $this->db->prepare("
            INSERT INTO session_reviews (session_id, student_id, review_text, created_at)
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                review_text = VALUES(review_text),
                created_at = NOW()
        ");
        $deleteStmt = $this->db->prepare("
            DELETE FROM session_reviews
            WHERE session_id = ?
            AND student_id = ?
        ");

        foreach ($reviews as $studentId => $reviewText) {
            $reviewText = trim((string) $reviewText);

            if ($reviewText === '') {
                $deleteStmt->execute([(int) $session_id, (int) $studentId]);
                continue;
            }

            $stmt->execute([
                (int) $session_id,
                (int) $studentId,
                $reviewText
            ]);
        }

        return true;
    }

    private function ensureReviewTable()
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS session_reviews (
                review_id INT AUTO_INCREMENT PRIMARY KEY,
                session_id INT NOT NULL,
                review_text TEXT NOT NULL,
                created_at DATETIME NOT NULL,
                UNIQUE KEY uq_session_reviews_session (session_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");

        $columns = $this->db->query("SHOW COLUMNS FROM session_reviews")->fetchAll(PDO::FETCH_COLUMN);

        if (!in_array('student_id', $columns, true)) {
            $this->db->exec("ALTER TABLE session_reviews ADD student_id INT NULL AFTER session_id");
        }

        $indexes = $this->db->query("SHOW INDEX FROM session_reviews")->fetchAll(PDO::FETCH_ASSOC);
        $hasSessionOnlyUnique = false;
        $hasSessionStudentUnique = false;

        foreach ($indexes as $index) {
            if ($index['Key_name'] === 'uq_session_reviews_session') {
                $hasSessionOnlyUnique = true;
            }

            if ($index['Key_name'] === 'uq_session_reviews_session_student') {
                $hasSessionStudentUnique = true;
            }
        }

        if ($hasSessionOnlyUnique) {
            $this->db->exec("ALTER TABLE session_reviews DROP INDEX uq_session_reviews_session");
        }

        if (!$hasSessionStudentUnique) {
            $this->db->exec("ALTER TABLE session_reviews ADD UNIQUE KEY uq_session_reviews_session_student (session_id, student_id)");
        }
    }

    private function ensureTeacherSpecializationsTable()
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS teacher_specializations (
                teacher_id INT NOT NULL,
                specialization_id INT NOT NULL,
                PRIMARY KEY (teacher_id, specialization_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");

        $this->db->exec("
            INSERT IGNORE INTO teacher_specializations (teacher_id, specialization_id)
            SELECT teacher_id, specialization_id
            FROM teachers
            WHERE specialization_id IS NOT NULL
            AND specialization_id <> 0
        ");
    }

    public function assignTeachersToSessions($sessionIds, $main, $assistants)
    {
        $sessionIds = $this->normalizeIds($sessionIds);
        $main = $main ? (int) $main : null;
        $assistants = $this->normalizeIds($assistants);
        $updated = 0;
        $skipped = 0;

        if (empty($sessionIds) || (!$main && empty($assistants))) {
            return ['updated' => 0, 'skipped' => count($sessionIds)];
        }

        foreach ($sessionIds as $sessionId) {
            if (!$this->isSessionAssignable($sessionId)) {
                $skipped++;
                continue;
            }

            $hasConflict = false;
            $mainForSession = $main ?: $this->getMainTeacherId($sessionId);

            if ($main && $this->isTeacherBusy($main, $sessionId)) {
                $hasConflict = true;
            }

            foreach ($assistants as $assistantId) {
                if ($assistantId === $main) {
                    continue;
                }

                if ($this->isTeacherBusy($assistantId, $sessionId)) {
                    $hasConflict = true;
                    break;
                }
            }

            if ($hasConflict) {
                $skipped++;
                continue;
            }

            $this->saveTeachersWithoutRedirect($sessionId, $mainForSession, $assistants);
            $updated++;
        }

        return ['updated' => $updated, 'skipped' => $skipped];
    }

    private function saveTeachersWithoutRedirect($sessionId, $main, $assistants)
    {
        $this->db->prepare("DELETE FROM session_teachers WHERE session_id = ?")
            ->execute([(int) $sessionId]);

        if ($main) {
            $this->db->prepare("
                INSERT INTO session_teachers (session_id, teacher_id, role)
                VALUES (?, ?, 'main')
            ")->execute([(int) $sessionId, (int) $main]);
        }

        if ($assistants) {
            $stmt = $this->db->prepare("
                INSERT INTO session_teachers (session_id, teacher_id, role)
                VALUES (?, ?, 'assistant')
            ");

            foreach ($assistants as $teacherId) {
                if ($teacherId === $main) {
                    continue;
                }

                $stmt->execute([(int) $sessionId, (int) $teacherId]);
            }
        }
    }

    private function isSessionAssignable($sessionId)
    {
        $stmt = $this->db->prepare("
            SELECT status
            FROM sessions
            WHERE session_id = ?
            LIMIT 1
        ");
        $stmt->execute([(int) $sessionId]);
        $status = $stmt->fetchColumn();

        return $status && !in_array($status, ['done', 'cancelled'], true);
    }

    private function getMainTeacherId($sessionId)
    {
        $stmt = $this->db->prepare("
            SELECT teacher_id
            FROM session_teachers
            WHERE session_id = ?
            AND role = 'main'
            LIMIT 1
        ");
        $stmt->execute([(int) $sessionId]);
        $teacherId = $stmt->fetchColumn();

        return $teacherId ? (int) $teacherId : null;
    }

    private function normalizeIds($ids)
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $normalized = [];

        foreach ($ids as $id) {
            $id = (int) $id;

            if ($id > 0) {
                $normalized[$id] = $id;
            }
        }

        return array_values($normalized);
    }
}
