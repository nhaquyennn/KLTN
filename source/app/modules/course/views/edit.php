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
                    <h4 class="card-title">Chỉnh sửa khóa học</h4>
                </div>

                <div class="card-body">
                    <form method="POST" action="?module=course&action=update">

                        <input type="hidden" name="course_id" value="<?= $course['course_id'] ?>">

                        <div class="row">

                            <!-- TÊN KHÓA -->
                            <select name="course_id" class="form-control">
                                <?php foreach ($courses as $c): ?>
                                    <option value="<?= $c['course_id'] ?>" <?= $c['course_id'] == $package['course_id'] ? 'selected' : '' ?>>
                                        <?= $c['name'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <!-- TRẠNG THÁI -->
                            <div class="col-md-6 mb-3">
                                <label>Trạng thái</label>
                                <select name="status" class="form-control">
                                    <option value="active" <?= $course['status'] == 'active' ? 'selected' : '' ?>>Active
                                    </option>
                                    <option value="inactive" <?= $course['status'] == 'inactive' ? 'selected' : '' ?>>Inactive
                                    </option>
                                </select>
                            </div>

                            <!-- MÔ TẢ -->
                            <div class="col-12 mb-3">
                                <label>Mô tả</label>
                                <textarea name="description"
                                    class="form-control"><?= htmlspecialchars($course['description']) ?></textarea>
                            </div>

                        </div>

                        <div class="mt-3 text-end">
                            <button class="btn btn-primary">
                                <i class="bi bi-save"></i> Cập nhật
                            </button>
                            <a href="?module=course" class="btn btn-secondary">Hủy</a>
                        </div>

                    </form>
                </div>
            </div>
        </section>
    </div>
</div>