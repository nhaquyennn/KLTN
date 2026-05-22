<?php

class TeacherController extends Controller
{
    // ===== DANH SÁCH =====
    public function index()
    {
        $model = new TeacherModel();

        $page = $_GET['page'] ?? 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $filters = [
            'keyword' => $_GET['keyword'] ?? null,
            'specialization' => $_GET['specialization'] ?? null,
            'salary_type' => $_GET['salary_type'] ?? null,
            'level_id' => $_GET['level_id'] ?? null,
            'status' => $_GET['status'] ?? null,
        ];

        $teachers = $model->getAll($filters, $limit, $offset);

        $total = $model->countAll($filters);

        $totalPages = ceil($total / $limit);

        // specialization list
        $specializationModel = new SpecializationModel();

        $specializations = $specializationModel
            ->getAll('', 'active', 999, 0);

        $view = ROOT_PATH . "/modules/teacher/views/index.php";

        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";

        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    // ===== FORM CREATE =====
    public function create()
    {
        $specializationModel = new SpecializationModel();

        $specializations = $specializationModel
            ->getAll('', 'active', 999, 0);

        $view = ROOT_PATH . "/modules/teacher/views/create.php";

        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";

        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    // ===== STORE =====
    public function store()
    {
        $model = new TeacherModel();
        $specializationIds = array_values(array_filter(array_map('intval', $_POST['specialization_ids'] ?? [$_POST['specialization_id'] ?? null])));
        $primarySpecializationId = (int) (reset($specializationIds) ?: ($_POST['specialization_id'] ?? 0));

        $data = [
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'password' => $_POST['password'] ?? '',
            'specialization_id' => $primarySpecializationId,
            'specialization_ids' => $specializationIds,
            'hire_date' => $_POST['hire_date'],
            'salary_type' => 'per_session',
            'salary_value' => $_POST['salary_value'],
            'status' => $_POST['status']
        ];

        $model->create($data);

        header("Location: ?module=teacher");

        exit;
    }

    // ===== FORM EDIT =====
    public function edit()
    {
        $id = $_GET['id'] ?? 0;

        $model = new TeacherModel();

        $teacher = $model->findById($id);

        $specializationModel = new SpecializationModel();

        $specializations = $specializationModel
            ->getAll('', 'active', 999, 0);

        $view = ROOT_PATH . "/modules/teacher/views/edit.php";

        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";

        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    // ===== UPDATE =====
    public function update()
    {
        $model = new TeacherModel();
        $specializationIds = array_values(array_filter(array_map('intval', $_POST['specialization_ids'] ?? [$_POST['specialization_id'] ?? null])));
        $primarySpecializationId = (int) (reset($specializationIds) ?: ($_POST['specialization_id'] ?? 0));

        $data = [
            'teacher_id' => $_POST['teacher_id'],
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'password' => $_POST['password'] ?? '',
            'specialization_id' => $primarySpecializationId,
            'specialization_ids' => $specializationIds,
            'hire_date' => $_POST['hire_date'],
            'salary_type' => 'per_session',
            'salary_value' => $_POST['salary_value'],
            'status' => $_POST['status'] ?? 1
        ];

        $model->update($data);

        header("Location: ?module=teacher&action=index");

        exit;
    }

    // ===== DELETE =====
    public function delete()
    {
        $id = $_GET['id'] ?? 0;

        if ($id) {

            $model = new TeacherModel();

            $model->delete($id);
        }

        header("Location: ?module=teacher&action=index");

        exit;
    }

    // ===== RESTORE =====
    public function restore()
    {
        $id = $_GET['id'];

        $model = new TeacherModel();

        $model->restore($id);

        header("Location: ?module=teacher");

        exit;
    }

    // ===== TEACHING HISTORY =====
    public function history()
    {
        $teacherId = $_SESSION['user']['id'];
        $month = $_GET['month'] ?? null;
        $year = $_GET['year'] ?? date('Y');

        $model = new TeacherModel();

        $data = $model->getTeachingHistoryByUserId($teacherId, $month, $year);
        $pageTitle = 'Lịch sử dạy';
        $resetUrl = '?module=teacher&action=history';
        $filterAction = 'history';

        $view = ROOT_PATH . "/modules/teacher/views/history.php";

        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";

        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    public function teaching_history()
    {
        $teacherId = $_GET['id'] ?? 0;

        $model = new TeacherModel();

        $teacher = $model->findById($teacherId);

        if (!$teacher) {
            header("Location: ?module=teacher&action=index");
            exit;
        }

        $month = $_GET['month'] ?? null;
        $year = $_GET['year'] ?? date('Y');

        $data = $model->getTeachingHistoryByTeacherId($teacherId, $month, $year);
        $pageTitle = 'Lịch sử buổi dạy - ' . ($teacher['name'] ?? '');
        $resetUrl = '?module=teacher&action=teaching_history&id=' . (int) $teacherId;
        $filterAction = 'teaching_history';

        $view = ROOT_PATH . "/modules/teacher/views/history.php";

        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";

        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    // ===== CẤU HÌNH BẬC LƯƠNG =====
    public function salary_config()
    {
        $model = new SalaryModel();

        $salary_levels = $model->getSalaryLevels();

        $view = ROOT_PATH . "/modules/teacher/views/salary_config.php";

        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";

        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    // ===== SAVE SALARY LEVELS =====
    public function saveSalaryLevels()
    {
        header('Content-Type: application/json');

        try {

            $data = json_decode(
                file_get_contents("php://input"),
                true
            );

            $model = new SalaryModel();

            $success = true;

            foreach ($data['levels'] as $level) {

                // level 1 luôn = 0
                if ($level['id'] == 1) {
                    $level['requirement_sessions'] = 0;
                }

                $ok = $model->updateSalaryLevel($level);

                if (!$ok) {
                    $success = false;
                }
            }

            echo json_encode([
                'success' => $success
            ]);

        } catch (Exception $e) {

            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }

        exit;
    }

    public function autoPromoteSalaryLevels()
    {
        $model = new SalaryModel();

        $result = $model->autoPromoteTeacherLevels('per_session');

        header("Location: ?module=teacher&action=salary_config&promoted={$result['promoted']}&checked={$result['checked']}&skipped={$result['skipped']}");
        exit;
    }

    // ===== PAYROLL =====
    public function payroll()
    {
        $model = new SalaryModel();

        $month = (int) ($_GET['month'] ?? date('m'));
        $year = (int) ($_GET['year'] ?? date('Y'));

        // calculate salary
        if (isset($_POST['calculate'])) {

            $model->calculateAllSalaries(
                $_POST['month'] ?? $month,
                $_POST['year'] ?? $year
            );

            header("Location: ?module=teacher&action=payroll&month=$month&year=$year");

            exit;
        }

        $payroll_data = $model->getPayroll($month, $year);

        $view = ROOT_PATH . "/modules/teacher/views/payroll.php";

        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";

        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    // ===== BONUS & PENALTIES =====
    public function bonus_penalties()
    {
        $this->role(['admin']);
        $salaryModel = new SalaryModel();

        $teacherModel = new TeacherModel();

        $month = $_GET['month'] ?? date('m');

        $year = $_GET['year'] ?? date('Y');

        $teachers = $teacherModel->getTeachers();

        $stats = $salaryModel->getStats($month, $year);

        $historyFilters = [
            'teacher_id' => $_GET['teacher_id'] ?? null,
            'kind' => $_GET['kind'] ?? 'all',
            'from_date' => $_GET['from_date'] ?? null,
            'to_date' => $_GET['to_date'] ?? null,
        ];

        $history = $salaryModel->getHistory($month, $year, $historyFilters);

        $sessions = $salaryModel->getTeachingSessionsForAdjustments($month, $year);

        $view = ROOT_PATH . "/modules/teacher/views/bonus_penalties.php";

        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";

        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    // ===== SAVE BONUS / PENALTY =====
    public function saveTransaction()
    {
        $this->role(['admin']);
        header('Content-Type: application/json');

        try {

            $data = json_decode(
                file_get_contents("php://input"),
                true
            );

            $model = new SalaryModel();

            if ($data['type'] == 'penalty') {

                $ok = $model->addPenalty($data);

            } else {

                $ok = $model->addBonus($data);
            }

            echo json_encode([
                'success' => $ok
            ]);

        } catch (Exception $e) {

            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }

        exit;
    }

    // ===== UPDATE PAYROLL STATUS =====
    public function updatePayrollStatus()
    {
        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents("php://input"), true);

            $model = new SalaryModel();

            $ok = $model->updatePayrollStatus(
                $data['id'] ?? 0,
                $data['status'] ?? ''
            );

            echo json_encode([
                'success' => $ok
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }

        exit;
    }

    // ===== TEACHER SALARY VIEW =====
    public function my_salary()
    {
        if (($_SESSION['user']['role'] ?? '') !== 'teacher') {
            header("Location: ?module=teacher&action=payroll");
            exit;
        }

        $salaryModel = new SalaryModel();

        $month = (int) ($_GET['month'] ?? date('m'));

        $year = (int) ($_GET['year'] ?? date('Y'));

        $payroll = $salaryModel->getTeacherPayrollByUserId(
            $_SESSION['user']['id'],
            $month,
            $year
        );

        $history = $salaryModel->getTeacherPayrollHistoryByUserId($_SESSION['user']['id']);

        $view = ROOT_PATH . "/modules/teacher/views/my_salary.php";

        $header = ROOT_PATH . "/modules/layouts/header_teacher.php";

        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    // ===== DELETE TRANSACTION =====
    public function deleteTransaction()
    {
        $this->role(['admin']);
        header('Content-Type: application/json');

        $id = $_GET['id'];

        $model = new SalaryModel();

        $ok = $model->cancelAdjustment(
            $id,
            $_POST['canceled_reason'] ?? 'Admin hủy thưởng/phạt',
            $_SESSION['user']['id'] ?? 0
        );

        echo json_encode([
            'success' => $ok
        ]);

        exit;
    }

    public function updateTransaction()
    {
        $this->role(['admin']);
        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents('php://input'), true) ?: [];
            $ok = (new SalaryModel())->updateAdjustment($data['id'] ?? 0, $data);

            echo json_encode([
                'success' => $ok,
                'message' => $ok ? 'Đã cập nhật thưởng/phạt.' : 'Không thể sửa bản ghi đã hủy hoặc không tồn tại.'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }

        exit;
    }

    public function cancelTransaction()
    {
        $this->role(['admin']);
        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents('php://input'), true) ?: [];
            $ok = (new SalaryModel())->cancelAdjustment(
                $data['id'] ?? 0,
                $data['canceled_reason'] ?? '',
                $_SESSION['user']['id'] ?? 0
            );

            echo json_encode([
                'success' => $ok,
                'message' => $ok ? 'Đã hủy thưởng/phạt.' : 'Bản ghi đã hủy hoặc không tồn tại.'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }

        exit;
    }
}
