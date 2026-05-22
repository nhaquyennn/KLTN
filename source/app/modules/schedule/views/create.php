<div id="main">
    <header class="mb-3">
        <a href="#" class="burger-btn d-block d-xl-none">
            <i class="bi bi-justify fs-3"></i>
        </a>
    </header>

    <div class="page-heading">
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Thêm lịch học</h4>
                </div>

                <div class="card-body">
                    <form method="POST" action="?module=schedule&action=store" id="scheduleForm">

                        <label>Tên lịch</label>
                        <input type="text" name="name" class="form-control" maxlength="100" required>

                        <label>Code</label>
                        <input type="text" name="code" class="form-control" maxlength="50" pattern="[A-Za-z0-9_-]+">

                        <label>Ngày học</label><br>

                        <?php
                        $days = [
                            1 => 'Chủ nhật',
                            2 => 'Thứ 2',
                            3 => 'Thứ 3',
                            4 => 'Thứ 4',
                            5 => 'Thứ 5',
                            6 => 'Thứ 6',
                            7 => 'Thứ 7'
                        ];
                        ?>

                        <?php foreach ($days as $i => $label): ?>
                            <label>
                                <input type="checkbox" name="days[]" value="<?= $i ?>">
                                <?= $label ?>
                            </label>
                        <?php endforeach; ?>

                        <br><br>
                        <button class="btn btn-success">Thêm</button>
                    </form>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
document.getElementById('scheduleForm')?.addEventListener('submit', function (event) {
    if (!this.querySelector('input[name="days[]"]:checked')) {
        event.preventDefault();
        alert('Vui lòng chọn ít nhất một ngày học.');
    }
});
</script>
