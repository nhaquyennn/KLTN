<div id="main">
    <header class="mb-3">
        <a href="#" class="burger-btn d-block d-xl-none">
            <i class="bi bi-justify fs-3"></i>
        </a>
    </header>

    <div class="page-heading">

        <!-- TITLE -->
        <div class="page-title mb-3">

            <!-- HÀNG 1 -->
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                <h3 class="mb-0">Danh sách phòng học</h3>

                <a href="?module=room&action=create" class="btn btn-success">
                    <i class="bi bi-plus"></i> Thêm phòng học
                </a>
            </div>

            <!-- BREADCRUMB -->
            <nav class="breadcrumb-header">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="<?= BASE_URL ?>?module=dashboard&action=index">
                            Trang chủ
                        </a>
                    </li>

                    <li class="breadcrumb-item active">
                        Phòng học
                    </li>
                </ol>
            </nav>

        </div>

        <!-- MAIN -->
        <section class="section">
            <div class="row">
                <div class="col-12">

                    <?php if (!empty($_SESSION['modal'])): ?>

                        <?php $modal = $_SESSION['modal']; ?>

                        <div class="modal fade show" id="roomModal" tabindex="-1"
                            style="display:block; background: rgba(0,0,0,0.5);">

                            <div class="modal-dialog">
                                <div class="modal-content">

                                    <div class="modal-header bg-<?= $modal['type'] ?> text-white">
                                        <h5 class="modal-title">
                                            <?= $modal['title'] ?>
                                        </h5>
                                    </div>

                                    <div class="modal-body">
                                        <?= $modal['message'] ?>
                                    </div>

                                    <div class="modal-footer">

                                        <?php if (
                                            isset($_GET['confirm_future'])
                                            && !empty($_SESSION['future_room'])
                                        ): ?>

                                            <a href="?module=room&action=inactive&id=<?= $_SESSION['future_room'] ?>&confirm=1"
                                                class="btn btn-danger">
                                                Đồng ý
                                            </a>
                                            <a href="?module=room" class="btn btn-secondary">
                                                Hủy
                                            </a>

                                        <?php else: ?>
                                            <a href="?module=room" class="btn btn-primary">
                                                OK
                                            </a>

                                        <?php endif; ?>

                                    </div>

                                </div>
                            </div>
                        </div>

                        <?php
                        unset($_SESSION['modal']);
                        unset($_SESSION['future_room']);
                        ?>

                    <?php endif; ?>

                    <!-- TABLE -->
                    <div class="card">
                        <!-- FILTER -->
                        <form method="GET" class="mb-3">
                            <input type="hidden" name="module" value="room">

                            <div class="row">

                                <!-- KEYWORD -->
                                <div class="col-md-3">
                                    <input type="text" name="keyword" class="form-control" maxlength="100"
                                        placeholder="Tìm tên phòng..."
                                        value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>">
                                </div>

                                <!-- STATUS -->
                                <div class="col-md-2">
                                    <select name="status" class="form-control">
                                        <option value="">-- Trạng thái --</option>

                                        <option value="active" <?= (($_GET['status'] ?? '') == 'active') ? 'selected' : '' ?>>
                                            Hoạt động
                                        </option>

                                        <option value="inactive" <?= (($_GET['status'] ?? '') == 'inactive') ? 'selected' : '' ?>>
                                            Ngưng hoạt động
                                        </option>
                                    </select>
                                </div>

                                <!-- MIN CAPACITY -->
                                <div class="col-md-2">
                                    <input type="number" name="min_capacity" class="form-control" min="1" max="500" step="1"
                                        placeholder="Sức chứa từ"
                                        value="<?= htmlspecialchars($_GET['min_capacity'] ?? '') ?>">
                                </div>

                                <!-- MAX CAPACITY -->
                                <div class="col-md-2">
                                    <input type="number" name="max_capacity" class="form-control" placeholder="Đến" min="1" max="500" step="1"
                                        value="<?= htmlspecialchars($_GET['max_capacity'] ?? '') ?>">
                                </div>

                                <!-- BUTTON -->
                                <div class="col-md-3 d-flex gap-2">
                                    <button class="btn btn-primary">
                                        <i class="bi bi-search"></i> Lọc
                                    </button>

                                    <a href="?module=room" class="btn btn-secondary">
                                        Bỏ lọc
                                    </a>
                                </div>

                            </div>
                        </form>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>STT</th>
                                        <th>Tên phòng</th>
                                        <th>Sức chứa</th>
                                        <th>Trạng thái</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $stt = $offset + 1; ?>
                                    <?php foreach ($rooms as $r): ?>
                                        <tr>
                                            <td><?= $stt++ ?></td>
                                            <td><?= $r['name'] ?></td>
                                            <td><?= $r['capacity'] ?></td>
                                            <td>
                                                <?= $r['status'] == 'active'
                                                    ? '<span class="badge bg-success">Hoạt động</span>'
                                                    : '<span class="badge bg-danger">Ngưng hoạt động</span>' ?>
                                            </td>
                                            <td>
                                                <a href="?module=room&action=edit&id=<?= $r['room_id'] ?>"
                                                    class="btn btn-warning btn-sm">Sửa</a>
                                                <?php if ($r['status'] == 'active'): ?>

                                                    <a href="?module=room&action=inactive&id=<?= $r['room_id'] ?>"
                                                        class="btn btn-danger btn-sm">
                                                        Ngưng hoạt động
                                                    </a>

                                                <?php else: ?>

                                                    <a href="?module=room&action=active&id=<?= $r['room_id'] ?>"
                                                        class="btn btn-success btn-sm"
                                                        onclick="return confirm('Kích hoạt lại phòng này?')">
                                                        Kích hoạt lại
                                                    </a>

                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <!-- INFO -->
                            <div class="text-center mt-2">
                                Hiển thị
                                <?= $total > 0 ? $offset + 1 : 0 ?>
                                -
                                <?= min($offset + $limit, $total) ?>
                                /
                                <?= $total ?> phòng
                            </div>

                            <!-- PAGINATION -->
                            <?php if ($totalPages > 1): ?>

                                <?php
                                $query = http_build_query([
                                    'module' => 'room',
                                    'keyword' => $_GET['keyword'] ?? '',
                                    'status' => $_GET['status'] ?? '',
                                    'min_capacity' => $_GET['min_capacity'] ?? '',
                                    'max_capacity' => $_GET['max_capacity'] ?? ''
                                ]);

                                $start = max(1, $page - 2);
                                $end = min($totalPages, $page + 2);
                                ?>

                                <nav class="mt-3">
                                    <ul class="pagination justify-content-center">

                                        <!-- PREV -->
                                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                            <a class="page-link" href="?<?= $query ?>&page=<?= $page - 1 ?>">«</a>
                                        </li>

                                        <!-- PAGE -->
                                        <?php for ($i = $start; $i <= $end; $i++): ?>
                                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                                <a class="page-link" href="?<?= $query ?>&page=<?= $i ?>">
                                                    <?= $i ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>

                                        <!-- NEXT -->
                                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                            <a class="page-link" href="?<?= $query ?>&page=<?= $page + 1 ?>">»</a>
                                        </li>

                                    </ul>
                                </nav>

                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>
</div>
