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
                    <h4 class="card-title">Chỉnh sửa lớp học</h4>
                </div>

                <div class="card-body">
                    <form method="POST" action="?module=class&action=update">

                        <input type="hidden" name="class_id" value="<?= $class['class_id'] ?>">

                        <div class="row">

                            <!-- COURSE -->
                            <div class="col-md-6">
                                <label>Khóa học</label>
                                <select name="course_id" class="form-control" required>
                                    <?php foreach ($courses as $c): ?>
                                        <option value="<?= $c['course_id'] ?>" <?= ($c['course_id'] == $class['course_id']) ? 'selected' : '' ?>>
                                            <?= $c['name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- PACKAGE -->
                            <div class="col-md-6">
                                <label>Gói học</label>
                                <select name="package_id" class="form-control" required>
                                    <?php foreach ($packages as $p): ?>
                                        <option value="<?= $p['package_id'] ?>" <?= ($p['package_id'] == $class['package_id']) ? 'selected' : '' ?>>
                                            <?= $p['name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- SCHEDULE -->
                            <div class="col-md-6">
                                <label>Lịch học</label>
                                <select name="schedule_id" class="form-control">
                                    <?php foreach ($schedules as $s): ?>
                                        <option value="<?= $s['schedule_id'] ?>"
                                            <?= ($s['schedule_id'] == $class['schedule_id']) ? 'selected' : '' ?>>
                                            <?= $s['name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- SHIFT -->
                            <div class="col-md-6">
                                <label>Ca học</label>
                                <select name="shift_id" class="form-control">
                                    <?php foreach ($shifts as $sh): ?>
                                        <option value="<?= $sh['shift_id'] ?>" <?= ($sh['shift_id'] == $class['shift_id']) ? 'selected' : '' ?>>
                                            <?= $sh['name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- START DATE -->
                            <div class="col-md-6">
                                <label>Ngày bắt đầu</label>
                                <input type="date" name="start_date" value="<?= $class['start_date'] ?>"
                                    class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label>Số học viên tối đa</label>
                                <input type="number" name="max_students" value="<?= (int) ($class['max_students'] ?? 10) ?>"
                                    class="form-control" min="1" max="200" step="1" required>
                            </div>

                        </div>

                        <div class="mt-3 text-end">
                            <button class="btn btn-primary">
                                <i class="bi bi-save"></i> Cập nhật
                            </button>
                            <a href="?module=class" class="btn btn-secondary">Hủy</a>
                        </div>

                    </form>
                </div>
            </div>
        </section>
    </div>
</div>
