<div class="packages-container">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-0">Đăng ký khóa học</h4>
            <small class="text-muted">Các lớp còn chỗ, chưa học quá 5 buổi kể từ ngày mở lớp.</small>
        </div>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <section class="mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold mb-1">Học phí gói đã đăng ký</h5>
                        <small class="text-muted">Thanh toán phần học phí còn lại qua VNPay.</small>
                    </div>
                </div>

                <?php if (empty($activeEnrollments)): ?>
                    <div class="text-muted">Học viên chưa có gói học đang theo dõi.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Khóa học</th>
                                    <th>Lớp</th>
                                    <th>Tổng học phí</th>
                                    <th>Đã đóng</th>
                                    <th>Còn lại</th>
                                    <th>Mã giao dịch</th>
                                    <th>Trạng thái</th>
                                    <th class="text-end">Thanh toán</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($activeEnrollments as $enrollment): ?>
                                    <?php
                                    $finalFee = (float) ($enrollment['final_fee'] ?? 0);
                                    $paidAmount = (float) ($enrollment['paid_amount'] ?? 0);
                                    $remainingFee = max(0, $finalFee - $paidAmount);
                                    $isPaid = $remainingFee <= 0 || ($enrollment['payment_status'] ?? '') === 'paid';
                                    $statusLabel = $isPaid
                                        ? 'Đã thanh toán'
                                        : (($enrollment['payment_status'] ?? '') === 'partial' ? 'Đã thanh toán một phần' : 'Chưa thanh toán');
                                    $statusClass = $isPaid ? 'bg-success' : (($enrollment['payment_status'] ?? '') === 'partial' ? 'bg-warning text-dark' : 'bg-danger');
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold"><?= htmlspecialchars($enrollment['course_name'] ?? '') ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($enrollment['package_name'] ?? '') ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($enrollment['class_code'] ?? '') ?></td>
                                        <td><?= number_format($finalFee) ?>đ</td>
                                        <td><?= number_format($paidAmount) ?>đ</td>
                                        <td class="fw-semibold"><?= number_format($remainingFee) ?>đ</td>
                                        <td><?= htmlspecialchars($enrollment['transaction_code'] ?: '-') ?></td>
                                        <td><span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span></td>
                                        <td class="text-end">
                                            <?php if ($isPaid): ?>
                                                <span class="text-muted small">Đã hoàn tất</span>
                                            <?php else: ?>
                                                <a
                                                    class="btn btn-sm btn-primary"
                                                    href="?module=parent&action=payment&id=<?= (int) $enrollment['enrollment_id'] ?>">
                                                    Thanh toán VNPay
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <div class="row g-3">
        <?php if (empty($classes)): ?>
            <div class="col-12">
                <div class="card p-4 text-muted">
                    Hiện chưa có lớp phù hợp để đăng ký. Lớp cần còn chỗ và chưa học quá 5 buổi.
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($classes as $class): ?>
                <?php $learnedSessions = (int) ($class['learned_sessions'] ?? 0); ?>

                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card p-4 h-100 border-0 shadow-sm">
                        <div class="d-flex justify-content-between align-items-start mb-3 gap-3">
                            <div>
                                <h5 class="fw-bold mb-1"><?= htmlspecialchars($class['course_name']) ?></h5>
                                <small class="text-muted"><?= htmlspecialchars($class['package_name']) ?></small>
                            </div>
                            <span class="badge bg-primary-subtle text-primary">
                                <?= (int) $class['student_count'] ?>/<?= (int) ($class['max_students'] ?? 10) ?>
                            </span>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="small text-muted">Số buổi</div>
                                <strong><?= (int) $class['total_sessions'] ?> buổi</strong>
                            </div>
                            <div class="col-6">
                                <div class="small text-muted">Lớp</div>
                                <strong><?= htmlspecialchars($class['class_code']) ?></strong>
                            </div>
                            <div class="col-6">
                                <div class="small text-muted">Ngày mở lớp</div>
                                <strong><?= !empty($class['start_date']) ? date('d/m/Y', strtotime($class['start_date'])) : 'Đang cập nhật' ?></strong>
                            </div>
                            <div class="col-6">
                                <div class="small text-muted">Đã học</div>
                                <strong><?= $learnedSessions ?>/5 buổi tối đa</strong>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="small text-muted">Học phí</div>
                            <h4 class="text-primary mb-0"><?= number_format($class['price']) ?>đ</h4>
                        </div>

                        <form method="POST" action="?module=parent&action=registerPackage">
                            <input type="hidden" name="student_id" value="<?= (int) $selectedStudentId ?>">
                            <input type="hidden" name="class_id" value="<?= (int) $class['class_id'] ?>">
                            <button class="btn btn-primary w-100">
                                Đăng ký và thanh toán
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
