<?php
$summary = $summary ?? [];
$dailyRevenue = $dailyRevenue ?? [];
$paymentMethods = $paymentMethods ?? [];
$recentPayments = $recentPayments ?? [];

$money = function ($amount) {
    return number_format((float) $amount, 0, ',', '.') . ' d';
};

$chartLabels = array_map(function ($row) {
    return date('d/m', strtotime($row['revenue_date']));
}, $dailyRevenue);
$chartValues = array_map(function ($row) {
    return (float) $row['total_amount'];
}, $dailyRevenue);
?>

<div id="main">
    <header class="mb-3">
        <a href="#" class="burger-btn d-block d-xl-none"><i class="bi bi-justify fs-3"></i></a>
    </header>

    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
            <div>
                <h3 class="mb-1">Thống kê doanh thu</h3>
                <div class="text-muted">Doanh thu học phí theo các lần thanh toán đã ghi nhận.</div>
            </div>

            <form method="get" class="d-flex align-items-end flex-wrap gap-2">
                <input type="hidden" name="module" value="revenue">
                <input type="hidden" name="action" value="index">
                <div>
                    <label class="form-label mb-1">Từ ngày</label>
                    <input type="date" name="from_date" class="form-control" value="<?= htmlspecialchars($fromDate) ?>">
                </div>
                <div>
                    <label class="form-label mb-1">Đến ngày</label>
                    <input type="date" name="to_date" class="form-control" value="<?= htmlspecialchars($toDate) ?>">
                </div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i> Lọc</button>
            </form>
        </div>

        <?php if (($summary['legacy_untracked'] ?? 0) > 0): ?>
            <div class="alert alert-warning">
                Có <?= $money($summary['legacy_untracked']) ?> tiền thu cũ chưa có dòng trong sổ thu.
                Chạy migration doanh thu để backfill trước khi đối chiếu báo cáo theo ngày.
            </div>
        <?php endif; ?>

        <section class="section">
            <div class="row">
                <?php
                $cards = [
                    ['label' => 'Thu trong kỳ lọc', 'value' => $money($summary['range_revenue'] ?? 0), 'icon' => 'bi-bar-chart-fill', 'tone' => 'blue'],
                    ['label' => 'Thu hôm nay', 'value' => $money($summary['today_revenue'] ?? 0), 'icon' => 'bi-cash-coin', 'tone' => 'green'],
                    ['label' => 'Thu tháng này', 'value' => $money($summary['month_revenue'] ?? 0), 'icon' => 'bi-calendar2-check-fill', 'tone' => 'purple'],
                    ['label' => 'Công nợ học phí', 'value' => $money($summary['tuition_debt'] ?? 0), 'icon' => 'bi-receipt', 'tone' => 'red'],
                    ['label' => 'Lượt thanh toán', 'value' => number_format((int) ($summary['payment_count'] ?? 0), 0, ',', '.'), 'icon' => 'bi-wallet2', 'tone' => 'blue'],
                    ['label' => 'Học viên đã thu trong kỳ', 'value' => number_format((int) ($summary['paid_students'] ?? 0), 0, ',', '.'), 'icon' => 'bi-people-fill', 'tone' => 'green'],
                ];
                ?>
                <?php foreach ($cards as $card): ?>
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card">
                            <div class="card-body px-4 py-4">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <div class="stats-icon <?= htmlspecialchars($card['tone']) ?> mb-2">
                                            <i class="bi <?= htmlspecialchars($card['icon']) ?>"></i>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <h6 class="text-muted font-semibold mb-1"><?= htmlspecialchars($card['label']) ?></h6>
                                        <h5 class="font-extrabold mb-0"><?= htmlspecialchars($card['value']) ?></h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="row">
                <div class="col-12 col-xl-8">
                    <div class="card">
                        <div class="card-header"><h4 class="mb-0">Doanh thu theo ngày</h4></div>
                        <div class="card-body">
                            <?php if (empty($dailyRevenue)): ?>
                                <div class="text-center text-muted py-5">Chưa có khoản thu trong kỳ đã chọn.</div>
                            <?php else: ?>
                                <div style="height: 320px;"><canvas id="dailyRevenueChart"></canvas></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-4">
                    <div class="card">
                        <div class="card-header"><h4 class="mb-0">Theo phương thức thu</h4></div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead><tr><th>Phương thức</th><th class="text-end">Lượt</th><th class="text-end">Số tiền</th></tr></thead>
                                    <tbody>
                                        <?php if (empty($paymentMethods)): ?>
                                            <tr><td colspan="3" class="text-center text-muted py-4">Chưa có dữ liệu.</td></tr>
                                        <?php else: ?>
                                            <?php foreach ($paymentMethods as $method): ?>
                                                <tr>
                                                    <td><span class="badge bg-light-primary text-primary"><?= htmlspecialchars($method['payment_method']) ?></span></td>
                                                    <td class="text-end"><?= number_format((int) $method['payment_count'], 0, ',', '.') ?></td>
                                                    <td class="text-end fw-semibold"><?= $money($method['total_amount']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h4 class="mb-0">Khoản thu gần nhất trong kỳ</h4>
                    <span class="badge bg-light-secondary text-secondary">Lũy kế sổ thu: <?= $money($summary['all_time_revenue'] ?? 0) ?></span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead><tr><th>Thời gian</th><th>Học viên</th><th>Khóa / gói</th><th>Lớp</th><th>Phương thức</th><th>Mã giao dịch</th><th class="text-end">Số tiền</th></tr></thead>
                            <tbody>
                                <?php if (empty($recentPayments)): ?>
                                    <tr><td colspan="7" class="text-center text-muted py-4">Chưa có khoản thu trong kỳ đã chọn.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($recentPayments as $payment): ?>
                                        <tr>
                                            <td class="text-nowrap"><?= date('d/m/Y H:i', strtotime($payment['paid_at'])) ?></td>
                                            <td><?= htmlspecialchars($payment['student_name']) ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($payment['course_name'] ?? 'Chưa rõ khóa') ?></strong>
                                                <div class="small text-muted"><?= htmlspecialchars($payment['package_name'] ?? 'Chưa rõ gói') ?></div>
                                            </td>
                                            <td><?= htmlspecialchars($payment['class_code'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($payment['payment_method']) ?></td>
                                            <td><?= htmlspecialchars($payment['transaction_code'] ?: '-') ?></td>
                                            <td class="text-end fw-semibold"><?= $money($payment['amount']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<?php if (!empty($dailyRevenue)): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        new Chart(document.getElementById('dailyRevenueChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($chartLabels, JSON_UNESCAPED_UNICODE) ?>,
                datasets: [{
                    data: <?= json_encode($chartValues) ?>,
                    backgroundColor: '#435ebe',
                    borderRadius: 4,
                    maxBarThickness: 44
                }]
            },
            options: {
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: (value) => new Intl.NumberFormat('vi-VN').format(value) + ' d' }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: { label: (context) => new Intl.NumberFormat('vi-VN').format(context.raw) + ' d' }
                    }
                }
            }
        });
    </script>
<?php endif; ?>
