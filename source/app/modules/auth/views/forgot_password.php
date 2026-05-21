<div id="auth">
    <div class="row h-100">
        <div>
            <div id="auth-left">
                <div class="auth-logo mb-5 text-center">
                    <img src="<?= BASE_URL ?>assets/images/logo/logo.png" width="150" alt="Logo">
                </div>

                <h3>Quên mật khẩu</h3>
                <p class="text-muted">Nhập email tài khoản để đặt lại mật khẩu.</p>

                <?php if (!empty($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="?module=auth&action=handleForgotPassword">
                    <div class="form-group position-relative has-icon-left mb-4">
                        <input type="email" name="email" class="form-control" placeholder="Email" required>
                        <div class="form-control-icon">
                            <i class="bi bi-envelope"></i>
                        </div>
                    </div>
                    <button class="btn btn-primary w-100">Tiếp tục</button>
                </form>

                <div class="text-center mt-4">
                    <a href="?module=auth&action=login">Quay lại đăng nhập</a>
                </div>
            </div>
        </div>
    </div>
</div>
