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

                <h3 class="mb-0">Danh sách chuyên môn</h3>

                <a href="?module=specialization&action=create"
                    class="btn btn-success">

                    <i class="bi bi-plus"></i>
                    Thêm chuyên môn
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

                    <li class="breadcrumb-item">

                        <a href="<?= BASE_URL ?>?module=teacher&action=index">
                            Quản lý giảng viên
                        </a>

                    </li>

                    <li class="breadcrumb-item active">
                        Chuyên môn
                    </li>

                </ol>

            </nav>

        </div>

        <!-- MAIN -->
        <section class="section">

            <div class="row">

                <div class="col-12">

                    <div class="card">

                        <!-- FILTER -->
                        <form method="GET" class="mb-3">

                            <input type="hidden"
                                name="module"
                                value="specialization">

                            <div class="row g-2">

                                <!-- KEYWORD -->
                                <div class="col-md-4">

                                    <input type="text"
                                        name="keyword"
                                        class="form-control"
                                        placeholder="Tìm chuyên môn..."
                                        value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>">

                                </div>

                                <!-- STATUS -->
                                <div class="col-md-3">

                                    <select name="status"
                                        class="form-control">

                                        <option value="">
                                            -- Trạng thái --
                                        </option>

                                        <option value="active"
                                            <?= (($_GET['status'] ?? '') == 'active') ? 'selected' : '' ?>>

                                            Hoạt động
                                        </option>

                                        <option value="inactive"
                                            <?= (($_GET['status'] ?? '') == 'inactive') ? 'selected' : '' ?>>

                                            Ngưng hoạt động
                                        </option>

                                    </select>

                                </div>

                                <!-- BUTTON -->
                                <div class="col-md-5 d-flex gap-2">

                                    <button class="btn btn-primary">

                                        <i class="bi bi-search"></i>
                                        Lọc
                                    </button>

                                    <a href="?module=specialization"
                                        class="btn btn-secondary">

                                        Reset
                                    </a>

                                </div>

                            </div>

                        </form>

                        <!-- TABLE -->
                        <div class="table-responsive">

                            <table class="table table-hover">

                                <thead>

                                    <tr>

                                        <th>STT</th>

                                        <th>Tên chuyên môn</th>

                                        <th>Mô tả</th>

                                        <th>Trạng thái</th>

                                        <th width="180">Action</th>

                                    </tr>

                                </thead>

                                <tbody>

                                    <?php if (!empty($specializations)): ?>

                                        <?php $stt = $offset + 1; ?>

                                        <?php foreach ($specializations as $s): ?>

                                            <tr>

                                                <td>
                                                    <?= $stt++ ?>
                                                </td>

                                                <td>
                                                    <?= htmlspecialchars($s['name']) ?>
                                                </td>

                                                <td>

                                                    <?= !empty($s['description'])
                                                        ? htmlspecialchars($s['description'])
                                                        : '<span class="text-muted">Không có mô tả</span>' ?>

                                                </td>

                                                <td>

                                                    <?= $s['status'] == 'active'
                                                        ? '<span class="badge bg-success">Hoạt động</span>'
                                                        : '<span class="badge bg-danger">Ngưng hoạt động</span>' ?>

                                                </td>

                                                <td>

                                                    <!-- EDIT -->
                                                    <a href="?module=specialization&action=edit&id=<?= $s['specialization_id'] ?>"
                                                        class="btn btn-warning btn-sm">

                                                        <i class="bi bi-pencil"></i>
                                                        Sửa
                                                    </a>

                                                    <!-- DELETE -->
                                                    <a href="?module=specialization&action=delete&id=<?= $s['specialization_id'] ?>"
                                                        class="btn btn-danger btn-sm"
                                                        onclick="return confirm('Xóa chuyên môn này?')">

                                                        <i class="bi bi-trash"></i>
                                                        Xóa
                                                    </a>

                                                </td>

                                            </tr>

                                        <?php endforeach; ?>

                                    <?php else: ?>

                                        <tr>

                                            <td colspan="5"
                                                class="text-center text-muted py-4">

                                                Không có dữ liệu chuyên môn

                                            </td>

                                        </tr>

                                    <?php endif; ?>

                                </tbody>

                            </table>

                            <!-- INFO -->
                            <div class="text-center mt-2">

                                Hiển thị
                                <?= $total > 0 ? $offset + 1 : 0 ?>
                                -
                                <?= min($offset + $limit, $total) ?>
                                /
                                <?= $total ?> chuyên môn

                            </div>

                            <!-- PAGINATION -->
                            <?php if ($totalPages > 1): ?>

                                <?php
                                $query = http_build_query([
                                    'module' => 'specialization',
                                    'keyword' => $_GET['keyword'] ?? '',
                                    'status' => $_GET['status'] ?? ''
                                ]);

                                $start = max(1, $page - 2);
                                $end = min($totalPages, $page + 2);
                                ?>

                                <nav class="mt-3">

                                    <ul class="pagination justify-content-center">

                                        <!-- PREV -->
                                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">

                                            <a class="page-link"
                                                href="?<?= $query ?>&page=<?= $page - 1 ?>">

                                                «
                                            </a>

                                        </li>

                                        <!-- PAGE -->
                                        <?php for ($i = $start; $i <= $end; $i++): ?>

                                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">

                                                <a class="page-link"
                                                    href="?<?= $query ?>&page=<?= $i ?>">

                                                    <?= $i ?>

                                                </a>

                                            </li>

                                        <?php endfor; ?>

                                        <!-- NEXT -->
                                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">

                                            <a class="page-link"
                                                href="?<?= $query ?>&page=<?= $page + 1 ?>">

                                                »
                                            </a>

                                        </li>

                                    </ul>

                                </nav>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            </div>

        </section>

    </div>

</div>