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
                    <h4 class="card-title">Thêm phòng học</h4>
                </div>

                <div class="card-body">
                    <form method="POST" action="?module=room&action=store">
                        <div class="card p-3">

                            <!-- TÊN PHÒNG -->
                            <div class="mb-3">
                                <label class="form-label">Tên phòng</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>

                            <!-- SỨC CHỨA -->
                            <div class="mb-3">
                                <label class="form-label">Sức chứa</label>
                                <input type="number" name="capacity" class="form-control" min="1" required>
                            </div>

                            <!-- TRẠNG THÁI -->
                            <div class="mb-3">
                                <label class="form-label">Trạng thái</label>
                                <select name="status" class="form-control">
                                    <option value="active">Hoạt động</option>
                                    <option value="inactive">Ngưng sử dụng</option>
                                </select>
                            </div>

                            <!-- BUTTON -->
                            <div class="text-end">
                                <a href="?module=room" class="btn btn-secondary">Quay lại</a>
                                <button type="submit" class="btn btn-primary">Lưu</button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
</div>