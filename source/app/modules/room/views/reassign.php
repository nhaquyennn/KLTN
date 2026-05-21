<div id="main">
    <header class="mb-3">
        <a href="#" class="burger-btn d-block d-xl-none">
            <i class="bi bi-justify fs-3"></i>
        </a>
    </header>
    <div class="card">
        <div class="card-body">

            <h4 class="mb-4">
                Chọn lại phòng cho các buổi học
            </h4>

            <form method="POST" action="?module=room&action=saveReassign">

                <table class="table table-bordered">

                    <thead>
                        <tr>
                            <th>Lớp</th>
                            <th>Ngày học</th>
                            <th>Ca học</th>
                            <th>Phòng mới</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($sessions as $s): ?>

                            <?php
                            $sessionModel = new SessionModel();
                            $rooms = $sessionModel
                                ->getRoomsWithStatus($s['session_id']);
                            ?>

                            <tr>

                                <td>
                                    <?php
                                    $suffix = '';

                                    if (
                                        !empty($s['class_code']) &&
                                        strpos($s['class_code'], '-') !== false
                                    ) {
                                        $parts = explode('-', $s['class_code']);
                                        $suffix = end($parts);
                                    }
                                    ?>

                                    <?= htmlspecialchars($s['course_name']) ?> -
                                    <?= htmlspecialchars($s['package_name']) ?> -
                                    <?= htmlspecialchars($suffix) ?>
                                </td>

                                <td>
                                    <?= date(
                                        'd/m/Y',
                                        strtotime($s['session_date'])
                                    ) ?>
                                </td>

                                <td><?= $s['shift_name'] ?></td>

                                <td>

                                    <select class="form-select" name="rooms[<?= $s['session_id'] ?>]" required>

                                        <option value="">
                                            -- Chọn phòng --
                                        </option>

                                        <?php foreach ($rooms as $r): ?>

                                            <option value="<?= $r['room_id'] ?>" <?= $r['is_busy'] ? 'disabled' : '' ?>>
                                                <?= $r['name'] ?>
                                                (<?= $r['capacity'] ?> chỗ)

                                                <?= $r['is_busy']
                                                    ? ' - Đang sử dụng'
                                                    : '' ?>
                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

                <button class="btn btn-danger">
                    Xác nhận ngưng hoạt động phòng
                </button>

            </form>

        </div>
    </div>
</div>