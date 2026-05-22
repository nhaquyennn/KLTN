<div id="main">
    <header class="mb-3">
        <a href="#" class="burger-btn d-block d-xl-none">
            <i class="bi bi-justify fs-3"></i>
        </a>
    </header>

    <div class="page-heading">
        <div class="page-title mb-3">
            <h3>Đổi mật khẩu</h3>
            <nav class="breadcrumb-header">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="?module=dashboard&action=index">Trang chủ</a>
                    </li>
                    <li class="breadcrumb-item active">Đổi mật khẩu</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-12 col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0">Cập nhật mật khẩu</h4>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($_SESSION['success'])): ?>
                                <div class="alert alert-success">
                                    <?= htmlspecialchars($_SESSION['success']) ?>
                                </div>
                                <?php unset($_SESSION['success']); ?>
                            <?php endif; ?>

                            <?php if (!empty($_SESSION['error'])): ?>
                                <div class="alert alert-danger">
                                    <?= htmlspecialchars($_SESSION['error']) ?>
                                </div>
                                <?php unset($_SESSION['error']); ?>
                            <?php endif; ?>

                            <form method="POST" action="?module=auth&action=updatePassword">
                                <div class="mb-3">
                                    <label>Mật khẩu hiện tại</label>
                                    <input type="password" name="current_password" class="form-control" maxlength="255" required>
                                </div>
                                <div class="mb-3">
                                    <label>Mật khẩu mới</label>
                                    <input type="password" name="new_password" class="form-control" minlength="6" maxlength="255" required>
                                </div>
                                <div class="mb-3">
                                    <label>Xác nhận mật khẩu mới</label>
                                    <input type="password" name="confirm_password" class="form-control" minlength="6" maxlength="255" required>
                                </div>
                                <button class="btn btn-primary">
                                    <i class="bi bi-shield-lock"></i> Lưu mật khẩu
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
