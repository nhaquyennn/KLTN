<div id="main">
    <header class="mb-3">
        <a href="#" class="burger-btn d-block d-xl-none">
            <i class="bi bi-justify fs-3"></i>
        </a>
    </header>

    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h3>Bảng lương giảng viên</h3>
                <p class="text-muted mb-0">Tính lương theo số buổi dạy, bậc lương, thưởng và phạt.</p>
            </div>

            <form method="GET" class="d-flex gap-2 flex-wrap">
                <input type="hidden" name="module" value="teacher">
                <input type="hidden" name="action" value="payroll">
                <select name="month" class="form-select" style="width:110px">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= (int) $month === $m ? 'selected' : '' ?>>
                            Tháng <?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>
                        </option>
                    <?php endfor; ?>
                </select>
                <input type="number" name="year" class="form-control" value="<?= (int) $year ?>" style="width:110px">
                <button class="btn btn-outline-primary" type="submit">
                    <i class="bi bi-filter"></i>
                    Lọc
                </button>
            </form>
        </div>
    </div>

    <div class="page-content">
        <div class="card mb-4">
            <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h5 class="mb-1">Bảng lương tháng <?= str_pad((int) $month, 2, '0', STR_PAD_LEFT) ?>/<?= (int) $year ?></h5>
                    <small class="text-muted">Nhấn tính lương để cập nhật lại dữ liệu từ lịch dạy và thưởng/phạt.</small>
                </div>

                <form method="POST" class="m-0">
                    <input type="hidden" name="month" value="<?= (int) $month ?>">
                    <input type="hidden" name="year" value="<?= (int) $year ?>">
                    <button type="submit" name="calculate" class="btn btn-primary">
                        <i class="bi bi-calculator"></i>
                        Tính lương
                    </button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Danh sách lương</h4>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Giảng viên</th>
                                <th>Bậc lương</th>
                                <th>Số tiền bậc</th>
                                <th>Loại lương</th>
                                <th>Số buổi</th>
                                <th>Lương cơ bản</th>
                                <th>Thưởng</th>
                                <th>Phạt</th>
                                <th>Thực nhận</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (!empty($payroll_data)): ?>
                                <?php foreach ($payroll_data as $index => $row): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= htmlspecialchars($row['teacher_name'] ?? $row['name'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($row['level_name'] ?? 'Chưa có bậc') ?></td>
                                        <td class="fw-semibold"><?= number_format($row['level_amount'] ?? 0) ?> VNĐ</td>
                                        <td>
                                            <?php if (($row['salary_type'] ?? '') === 'fixed'): ?>
                                                <span class="badge bg-primary">Cố định</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Theo buổi</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= (int) ($row['total_sessions'] ?? 0) ?></td>
                                        <td><?= number_format($row['base_salary'] ?? 0) ?> VNĐ</td>
                                        <td class="text-success">+<?= number_format($row['total_bonus'] ?? 0) ?> VNĐ</td>
                                        <td class="text-danger">-<?= number_format($row['total_penalty'] ?? 0) ?> VNĐ</td>
                                        <td class="fw-bold text-primary"><?= number_format($row['final_salary'] ?? 0) ?> VNĐ</td>
                                        <td>
                                            <select
                                                class="form-select form-select-sm payroll-status"
                                                data-id="<?= (int) ($row['id'] ?? 0) ?>"
                                                style="min-width:130px">
                                                <option value="draft" <?= ($row['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Nháp</option>
                                                <option value="confirmed" <?= ($row['status'] ?? '') === 'confirmed' ? 'selected' : '' ?>>Đã xác nhận</option>
                                                <option value="paid" <?= ($row['status'] ?? '') === 'paid' ? 'selected' : '' ?>>Đã thanh toán</option>
                                            </select>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="11" class="text-center text-muted py-4">
                                        Chưa có dữ liệu lương. Bấm "Tính lương" để tạo bảng lương tháng này.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.payroll-status').forEach(function (select) {
    select.addEventListener('change', async function () {
        const current = this;
        const previousValue = current.dataset.previousValue || current.value;
        current.disabled = true;

        try {
            const response = await fetch('?module=teacher&action=updatePayrollStatus', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: current.dataset.id,
                    status: current.value
                })
            });

            const data = await response.json();

            if (!data.success) {
                current.value = previousValue;
                alert('Không thể cập nhật trạng thái lương.');
                return;
            }

            current.dataset.previousValue = current.value;
        } catch (error) {
            current.value = previousValue;
            console.error(error);
            alert('Không thể kết nối máy chủ.');
        } finally {
            current.disabled = false;
        }
    });

    select.dataset.previousValue = select.value;
});
</script>
