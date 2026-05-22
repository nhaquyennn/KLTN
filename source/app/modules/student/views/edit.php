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
                    <h4 class="card-title">Cập nhật học viên</h4>
                </div>

                <div class="card-body">
                    <form method="POST" action="?module=student&action=update">
                        <input type="hidden" name="student_id" value="<?= (int) $student['student_id'] ?>">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Tên học viên</label>
                                <input type="text" name="name" class="form-control" maxlength="100"
                                    value="<?= htmlspecialchars($student['student_name'] ?? '') ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>SĐT học viên</label>
                                <input type="tel" name="phone" class="form-control" maxlength="20" pattern="[0-9+\-\s]{9,20}"
                                    value="<?= htmlspecialchars($student['student_phone'] ?? '') ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Email đăng nhập</label>
                                <input type="email" name="email" class="form-control" maxlength="150"
                                    value="<?= htmlspecialchars($student['student_email'] ?? '') ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Mật khẩu mới</label>
                                <input type="password" name="password" class="form-control" minlength="6" maxlength="255" autocomplete="new-password"
                                    placeholder="Để trống nếu không đổi">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Phụ huynh</label>
                                <input type="text" name="parent_name" class="form-control" maxlength="100"
                                    value="<?= htmlspecialchars($student['parent_name'] ?? '') ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>SĐT phụ huynh</label>
                                <input type="tel" name="parent_phone" class="form-control" maxlength="20" pattern="[0-9+\-\s]{9,20}"
                                    value="<?= htmlspecialchars($student['parent_phone'] ?? '') ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Ngày sinh</label>
                                <input type="date" name="date_of_birth" class="form-control" max="<?= date('Y-m-d') ?>"
                                    value="<?= htmlspecialchars($student['date_of_birth'] ?? '') ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Trạng thái</label>
                                <select name="status" class="form-control">
                                    <option value="1" <?= ($student['status'] == 1) ? 'selected' : '' ?>>Đang học</option>
                                    <option value="0" <?= ($student['status'] == 0) ? 'selected' : '' ?>>Ngừng</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-3 text-end">
                            <button class="btn btn-primary">Cập nhật</button>
                            <a href="?module=student" class="btn btn-secondary">Quay lại</a>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
</div>
