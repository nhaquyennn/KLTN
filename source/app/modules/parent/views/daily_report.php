<div class="daily-report-container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">Sổ liên lạc hằng ngày</h5>
        <span class="text-muted small"><?= date('d/m/Y') ?></span>
    </div>

    <?php if (empty($reviews)): ?>
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 text-muted">
                Chưa có nhận xét từ giáo viên.
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($reviews as $review): ?>
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-3">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-warning text-white d-flex align-items-center justify-content-center me-3" style="width:45px;height:45px;">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold"><?= htmlspecialchars($review['teachers'] ?: 'Giáo viên') ?></h6>
                            <small class="text-muted">
                                <?= htmlspecialchars($review['course_name']) ?> • <?= date('d/m/Y', strtotime($review['session_date'])) ?>
                            </small>
                        </div>
                    </div>

                    <div class="teacher-comment p-3 bg-light rounded-3">
                        <p class="mb-0 text-dark">
                            <?= nl2br(htmlspecialchars($review['review_text'])) ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
