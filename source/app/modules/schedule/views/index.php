<div id="main">
    <header class="mb-3">
        <a href="#" class="burger-btn d-block d-xl-none">
            <i class="bi bi-justify fs-3"></i>
        </a>
    </header>

    <div class="page-heading">

        <!-- TITLE -->
        <div class="page-title mb-3">

            <!-- HÀNG 1 -->
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                <h3 class="mb-0">Danh sách lịch học</h3>

                <a href="?module=schedule&action=create" class="btn btn-success">
                    <i class="bi bi-plus"></i> Thêm lịch học
                </a>
            </div>

            <!-- BREADCRUMB -->
            <nav class="breadcrumb-header">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="<?= BASE_URL ?>?module=dashboard&action=index">
                            Trang chủ
                        </a>
                    </li>

                    <li class="breadcrumb-item active">
                        Lịch học
                    </li>
                </ol>
            </nav>

        </div>

        <!-- MAIN -->
        <section class="section">
            <div class="row">
                <div class="col-12">

                    <!-- TABLE -->
                    <div class="card">

                        <!-- FILTER -->
                        <form method="GET" class="mb-3">
                            <input type="hidden" name="module" value="schedule">

                            <div class="row g-2">

                                <!-- Keyword -->
                                <div class="col-md-3">
                                    <input type="text" name="keyword" class="form-control" placeholder="Tìm lịch học..." maxlength="100"
                                        value="<?= htmlspecialchars($filters['keyword']) ?>">
                                </div>

                                <!-- Day -->
                                <div class="col-md-3">
                                    <select name="day" class="form-control">
                                        <option value="">-- Chọn thứ --</option>

                                        <?php
                                        $days = [
                                            2 => 'Thứ 2',
                                            3 => 'Thứ 3',
                                            4 => 'Thứ 4',
                                            5 => 'Thứ 5',
                                            6 => 'Thứ 6',
                                            7 => 'Thứ 7',
                                            1 => 'Chủ nhật'
                                        ];
                                        ?>

                                        <?php foreach ($days as $value => $label): ?>
                                            <option value="<?= $value ?>" <?= $filters['day'] == $value ? 'selected' : '' ?>>
                                                <?= $label ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Status -->
                                <div class="col-md-2">
                                    <select name="status" class="form-control">
                                        <option value="">-- Trạng thái --</option>

                                        <option value="active" <?= $filters['status'] == 'active' ? 'selected' : '' ?>>
                                            Hoạt động
                                        </option>

                                        <option value="inactive" <?= $filters['status'] == 'inactive' ? 'selected' : '' ?>>
                                            Ngưng
                                        </option>
                                    </select>
                                </div>

                                <!-- Button -->
                                <div class="col-md-4">
                                    <button class="btn btn-primary">
                                        <i class="bi bi-funnel"></i> Lọc
                                    </button>

                                    <a href="?module=schedule" class="btn btn-secondary">
                                        Reset
                                    </a>
                                </div>

                            </div>
                        </form>

                        <!-- MAIN -->
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>STT</th>
                                        <th>Tên</th>
                                        <th>Ngày học</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach ($schedules as $i => $s): ?>
                                        <tr>
                                            <td><?= $offset + $i + 1 ?></td>

                                            <td><?= $s['name'] ?></td>

                                            <td><?= $s['days'] ?></td>

                                            <td>
                                                <?php if ($s['status'] == 'active'): ?>
                                                    <span class="badge bg-success">
                                                        Hoạt động
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">
                                                        Ngưng hoạt động
                                                    </span>
                                                <?php endif; ?>
                                            </td>

                                            <td>
                                                <a href="?module=schedule&action=edit&id=<?= $s['schedule_id'] ?>"
                                                    class="btn btn-warning btn-sm">
                                                    Sửa
                                                </a>

                                                <?php if ($s['status'] == 'active'): ?>
                                                    <a href="?module=schedule&action=inactive&id=<?= $s['schedule_id'] ?>"
                                                        class="btn btn-danger btn-sm"
                                                        onclick="return confirm('Ngưng hoạt động lịch học này?')">
                                                        Ngưng hoạt động
                                                    </a>
                                                <?php else: ?>
                                                    <a href="?module=schedule&action=active&id=<?= $s['schedule_id'] ?>"
                                                        class="btn btn-success btn-sm"
                                                        onclick="return confirm('Khôi phục lịch học này?')">
                                                        Kích hoạt
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <!-- INFO -->
                            <div class="text-center mt-2">
                                Hiển thị
                                <?= $total > 0 ? $offset + 1 : 0 ?>
                                -
                                <?= min($offset + $limit, $total) ?>
                                /
                                <?= $total ?> lịch học
                            </div>
                            <?php if ($totalPages > 1): ?>
                                <nav class="mt-3">
                                    <ul class="pagination justify-content-center">

                                        <!-- PREV -->
                                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                            <a class="page-link" href="?module=schedule
                                                &page=<?= $page - 1 ?>
                                                &keyword=<?= urlencode($filters['keyword']) ?>
                                                &day=<?= urlencode($filters['day']) ?>
                                                &status=<?= urlencode($filters['status']) ?>">
                                                «
                                            </a>
                                        </li>

                                        <?php
                                        $start = max(1, $page - 2);
                                        $end = min($totalPages, $page + 2);
                                        ?>

                                        <!-- PAGE -->
                                        <?php for ($i = $start; $i <= $end; $i++): ?>
                                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                                <a class="page-link" href="?module=schedule
                                                    &page=<?= $i ?>
                                                    &keyword=<?= urlencode($filters['keyword']) ?>
                                                    &day=<?= urlencode($filters['day']) ?>
                                                    &status=<?= urlencode($filters['status']) ?>">
                                                    <?= $i ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>

                                        <!-- NEXT -->
                                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                            <a class="page-link" href="?module=schedule
                                                &page=<?= $page + 1 ?>
                                                &keyword=<?= urlencode($filters['keyword']) ?>
                                                &day=<?= urlencode($filters['day']) ?>
                                                &status=<?= urlencode($filters['status']) ?>">
                                                »
                                            </a>
                                        </li>

                                    </ul>
                                </nav>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
        </section>
    </div>
</div>
