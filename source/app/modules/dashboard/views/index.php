<?php
$summary = $summary ?? [];
$todaySchedule = $todaySchedule ?? [];
$roomConflicts = $roomConflicts ?? [];
$unreviewedSessions = $unreviewedSessions ?? [];
$recentEnrollments = $recentEnrollments ?? [];

$formatNumber = function ($value) {
    return number_format((float) $value, 0, ',', '.');
};

$formatMoney = function ($value) {
    return number_format((float) $value, 0, ',', '.') . ' đ';
};

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

$statCards = [
    ['label' => 'Tổng học viên', 'value' => $formatNumber($summary['total_students'] ?? 0), 'icon' => 'bi-person-lines-fill', 'color' => 'blue', 'url' => '?module=student&action=index'],
    ['label' => 'Tổng giáo viên', 'value' => $formatNumber($summary['total_teachers'] ?? 0), 'icon' => 'bi-people-fill', 'color' => 'green', 'url' => '?module=teacher&action=index'],
    ['label' => 'Lớp đang hoạt động', 'value' => $formatNumber($summary['active_classes'] ?? 0), 'icon' => 'bi-collection-fill', 'color' => 'purple', 'url' => '?module=class&action=index&status=studying'],
    ['label' => 'Buổi học hôm nay', 'value' => $formatNumber($summary['today_sessions'] ?? 0), 'icon' => 'bi-calendar-check-fill', 'color' => 'blue', 'url' => '?module=daily_schedule&action=index'],
    ['label' => 'Công nợ học phí', 'value' => $formatMoney($summary['tuition_debt'] ?? 0), 'icon' => 'bi-cash-coin', 'color' => 'red', 'url' => '?module=enrollment&action=index'],
    ['label' => 'Xung đột phòng', 'value' => $formatNumber($summary['room_conflicts'] ?? 0), 'icon' => 'bi-exclamation-triangle-fill', 'color' => ($summary['room_conflicts'] ?? 0) > 0 ? 'red' : 'green', 'url' => '#room-conflicts'],
    ['label' => 'Buổi chưa nhận xét', 'value' => $formatNumber($summary['unreviewed_sessions'] ?? 0), 'icon' => 'bi-chat-left-text-fill', 'color' => ($summary['unreviewed_sessions'] ?? 0) > 0 ? 'red' : 'green', 'url' => '#unreviewed-sessions'],
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
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                <h3 class="mb-0">Dashboard quản trị</h3>
                <span class="badge bg-light-primary text-primary">Hôm nay: <?= date('d/m/Y') ?></span>
            </div>

            <nav class="breadcrumb-header">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item active">Trang chủ</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <?php foreach ($statCards as $card): ?>
                    <div class="col-12 col-sm-6 col-xl-4">
                        <a href="<?= htmlspecialchars($card['url']) ?>" class="text-decoration-none text-reset">
                        <div class="card">
                            <div class="card-body px-4 py-4">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <div class="stats-icon <?= htmlspecialchars($card['color']) ?> mb-2">
                                            <i class="bi <?= htmlspecialchars($card['icon']) ?>"></i>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <h6 class="text-muted font-semibold mb-1"><?= htmlspecialchars($card['label']) ?></h6>
                                        <h5 class="font-extrabold mb-0"><?= htmlspecialchars($card['value']) ?></h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h4 class="mb-0">Đăng ký mới</h4>
                            <a href="?module=enrollment&action=index" class="btn btn-sm btn-outline-primary">
                                Xem danh sách ghi danh
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Thời gian</th>
                                            <th>Học viên</th>
                                            <th>Khóa / gói</th>
                                            <th>Lớp</th>
                                            <th>Học phí</th>
                                            <th>Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($recentEnrollments)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">Chưa có đăng ký mới.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($recentEnrollments as $enrollment): ?>
                                                <?php
                                                $remaining = (float) $enrollment['final_fee'] - (float) $enrollment['paid_amount'];
                                                $paymentLabel = $enrollment['payment_status'] === 'paid'
                                                    ? ['text' => 'Đã thanh toán', 'class' => 'bg-success']
                                                    : ['text' => 'Còn nợ ' . $formatMoney($remaining), 'class' => 'bg-warning text-dark'];
                                                ?>
                                                <tr>
                                                    <td>
                                                        <?= !empty($enrollment['created_at'])
                                                            ? date('d/m/Y H:i', strtotime($enrollment['created_at']))
                                                            : date('d/m/Y', strtotime($enrollment['enroll_date'])) ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($enrollment['student_name']) ?></td>
                                                    <td>
                                                        <strong><?= htmlspecialchars($enrollment['course_name']) ?></strong>
                                                        <div class="small text-muted"><?= htmlspecialchars($enrollment['package_name']) ?></div>
                                                    </td>
                                                    <td><?= htmlspecialchars($enrollment['class_code']) ?></td>
                                                    <td>
                                                        <?= number_format($enrollment['paid_amount']) ?>
                                                        /
                                                        <?= number_format($enrollment['final_fee']) ?> đ
                                                    </td>
                                                    <td>
                                                        <span class="badge <?= $paymentLabel['class'] ?>">
                                                            <?= htmlspecialchars($paymentLabel['text']) ?>
                                                        </span>
                                                    </td>
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

            <div class="row">
                <div class="col-12 col-xl-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h4 class="mb-0">Lịch học hôm nay</h4>
                            <a href="?module=daily_schedule&action=index" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-calendar-week"></i> Xem lịch học
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Giờ học</th>
                                            <th>Lớp</th>
                                            <th>Phòng</th>
                                            <th>Giáo viên</th>
                                            <th>Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($todaySchedule)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">Hôm nay chưa có buổi học.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($todaySchedule as $session): ?>
                                                <?php
                                                $status = $statusLabels[$session['status']] ?? [
                                                    'label' => $session['status'] ?: 'Chưa xác định',
                                                    'class' => 'bg-secondary',
                                                ];
                                                ?>
                                                <tr>
                                                    <td class="text-nowrap">
                                                        <strong><?= $formatTime($session['start_time']) ?></strong>
                                                        -
                                                        <?= $formatTime($session['end_time']) ?>
                                                        <div class="small text-muted"><?= htmlspecialchars($session['shift_name'] ?? '') ?></div>
                                                    </td>
                                                    <td>
                                                        <a href="?module=session&action=index&class_id=<?= (int) $session['class_id'] ?>"
                                                            class="fw-bold text-decoration-none">
                                                            <?= htmlspecialchars($session['class_code'] ?? 'N/A') ?>
                                                        </a>
                                                        <div class="small text-muted">
                                                            <?= htmlspecialchars(trim(($session['course_name'] ?? '') . ' - ' . ($session['package_name'] ?? ''), ' -')) ?>
                                                        </div>
                                                    </td>
                                                    <td><?= htmlspecialchars($session['room_name'] ?? 'Chưa xếp phòng') ?></td>
                                                    <td><?= htmlspecialchars($session['teachers'] ?? 'Chưa phân công') ?></td>
                                                    <td>
                                                        <span class="badge <?= htmlspecialchars($status['class']) ?>">
                                                            <?= htmlspecialchars($status['label']) ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-4">
                    <div class="card" id="room-conflicts">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h4 class="mb-0">Cảnh báo xung đột phòng</h4>
                            <span class="badge <?= empty($roomConflicts) ? 'bg-success' : 'bg-danger' ?>">
                                <?= empty($roomConflicts) ? 'Ổn định' : count($roomConflicts) . ' cảnh báo' ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <?php if (empty($roomConflicts)): ?>
                                <div class="text-center text-muted py-4">Không có phòng bị trùng lịch hôm nay.</div>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($roomConflicts as $conflict): ?>
                                        <div class="list-group-item px-0">
                                            <div class="d-flex justify-content-between align-items-start gap-2">
                                                <div>
                                                    <h6 class="mb-1 text-danger"><?= htmlspecialchars($conflict['room_name']) ?></h6>
                                                    <div class="small">
                                                        <?= htmlspecialchars($conflict['first_class_code']) ?>
                                                        và
                                                        <?= htmlspecialchars($conflict['second_class_code']) ?>
                                                    </div>
                                                    <div class="small text-muted">
                                                        <?= $formatTime($conflict['first_start_time']) ?>
                                                        -
                                                        <?= $formatTime($conflict['first_end_time']) ?>
                                                        /
                                                        <?= $formatTime($conflict['second_start_time']) ?>
                                                        -
                                                        <?= $formatTime($conflict['second_end_time']) ?>
                                                    </div>
                                                </div>
                                                <a href="?module=session&action=index&class_id=<?= (int) $conflict['first_class_id'] ?>"
                                                    class="btn btn-sm btn-outline-danger">
                                                    Kiểm tra
                                                </a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row" id="unreviewed-sessions">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h4 class="mb-0">Buổi học đã điểm danh, chưa nhận xét</h4>
                            <span class="badge <?= empty($unreviewedSessions) ? 'bg-success' : 'bg-warning' ?>">
                                <?= empty($unreviewedSessions) ? 'Đầy đủ' : count($unreviewedSessions) . ' buổi cần xử lý' ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Ngày học</th>
                                            <th>Giờ học</th>
                                            <th>Lớp</th>
                                            <th>Trạng thái</th>
                                            <th class="text-end">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($unreviewedSessions)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">
                                                    Không có buổi học thiếu nhận xét.
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($unreviewedSessions as $session): ?>
                                                <tr>
                                                    <td><?= date('d/m/Y', strtotime($session['session_date'])) ?></td>
                                                    <td class="text-nowrap">
                                                        <?= $formatTime($session['start_time']) ?>
                                                        -
                                                        <?= $formatTime($session['end_time']) ?>
                                                        <div class="small text-muted"><?= htmlspecialchars($session['shift_name'] ?? '') ?></div>
                                                    </td>
                                                    <td>
                                                        <strong><?= htmlspecialchars($session['class_code'] ?? 'N/A') ?></strong>
                                                        <div class="small text-muted">
                                                            <?= htmlspecialchars(trim(($session['course_name'] ?? '') . ' - ' . ($session['package_name'] ?? ''), ' -')) ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-warning">Đã điểm danh, chưa nhận xét buổi học</span>
                                                    </td>
                                                    <td class="text-end">
                                                        <a href="?module=session&action=index&class_id=<?= (int) $session['class_id'] ?>"
                                                            class="btn btn-sm btn-outline-primary">
                                                            Nhận xét
                                                        </a>
                                                    </td>
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
