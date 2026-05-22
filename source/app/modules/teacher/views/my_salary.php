<div id="main">
    <header class="mb-3">
        <a href="#" class="burger-btn d-block d-xl-none">
            <i class="bi bi-justify fs-3"></i>
        </a>
    </header>

    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h3>Lương của tôi</h3>
                <p class="text-muted mb-0">Xem lương theo tháng, số buổi dạy, bậc lương, thưởng và phạt.</p>
            </div>

            <form method="GET" class="d-flex gap-2 flex-wrap">
                <input type="hidden" name="module" value="teacher">
                <input type="hidden" name="action" value="my_salary">
                <select name="month" class="form-select" style="width:110px">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= (int) $month === $m ? 'selected' : '' ?>>
                            Tháng <?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>
                        </option>
                    <?php endfor; ?>
                </select>
                <input type="number" name="year" class="form-control" value="<?= (int) $year ?>" min="2000" max="2100" step="1" style="width:110px">
                <button class="btn btn-outline-primary" type="submit">
                    <i class="bi bi-filter"></i>
                    Xem
                </button>
            </form>
        </div>
    </div>

    <div class="page-content">
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-muted small">Số buổi dạy</div>
                        <h4 class="mb-0"><?= (int) ($payroll['total_sessions'] ?? 0) ?> buổi</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-muted small">Bậc lương</div>
                        <h4 class="mb-0"><?= htmlspecialchars($payroll['level_name'] ?? 'Chưa có') ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-muted small">Số tiền bậc</div>
                        <h4 class="mb-0"><?= number_format($payroll['level_amount'] ?? 0) ?> VNĐ</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-muted small">Thực nhận</div>
                        <h4 class="mb-0 text-primary"><?= number_format($payroll['final_salary'] ?? 0) ?> VNĐ</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Chi tiết lương tháng <?= str_pad((int) $month, 2, '0', STR_PAD_LEFT) ?>/<?= (int) $year ?></h4>
            </div>

            <div class="card-body">
                <?php if ($payroll): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <tbody>
                                <tr>
                                    <th style="width:260px">Loại lương</th>
                                    <td><?= ($payroll['salary_type'] ?? '') === 'fixed' ? 'Cố định' : 'Theo buổi' ?></td>
                                </tr>
                                <tr>
                                    <th>Bậc lương</th>
                                    <td><?= htmlspecialchars($payroll['level_name'] ?? 'Chưa có') ?></td>
                                </tr>
                                <tr>
                                    <th>Số tiền bậc lương</th>
                                    <td class="fw-semibold"><?= number_format($payroll['level_amount'] ?? 0) ?> VNĐ</td>
                                </tr>
                                <tr>
                                    <th>Số buổi dạy trong tháng</th>
                                    <td><?= (int) ($payroll['total_sessions'] ?? 0) ?> buổi</td>
                                </tr>
                                <tr>
                                    <th>Lương cơ bản</th>
                                    <td><?= number_format($payroll['base_salary'] ?? 0) ?> VNĐ</td>
                                </tr>
                                <tr>
                                    <th>Thưởng</th>
                                    <td class="text-success">+<?= number_format($payroll['total_bonus'] ?? 0) ?> VNĐ</td>
                                </tr>
                                <tr>
                                    <th>Phạt</th>
                                    <td class="text-danger">-<?= number_format($payroll['total_penalty'] ?? 0) ?> VNĐ</td>
                                </tr>
                                <tr>
                                    <th>Thực nhận</th>
                                    <td class="fw-bold text-primary"><?= number_format($payroll['final_salary'] ?? 0) ?> VNĐ</td>
                                </tr>
                                <tr>
                                    <th>Trạng thái</th>
                                    <td>
                                        <?php
                                            $statusLabels = [
                                                'draft' => 'Nháp',
                                                'confirmed' => 'Đã xác nhận',
                                                'paid' => 'Đã thanh toán'
                                            ];
                                            echo htmlspecialchars($statusLabels[$payroll['status'] ?? 'draft'] ?? 'Nháp');
                                        ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-4">Chưa có dữ liệu lương.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Lịch sử lương</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Tháng</th>
                                <th>Bậc</th>
                                <th>Số tiền bậc</th>
                                <th>Số buổi</th>
                                <th>Lương cơ bản</th>
                                <th>Thưởng</th>
                                <th>Phạt</th>
                                <th>Thực nhận</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($history)): ?>
                                <?php foreach ($history as $row): ?>
                                    <tr>
                                        <td><?= str_pad((int) $row['month'], 2, '0', STR_PAD_LEFT) ?>/<?= (int) $row['year'] ?></td>
                                        <td><?= htmlspecialchars($row['level_name'] ?? 'Chưa có') ?></td>
                                        <td><?= number_format($row['level_amount'] ?? 0) ?> VNĐ</td>
                                        <td><?= (int) ($row['total_sessions'] ?? 0) ?></td>
                                        <td><?= number_format($row['base_salary'] ?? 0) ?> VNĐ</td>
                                        <td class="text-success">+<?= number_format($row['total_bonus'] ?? 0) ?></td>
                                        <td class="text-danger">-<?= number_format($row['total_penalty'] ?? 0) ?></td>
                                        <td class="fw-bold"><?= number_format($row['final_salary'] ?? 0) ?> VNĐ</td>
                                        <td><?= htmlspecialchars($statusLabels[$row['status'] ?? 'draft'] ?? 'Nháp') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">Chưa có lịch sử lương.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
