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
                    <h4 class="card-title">Chỉnh sửa lịch học</h4>
                </div>

                <div class="card-body">
                    <form method="POST" action="?module=schedule&action=update">

                        <input type="hidden" name="schedule_id" value="<?= $schedule['schedule_id'] ?>">

                        <input type="text" name="name" value="<?= $schedule['name'] ?>" class="form-control">
                        <input type="text" name="code" value="<?= $schedule['code'] ?>" class="form-control">

                        <label>Ngày học</label><br>

                        <?php for ($i = 2; $i <= 7; $i++): ?>
                            <label>
                                <input type="checkbox" name="days[]" value="<?= $i ?>" <?= in_array($i, $schedule['days']) ? 'checked' : '' ?>>
                                Thứ <?= $i ?>
                            </label>
                        <?php endfor; ?>

                        <br><br>
                        <button class="btn btn-primary">Cập nhật</button>
                    </form>
                </div>
            </div>
        </section>
        <!-- Edit Form End -->

    </div>
</div>