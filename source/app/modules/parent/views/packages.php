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
