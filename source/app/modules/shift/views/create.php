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
                    <h4 class="card-title">Thêm ca học</h4>
                </div>

                <div class="card-body">
                    <form method="POST" action="?module=shift&action=store">
                        <div class="mb-2">
                            <label>Tên ca</label>
                            <input type="text" name="name" class="form-control">
                        </div>

                        <div class="mb-2">
                            <label>Giờ bắt đầu</label>
                            <input type="time" name="start_time" class="form-control">
                        </div>

                        <div class="mb-2">
                            <label>Giờ kết thúc</label>
                            <input type="time" name="end_time" class="form-control">
                        </div>

                        <button class="btn btn-success">Thêm</button>
                    </form>
                </div>
            </div>
        </section>
    </div>
</div>