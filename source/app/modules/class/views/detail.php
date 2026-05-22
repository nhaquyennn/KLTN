<div id="main">
    <header class="mb-3">
        <a href="#" class="burger-btn d-block d-xl-none">
            <i class="bi bi-justify fs-3"></i>
        </a>
    </header>
<div class="container mt-4">

    <h3 class="mb-3">
        <?= $class['class_name'] ?>
    </h3>

    <div class="card">
        <div class="card-body">

            <p><strong>Khóa học:</strong> <?= $class['course_name'] ?></p>

            <p>
                <strong>Gói học:</strong>
                <span class="badge bg-info">
                    <?= $class['name'] ?>
                </span>
            </p>

            <p><strong>Ngày bắt đầu:</strong> <?= $class['start_date'] ?></p>

            <?php
            $percent = ($class['total'] > 0)
                ? round(($class['learned'] / $class['total']) * 100)
                : 0;

            $status = 'Sắp học';
            $badge = 'secondary';
            $today = date('Y-m-d');

            if ($class['start_date'] <= $today) {
                if ($class['learned'] >= $class['total']) {
                    $status = 'Đã kết thúc';
                    $badge = 'dark';
                } else {
                    $status = 'Đang học';
                    $badge = 'success';
                }
            }
            ?>

            <p>
                <strong>Trạng thái:</strong>
                <span class="badge bg-<?= $badge ?>">
                    <?= $status ?>
                </span>
            </p>

            <hr>

            <p><strong>Tiến độ:</strong></p>

            <div class="progress mb-2" style="height: 10px;">
                <div class="progress-bar" style="width: <?= $percent ?>%;">
                </div>
            </div>

            <p><?= $class['learned'] ?> / <?= $class['total'] ?> buổi</p>

            <hr>

            <div class="d-flex gap-2">
                <a href="?module=class&action=edit&id=<?= $class['class_id'] ?>" class="btn btn-warning">
                    Sửa
                </a>

                <a href="?module=session&action=calendar&class_id=<?= $class['class_id'] ?>" class="btn btn-primary">
                    Xếp lịch
                </a>

                <a href="?module=class" class="btn btn-secondary">
                    Quay lại
                </a>
            </div>

        </div>
    </div>
</div>
</div>
