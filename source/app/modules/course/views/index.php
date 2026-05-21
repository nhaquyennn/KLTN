<div id="main">
    <header class="mb-3">
        <a href="#" class="burger-btn d-block d-xl-none">
            <i class="bi bi-justify fs-3"></i>
        </a>
    </header>

    <div class="page-heading">
        <!-- Breadcrumb Start -->
        <div class="page-title mb-3">

            <!-- HÀNG 1 -->
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                <h3 class="mb-0">Danh sách khóa học</h3>

                <a href="?module=course&action=create" class="btn btn-success">
                    <i class="bi bi-plus"></i> Thêm khóa học
                </a>
            </div>

            <!-- HÀNG 2 -->
            <nav aria-label="breadcrumb" class="breadcrumb-header">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="<?= BASE_URL ?>?module=dashboard&action=index">
                            Trang chủ
                        </a>
                    </li>

                    <li class="breadcrumb-item active">
                        Khóa học
                    </li>
                </ol>
            </nav>

        </div>
        <!-- Breadcrumb End -->


        <!-- Main Start -->
        <section class="section">
            <div class="row" id="table-hover-row">
                <div class="col-12">

                    <!-- Filter Start-->
                    <form method="GET" class="mb-3">
                        <input type="hidden" name="module" value="course">

                        <div class="row">

                            <!-- KEYWORD -->
                            <div class="col-md-4">
                                <input type="text" name="keyword" class="form-control" placeholder="Tìm tên khóa..."
                                    value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>">
                            </div>

                            <!-- STATUS -->
                            <div class="col-md-3">
                                <select name="status" class="form-control">
                                    <option value="">-- Trạng thái --</option>
                                    <option value="active" <?= ($_GET['status'] ?? '') == 'active' ? 'selected' : '' ?>>
                                        Active
                                    </option>
                                    <option value="inactive" <?= ($_GET['status'] ?? '') == 'inactive' ? 'selected' : '' ?>>
                                        Inactive</option>
                                </select>
                            </div>

                            <!-- BUTTON -->
                            <div class="col-md-5 d-flex gap-2">
                                <button class="btn btn-primary">
                                    <i class="bi bi-search"></i> Lọc
                                </button>

                                <!-- RESET -->
                                <a href="?module=course" class="btn btn-secondary">
                                    <i class="bi bi-arrow-clockwise"></i> Reset
                                </a>
                            </div>

                        </div>
                    </form>
                    <!-- Filter Start-->

                    <!-- Table Content Start -->
                    <div class="card">
                        <div class="card-content">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>STT</th>
                                            <th>TÊN KHÓA</th>
                                            <th>MÔ TẢ</th>
                                            <th>TRẠNG THÁI</th>
                                            <th class="text-center">ACTION</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php if (!empty($courses)): ?>
                                            <?php foreach ($courses as $index => $c): ?>
                                                <tr>
                                                    <!-- STT -->
                                                    <td><?= $offset + $index + 1 ?></td>

                                                    <!-- NAME -->
                                                    <td><?= htmlspecialchars($c['name']) ?></td>

                                                    <!-- DESCRIPTION -->
                                                    <td title="<?= htmlspecialchars($c['description']) ?>">
                                                        <?= !empty($c['description'])
                                                            ? htmlspecialchars(mb_strimwidth($c['description'], 0, 50, "..."))
                                                            : '<span class="text-muted">Không có</span>' ?>
                                                    </td>

                                                    <!-- STATUS -->
                                                    <td>
                                                        <?php if ($c['status'] == 'active'): ?>
                                                            <span class="badge bg-success">Active</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">Inactive</span>
                                                        <?php endif; ?>
                                                    </td>

                                                    <!-- ACTION -->
                                                    <td class="text-center">
                                                        <a href="?module=course&action=edit&id=<?= $c['course_id'] ?>"
                                                            class="btn btn-sm btn-warning">
                                                            Edit
                                                        </a>

                                                        <a href="?module=course&action=delete&id=<?= $c['course_id'] ?>"
                                                            class="btn btn-sm btn-danger"
                                                            onclick="return confirm('Xóa khóa này?')">
                                                            Delete
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center">Không có dữ liệu</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- Table Content End -->
                    <!-- SHOWING INFO -->
                    <div class="text-center mt-2">
                        Hiển thị
                        <?= $total > 0 ? $offset + 1 : 0 ?>
                        -
                        <?= min($offset + $limit, $total) ?>
                        /
                        <?= $total ?> khóa học
                    </div>

                    <!-- PAGINATION -->
                    <?php if ($totalPages > 1): ?>
                        <nav class="mt-3">
                            <ul class="pagination justify-content-center">

                                <!-- PREV -->
                                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?module=course&page=<?= $page - 1 ?>
                                    &keyword=<?= urlencode($keyword) ?>
                                    &status=<?= $status ?>">
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
                                        <a class="page-link"
                                            href="?module=course&page=<?= $i ?>&keyword=<?= urlencode(trim($keyword)) ?>&status=<?= trim($status) ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <!-- NEXT -->
                                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?module=course&page=<?= $page + 1 ?>
                        &keyword=<?= urlencode($keyword) ?>
                        &status=<?= $status ?>">
                                        »
                                    </a>
                                </li>

                            </ul>
                        </nav>
                    <?php endif; ?>

                </div>
            </div>
        </section>
        <!-- Main End -->
    </div>
</div>