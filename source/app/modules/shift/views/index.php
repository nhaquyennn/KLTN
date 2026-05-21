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
                <h3 class="mb-0">Danh sách ca học</h3>

                <a href="?module=shift&action=create" class="btn btn-success">
                    <i class="bi bi-plus"></i> Thêm ca học
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
                        Ca học
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
                            <input type="hidden" name="module" value="shift">

                            <div class="row g-2">

                                <!-- KEYWORD -->
                                <div class="col-md-3">
                                    <input type="text" name="keyword" class="form-control" placeholder="Tìm ca học..."
                                        value="<?= htmlspecialchars($filters['keyword']) ?>">
                                </div>

                                <!-- BUTTON -->
                                <div class="col-md-3 d-flex gap-2">
                                    <button class="btn btn-primary">
                                        Lọc
                                    </button>

                                    <a href="?module=shift" class="btn btn-secondary">

                                        Reset
                                    </a>
                                </div>

                            </div>
                        </form>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>STT</th>
                                        <th>Tên ca</th>
                                        <th>Bắt đầu</th>
                                        <th>Kết thúc</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach ($shifts as $index => $s): ?>
                                        <tr>
                                            <td><?= $offset + $index + 1 ?></td>
                                            <td><?= $s['name'] ?></td>
                                            <td><?= $s['start_time'] ?></td>
                                            <td><?= $s['end_time'] ?></td>
                                            <td>
                                                <a href="?module=shift&action=edit&id=<?= $s['shift_id'] ?>"
                                                    class="btn btn-warning btn-sm">Edit</a>
                                                <a href="?module=shift&action=delete&id=<?= $s['shift_id'] ?>"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Xóa?')">Delete</a>
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
                                <?= $total ?> ca học
                            </div>
                            <!-- PAGINATION -->
                            <?php if ($totalPages > 1): ?>

                                <?php
                                $query = http_build_query([
                                    'module' => 'shift',
                                    'keyword' => $filters['keyword']
                                ]);

                                $start = max(1, $page - 2);
                                $end = min($totalPages, $page + 2);
                                ?>

                                <nav class="mt-3">
                                    <ul class="pagination justify-content-center">

                                        <!-- PREV -->
                                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">

                                            <a class="page-link" href="?<?= $query ?>&page=<?= $page - 1 ?>">

                                                «
                                            </a>

                                        </li>

                                        <!-- PAGE -->
                                        <?php for ($i = $start; $i <= $end; $i++): ?>

                                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">

                                                <a class="page-link" href="?<?= $query ?>&page=<?= $i ?>">

                                                    <?= $i ?>
                                                </a>

                                            </li>

                                        <?php endfor; ?>

                                        <!-- NEXT -->
                                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">

                                            <a class="page-link" href="?<?= $query ?>&page=<?= $page + 1 ?>">

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