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
                    <h4 class="card-title">Thêm học viên</h4>
                </div>

                <div class="card-body">
                    <form method="POST" action="?module=student&action=store">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Tên học viên</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>SĐT học viên</label>
                                <input type="text" name="phone" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Email đăng nhập</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Mật khẩu đăng nhập</label>
                                <input type="text" name="password" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Phụ huynh</label>
                                <input type="text" name="parent_name" class="form-control">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>SĐT phụ huynh</label>
                                <input type="text" name="parent_phone" class="form-control">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Ngày sinh</label>
                                <input type="date" name="date_of_birth" class="form-control">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Trạng thái</label>
                                <select name="status" class="form-control">
                                    <option value="1">Đang học</option>
                                    <option value="0">Ngừng</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-3 text-end">
                            <button class="btn btn-primary">Lưu</button>
                            <a href="?module=student" class="btn btn-secondary">Quay lại</a>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
</div>
