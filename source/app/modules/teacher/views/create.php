<div id="main">
    <style>
        .teacher-specialization-select-holder + small.text-muted {
            display: none;
        }
    </style>
    <header class="mb-3">
        <a href="#" class="burger-btn d-block d-xl-none">
            <i class="bi bi-justify fs-3"></i>
        </a>
    </header>

    <div class="page-heading">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Thêm giảng viên</h4>
                </div>

                <div class="card-body">
                    <form method="POST" action="?module=teacher&action=store" id="teacherForm">

                        <div class="row">

                            <!-- TÊN -->
                            <div class="col-md-6">
                                <label>Tên giảng viên</label>
                                <input type="text" name="name" class="form-control" maxlength="100" required>
                            </div>

                            <!-- EMAIL -->
                            <div class="col-md-6">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" maxlength="150" required>
                            </div>

                            <div class="col-md-6">
                                <label>Mật khẩu đăng nhập</label>
                                <input type="password" name="password" class="form-control" minlength="6" maxlength="255" autocomplete="new-password" required>
                            </div>

                            <!-- CHUYÊN MÔN -->
                            <div class="col-md-6">
                                <label>Chuyên môn</label>
                                <div class="input-group">
                                    <input type="text" id="specializationSummary" class="form-control" value="Chưa chọn chuyên môn" readonly>
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#specializationModal">
                                        Chọn
                                    </button>
                                </div>
                                <div id="specializationHiddenInputs"></div>
                                <small class="text-muted">Bấm Chọn để mở danh sách chuyên môn.</small>
                                <div class="d-none teacher-specialization-select-holder">
                                <select id="oldSpecializationSelect" class="form-control" multiple size="5">
                                    <option value="">
                                        -- Chọn chuyên môn --
                                    </option>
                                    <?php foreach ($specializations as $sp): ?>
                                        <option value="<?= $sp['specialization_id'] ?>">
                                            <?= $sp['name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                </div>
                                <small class="text-muted">Giữ Ctrl để chọn nhiều chuyên môn. Chuyên môn đầu tiên sẽ là chuyên môn chính.</small>
                            </div>

                            <!-- NGÀY VÀO -->
                            <div class="col-md-6">
                                <label>Ngày vào</label>
                                <input type="date" name="hire_date" class="form-control" max="<?= date('Y-m-d') ?>">
                            </div>

                            <!-- LOẠI LƯƠNG -->
                            <div class="col-md-6" hidden>
                                <label>Loại lương</label>
                                <select name="salary_type" class="form-control" hidden>
                                    <option value="per_session">Theo buổi</option>
                                </select>
                            </div>

                            <!-- GIÁ TRỊ LƯƠNG -->
                            <div class="col-md-6">
                                <label>Giá trị lương</label>
                                <input type="number" name="salary_value" class="form-control" min="0" max="10000000" step="1000" required>
                            </div>

                            <!-- TRẠNG THÁI -->
                            <div class="col-md-6">
                                <label>Trạng thái</label>
                                <select name="status" class="form-control">
                                    <option value="1">Hoạt động</option>
                                    <option value="0">Ngưng hoạt động</option>
                                </select>
                            </div>

                        </div>

                        <div class="mt-3 text-end">
                            <button class="btn btn-success">
                                <i class="bi bi-plus"></i> Thêm mới
                            </button>
                            <a href="?module=teacher" class="btn btn-secondary">Hủy</a>
                        </div>

                    </form>
                </div>
            </div>
        </section>
    </div>
</div>

<div class="modal fade" id="specializationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chọn chuyên môn</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="border rounded p-2" style="max-height:320px; overflow:auto">
                    <?php foreach ($specializations as $sp): ?>
                        <div class="form-check">
                            <input class="form-check-input specialization-check" type="checkbox"
                                value="<?= (int) $sp['specialization_id'] ?>"
                                data-name="<?= htmlspecialchars($sp['name']) ?>"
                                id="sp_<?= (int) $sp['specialization_id'] ?>">
                            <label class="form-check-label" for="sp_<?= (int) $sp['specialization_id'] ?>">
                                <?= htmlspecialchars($sp['name']) ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Xong</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var salaryType = document.querySelector('select[name="salary_type"]');
    if (salaryType && salaryType.closest('.col-md-6')) {
        salaryType.value = 'per_session';
        salaryType.closest('.col-md-6').style.display = 'none';
    }

    const form = document.getElementById('teacherForm');
    const summary = document.getElementById('specializationSummary');
    const hiddenInputs = document.getElementById('specializationHiddenInputs');
    const checks = Array.from(document.querySelectorAll('.specialization-check'));

    function syncSpecializations() {
        const selected = checks.filter(item => item.checked);
        hiddenInputs.innerHTML = selected.map(item => (
            `<input type="hidden" name="specialization_ids[]" value="${item.value}">`
        )).join('');
        summary.value = selected.length
            ? selected.map(item => item.dataset.name).join(', ')
            : 'Chưa chọn chuyên môn';
    }

    checks.forEach(item => item.addEventListener('change', syncSpecializations));
    syncSpecializations();

    if (form) {
        form.addEventListener('submit', function (event) {
            syncSpecializations();
            if (!checks.some(item => item.checked)) {
                event.preventDefault();
                alert('Vui lòng chọn ít nhất một chuyên môn.');
            }
        });
    }
});
</script>
