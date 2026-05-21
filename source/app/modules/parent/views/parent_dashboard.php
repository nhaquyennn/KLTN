<?php
$tracker = $learningTracker ?? [];
$overview = $tracker['overview'] ?? [];
$upcoming = $tracker['upcoming_session'] ?? null;
$latestCompleted = $tracker['latest_completed_session'] ?? null;
$history = $tracker['history'] ?? [];
$schemaNotes = $tracker['schema_notes'] ?? [];

$studentName = $overview['student_name'] ?? ($students[0]['student_name'] ?? 'Học viên');
$totalSessions = (int) ($overview['total_sessions'] ?? 0);
$completedSessions = (int) ($overview['completed_sessions'] ?? 0);
$remainingSessions = (int) ($overview['remaining_sessions'] ?? max(0, $totalSessions - $completedSessions));
$progressPercent = $totalSessions > 0 ? min(100, round($completedSessions / $totalSessions * 100)) : 0;

function parentTrackerText($value, $fallback = 'Chưa có dữ liệu')
{
    $value = trim((string) ($value ?? ''));
    return $value !== '' ? htmlspecialchars($value) : $fallback;
}

function parentTrackerDate($date)
{
    return !empty($date) ? date('d/m/Y', strtotime($date)) : 'Chưa có dữ liệu';
}

function parentTrackerTime($session)
{
    $shift = trim((string) ($session['shift_name'] ?? ''));
    $start = !empty($session['start_time']) ? substr($session['start_time'], 0, 5) : '';
    $end = !empty($session['end_time']) ? substr($session['end_time'], 0, 5) : '';
    $time = trim($start . ($start || $end ? ' - ' : '') . $end);

    if ($shift && $time) {
        return htmlspecialchars($shift . ' / ' . $time);
    }

    return htmlspecialchars($shift ?: ($time ?: 'Chưa xếp ca'));
}

function parentTrackerBadge($type, $value)
{
    $maps = [
        'session' => [
            'done' => ['Đã hoàn thành', 'bg-success'],
            'scheduled' => ['Sắp học', 'bg-primary'],
            'conflict' => ['Cần xếp lại', 'bg-warning text-dark'],
            'cancelled' => ['Đã hủy', 'bg-danger'],
        ],
        'attendance' => [
            'present' => ['Có mặt', 'bg-success'],
            'late' => ['Đi trễ', 'bg-warning text-dark'],
            'absent' => ['Vắng', 'bg-danger'],
        ],
    ];

    [$label, $class] = $maps[$type][$value] ?? ['Chưa cập nhật', 'bg-secondary'];
    return '<span class="badge ' . $class . '">' . htmlspecialchars($label) . '</span>';
}
?>

<style>
    .learning-dashboard .metric-card {
        border: 0;
        border-radius: 8px;
        box-shadow: 0 8px 22px rgba(31, 45, 61, 0.08);
    }

    .learning-dashboard .info-label {
        color: #6c757d;
        font-size: 0.82rem;
        margin-bottom: 0.25rem;
    }

    .learning-dashboard .info-value {
        color: #25324b;
        font-weight: 700;
        margin-bottom: 0;
    }

    .learning-dashboard .section-card {
        border: 0;
        border-radius: 8px;
        box-shadow: 0 8px 22px rgba(31, 45, 61, 0.08);
    }

    .learning-dashboard .section-title {
        color: #25324b;
        font-weight: 800;
    }

    .learning-dashboard .empty-state {
        border: 1px dashed #d8dee9;
        border-radius: 8px;
        color: #6c757d;
        padding: 24px;
        text-align: center;
        background: #fbfcfe;
    }

    .learning-dashboard .table td,
    .learning-dashboard .table th {
        vertical-align: middle;
    }
</style>

<div class="learning-dashboard">
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h3 class="mb-1 fw-bold">Phụ huynh theo dõi học tập</h3>
            <div class="text-muted">Tổng quan quá trình học của <?= htmlspecialchars($studentName) ?></div>
        </div>
        <a href="?module=parent&action=calendar&student_id=<?= (int) $selectedStudentId ?>" class="btn btn-outline-primary">
            <i class="bi bi-calendar-check me-1"></i> Xem lịch học
        </a>
    </div>

    <?php if (empty($overview)): ?>
        <div class="empty-state">Chưa tìm thấy dữ liệu học viên thuộc tài khoản phụ huynh hiện tại.</div>
    <?php else: ?>
        <section class="mb-4">
            <div class="card section-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                        <h5 class="section-title mb-0">Thông tin tổng quan học viên</h5>
                        <span class="badge bg-primary-subtle text-primary">Tiến độ <?= $progressPercent ?>%</span>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <div class="info-label">Tên con</div>
                                <p class="info-value"><?= parentTrackerText($overview['student_name'] ?? '') ?></p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <div class="info-label">Ngày sinh</div>
                                <p class="info-value"><?= parentTrackerDate($overview['date_of_birth'] ?? null) ?></p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <div class="info-label">Khóa học</div>
                                <p class="info-value"><?= parentTrackerText($overview['course_names'] ?? '', 'Chưa đăng ký khóa học') ?></p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <div class="info-label">Phụ huynh</div>
                                <p class="info-value"><?= parentTrackerText($overview['parent_name'] ?? '') ?></p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <div class="info-label">Số điện thoại phụ huynh</div>
                                <p class="info-value"><?= parentTrackerText($overview['parent_phone'] ?? '') ?></p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="card metric-card h-100">
                                <div class="card-body">
                                    <div class="info-label">Tổng số buổi học</div>
                                    <h3 class="fw-bold mb-0 text-primary"><?= $totalSessions ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="card metric-card h-100">
                                <div class="card-body">
                                    <div class="info-label">Số buổi đã hoàn thành</div>
                                    <h3 class="fw-bold mb-0 text-success"><?= $completedSessions ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="card metric-card h-100">
                                <div class="card-body">
                                    <div class="info-label">Số buổi còn lại</div>
                                    <h3 class="fw-bold mb-0 text-warning"><?= $remainingSessions ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $progressPercent ?>%"></div>
                    </div>
                </div>
            </div>
        </section>

        <div class="row g-4 mb-4">
            <section class="col-12 col-xl-5">
                <div class="card section-card h-100">
                    <div class="card-body p-4">
                        <h5 class="section-title mb-3">Buổi học sắp tới</h5>

                        <?php if (!$upcoming): ?>
                            <div class="empty-state">Chưa có buổi học sắp tới.</div>
                        <?php else: ?>
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="info-label">Ngày học</div>
                                    <p class="info-value"><?= parentTrackerDate($upcoming['session_date'] ?? null) ?></p>
                                </div>
                                <div class="col-6">
                                    <div class="info-label">Ca học / giờ học</div>
                                    <p class="info-value"><?= parentTrackerTime($upcoming) ?></p>
                                </div>
                                <div class="col-6">
                                    <div class="info-label">Phòng học</div>
                                    <p class="info-value"><?= parentTrackerText($upcoming['room_name'] ?? '', 'Chưa xếp phòng') ?></p>
                                </div>
                                <div class="col-6">
                                    <div class="info-label">Giảng viên</div>
                                    <p class="info-value"><?= parentTrackerText($upcoming['teachers'] ?? '', 'Chưa phân công') ?></p>
                                </div>
                                <div class="col-12">
                                    <div class="info-label">Trạng thái buổi học</div>
                                    <?= parentTrackerBadge('session', $upcoming['status'] ?? '') ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <section class="col-12 col-xl-7">
                <div class="card section-card h-100">
                    <div class="card-body p-4">
                        <h5 class="section-title mb-3">Nội dung bài học và nhận xét giảng viên</h5>

                        <?php if (!$latestCompleted): ?>
                            <div class="empty-state">Chưa có buổi học đã hoàn thành.</div>
                        <?php else: ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                                    <strong><?= parentTrackerDate($latestCompleted['session_date'] ?? null) ?> - <?= parentTrackerTime($latestCompleted) ?></strong>
                                    <?= parentTrackerBadge('attendance', $latestCompleted['attendance_status'] ?? '') ?>
                                </div>
                                <div class="text-muted small">
                                    <?= parentTrackerText($latestCompleted['course_name'] ?? '') ?>
                                    <?php if (!empty($latestCompleted['teachers'])): ?>
                                        · <?= parentTrackerText($latestCompleted['teachers']) ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-12 col-lg-6">
                                    <div class="p-3 bg-light rounded-3 h-100">
                                        <div class="info-label">Nội dung bài học</div>
                                        <div><?= nl2br(parentTrackerText($latestCompleted['lesson_content'] ?? '', 'Chưa cập nhật nội dung bài học')) ?></div>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <div class="p-3 bg-light rounded-3 h-100">
                                        <div class="info-label">Nhận xét từ giảng viên</div>
                                        <div><?= nl2br(parentTrackerText($latestCompleted['review_text'] ?? '', 'Chưa có nhận xét')) ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </div>

        <section class="mb-4">
            <div class="card section-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <h5 class="section-title mb-0">Lịch sử học tập chi tiết</h5>
                        <span class="text-muted small"><?= count($history) ?> buổi học</span>
                    </div>

                    <?php if (empty($history)): ?>
                        <div class="empty-state">Chưa có lịch sử học tập.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>STT</th>
                                        <th>Ngày học</th>
                                        <th>Ca học / giờ học</th>
                                        <th>Phòng học</th>
                                        <th>Giảng viên</th>
                                        <th>Nội dung bài học</th>
                                        <th>Nhận xét giảng viên</th>
                                        <th>Trạng thái điểm danh</th>
                                        <th>Trạng thái buổi học</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($history as $index => $session): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= parentTrackerDate($session['session_date'] ?? null) ?></td>
                                            <td><?= parentTrackerTime($session) ?></td>
                                            <td><?= parentTrackerText($session['room_name'] ?? '', 'Chưa xếp') ?></td>
                                            <td><?= parentTrackerText($session['teachers'] ?? '', 'Chưa phân công') ?></td>
                                            <td style="min-width: 220px;"><?= nl2br(parentTrackerText($session['lesson_content'] ?? '', 'Chưa cập nhật')) ?></td>
                                            <td style="min-width: 220px;"><?= nl2br(parentTrackerText($session['review_text'] ?? '', 'Chưa có nhận xét')) ?></td>
                                            <td><?= parentTrackerBadge('attendance', $session['attendance_status'] ?? '') ?></td>
                                            <td><?= parentTrackerBadge('session', $session['status'] ?? '') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <?php if (!empty($schemaNotes)): ?>
            <section class="mb-4">
                <div class="alert alert-warning mb-0">
                    <div class="fw-bold mb-2">Cần bổ sung field trong database để lưu đủ nội dung bài học / nhận xét:</div>
                    <?php foreach ($schemaNotes as $note): ?>
                        <code class="d-block mb-1"><?= htmlspecialchars($note['sql']) ?></code>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    <?php endif; ?>
</div>
