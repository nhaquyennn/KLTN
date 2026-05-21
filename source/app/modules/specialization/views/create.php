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

                    <h3>Thêm chuyên môn</h3>

                </div>

            </div>

        </div>

        <!-- MAIN -->
        <section class="section">

            <div class="card">

                <div class="card-body">

                    <form method="POST"
                        action="?module=specialization&action=store">

                        <!-- TÊN -->
                        <div class="mb-3">

                            <label class="form-label">
                                Tên chuyên môn
                            </label>

                            <input type="text"
                                name="name"
                                class="form-control"
                                required>

                        </div>

                        <!-- MÔ TẢ -->
                        <div class="mb-3">

                            <label class="form-label">
                                Mô tả
                            </label>

                            <textarea name="description"
                                class="form-control"
                                rows="4"></textarea>

                        </div>

                        <!-- STATUS -->
                        <div class="mb-3">

                            <label class="form-label">
                                Trạng thái
                            </label>

                            <select name="status"
                                class="form-control">

                                <option value="active">
                                    Hoạt động
                                </option>

                                <option value="inactive">
                                    Ngưng hoạt động
                                </option>

                            </select>

                        </div>

                        <!-- BUTTON -->
                        <div class="d-flex gap-2">

                            <button class="btn btn-success">

                                <i class="bi bi-check-circle"></i>
                                Lưu

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