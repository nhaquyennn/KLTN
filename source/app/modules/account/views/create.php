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
                    <h4 class="card-title">Tạo tài khoản người dùng</h4>
                </div>

                <div class="card-body">
                    <form method="POST" action="?module=account&action=store">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Họ tên</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Email đăng nhập</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Mật khẩu</label>
                                <input type="text" name="password" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Số điện thoại</label>
                                <input type="text" name="phone" class="form-control">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Vai trò</label>
                                <select name="role" class="form-control" required>
                                    <option value="student">Học viên</option>
                                    <option value="teacher">Giảng viên</option>
                                    <option value="parent">Phụ huynh</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Trạng thái</label>
                                <select name="status" class="form-control">
                                    <option value="1">Hoạt động</option>
                                    <option value="0">Khóa</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-3 text-end">
                            <button class="btn btn-primary">
                                <i class="bi bi-save"></i>
                                Lưu
                            </button>
                            <a href="?module=account&action=index" class="btn btn-secondary">Quay lại</a>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
</div>
