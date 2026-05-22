<div id="main">
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
                    <form method="POST" action="?module=teacher&action=update">

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

                            <!-- CHUYÊN MÔN -->
                            <div class="col-md-6">
                                <div class="form-group row align-items-center">
                                    <div class="col-lg-4 col-4">
                                        <label>Chuyên môn</label>
                                    </div>
                                    <div class="col-lg-8 col-8">
                                        <input type="text" name="specialization" class="form-control" maxlength="100"
                                            value="<?= $teacher['specialization'] ?>">
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
                            <div class="col-md-6">
                                <div class="form-group row align-items-center">
                                    <div class="col-lg-4 col-4">
                                        <label>Loại lương</label>
                                    </div>
                                    <div class="col-lg-8 col-8">
                                        <select name="salary_type" class="form-control">
                                            <option value="per_session" <?= $teacher['salary_type'] == 'per_session' ? 'selected' : '' ?>>
                                                Theo buổi
                                            </option>
                                            <option value="fixed" <?= $teacher['salary_type'] == 'fixed' ? 'selected' : '' ?>>
                                                Cố định
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
                                        <input type="number" name="salary_value" class="form-control" min="0" max="10000000" step="1000"
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
