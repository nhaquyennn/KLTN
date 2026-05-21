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
                    <h4 class="card-title">Chỉnh sửa gói học</h4>
                </div>

                <div class="card-body">
                    <form method="POST" action="?module=package&action=update">

                        <input type="hidden" name="package_id" value="<?= $package['package_id'] ?>">

                        <div class="row">

                            <!-- KHÓA HỌC -->
                            <div class="col-md-6">
                                <div class="form-group row align-items-center">
                                    <div class="col-lg-4 col-4">
                                        <label>Khóa học</label>
                                    </div>
                                    <div class="col-lg-8 col-8">
                                        <select name="course_id" class="form-control">
                                            <?php foreach ($courses as $c): ?>
                                                <option value="<?= $c['course_id'] ?>"
                                                    <?= $c['course_id'] == $package['course_id'] ? 'selected' : '' ?>>
                                                    <?= $c['name'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- TÊN GÓI -->
                            <div class="col-md-6">
                                <div class="form-group row align-items-center">
                                    <div class="col-lg-4 col-4">
                                        <label>Tên gói</label>
                                    </div>
                                    <div class="col-lg-8 col-8">
                                        <input type="text" name="name" class="form-control"
                                            value="<?= htmlspecialchars($package['name']) ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- SỐ BUỔI -->
                            <div class="col-md-6">
                                <div class="form-group row align-items-center">
                                    <div class="col-lg-4 col-4">
                                        <label>Số buổi</label>
                                    </div>
                                    <div class="col-lg-8 col-8">
                                        <input type="number" name="total_sessions" class="form-control"
                                            value="<?= $package['total_sessions'] ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- GIÁ -->
                            <div class="col-md-6">
                                <div class="form-group row align-items-center">
                                    <div class="col-lg-4 col-4">
                                        <label>Giá (VNĐ)</label>
                                    </div>
                                    <div class="col-lg-8 col-8">
                                        <input type="number" name="price" class="form-control"
                                            value="<?= $package['price'] ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- TRẠNG THÁI -->
                            <div class="col-md-6">
                                <div class="form-group row align-items-center">
                                    <div class="col-lg-4 col-4">
                                        <label>Trạng thái</label>
                                    </div>
                                    <div class="col-lg-8 col-8">
                                        <select name="status" class="form-control">
                                            <option value="active" <?= $package['status'] == 'active' ? 'selected' : '' ?>>
                                                Active
                                            </option>
                                            <option value="inactive" <?= $package['status'] == 'inactive' ? 'selected' : '' ?>>Inactive
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- BUTTON -->
                        <div class="mt-3 text-end">
                            <button class="btn btn-primary">
                                <i class="bi bi-check"></i> Cập nhật
                            </button>
                            <a href="?module=package" class="btn btn-secondary">
                                Hủy
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </section>
        <!-- Edit Form End -->

    </div>
</div>