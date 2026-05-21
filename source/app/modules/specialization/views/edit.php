<div id="main">

    <header class="mb-3">
        <a href="#" class="burger-btn d-block d-xl-none">
            <i class="bi bi-justify fs-3"></i>
        </a>
    </header>

    <div class="page-heading">

        <!-- TITLE -->
        <div class="page-title">

            <div class="row">

                <div class="col-12 col-md-6 order-md-1 order-last">

                    <h3>Cập nhật chuyên môn</h3>

                </div>

            </div>

        </div>

        <!-- MAIN -->
        <section class="section">

            <div class="card">

                <div class="card-body">

                    <form method="POST"
                        action="?module=specialization&action=update">

                        <input type="hidden"
                            name="specialization_id"
                            value="<?= $specialization['specialization_id'] ?>">

                        <!-- TÊN -->
                        <div class="mb-3">

                            <label class="form-label">
                                Tên chuyên môn
                            </label>

                            <input type="text"
                                name="name"
                                class="form-control"
                                value="<?= htmlspecialchars($specialization['name']) ?>"
                                required>

                        </div>

                        <!-- MÔ TẢ -->
                        <div class="mb-3">

                            <label class="form-label">
                                Mô tả
                            </label>

                            <textarea name="description"
                                class="form-control"
                                rows="4"><?= htmlspecialchars($specialization['description']) ?></textarea>

                        </div>

                        <!-- STATUS -->
                        <div class="mb-3">

                            <label class="form-label">
                                Trạng thái
                            </label>

                            <select name="status"
                                class="form-control">

                                <option value="active"
                                    <?= $specialization['status'] == 'active' ? 'selected' : '' ?>>

                                    Hoạt động

                                </option>

                                <option value="inactive"
                                    <?= $specialization['status'] == 'inactive' ? 'selected' : '' ?>>

                                    Ngưng hoạt động

                                </option>

                            </select>

                        </div>

                        <!-- BUTTON -->
                        <div class="d-flex gap-2">

                            <button class="btn btn-primary">

                                <i class="bi bi-save"></i>
                                Cập nhật

                            </button>

                            <a href="?module=specialization"
                                class="btn btn-secondary">

                                Quay lại

                            </a>

                        </div>

                    </form>

                </div>

            </div>

        </section>

    </div>

</div>