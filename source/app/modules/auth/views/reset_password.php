<div id="auth">
    <div class="row h-100">
        <div>
            <div id="auth-left">
                <div class="auth-logo mb-5 text-center">
                    <img src="<?= BASE_URL ?>assets/images/logo/logo.png" width="150" alt="Logo">
                </div>

                <h3>Đặt lại mật khẩu</h3>
                <p class="text-muted">
                    Tài khoản: <?= htmlspecialchars($_SESSION['reset_email'] ?? '') ?>
                </p>

                <?php if (!empty($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="?module=auth&action=handleResetPassword">
                    <div class="form-group position-relative has-icon-left mb-4">
                        <input type="password" name="new_password" class="form-control" minlength="6" placeholder="Mật khẩu mới" required>
                        <div class="form-control-icon">
                            <i class="bi bi-shield-lock"></i>
                        </div>
                    </div>
                    <div class="form-group position-relative has-icon-left mb-4">
                        <input type="password" name="confirm_password" class="form-control" minlength="6" placeholder="Xác nhận mật khẩu mới" required>
                        <div class="form-control-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                    </div>
                    <button class="btn btn-primary w-100">Đặt lại mật khẩu</button>
                </form>

                <div class="text-center mt-4">
                    <a href="?module=auth&action=login">Quay lại đăng nhập</a>
                </div>
            </div>
        </div>
    </div>
</div>
