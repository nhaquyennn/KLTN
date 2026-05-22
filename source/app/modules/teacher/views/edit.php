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

        <!-- Edit Form Start -->
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Chỉnh sửa giảng viên</h4>
                </div>

                <div class="card-body">
                    <form method="POST" action="?module=teacher&action=update" id="teacherForm">

                        <input type="hidden" name="teacher_id" value="<?= $teacher['teacher_id'] ?>">

                        <div class="row">

                            <!-- TÊN -->
                            <div class="col-md-6">
                                <div class="form-group row align-items-center">
                                    <div class="col-lg-4 col-4">
                                        <label>Tên</label>
                                    </div>
                                    <div class="col-lg-8 col-8">
                                        <input type="text" name="name" class="form-control" maxlength="100"
                                            value="<?= htmlspecialchars($teacher['name']) ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- EMAIL -->
                            <div class="col-md-6">
                                <div class="form-group row align-items-center">
                                    <div class="col-lg-4 col-4">
                                        <label>Email</label>
                                    </div>
                                    <div class="col-lg-8 col-8">
                                        <input type="email" name="email" class="form-control" maxlength="150"
                                            value="<?= htmlspecialchars($teacher['email']) ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group row align-items-center">
                                    <div class="col-lg-4 col-4">
                                        <label>Mật khẩu mới</label>
                                    </div>
                                    <div class="col-lg-8 col-8">
                                        <input type="password" name="password" class="form-control" minlength="6" maxlength="255" autocomplete="new-password"
                                            placeholder="Để trống nếu không đổi">
                                    </div>
                                </div>
                            </div>

                            <!-- CHUYÊN MÔN -->
                            <div class="col-md-6">

                                <div class="form-group row align-items-center">

                                    <div class="col-lg-4 col-4">
                                        <label>Chuyên môn</label>
                                    </div>

                                    <div class="col-lg-8 col-8">

                                        <?php
                                        $selectedSpecializationIds = array_filter(array_map('intval', explode(',', (string) ($teacher['specialization_ids'] ?? ''))));
                                        if (empty($selectedSpecializationIds) && !empty($teacher['specialization_id'])) {
                                            $selectedSpecializationIds = [(int) $teacher['specialization_id']];
                                        }
                                        ?>

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

                                            <?php foreach ($specializations as $s): ?>

                                                <option value="<?= $s['specialization_id'] ?>"
                                                    <?= in_array((int) $s['specialization_id'], $selectedSpecializationIds, true)
                                                        ? 'selected'
                                                        : '' ?>>

                                                        <?= htmlspecialchars($s['name']) ?>

                                                </option>

                                            <?php endforeach; ?>

                                        </select>
                                        </div>
                                        <small class="text-muted">Giữ Ctrl để chọn nhiều chuyên môn. Chuyên môn đầu tiên sẽ là chuyên môn chính.</small>

                                    </div>

                                </div>

                            </div>

                            <!-- NGÀY VÀO -->
                            <div class="col-md-6">
                                <div class="form-group row align-items-center">
                                    <div class="col-lg-4 col-4">
                                        <label>Ngày vào</label>
                                    </div>
                                    <div class="col-lg-8 col-8">
                                        <input type="date" name="hire_date" class="form-control" max="<?= date('Y-m-d') ?>"
                                            value="<?= $teacher['hire_date'] ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- LOẠI LƯƠNG -->
                            <div class="col-md-6" hidden>
                                <div class="form-group row align-items-center">
                                    <div class="col-lg-4 col-4">
                                        <label>Loại lương</label>
                                    </div>
                                    <div class="col-lg-8 col-8">
                                        <select name="salary_type" class="form-control" hidden>
                                            <option value="per_session" <?= $teacher['salary_type'] == 'per_session' ? 'selected' : '' ?>>
                                                Theo buổi
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- GIÁ TRỊ LƯƠNG -->
                            <div class="col-md-6">
                                <div class="form-group row align-items-center">
                                    <div class="col-lg-4 col-4">
                                        <label>Giá trị</label>
                                    </div>
                                    <div class="col-lg-8 col-8">
                                        <input type="number" name="salary_value" class="form-control" min="0" max="10000000" step="1000" required
                                            value="<?= $teacher['salary_value'] ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- TRẠNG THÁI -->
                            <div class="col-md-6">
                                <div class="form-group row align-items-center">
                                    <div class="col-lg-4 col-4">
                                        <label>Trạng thái</label>
                                    </div>
                                    <div class="col-lg-8 col-8">
                                        <select name="status" class="form-control">
                                            <option value="1" <?= $teacher['status'] == 1 ? 'selected' : '' ?>>
                                                Hoạt động
                                            </option>
                                            <option value="0" <?= $teacher['status'] == 0 ? 'selected' : '' ?>>
                                                Ngưng hoạt động
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- BUTTON -->
                        <div class="mt-3 text-end">
                            <button class="btn btn-primary">
                                <i class="bi bi-check"></i> Cập nhật
                            </button>
                            <a href="?module=teacher" class="btn btn-secondary">
                                Hủy
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </section>
        <!-- Edit Form End -->

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
                        <?php $checked = in_array((int) $sp['specialization_id'], $selectedSpecializationIds ?? [], true); ?>
                        <div class="form-check">
                            <input class="form-check-input specialization-check" type="checkbox"
                                value="<?= (int) $sp['specialization_id'] ?>"
                                data-name="<?= htmlspecialchars($sp['name']) ?>"
                                id="sp_<?= (int) $sp['specialization_id'] ?>"
                                <?= $checked ? 'checked' : '' ?>>
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
