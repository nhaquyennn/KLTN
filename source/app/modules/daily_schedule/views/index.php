<?php
$formatTime = function ($value) {
    return $value ? substr($value, 0, 5) : '--:--';
};

$statusLabels = [
    'scheduled' => ['label' => 'Đã lên lịch', 'class' => 'bg-primary'],
    'ongoing' => ['label' => 'Đang học', 'class' => 'bg-info'],
    'done' => ['label' => 'Hoàn thành', 'class' => 'bg-success'],
    'conflict' => ['label' => 'Xung đột', 'class' => 'bg-danger'],
    'cancelled' => ['label' => 'Đã hủy', 'class' => 'bg-secondary'],
];

$attendanceLabels = [
    'present' => ['label' => 'Có mặt', 'class' => 'bg-success'],
    'late' => ['label' => 'Trễ', 'class' => 'bg-warning'],
    'absent' => ['label' => 'Vắng', 'class' => 'bg-danger'],
];
?>

<div id="main">
    <header class="mb-3">
        <a href="#" class="burger-btn d-block d-xl-none">
            <i class="bi bi-justify fs-3"></i>
        </a>
    </header>

    <div class="page-heading">
        <div class="page-title mb-3">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h3 class="mb-1">Lịch học và lịch dạy trong ngày</h3>
                    <nav class="breadcrumb-header">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="?module=dashboard&action=index">Trang chủ</a></li>
                            <li class="breadcrumb-item active">Lịch trong ngày</li>
                        </ol>
                    </nav>
                </div>
                <span class="badge bg-light-primary text-primary">
                    <?= date('d/m/Y', strtotime($filters['date'])) ?>
                </span>
            </div>
        </div>

        <section class="section">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-2 align-items-end">
                        <input type="hidden" name="module" value="daily_schedule">
                        <input type="hidden" name="action" value="index">

                        <div class="col-md-3">
                            <label>Ngày</label>
                            <input type="date" name="date" class="form-control"
                                value="<?= htmlspecialchars($filters['date']) ?>">
                        </div>

                        <div class="col-md-3">
                            <label>Học sinh</label>
                            <select name="student_id" class="form-control">
                                <option value="">-- Tất cả học sinh --</option>
                                <?php foreach ($students as $student): ?>
                                    <option value="<?= (int) $student['student_id'] ?>"
                                        <?= (string) $filters['student_id'] === (string) $student['student_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($student['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label>Giảng viên</label>
                            <select name="teacher_id" class="form-control">
                                <option value="">-- Tất cả giảng viên --</option>
                                <?php foreach ($teachers as $teacher): ?>
                                    <option value="<?= (int) $teacher['teacher_id'] ?>"
                                        <?= (string) $filters['teacher_id'] === (string) $teacher['teacher_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($teacher['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <button class="btn btn-primary">
                                <i class="bi bi-filter"></i>
                                Lọc
                            </button>
                            <a href="?module=daily_schedule&action=index" class="btn btn-secondary">Hôm nay</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h4 class="mb-0">Lịch học hôm nay của học sinh</h4>
                            <span class="badge bg-primary"><?= count($studentSchedules) ?> dòng</span>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Giờ học</th>
                                            <th>Học sinh</th>
                                            <th>Lớp</th>
                                            <th>Phòng</th>
                                            <th>Giảng viên</th>
                                            <th>Điểm danh</th>
                                            <th>Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($studentSchedules)): ?>
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">Không có lịch học phù hợp.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($studentSchedules as $row): ?>
                                                <?php
                                                $status = $statusLabels[$row['status']] ?? ['label' => $row['status'] ?? 'N/A', 'class' => 'bg-secondary'];
                                                $attendance = $attendanceLabels[$row['attendance_status'] ?? ''] ?? ['label' => 'Chưa điểm danh', 'class' => 'bg-light text-dark'];
                                                ?>
                                                <tr>
                                                    <td class="text-nowrap">
                                                        <strong><?= $formatTime($row['start_time']) ?></strong>
                                                        -
                                                        <?= $formatTime($row['end_time']) ?>
                                                        <div class="small text-muted"><?= htmlspecialchars($row['shift_name'] ?? '') ?></div>
                                                    </td>
                                                    <td><?= htmlspecialchars($row['student_name']) ?></td>
                                                    <td>
                                                        <a href="?module=session&action=index&class_id=<?= (int) $row['class_id'] ?>"
                                                            class="fw-bold text-decoration-none">
                                                            <?= htmlspecialchars($row['class_code']) ?>
                                                        </a>
                                                        <div class="small text-muted">
                                                            <?= htmlspecialchars($row['course_name'] . ' - ' . $row['package_name']) ?>
                                                        </div>
                                                    </td>
                                                    <td><?= htmlspecialchars($row['room_name'] ?? 'Chưa xếp phòng') ?></td>
                                                    <td><?= htmlspecialchars($row['teachers'] ?? 'Chưa phân công') ?></td>
                                                    <td><span class="badge <?= $attendance['class'] ?>"><?= $attendance['label'] ?></span></td>
                                                    <td><span class="badge <?= $status['class'] ?>"><?= $status['label'] ?></span></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h4 class="mb-0">Lịch dạy hôm nay của giảng viên</h4>
                            <span class="badge bg-success"><?= count($teacherSchedules) ?> dòng</span>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Giờ dạy</th>
                                            <th>Giảng viên</th>
                                            <th>Vai trò</th>
                                            <th>Lớp</th>
                                            <th>Phòng</th>
                                            <th>Số học sinh</th>
                                            <th>Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($teacherSchedules)): ?>
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">Không có lịch dạy phù hợp.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($teacherSchedules as $row): ?>
                                                <?php $status = $statusLabels[$row['status']] ?? ['label' => $row['status'] ?? 'N/A', 'class' => 'bg-secondary']; ?>
                                                <tr>
                                                    <td class="text-nowrap">
                                                        <strong><?= $formatTime($row['start_time']) ?></strong>
                                                        -
                                                        <?= $formatTime($row['end_time']) ?>
                                                        <div class="small text-muted"><?= htmlspecialchars($row['shift_name'] ?? '') ?></div>
                                                    </td>
                                                    <td><?= htmlspecialchars($row['teacher_name']) ?></td>
                                                    <td>
                                                        <?= ($row['teacher_role'] ?? '') === 'main'
                                                            ? '<span class="badge bg-primary">Chính</span>'
                                                            : '<span class="badge bg-secondary">Trợ giảng</span>' ?>
                                                    </td>
                                                    <td>
                                                        <a href="?module=session&action=index&class_id=<?= (int) $row['class_id'] ?>"
                                                            class="fw-bold text-decoration-none">
                                                            <?= htmlspecialchars($row['class_code']) ?>
                                                        </a>
                                                        <div class="small text-muted">
                                                            <?= htmlspecialchars($row['course_name'] . ' - ' . $row['package_name']) ?>
                                                        </div>
                                                    </td>
                                                    <td><?= htmlspecialchars($row['room_name'] ?? 'Chưa xếp phòng') ?></td>
                                                    <td><?= (int) $row['student_count'] ?></td>
                                                    <td><span class="badge <?= $status['class'] ?>"><?= $status['label'] ?></span></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
