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
                <h3 class="mb-0">Danh sách gói học</h3>

                <a href="?module=package&action=create" class="btn btn-success">
                    <i class="bi bi-plus"></i> Thêm gói học
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
                        Gói học
                    </li>
                </ol>
            </nav>

        </div>

        <!-- MAIN -->
        <section class="section">
            <div class="row">
                <div class="col-12">

                    <!-- FILTER -->
                    <form method="GET" class="mb-3">
                        <input type="hidden" name="module" value="package">

                        <div class="row">

                            <!-- KEYWORD -->
                            <div class="col-md-3">
                                <input type="text" name="keyword" class="form-control" placeholder="Tìm tên gói..."
                                    value="<?= htmlspecialchars($keyword ?? '') ?>">
                            </div>

                            <!-- COURSE -->
                            <div class="col-md-3">
                                <select name="course_id" class="form-control">
                                    <option value="">-- Khóa học --</option>
                                    <?php foreach ($courses as $c): ?>
                                        <option value="<?= $c['course_id'] ?>" <?= ($course_id == $c['course_id']) ? 'selected' : '' ?>>
                                            <?= $c['name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- STATUS -->
                            <div class="col-md-2">
                                <select name="status" class="form-control">
                                    <option value="">-- Trạng thái --</option>
                                    <option value="active" <?= $status == 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="inactive" <?= $status == 'inactive' ? 'selected' : '' ?>>Inactive
                                    </option>
                                </select>
                            </div>

                            <!-- BUTTON -->
                            <div class="col-md-4 d-flex gap-2">
                                <button class="btn btn-primary">
                                    <i class="bi bi-search"></i> Lọc
                                </button>

                                <a href="?module=package" class="btn btn-secondary">
                                    <i class="bi bi-arrow-clockwise"></i> Reset
                                </a>
                            </div>

                        </div>
                    </form>

                    <!-- TABLE -->
                    <div class="card">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">

                                <thead>
                                    <tr>
                                        <th>STT</th>
                                        <th>TÊN GÓI</th>
                                        <th>KHÓA HỌC</th>
                                        <th>SỐ BUỔI</th>
                                        <th>GIÁ</th>
                                        <th>TRẠNG THÁI</th>
                                        <th class="text-center">ACTION</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php if (!empty($packages)): ?>
                                        <?php foreach ($packages as $index => $p): ?>
                                            <tr>
                                                <td><?= $offset + $index + 1 ?></td>

                                                <td><?= htmlspecialchars($p['name']) ?></td>

                                                <td><?= htmlspecialchars($p['course_name'] ?? '') ?></td>

                                                <td><?= $p['total_sessions'] ?></td>

                                                <td><?= number_format($p['price']) ?> đ</td>

                                                <td>
                                                    <?php if (($p['status'] ?? 'active') == 'active'): ?>
                                                        <span class="badge bg-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Inactive</span>
                                                    <?php endif; ?>
                                                </td>

                                                <td class="text-center">
                                                    <a href="?module=package&action=edit&id=<?= $p['package_id'] ?>"
                                                        class="btn btn-sm btn-warning">Edit</a>

                                                    <a href="?module=package&action=delete&id=<?= $p['package_id'] ?>"
                                                        class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Xóa gói này?')">Delete</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">Không có dữ liệu</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>

                            </table>
                        </div>
                    </div>

                    <!-- INFO -->
                    <div class="text-center mt-2">
                        Hiển thị
                        <?= $total > 0 ? $offset + 1 : 0 ?>
                        -
                        <?= min($offset + $limit, $total) ?>
                        /
                        <?= $total ?> gói học
                    </div>

                    <!-- PAGINATION -->
                    <?php if ($totalPages > 1): ?>
                        <nav class="mt-3">
                            <ul class="pagination justify-content-center">

                                <!-- PREV -->
                                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link"
                                        href="?module=package&page=<?= $page - 1 ?>&keyword=<?= urlencode($keyword) ?>&status=<?= $status ?>&course_id=<?= $course_id ?>">
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
                                            href="?module=package&page=<?= $i ?>&keyword=<?= urlencode($keyword) ?>&status=<?= $status ?>&course_id=<?= $course_id ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <!-- NEXT -->
                                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                    <a class="page-link"
                                        href="?module=package&page=<?= $page + 1 ?>&keyword=<?= urlencode($keyword) ?>&status=<?= $status ?>&course_id=<?= $course_id ?>">
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