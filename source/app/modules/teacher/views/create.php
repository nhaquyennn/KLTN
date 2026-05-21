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
                    <h4 class="card-title">Thêm giảng viên</h4>
                </div>

                <div class="card-body">
                    <form method="POST" action="?module=teacher&action=store">

                        <div class="row">

                            <!-- TÊN -->
                            <div class="col-md-6">
                                <label>Tên giảng viên</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>

                            <!-- EMAIL -->
                            <div class="col-md-6">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label>Mật khẩu đăng nhập</label>
                                <input type="text" name="password" class="form-control" required>
                            </div>

                            <!-- CHUYÊN MÔN -->
                            <div class="col-md-6">
                                <label>Chuyên môn</label>
                                <select name="specialization_id" class="form-control" required>
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

                            <!-- NGÀY VÀO -->
                            <div class="col-md-6">
                                <label>Ngày vào</label>
                                <input type="date" name="hire_date" class="form-control">
                            </div>

                            <!-- LOẠI LƯƠNG -->
                            <div class="col-md-6">
                                <label>Loại lương</label>
                                <select name="salary_type" class="form-control">
                                    <option value="per_session">Theo buổi</option>
                                    <option value="fixed">Cố định</option>
                                </select>
                            </div>

                            <!-- GIÁ TRỊ LƯƠNG -->
                            <div class="col-md-6">
                                <label>Giá trị lương</label>
                                <input type="number" name="salary_value" class="form-control">
                            </div>

                            <!-- TRẠNG THÁI -->
                            <div class="col-md-6">
                                <label>Trạng thái</label>
                                <select name="status" class="form-control">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
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
