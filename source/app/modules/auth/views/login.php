    <div id="auth">

    <div class="row h-100">
        <div class="">
            <div id="auth-left">
                <div class="auth-logo mb-5 text-center">
                    <img src="<?= BASE_URL ?>assets/images/logo/logo.png" width="150">
                </div>

                <?php if (!empty($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <form action="index.php?module=auth&action=handleLogin" method="POST">

                    <div class="form-group position-relative has-icon-left mb-4">
                        <input type="text" name="email" class="form-control" placeholder="Email">
                        <div class="form-control-icon">
                            <i class="bi bi-person"></i>
                        </div>
                    </div>

                    <div class="form-group position-relative has-icon-left mb-4">
                        <input type="password" name="password" class="form-control" placeholder="Mật khẩu">
                        <div class="form-control-icon">
                            <i class="bi bi-shield-lock"></i>
                        </div>
                    </div>

                    <button class="btn btn-primary w-100">Đăng nhập</button>
                </form>

                <div class="text-center mt-4">
                    <a href="?module=auth&action=forgotPassword">Quên mật khẩu?</a>
                </div>

            </div>
        </div>
    </div>

</div>
