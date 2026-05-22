<div id="main">
    <header class="mb-3">
        <a href="#" class="burger-btn d-block d-xl-none">
            <i class="bi bi-justify fs-3"></i>
        </a>
    </header>

    <div class="page-heading">

        <!-- Edit Form Start -->
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Chỉnh sửa phòng học</h4>
                </div>

                <div class="card-body">
                    <form method="POST" action="?module=room&action=update">
                        <input type="hidden" name="room_id" value="<?= $room['room_id'] ?>">

                        <div class="card p-3">

                            <!-- TÊN PHÒNG -->
                            <div class="mb-3">
                                <label class="form-label">Tên phòng</label>
                                <input type="text" name="name" class="form-control" maxlength="50"
                                    value="<?= htmlspecialchars($room['name']) ?>" required>
                            </div>

                            <!-- SỨC CHỨA -->
                            <div class="mb-3">
                                <label class="form-label">Sức chứa</label>
                                <input type="number" name="capacity" class="form-control" min="1" max="500" step="1"
                                    value="<?= $room['capacity'] ?>" required>
                            </div>

                            <!-- BUTTON -->
                            <div class="text-end">
                                <a href="?module=room" class="btn btn-secondary">Quay lại</a>
                                <button type="submit" class="btn btn-primary">Cập nhật</button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </section>
        <!-- Edit Form End -->

    </div>
</div>
