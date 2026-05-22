<?php
$money = function ($value) {
    return number_format((float) $value) . ' VNĐ';
};
$statusBadges = [
    'Đã hủy' => 'bg-danger',
    'Đang diễn ra' => 'bg-success',
    'Đã kết thúc' => 'bg-secondary',
    'Chưa bắt đầu' => 'bg-primary',
];
?>

<style>
.teacher-dashboard .metric-card,.teacher-dashboard .panel{border:0;border-radius:8px;box-shadow:0 8px 22px rgba(31,45,61,.08)}
.teacher-dashboard .metric-label{color:#667085;font-size:.82rem}
.teacher-dashboard .metric-value{color:#1d2939;font-size:1.45rem;font-weight:800}
.teacher-dashboard .progress{height:8px}
.teacher-dashboard .table td,.teacher-dashboard .table th{vertical-align:middle}
.teacher-dashboard .notification-row{border-bottom:1px solid #edf0f5;padding:12px 0}
.teacher-dashboard .notification-row:last-child{border-bottom:0}
.teacher-dashboard .chart-wrap{height:290px}
</style>

<div class="teacher-dashboard" id="main">
    <header class="mb-3">
        <a href="#" class="burger-btn d-block d-xl-none"><i class="bi bi-justify fs-3"></i></a>
    </header>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h3 class="mb-1">Dashboard giảng viên</h3>
            <div class="text-muted">Xin chào, <?= htmlspecialchars($teacher['name'] ?? '') ?></div>
        </div>
        <a href="?module=face&action=enroll" class="btn btn-outline-primary">
            <i class="bi bi-person-bounding-box"></i> Đăng ký khuôn mặt
        </a>
    </div>

    <section class="row g-3 mb-4">
        <?php
        $metrics = [
            ['Lớp đang dạy', $overview['class_count'] ?? 0, 'bi-easel'],
            ['Buổi dạy hôm nay', $overview['today_sessions'] ?? 0, 'bi-calendar-check'],
            ['Học viên phụ trách', $overview['student_count'] ?? 0, 'bi-people'],
            ['Buổi đã dạy tháng', $overview['taught_sessions'] ?? 0, 'bi-clock-history'],
            ['Lương tạm tính', $money($overview['estimated_salary'] ?? 0), 'bi-wallet2'],
            ['Tổng thưởng', $money($overview['reward_total'] ?? 0), 'bi-gift'],
            ['Tổng phạt', $money($overview['penalty_total'] ?? 0), 'bi-exclamation-circle'],
            ['Trễ / vắng tháng', $overview['late_absent_count'] ?? 0, 'bi-person-x'],
        ];
        foreach ($metrics as [$label, $value, $icon]): ?>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card metric-card h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="metric-label"><?= htmlspecialchars($label) ?></div>
                            <div class="metric-value"><?= htmlspecialchars((string) $value) ?></div>
                        </div>
                        <i class="bi <?= $icon ?> fs-2 text-primary"></i>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </section>

    <section class="card panel mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h5 class="mb-0 fw-bold">Lịch dạy hôm nay</h5>
                <a href="?module=teacher&action=history" class="btn btn-sm btn-outline-secondary">Lịch sử dạy</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Lớp</th><th>Khóa học</th><th>Ca / giờ</th><th>Phòng</th><th>Sĩ số</th><th>Trạng thái</th><th>Điểm danh</th><th>Học viên</th></tr></thead>
                    <tbody>
                    <?php if (empty($todaySchedule)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">Hôm nay chưa có buổi dạy.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($todaySchedule as $session): ?>
                        <tr>
                            <td class="fw-semibold"><?= htmlspecialchars($session['class_code'] ?? '') ?></td>
                            <td><?= htmlspecialchars($session['course_name'] ?? '') ?></td>
                            <td><?= htmlspecialchars(($session['shift_name'] ?? '') . ' ' . substr((string) ($session['start_time'] ?? ''), 0, 5) . ' - ' . substr((string) ($session['end_time'] ?? ''), 0, 5)) ?></td>
                            <td><?= htmlspecialchars($session['room_name'] ?? 'Chưa xếp') ?></td>
                            <td><?= (int) ($session['student_count'] ?? 0) ?></td>
                            <td><span class="badge <?= $statusBadges[$session['display_status']] ?? 'bg-light text-dark' ?>"><?= htmlspecialchars($session['display_status']) ?></span></td>
                            <td>
                                <?php if (($session['session_status'] ?? '') === 'cancelled'): ?>
                                    <span class="text-muted small">Đã hủy</span>
                                <?php elseif (!empty($session['attendance_id'])): ?>
                                    <span class="badge bg-success"><?= htmlspecialchars($session['attendance_status'] ?? 'Đã lưu') ?></span>
                                <?php else: ?>
                                    <a class="btn btn-sm btn-primary" href="?module=teacherAttendance&action=showAttendancePage&session_id=<?= (int) $session['session_id'] ?>">Điểm danh</a>
                                <?php endif; ?>
                            </td>
                            <td><button class="btn btn-sm btn-outline-info" data-class-students="<?= (int) $session['class_id'] ?>">Xem học viên</button></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="row g-4 mb-4">
        <div class="col-12 col-xl-7">
            <div class="card panel h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Lớp học đang phụ trách</h5>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead><tr><th>Lớp</th><th>Khóa học</th><th>Sĩ số</th><th>Tiến độ</th><th>Chuyên cần</th><th></th></tr></thead>
                            <tbody>
                            <?php foreach ($myClasses as $class): ?>
                                <?php $progress = (int) ($class['total_sessions'] ?? 0) > 0 ? min(100, round((int) $class['completed_sessions'] * 100 / (int) $class['total_sessions'])) : 0; ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($class['class_code'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($class['course_name'] ?? '') ?></td>
                                    <td><?= (int) ($class['student_count'] ?? 0) ?></td>
                                    <td style="min-width:150px">
                                        <div class="small"><?= (int) ($class['completed_sessions'] ?? 0) ?>/<?= (int) ($class['total_sessions'] ?? 0) ?> buổi</div>
                                        <div class="progress"><div class="progress-bar" style="width:<?= $progress ?>%"></div></div>
                                    </td>
                                    <td><?= number_format((float) ($class['attendance_rate'] ?? 0), 1) ?>%</td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-info" data-class-students="<?= (int) $class['class_id'] ?>">Học viên</button>
                                        <a class="btn btn-sm btn-outline-secondary" href="?module=session&action=index&class_id=<?= (int) $class['class_id'] ?>">Lịch học</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($myClasses)): ?><tr><td colspan="6" class="text-muted text-center">Chưa có lớp phụ trách.</td></tr><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-5">
            <div class="card panel h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Lương / thưởng / phạt</h5>
                    <div class="row g-3 mb-3">
                        <div class="col-6"><div class="metric-label">Lương tháng</div><div class="fw-bold"><?= $money($salary['base_salary'] ?? 0) ?></div></div>
                        <div class="col-6"><div class="metric-label">Thực nhận</div><div class="fw-bold text-primary"><?= $money($salary['final_salary'] ?? 0) ?></div></div>
                        <div class="col-6"><div class="metric-label">Thưởng</div><div class="fw-bold text-success">+<?= $money($salary['total_bonus'] ?? 0) ?></div></div>
                        <div class="col-6"><div class="metric-label">Phạt</div><div class="fw-bold text-danger">-<?= $money($salary['total_penalty'] ?? 0) ?></div></div>
                    </div>
                    <form class="row g-2 mb-3" method="GET">
                        <input type="hidden" name="module" value="teacherDashboard"><input type="hidden" name="action" value="index">
                        <div class="col-5"><select class="form-select form-select-sm" name="kind"><option value="all">Tất cả</option><option value="reward" <?= ($_GET['kind'] ?? '') === 'reward' ? 'selected' : '' ?>>Thưởng</option><option value="penalty" <?= ($_GET['kind'] ?? '') === 'penalty' ? 'selected' : '' ?>>Phạt</option><option value="active" <?= ($_GET['kind'] ?? '') === 'active' ? 'selected' : '' ?>>Chưa hủy</option><option value="canceled" <?= ($_GET['kind'] ?? '') === 'canceled' ? 'selected' : '' ?>>Đã hủy</option></select></div>
                        <div class="col-3"><input class="form-control form-control-sm" type="number" min="1" max="12" name="month" value="<?= htmlspecialchars($_GET['month'] ?? date('m')) ?>"></div>
                        <div class="col-4"><button class="btn btn-sm btn-primary w-100">Lọc</button></div>
                    </form>
                    <div style="max-height:250px;overflow:auto">
                        <?php foreach ($rewardPenaltyHistory as $row): ?>
                            <div class="border-top py-2">
                                <div class="d-flex justify-content-between gap-2">
                                    <span class="fw-semibold <?= ($row['type'] ?? '') === 'penalty' ? 'text-danger' : 'text-success' ?>"><?= htmlspecialchars(($row['type'] ?? '') === 'penalty' ? 'Phạt' : 'Thưởng') ?> <?= $money($row['amount'] ?? 0) ?></span>
                                    <span class="badge <?= ($row['status'] ?? '') === 'canceled' ? 'bg-secondary' : 'bg-success' ?>"><?= htmlspecialchars($row['status'] ?? '') ?></span>
                                </div>
                                <div class="small"><?= htmlspecialchars($row['reason'] ?? '') ?></div>
                                <div class="small text-muted">Điểm danh: <?= htmlspecialchars($row['attendance_method'] ?? '-') ?></div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($rewardPenaltyHistory)): ?><div class="text-muted">Chưa có thưởng/phạt trong kỳ lọc.</div><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="row g-4">
        <div class="col-12 col-xl-7">
            <div class="card panel">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Báo cáo cá nhân</h5>
                    <div class="chart-wrap"><canvas id="teacherReportChart"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-5">
            <div class="card panel">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Thông báo</h5>
                    <?php foreach ($notifications as $notification): ?>
                        <div class="notification-row">
                            <div class="d-flex justify-content-between gap-2"><strong><?= htmlspecialchars($notification['title'] ?? 'Thông báo') ?></strong><small class="text-muted"><?= htmlspecialchars($notification['created_at'] ?? '') ?></small></div>
                            <div class="small"><?= htmlspecialchars($notification['message'] ?? '') ?></div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($notifications)): ?><div class="text-muted">Chưa có thông báo.</div><?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="studentsModal" tabindex="-1"><div class="modal-dialog modal-xl modal-dialog-scrollable"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Danh sách học viên</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body" id="classStudentsBody">Đang tải...</div></div></div></div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
const reportSeries = <?= json_encode($reportSeries, JSON_UNESCAPED_UNICODE) ?>;
if (window.Chart) {
    new Chart(document.getElementById('teacherReportChart'), {type:'bar',data:{labels:reportSeries.labels,datasets:[{label:'Buổi dạy',data:reportSeries.sessions,backgroundColor:'#2563eb',yAxisID:'y'},{label:'Trễ/vắng',data:reportSeries.lateAbsent,backgroundColor:'#dc3545',yAxisID:'y'},{label:'Thu nhập',data:reportSeries.income,type:'line',borderColor:'#198754',backgroundColor:'#198754',yAxisID:'money'}]},options:{responsive:true,maintainAspectRatio:false,scales:{y:{beginAtZero:true},money:{beginAtZero:true,position:'right',grid:{drawOnChartArea:false}}}}});
}
document.querySelectorAll('[data-class-students]').forEach(button => button.addEventListener('click', async () => {
    const body = document.getElementById('classStudentsBody');
    body.textContent = 'Đang tải...';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('studentsModal')).show();
    const res = await fetch(`?module=teacherDashboard&action=getStudentsByClass&class_id=${button.dataset.classStudents}`);
    const json = await res.json();
    if (!json.success) { body.textContent = json.message; return; }
    const rows = (json.data || []).map(row => `<tr><td>${escapeHtml(row.student_name)}</td><td>${escapeHtml(row.student_phone || '')}</td><td>${escapeHtml(row.parent_name || '')}</td><td>${escapeHtml(row.parent_phone || '')}</td><td>${escapeHtml(row.payment_status || '-')}</td><td>${Number(row.attendance_rate || 0).toFixed(1)}%</td><td>${escapeHtml(row.latest_learning_status || '-')}</td></tr>`).join('');
    body.innerHTML = `<div class="table-responsive"><table class="table"><thead><tr><th>Họ tên</th><th>SĐT</th><th>Phụ huynh</th><th>SĐT phụ huynh</th><th>Học phí</th><th>Chuyên cần</th><th>Gần nhất</th></tr></thead><tbody>${rows || '<tr><td colspan="7" class="text-muted text-center">Không có học viên.</td></tr>'}</tbody></table></div>`;
}));
function escapeHtml(value){const div=document.createElement('div');div.textContent=value || '';return div.innerHTML;}
</script>
