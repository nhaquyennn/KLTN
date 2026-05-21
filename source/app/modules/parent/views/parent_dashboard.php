<?php
$student = $summary['student'] ?? [];
$studentName = $student['student_name'] ?? ($students[0]['student_name'] ?? 'Học viên');
$remaining = (int) ($student['remaining_sessions'] ?? 0);
$attended = (int) ($student['attended_sessions'] ?? 0);
$attendanceCount = (int) ($student['attendance_count'] ?? 0);
$presentCount = (int) ($student['present_count'] ?? 0);
$attendanceRate = $attendanceCount > 0 ? round($presentCount / $attendanceCount * 100) : 0;
$nextSession = $summary['next_session'] ?? null;
$latestReview = $summary['latest_review'] ?? null;
$activeEnrollments = $summary['active_enrollments'] ?? [];
?>

<div class="dashboard-container">
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="dashboard-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold text-dark">Tổng quan - <?= htmlspecialchars($studentName) ?></h4>
            <small class="text-dark opacity-75"><?= date('d/m/Y') ?></small>
        </div>
        <a href="?module=parent&action=packages&student_id=<?= (int)$selectedStudentId ?>" class="btn btn-dark d-none d-sm-block px-4">
            Đăng ký gói học
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card p-3 h-100">
                <small class="text-muted">Buổi còn lại</small>
                <h3 class="text-warning mb-0"><?= $remaining ?></h3>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card p-3 h-100">
                <small class="text-muted">Đã học</small>
                <h3 class="text-success mb-0"><?= $attended ?></h3>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card p-3 h-100">
                <small class="text-muted">Chuyên cần</small>
                <h3 class="text-primary mb-0"><?= $attendanceRate ?>%</h3>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card p-3 h-100">
                <small class="text-muted">Buổi tiếp theo</small>
                <h5 class="mb-0 mt-2">
                    <?= $nextSession ? date('d/m', strtotime($nextSession['session_date'])) : 'Chưa có' ?>
                </h5>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card p-4 h-100 border-0 shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-calendar-event me-2"></i>Lịch học sắp tới</h6>
                    <a href="?module=parent&action=calendar&student_id=<?= (int)$selectedStudentId ?>" class="text-primary small text-decoration-none">Xem tất cả</a>
                </div>
                <div class="schedule-list">
                    <?php if (empty($upcomingSessions)): ?>
                        <div class="text-muted">Chưa có lịch học sắp tới.</div>
                    <?php else: ?>
                        <?php foreach ($upcomingSessions as $session): ?>
                            <div class="d-flex align-items-center py-3 border-bottom">
                                <div class="date-box me-3 bg-primary-subtle text-primary fw-bold rounded-3 p-2 text-center" style="width: 54px;">
                                    <?= date('d', strtotime($session['session_date'])) ?><br>
                                    <small class="fw-normal"><?= date('m/Y', strtotime($session['session_date'])) ?></small>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold mb-0"><?= htmlspecialchars($session['course_name']) ?></div>
                                    <small class="text-muted">
                                        <?= substr($session['start_time'] ?? '', 0, 5) ?> - <?= substr($session['end_time'] ?? '', 0, 5) ?>
                                        <?= $session['room_name'] ? ' • ' . htmlspecialchars($session['room_name']) : '' ?>
                                    </small>
                                </div>
                                <span class="badge rounded-pill bg-primary-subtle text-primary">Sắp tới</span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card p-4 h-100 border-0 shadow-sm">
                <h6 class="fw-bold mb-3">Nhận xét từ trung tâm</h6>
                <?php if (!$latestReview): ?>
                    <div class="text-muted">Chưa có nhận xét mới.</div>
                <?php else: ?>
                    <div class="d-flex gap-3">
                        <div class="bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 45px; height: 45px;">
                            <i class="bi bi-chat-quote-fill fs-5"></i>
                        </div>
                        <div>
                            <div class="fw-bold small"><?= htmlspecialchars($latestReview['teachers'] ?: 'Giáo viên') ?> - <?= htmlspecialchars($latestReview['course_name']) ?></div>
                            <p class="text-muted small mt-1 mb-0"><?= htmlspecialchars($latestReview['review_text']) ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card p-4 border-0 shadow-sm">
        <h6 class="fw-bold mb-3">Gói học & Tiến độ</h6>
        <?php if (empty($activeEnrollments)): ?>
            <div class="text-muted">Học viên chưa đăng ký gói học.</div>
        <?php else: ?>
            <?php foreach ($activeEnrollments as $e): ?>
                <?php $percent = $e['total_sessions'] > 0 ? min(100, round($e['attended_sessions'] / $e['total_sessions'] * 100)) : 0; ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small fw-bold"><?= htmlspecialchars($e['course_name'] . ' - ' . $e['package_name']) ?></span>
                        <span class="small text-muted">Đã học <?= (int)$e['attended_sessions'] ?>/<?= (int)$e['total_sessions'] ?> buổi</span>
                    </div>
                    <div class="progress mb-2" style="height: 10px; border-radius: 10px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $percent ?>%"></div>
                    </div>
                    <div class="d-flex justify-content-between small">
                        <span>Đã đóng: <strong><?= number_format($e['paid_amount']) ?>đ</strong></span>
                        <span class="<?= $e['payment_status'] === 'paid' ? 'text-success' : 'text-danger' ?>">
                            <?= $e['payment_status'] === 'paid' ? 'Đã thanh toán' : 'Còn nợ: ' . number_format($e['final_fee'] - $e['paid_amount']) . 'đ' ?>
                        </span>
                    </div>
                    <?php if ($e['payment_status'] !== 'paid'): ?>
                        <div class="mt-2 text-end">
                            <a href="?module=parent&action=payment&id=<?= (int) $e['enrollment_id'] ?>"
                                class="btn btn-sm btn-primary">
                                Thanh toán VNPay
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
