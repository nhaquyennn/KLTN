<div id="main">
    <header class="mb-3">
        <a href="#" class="burger-btn d-block d-xl-none">
            <i class="bi bi-justify fs-3"></i>
        </a>
    </header>

    <div class="page-heading">
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Thêm gói học</h4>
                </div>

                <div class="card-body">
                    <form method="POST" action="?module=package&action=store">

                        <div class="row">

                            <!-- KHÓA HỌC -->
                            <div class="col-md-6">
                                <label>Khóa học</label>
                                <select name="course_id" class="form-control" required>
                                    <option value="">-- Chọn khóa học --</option>
                                    <?php foreach ($courses as $c): ?>
                                        <option value="<?= $c['course_id'] ?>">
                                            <?= htmlspecialchars($c['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- TÊN -->
                            <div class="col-md-6">
                                <label>Tên gói</label>
                                <input type="text" name="name" class="form-control" maxlength="100" required>
                            </div>

                            <!-- SỐ BUỔI -->
                            <div class="col-md-6">
                                <label>Số buổi</label>
                                <input type="number" name="total_sessions" class="form-control" min="1" max="500" step="1" required>
                            </div>

                            <!-- GIÁ -->
                            <div class="col-md-6">
                                <label>Giá</label>
                                <input type="number" name="price" class="form-control" min="0" max="1000000000" step="1000" required>
                            </div>

                            <!-- TRẠNG THÁI -->
                            <div class="col-md-6">
                                <label>Trạng thái</label>
                                <select name="status" class="form-control">
                                    <option value="active">Hoạt động</option>
                                    <option value="inactive">Ngưng hoạt động</option>
                                </select>
                            </div>

                        </div>

                        <div class="mt-3 text-end">
                            <button class="btn btn-success">
                                <i class="bi bi-plus"></i> Thêm mới
                            </button>
                            <a href="?module=package" class="btn btn-secondary">Hủy</a>
                        </div>

                    </form>
                </div>
            </div>
        </section>
    </div>
</div>
