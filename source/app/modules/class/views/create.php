<div id="main">
    <header class="mb-3">
        <a href="#" class="burger-btn d-block d-xl-none">
            <i class="bi bi-justify fs-3"></i>
        </a>
    </header>

    <div class="page-heading">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Thêm lớp học</h4>
                </div>

                <div class="card-body">
                    <form method="POST" action="?module=class&action=store">

                        <div class="row">

                            <!-- COURSE -->
                            <div class="col-md-6">
                                <label>Khóa học</label>
                                <select name="course_id" id="courseSelect" class="form-control" required>
                                    <option value="">-- Chọn khóa học --</option>

                                    <?php foreach ($courses as $c): ?>
                                        <option value="<?= $c['course_id'] ?>">
                                            <?= htmlspecialchars($c['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- PACKAGE -->
                            <div class="col-md-6">
                                <label>Gói học</label>
                                <select name="package_id" id="packageSelect" class="form-control" required disabled>
                                    <option value="">-- Chọn khóa học trước --</option>
                                </select>
                            </div>

                            <!-- SCHEDULE -->
                            <div class="col-md-6">
                                <label>Lịch học</label>
                                <select name="schedule_id" class="form-control">
                                    <option value="">-- Chọn lịch --</option>
                                    <?php foreach ($schedules as $s): ?>
                                        <option value="<?= $s['schedule_id'] ?>">
                                            <?= $s['name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- START DATE -->
                            <div class="col-md-6">
                                <label>Ngày bắt đầu</label>
                                <input type="date" name="start_date" class="form-control" min="<?= date('Y-m-d') ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label>Số học viên tối đa</label>
                                <input type="number" name="max_students" class="form-control" min="1" max="200" step="1" value="10" required>
                            </div>

                        </div>

                        <div class="mt-3 text-end">
                            <button class="btn btn-success">
                                <i class="bi bi-plus"></i> Thêm mới
                            </button>
                            <a href="?module=class" class="btn btn-secondary">Hủy</a>
                        </div>

                    </form>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {

        const courseSelect = document.getElementById('courseSelect');
        const packageSelect = document.getElementById('packageSelect');

        courseSelect.addEventListener('change', function () {

            let courseId = this.value;

            // reset
            packageSelect.innerHTML = '<option value="">-- Chọn gói --</option>';
            packageSelect.disabled = true;

            if (!courseId) {
                packageSelect.innerHTML = '<option value="">-- Chọn khóa học trước --</option>';
                return;
            }

            // loading
            packageSelect.innerHTML = '<option>Đang tải...</option>';

            fetch(`?module=package&action=getByCourse&course_id=${courseId}`)
                .then(res => res.json())
                .then(data => {

                    let html = '<option value="">-- Chọn gói --</option>';

                    if (data.length === 0) {
                        html = '<option value="">Không có gói</option>';
                    } else {
                        data.forEach(p => {
                            html += `
                            <option value="${p.package_id}">
                                ${p.name} (${p.total_sessions ?? 0} buổi)
                            </option>
                        `;
                        });
                    }

                    packageSelect.innerHTML = html;
                    packageSelect.disabled = false;
                })
                .catch(err => {
                    console.error(err);
                    packageSelect.innerHTML = '<option value="">Lỗi load dữ liệu</option>';
                });
        });

    });
</script>
