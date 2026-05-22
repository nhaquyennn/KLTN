<div id="main">
    <header class="mb-3">
        <a href="#" class="burger-btn d-block d-xl-none">
            <i class="bi bi-justify fs-3"></i>
        </a>
    </header>

    <div class="page-heading">
        <!-- TITLE -->
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 mb-3">
                    <?php if (!empty($pageTitle)): ?>
                        <h3><?= htmlspecialchars($pageTitle) ?></h3>
                        <?php if (!empty($teacher)): ?>
                            <div class="text-muted"><?= htmlspecialchars($teacher['email'] ?? '') ?></div>
                        <?php endif; ?>
                    <?php else: ?>
                    <h3>Lịch sử dạy</h3>
                    <?php endif; ?>
                </div>
            </div>

            <!-- BREADCRUMB -->
            <div class="row">
                <div class="col-12">
                    <nav class="breadcrumb-header">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="<?= BASE_URL ?>?module=dashboard&action=index">Trang chủ</a>
                            </li>
                            <li class="breadcrumb-item active">Lịch sử dạy</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <!-- MAIN -->
        <section class="section">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body"> <!-- Thêm card-body để padding đẹp hơn -->

                            <!-- BỘ LỌC -->
                            <form method="GET" class="mb-4">
                                <input type="hidden" name="module" value="teacher">
                                <input type="hidden" name="action" value="<?= htmlspecialchars($filterAction ?? 'history') ?>">
                                <?php if (!empty($teacher['teacher_id'])): ?>
                                    <input type="hidden" name="id" value="<?= (int) $teacher['teacher_id'] ?>">
                                <?php endif; ?>

                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <select name="month" class="form-select">
                                            <option value="">-- Chọn tất cả tháng --</option>
                                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                                <option value="<?= $m ?>" <?= ($month == $m) ? 'selected' : '' ?>>Tháng
                                                    <?= $m ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select name="year" class="form-select">
                                            <?php
                                            $currentYear = date('Y');
                                            for ($y = $currentYear; $y >= $currentYear - 2; $y--):
                                                ?>
                                                <option value="<?= $y ?>" <?= (($year ?? date('Y')) == $y) ? 'selected' : '' ?>>Năm <?= $y ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-primary">Lọc dữ liệu</button>
                                        <a href="<?= htmlspecialchars($resetUrl ?? '?module=teacher&action=history') ?>" class="btn btn-secondary">Đặt lại</a>
                                        <?php if (!empty($teacher)): ?>
                                            <a href="?module=teacher&action=index" class="btn btn-outline-secondary">Quay lại danh sách</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </form>

                            <!-- BẢNG DỮ LIỆU -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="">
                                        <tr>
                                            <th>STT</th>
                                            <th>Lớp</th>
                                            <th>Thời gian</th>
                                            <th>Ngày dạy</th>
                                            <th>Vai trò</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($data)): ?>
                                            <?php
                                            $stt = 1;
                                            foreach ($data as $row):
                                                ?>
                                                <tr>
                                                    <td><?= $stt++ ?></td>
                                                    <td><strong><?= htmlspecialchars($row['class_display_name']) ?></strong>
                                                    </td>
                                                    <td><?= htmlspecialchars($row['time_range']) ?></td>
                                                    <td><?= date('d/m/Y', strtotime($row['session_date'])) ?></td>
                                                    <td><?= ($row['role'] === 'main') ? 'Chính' : 'Trợ giảng' ?></td>
                                                </tr>
                                            <?php endforeach; ?>

                                            <!-- HÀNG TỔNG CỘNG -->
                                            <tr class="table-warning">
                                                <td colspan="4" class="text-end"><strong>Tổng số buổi dạy trong danh
                                                        sách:</strong></td>
                                                <td class="text-center"><strong><?= count($data) ?> buổi</strong></td>
                                            </tr>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center">Không có dữ liệu cho thời gian này.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
