<?php $isTeacher = ($_SESSION['user']['role'] ?? '') === 'teacher'; ?>
<div id="main">
    <header class="mb-3">
        <a href="#" class="burger-btn d-block d-xl-none">
            <i class="bi bi-justify fs-3"></i>
        </a>
    </header>

    <div class="page-heading">
        <div class="page-title mb-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h3 class="mb-0">Danh sách lớp học</h3>

                <?php if (!$isTeacher): ?>
                    <a href="?module=class&action=create" class="btn btn-success">
                        <i class="bi bi-plus"></i> Thêm lớp học
                    </a>
                <?php endif; ?>
            </div>

            <nav class="breadcrumb-header mt-2">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="<?= BASE_URL ?>?module=dashboard&action=index">Trang chủ</a>
                    </li>
                    <li class="breadcrumb-item active">Lớp học</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <form method="GET" class="mb-3">
                            <input type="hidden" name="module" value="class">

                            <div class="row">
                                <div class="col-md-3">
                                    <input type="text" name="keyword" class="form-control" placeholder="Tìm khóa học..." maxlength="100"
                                        value="<?= htmlspecialchars($filters['keyword'] ?? '') ?>">
                                </div>

                                <div class="col-md-2">
                                    <select name="course_id" class="form-control">
                                        <option value="">Khóa học</option>
                                        <?php foreach ($courses as $c): ?>
                                            <option value="<?= $c['course_id'] ?>"
                                                <?= ($filters['course_id'] == $c['course_id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($c['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <select name="package_id" class="form-control">
                                        <option value="">Gói học</option>
                                        <?php foreach ($packages as $p): ?>
                                            <option value="<?= $p['package_id'] ?>"
                                                <?= ($filters['package_id'] == $p['package_id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($p['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <select name="schedule_id" class="form-control">
                                        <option value="">Lịch học</option>
                                        <?php foreach ($schedules as $s): ?>
                                            <option value="<?= $s['schedule_id'] ?>"
                                                <?= ($filters['schedule_id'] == $s['schedule_id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($s['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <select name="shift_id" class="form-control">
                                        <option value="">Ca học</option>
                                        <?php foreach ($shifts as $sh): ?>
                                            <option value="<?= $sh['shift_id'] ?>"
                                                <?= ($filters['shift_id'] == $sh['shift_id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($sh['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-2 mt-2">
                                    <select name="status" class="form-control">
                                        <option value="">-- Trạng thái --</option>
                                        <option value="upcoming" <?= ($filters['status'] ?? '') === 'upcoming' ? 'selected' : '' ?>>Sắp học</option>
                                        <option value="studying" <?= ($filters['status'] ?? '') === 'studying' ? 'selected' : '' ?>>Đang học</option>
                                        <option value="done" <?= ($filters['status'] ?? '') === 'done' ? 'selected' : '' ?>>Đã xong</option>
                                        <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Đã ngưng</option>
                                    </select>
                                </div>

                                <div class="col-md-3 mt-2 d-flex gap-2">
                                    <button class="btn btn-primary">Lọc</button>
                                    <a href="?module=class" class="btn btn-secondary">Đặt lại</a>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>STT</th>
                                        <th>Mã lớp</th>
                                        <th>Lớp học</th>
                                        <th>Khóa</th>
                                        <th>Gói</th>
                                        <th>Học viên</th>
                                        <th>Tối đa</th>
                                        <th>Lịch</th>
                                        <th>Ngày bắt đầu</th>
                                        <th>Trạng thái</th>
                                        <th>Tiến độ</th>
                                        <th class="text-center">Thao tác</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php if (!empty($classes)): ?>
                                        <?php foreach ($classes as $index => $c): ?>
                                            <tr>
                                                <td><?= $offset + $index + 1 ?></td>

                                                <td>
                                                    <span class="badge bg-dark">
                                                        <?= htmlspecialchars($c['class_code']) ?>
                                                    </span>
                                                </td>

                                                <td>
                                                    <?php
                                                    $code = $c['class_code'] ?? '';
                                                    $suffix = '';

                                                    if ($code && strpos($code, '-') !== false) {
                                                        $parts = explode('-', $code);
                                                        $suffix = end($parts);
                                                    }
                                                    ?>
                                                    <?= htmlspecialchars($c['course_name']) ?> -
                                                    <?= htmlspecialchars($c['package_name']) ?> -
                                                    <?= htmlspecialchars($suffix) ?>
                                                </td>

                                                <td><?= htmlspecialchars($c['course_name']) ?></td>
                                                <td><?= htmlspecialchars($c['package_name']) ?></td>

                                                <td>
                                                    <span class="badge bg-primary">
                                                        <?= (int) $c['student_count'] ?>
                                                    </span>
                                                </td>

                                                <td>
                                                    <span class="badge bg-secondary">
                                                        <?= (int) ($c['max_students'] ?? 10) ?>
                                                    </span>
                                                </td>

                                                <td><?= htmlspecialchars($c['schedule_name'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($c['start_date']) ?></td>

                                                <td>
                                                    <?php if ($c['status'] == 'unscheduled'): ?>
                                                        <span class="badge bg-secondary">Chưa xếp lịch</span>
                                                    <?php elseif ($c['status'] == 'upcoming'): ?>
                                                        <span class="badge bg-info">Sắp học</span>
                                                    <?php elseif ($c['status'] == 'studying'): ?>
                                                        <span class="badge bg-primary">Đang học</span>
                                                    <?php elseif ($c['status'] == 'done'): ?>
                                                        <span class="badge bg-success">Hoàn thành</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Ngưng</span>
                                                    <?php endif; ?>
                                                </td>

                                                <td style="width:200px;">
                                                    <?php
                                                    $percent = $c['total_sessions'] > 0
                                                        ? ($c['learned'] / $c['total_sessions']) * 100
                                                        : 0;
                                                    ?>

                                                    <div class="progress">
                                                        <div class="progress-bar bg-success" style="width: <?= $percent ?>%">
                                                            <?= (int) $c['learned'] ?>/<?= (int) $c['total_sessions'] ?>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td class="text-center">
                                                    <?php if (!$isTeacher): ?>
                                                        <a href="?module=class&action=edit&id=<?= $c['class_id'] ?>"
                                                            class="btn btn-sm btn-warning">
                                                            Sửa
                                                        </a>

                                                        <?php if ($c['status'] == 'inactive'): ?>
                                                            <a href="?module=class&action=activate&id=<?= $c['class_id'] ?>"
                                                                class="btn btn-sm btn-success">
                                                                Kích hoạt
                                                            </a>
                                                        <?php else: ?>
                                                            <a href="?module=class&action=deactivate&id=<?= $c['class_id'] ?>"
                                                                class="btn btn-danger btn-sm"
                                                                onclick="return confirm('Ngưng lớp này?')">
                                                                Ngưng
                                                            </a>
                                                        <?php endif; ?>
                                                    <?php endif; ?>

                                                    <a href="?module=session&action=index&class_id=<?= $c['class_id'] ?>"
                                                        class="btn btn-sm btn-primary">
                                                        Buổi học
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="12" class="text-center">Không có dữ liệu</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="text-center mt-2">
                        Hiển thị
                        <?= $total > 0 ? $offset + 1 : 0 ?>
                        -
                        <?= min($offset + $limit, $total) ?>
                        /
                        <?= $total ?> lớp học
                    </div>

                    <?php if ($totalPages > 1): ?>
                        <nav class="mt-3">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?module=class&page=<?= $page - 1 ?>&keyword=<?= urlencode($filters['keyword'] ?? '') ?>&course_id=<?= $filters['course_id'] ?? '' ?>">
                                        «
                                    </a>
                                </li>

                                <?php
                                $start = max(1, $page - 2);
                                $end = min($totalPages, $page + 2);
                                ?>

                                <?php for ($i = $start; $i <= $end; $i++): ?>
                                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                        <a class="page-link"
                                            href="?module=class&page=<?= $i ?>&keyword=<?= urlencode(trim($filters['keyword'] ?? '')) ?>&course_id=<?= $filters['course_id'] ?? '' ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                    <a class="page-link"
                                        href="?module=class&page=<?= $page + 1 ?>&keyword=<?= urlencode(trim($filters['keyword'] ?? '')) ?>&course_id=<?= $filters['course_id'] ?? '' ?>">
                                        »
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</div>
