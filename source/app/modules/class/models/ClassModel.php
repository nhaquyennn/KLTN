<?php
class ClassModel
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    public function getAll($filters, $limit, $offset)
    {
        $sql = "SELECT
        c.class_id,
        c.class_code,
        c.start_date,
        COALESCE(c.max_students, 10) AS max_students,

        co.name AS course_name,
        p.name AS package_name,
        p.total_sessions,

        s.name AS schedule_name,
        sh.name AS shift_name,

        COUNT(DISTINCT CASE 
            WHEN cs.status = 'done' THEN cs.session_id 
        END) AS learned,

        COUNT(DISTINCT cs.session_id) AS total_sessions_learned,

        (
            SELECT COUNT(*) 
            FROM enrollments e 
            WHERE e.class_id = c.class_id
        ) AS student_count,

        CASE
            WHEN c.is_active = 0 THEN 'inactive'
            WHEN COUNT(DISTINCT cs.session_id) = 0 THEN 'unscheduled'
            WHEN COUNT(DISTINCT CASE 
                    WHEN cs.status = 'done' THEN cs.session_id 
                END) >= p.total_sessions THEN 'done'
            WHEN c.start_date > CURDATE() THEN 'upcoming'
            ELSE 'studying'
        END AS status

        FROM classes c
        LEFT JOIN courses co ON c.course_id = co.course_id
        LEFT JOIN packages p ON c.package_id = p.package_id
        LEFT JOIN schedules s ON c.schedule_id = s.schedule_id
        LEFT JOIN shifts sh ON c.shift_id = sh.shift_id
        LEFT JOIN sessions cs ON cs.class_id = c.class_id

        WHERE 1";

        // Nếu là teacher thì chỉ lấy lớp có buổi được phân công dạy
        if (!empty($filters['teacher_id'])) {
            $sql .= " AND EXISTS (
            SELECT 1
            FROM sessions ss
            INNER JOIN session_teachers st ON st.session_id = ss.session_id
            WHERE ss.class_id = c.class_id
            AND st.teacher_id = :teacher_id
        )";
        }

        if (!empty($filters['keyword'])) {
            $sql .= " AND co.name LIKE :keyword";
        }
        if (!empty($filters['course_id'])) {
            $sql .= " AND c.course_id = :course_id";
        }
        if (!empty($filters['package_id'])) {
            $sql .= " AND c.package_id = :package_id";
        }
        if (!empty($filters['schedule_id'])) {
            $sql .= " AND c.schedule_id = :schedule_id";
        }
        if (!empty($filters['shift_id'])) {
            $sql .= " AND c.shift_id = :shift_id";
        }

        $sql .= " GROUP BY c.class_id";

        if (!empty($filters['status'])) {
            $sql .= " HAVING status = :status";
        }

        $sql .= " ORDER BY c.class_id DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        if (!empty($filters['teacher_id'])) {
            $stmt->bindValue(':teacher_id', $filters['teacher_id'], PDO::PARAM_INT);
        }
        if (!empty($filters['keyword'])) {
            $stmt->bindValue(':keyword', '%' . $filters['keyword'] . '%');
        }
        if (!empty($filters['course_id'])) {
            $stmt->bindValue(':course_id', $filters['course_id']);
        }
        if (!empty($filters['package_id'])) {
            $stmt->bindValue(':package_id', $filters['package_id']);
        }
        if (!empty($filters['schedule_id'])) {
            $stmt->bindValue(':schedule_id', $filters['schedule_id']);
        }
        if (!empty($filters['shift_id'])) {
            $stmt->bindValue(':shift_id', $filters['shift_id']);
        }
        if (!empty($filters['status'])) {
            $stmt->bindValue(':status', $filters['status']);
        }

        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAll($filters)
    {
        $sql = "SELECT COUNT(DISTINCT c.class_id)
            FROM classes c
            LEFT JOIN courses co ON c.course_id = co.course_id
            WHERE 1";

        if (!empty($filters['teacher_id'])) {
            $sql .= " AND EXISTS (
            SELECT 1
            FROM sessions ss
            INNER JOIN session_teachers st ON st.session_id = ss.session_id
            WHERE ss.class_id = c.class_id
            AND st.teacher_id = :teacher_id
        )";
        }

        if (!empty($filters['keyword'])) {
            $sql .= " AND co.name LIKE :keyword";
        }
        if (!empty($filters['course_id'])) {
            $sql .= " AND c.course_id = :course_id";
        }
        if (!empty($filters['package_id'])) {
            $sql .= " AND c.package_id = :package_id";
        }
        if (!empty($filters['schedule_id'])) {
            $sql .= " AND c.schedule_id = :schedule_id";
        }
        if (!empty($filters['shift_id'])) {
            $sql .= " AND c.shift_id = :shift_id";
        }

        $stmt = $this->db->prepare($sql);

        if (!empty($filters['teacher_id'])) {
            $stmt->bindValue(':teacher_id', $filters['teacher_id'], PDO::PARAM_INT);
        }
        if (!empty($filters['keyword'])) {
            $stmt->bindValue(':keyword', '%' . $filters['keyword'] . '%');
        }
        if (!empty($filters['course_id'])) {
            $stmt->bindValue(':course_id', $filters['course_id']);
        }
        if (!empty($filters['package_id'])) {
            $stmt->bindValue(':package_id', $filters['package_id']);
        }
        if (!empty($filters['schedule_id'])) {
            $stmt->bindValue(':schedule_id', $filters['schedule_id']);
        }
        if (!empty($filters['shift_id'])) {
            $stmt->bindValue(':shift_id', $filters['shift_id']);
        }

        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getTeacherIdByUserId($userId)
    {
        $stmt = $this->db->prepare("
            SELECT teacher_id
            FROM teachers
            WHERE user_id = ?
            AND status = 1
            LIMIT 1
        ");
        $stmt->execute([(int) $userId]);
        return $stmt->fetchColumn() ?: null;
    }

    public function create($data)
    {
        $stmt = $this->db->prepare("SELECT code FROM courses WHERE course_id = ?");
        $stmt->execute([$data['course_id']]);
        $prefix = $stmt->fetchColumn() ?? 'CLS';

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM classes WHERE course_id = ?");
        $stmt->execute([$data['course_id']]);
        $count = $stmt->fetchColumn() + 1;

        $data['class_code'] = $prefix . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

        $sql = "INSERT INTO classes 
            (class_code, course_id, package_id, schedule_id, shift_id, start_date, max_students)
            VALUES (:class_code, :course_id, :package_id, :schedule_id, :shift_id, :start_date, :max_students)";

        $this->db->prepare($sql)->execute($data);
    }

    public function update($data)
    {
        $sql = "UPDATE classes 
                SET course_id = :course_id,
                    package_id = :package_id,
                    schedule_id = :schedule_id,
                    shift_id = :shift_id,
                    start_date = :start_date,
                    max_students = :max_students
                WHERE class_id = :class_id";

        $this->db->prepare($sql)->execute($data);
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("
            SELECT 
                c.*,
                co.name AS course_name,
                p.name AS package_name,
                sh.name AS shift_name

            FROM classes c
            LEFT JOIN courses co ON c.course_id = co.course_id
            LEFT JOIN packages p ON c.package_id = p.package_id
            LEFT JOIN shifts sh ON c.shift_id = sh.shift_id

            WHERE c.class_id = ?
        ");

        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deactivate($id)
    {
        $this->db->prepare("UPDATE classes SET is_active = 0 WHERE class_id = ?")
            ->execute([$id]);
    }

    public function activate($id)
    {
        $this->db->prepare("UPDATE classes SET is_active = 1 WHERE class_id = ?")
            ->execute([$id]);
    }

    public function getLastInsertId()
    {
        return $this->db->lastInsertId();
    }
}
