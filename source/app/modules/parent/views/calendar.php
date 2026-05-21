<?php
$month = max(1, min(12, (int) ($_GET['month'] ?? date('m'))));
$year = (int) ($_GET['year'] ?? date('Y'));
$firstDay = new DateTime(sprintf('%04d-%02d-01', $year, $month));
$start = clone $firstDay;
$start->modify('-' . ((int) $start->format('N') - 1) . ' days');
$end = clone $start;
$end->modify('+41 days');

$sessionsByDate = [];
$doneCount = 0;
$presentCount = 0;
$absentCount = 0;

foreach ($calendarSessions as $session) {
    $dateKey = $session['session_date'];
    $sessionsByDate[$dateKey][] = $session;

    if (($session['status'] ?? '') === 'done') {
        $doneCount++;
    }

    if (in_array(($session['attendance_status'] ?? ''), ['present', 'late'], true)) {
        $presentCount++;
    }

    if (($session['attendance_status'] ?? '') === 'absent') {
        $absentCount++;
    }
}

$prev = (clone $firstDay)->modify('-1 month');
$next = (clone $firstDay)->modify('+1 month');
?>

<div class="calendar-container bg-white p-4 rounded-4 shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h5 class="fw-bold mb-1">Lịch học tháng <?= str_pad($month, 2, '0', STR_PAD_LEFT) ?>/<?= $year ?></h5>
            <div class="calendar-stats d-flex gap-3 small text-muted flex-wrap">
                <span>Buổi đã học: <b class="text-dark"><?= $doneCount ?></b></span>
                <span>Có mặt/trễ: <b class="text-success"><?= $presentCount ?></b></span>
                <span>Vắng: <b class="text-danger"><?= $absentCount ?></b></span>
            </div>
        </div>
        <div class="calendar-nav d-flex align-items-center gap-2">
            <a class="btn btn-sm btn-outline-secondary"
                href="?module=parent&action=calendar&student_id=<?= (int) $selectedStudentId ?>&month=<?= (int) $prev->format('m') ?>&year=<?= (int) $prev->format('Y') ?>">
                <i class="bi bi-chevron-left"></i>
            </a>
            <a class="btn btn-sm btn-light fw-bold"
                href="?module=parent&action=calendar&student_id=<?= (int) $selectedStudentId ?>&month=<?= date('m') ?>&year=<?= date('Y') ?>">
                Hôm nay
            </a>
            <a class="btn btn-sm btn-outline-secondary"
                href="?module=parent&action=calendar&student_id=<?= (int) $selectedStudentId ?>&month=<?= (int) $next->format('m') ?>&year=<?= (int) $next->format('Y') ?>">
                <i class="bi bi-chevron-right"></i>
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered calendar-table mb-0">
            <thead>
                <tr class="text-center bg-light">
                    <th style="width: 14.28%">T2</th>
                    <th style="width: 14.28%">T3</th>
                    <th style="width: 14.28%">T4</th>
                    <th style="width: 14.28%">T5</th>
                    <th style="width: 14.28%">T6</th>
                    <th style="width: 14.28%">T7</th>
                    <th style="width: 14.28%">CN</th>
                </tr>
            </thead>
            <tbody>
                <?php $cursor = clone $start; ?>
                <?php for ($week = 0; $week < 6; $week++): ?>
                    <tr>
                        <?php for ($day = 0; $day < 7; $day++): ?>
                            <?php
                            $dateKey = $cursor->format('Y-m-d');
                            $isCurrentMonth = (int) $cursor->format('m') === $month;
                            $daySessions = $sessionsByDate[$dateKey] ?? [];
                            ?>
                            <td class="<?= $isCurrentMonth ? '' : 'text-muted bg-light' ?> align-top p-1" style="min-width:150px; height:150px;">
                                <div class="day-number fw-semibold mb-1"><?= (int) $cursor->format('d') ?></div>

                                <?php foreach ($daySessions as $session): ?>
                                    <?php
                                    $status = $session['attendance_status'] ?? '';
                                    $badgeClass = $status === 'absent'
                                        ? 'bg-danger'
                                        : (in_array($status, ['present', 'late'], true) ? 'bg-success' : 'bg-primary');
                                    $statusLabel = [
                                        'present' => 'Có mặt',
                                        'late' => 'Trễ',
                                        'absent' => 'Vắng'
                                    ][$status] ?? (($session['status'] ?? '') === 'done' ? 'Đã học' : 'Sắp học');
                                    ?>
                                    <div class="class-card p-2 rounded-3 border mb-2 bg-white">
                                        <div class="d-flex justify-content-between gap-2 mb-1">
                                            <div class="fw-bold small text-truncate"><?= htmlspecialchars($session['course_name']) ?></div>
                                            <span class="badge <?= $badgeClass ?> small"><?= $statusLabel ?></span>
                                        </div>
                                        <div class="small text-muted text-truncate">
                                            <i class="bi bi-book me-1"></i><?= htmlspecialchars($session['package_name']) ?>
                                        </div>
                                        <div class="small text-muted">
                                            <i class="bi bi-clock me-1"></i><?= substr($session['start_time'] ?? '', 0, 5) ?> - <?= substr($session['end_time'] ?? '', 0, 5) ?>
                                        </div>
                                        <?php if (!empty($session['room_name'])): ?>
                                            <div class="small text-muted text-truncate">
                                                <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($session['room_name']) ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($session['teachers'])): ?>
                                            <div class="small text-muted text-truncate">
                                                <i class="bi bi-person me-1"></i><?= htmlspecialchars($session['teachers']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </td>
                            <?php $cursor->modify('+1 day'); ?>
                        <?php endfor; ?>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>
</div>
