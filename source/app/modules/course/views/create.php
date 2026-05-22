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
                    <h4 class="card-title">Thêm khóa học</h4>
                </div>

                <div class="card-body">
                    <form method="POST" action="?module=course&action=store">

                        <div class="row">

                            <!-- TÊN KHÓA -->
                            <div class="col-md-6 mb-3">
                                <label>Tên khóa</label>
                                <input type="text" name="name" class="form-control" maxlength="100" required>
                            </div>

                            <!-- TRẠNG THÁI -->
                            <div class="col-md-6 mb-3">
                                <label>Trạng thái</label>
                                <select name="status" class="form-control">
                                    <option value="active">Hoạt động</option>
                                    <option value="inactive">Ngưng hoạt động</option>
                                </select>
                            </div>

                            <!-- MÔ TẢ -->
                            <div class="col-12 mb-3">
                                <label>Mô tả</label>
                                <textarea name="description" class="form-control" maxlength="1000"></textarea>
                            </div>

                        </div>

                        <div class="mt-3 text-end">
                            <button class="btn btn-success">
                                <i class="bi bi-plus"></i> Thêm mới
                            </button>
                            <a href="?module=course" class="btn btn-secondary">Hủy</a>
                        </div>

                    </form>
                </div>
            </div>
        </section>
    </div>
</div>
