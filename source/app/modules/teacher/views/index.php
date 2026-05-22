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

                <h3 class="mb-0">Danh sách giảng viên</h3>

                <a href="?module=teacher&action=create" class="btn btn-primary">

                    <i class="bi bi-plus"></i>
                    Thêm giảng viên
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
                        Giảng viên
                    </li>

                </ol>

            </nav>

        </div>
        <!-- Breadcrumb End -->

        <!-- Table start -->
        <section class="section">
            <div class="row" id="table-hover-row">
                <div class="col-12">

                    <!-- Filter Start-->
                    <div class="filter-box mb-3">
                        <form method="GET" class="mb-3">
                            <input type="hidden" name="module" value="teacher">
                            <input type="hidden" name="action" value="index">
                            <?php if (!empty($_GET['level_id'])): ?>
                                <input type="hidden" name="level_id" value="<?= htmlspecialchars($_GET['level_id']) ?>">
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-md-3">
                                    <input type="text" name="keyword" class="form-control" placeholder="Tìm tên / email" maxlength="100"
                                        value="<?= $_GET['keyword'] ?? '' ?>">
                                </div>

                                <div class="col-md-2">
                                    <input type="text" name="specialization_name" class="form-control" maxlength="100"
                                        placeholder="Chuyên môn" value="<?= $_GET['specialization_name_name'] ?? '' ?>">
                                </div>

                                <div class="col-md-2">
                                    <select name="salary_type" class="form-control">
                                        <option value="">-- Loại lương --</option>
                                        <option value="per_session" <?= ($_GET['salary_type'] ?? '') == 'per_session' ? 'selected' : '' ?>>
                                            Theo buổi
                                        </option>
                                        <option value="fixed" <?= ($_GET['salary_type'] ?? '') == 'fixed' ? 'selected' : '' ?>>
                                            Cố định
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <select name="status" class="form-control">
                                        <option value="">-- Trạng thái --</option>
                                        <option value="1" <?= ($_GET['status'] ?? '') == '1' ? 'selected' : '' ?>>
                                            Hoạt động
                                        </option>
                                        <option value="0" <?= ($_GET['status'] ?? '') == '0' ? 'selected' : '' ?>>
                                            Ngưng hoạt động
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <button class="btn btn-primary">
                                        <i class="bi bi-search"></i> Lọc
                                    </button>
                                    <a href="?module=teacher" class="btn btn-secondary">
                                        Đặt lại
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                    <!-- Filter End-->

                    <div class="card">

                        <div class="card-content">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>STT</th>
                                            <th>TÊN</th>
                                            <th>EMAIL</th>
                                            <th>CHUYÊN MÔN</th>
                                            <th>NGÀY VÀO</th>
                                            <th>LƯƠNG</th>
                                            <th>TRẠNG THÁI</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($teachers)): ?>
                                            <?php $i = ($page - 1) * 10 + 1; // STT ?>
                                            <?php foreach ($teachers as $t): ?>
                                                <tr>
                                                    <td class="text-center"><?= $i++ ?></td>
                                                    <td class="text-bold-500"><?= htmlspecialchars($t['name']) ?></td>
                                                    <td><?= htmlspecialchars($t['email']) ?></td>
                                                    <td><?= htmlspecialchars($t['specialization_name'] ?? '') ?></td>
                                                    <td><?= $t['hire_date'] ?></td>
                                                    <td>
                                                        <?php if (!empty($t['level_name'])): ?>
                                                            <div class="fw-semibold"><?= htmlspecialchars($t['level_name']) ?></div>
                                                        <?php endif; ?>
                                                        <?php if ($t['salary_type'] == 'per_session'): ?>
                                                            <?= number_format($t['salary_value']) ?> / buổi
                                                        <?php else: ?>
                                                            <?= number_format($t['salary_value']) ?> / tháng
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($t['status'] == 1): ?>
                                                            <span class="badge bg-success">Hoạt động</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">Ngưng hoạt động</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="?module=teacher&action=teaching_history&id=<?= $t['teacher_id'] ?>"
                                                            class="btn btn-sm btn-info">
                                                            <i class="bi bi-clock-history"></i> Lịch sử dạy
                                                        </a>

                                                        <?php if ($t['status'] == 1): ?>
                                                            <!-- ACTIVE -->
                                                            <a href="?module=teacher&action=edit&id=<?= $t['teacher_id'] ?>"
                                                                class="btn btn-sm btn-warning">
                                                                <i class="bi bi-pencil"></i> Sửa
                                                            </a>

                                                            <a href="?module=teacher&action=delete&id=<?= $t['teacher_id'] ?>"
                                                                class="btn btn-sm btn-secondary"
                                                                onclick="return confirm('Ngưng hoạt động giảng viên này?')">
                                                                <i class="bi bi-person-x"></i> Ngưng
                                                            </a>

                                                        <?php else: ?>
                                                            <!-- INACTIVE -->
                                                            <a href="?module=teacher&action=restore&id=<?= $t['teacher_id'] ?>"
                                                                class="btn btn-sm btn-success"
                                                                onclick="return confirm('Khôi phục giảng viên này?')">
                                                                <i class="bi bi-arrow-repeat"></i> Khôi phục
                                                            </a>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="8">Không có giảng viên nào</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination Start-->
                            <nav>
                                <ul class="pagination justify-content-end mt-3">

                                    <!-- prev -->
                                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?module=teacher&page=<?= $page - 1 ?>
                                            &keyword=<?= $_GET['keyword'] ?? '' ?>
                                            &specialization_name=<?= $_GET['specialization_name'] ?? '' ?>
                                            &salary_type=<?= $_GET['salary_type'] ?? '' ?>
                                            &level_id=<?= $_GET['level_id'] ?? '' ?>
                                            &status=<?= $_GET['status'] ?? '' ?>">
                                            «
                                        </a>
                                    </li>

                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                            <a class="page-link" href="?module=teacher&page=<?= $i ?>
                                                &keyword=<?= $_GET['keyword'] ?? '' ?>
                                                &specialization_name=<?= $_GET['specialization_name'] ?? '' ?>
                                                &salary_type=<?= $_GET['salary_type'] ?? '' ?>
                                                &level_id=<?= $_GET['level_id'] ?? '' ?>
                                                &status=<?= $_GET['status'] ?? '' ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <!-- next -->
                                    <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?module=teacher&page=<?= $page + 1 ?>
                                            &keyword=<?= $_GET['keyword'] ?? '' ?>
                                            &specialization_name=<?= $_GET['specialization_name'] ?? '' ?>
                                            &salary_type=<?= $_GET['salary_type'] ?? '' ?>
                                            &level_id=<?= $_GET['level_id'] ?? '' ?>
                                            &status=<?= $_GET['status'] ?? '' ?>">
                                            »
                                        </a>
                                    </li>

                                </ul>
                            </nav>
                            <!-- Pagination End-->

                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Table end -->
    </div>
</div>
