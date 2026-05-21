<?php $isTeacher = ($_SESSION['user']['role'] ?? '') === 'teacher'; ?>

<div id="app">
    <div id="main">
        <header class="mb-3">
            <a href="#" class="burger-btn d-block d-xl-none">
                <i class="bi bi-justify fs-3"></i>
            </a>
        </header>

        <div class="page-heading">

            <!-- TITLE START -->
            <div class="page-title mb-3">

                <!-- HÀNG 1 -->
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">

                    <h3 class="mb-0">Danh sách học viên</h3>

                    <?php if (!$isTeacher): ?>
                        <a href="?module=student&action=create" class="btn btn-success">
                            Thêm học viên
                        </a>
                    <?php endif; ?>

                </div>

                <!-- BREADCRUMB START -->
                <nav class="breadcrumb-header">

                    <ol class="breadcrumb mb-0">

                        <li class="breadcrumb-item">
                            <a href="<?= BASE_URL ?>?module=dashboard&action=index">
                                Trang chủ
                            </a>
                        </li>

                        <li class="breadcrumb-item active">
                            Học viên
                        </li>

                    </ol>

                </nav>
                <!-- BREADCRUMB END -->

            </div>
            <!-- TITLE END -->


            <!-- CONTENT START -->
            <div class="card">

                <!-- FILTER START-->
                <form method="GET" class="mb-3">
                    <input type="hidden" name="module" value="student">

                    <div class="row">

                        <!-- KEYWORD -->
                        <div class="col-md-4">
                            <input type="text" name="keyword" class="form-control"
                                placeholder="Tên học viên / SĐT / phụ huynh" value="<?= $_GET['keyword'] ?? '' ?>">
                        </div>

                        <!-- STATUS -->
                        <div class="col-md-3">
                            <select name="status" class="form-control">
                                <option value="">-- Trạng thái --</option>
                                <option value="1" <?= (($_GET['status'] ?? '') == '1') ? 'selected' : '' ?>>
                                    Đang học
                                </option>
                                <option value="0" <?= (($_GET['status'] ?? '') == '0') ? 'selected' : '' ?>>
                                    Ngừng
                                </option>
                            </select>
                        </div>

                        <!-- BUTTON -->
                        <div class="col-md-3">
                            <button class="btn btn-primary">Lọc</button>
                            <a href="?module=student" class="btn btn-secondary">Reset</a>
                        </div>

                    </div>
                </form>
                <!-- FILTER END-->
                <div class="card-content">
                    <div class="table-responsive">

                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Học viên</th>
                                    <th>Phụ huynh</th>
                                    <th>SĐT</th>
                                    <th>Ngày sinh</th>
                                    <th>Trạng thái</th>
                                    <?php if (!$isTeacher): ?>
                                        <th class="text-center">ACTION</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($students as $index => $s): ?>
                                    <tr>

                                        <!-- STT -->
                                        <td>
                                            <?= $offset + $index + 1 ?>
                                        </td>

                                        <!-- TÊN HỌC VIÊN -->
                                        <td>
                                            <b>
                                                <?= htmlspecialchars($s['student_name']) ?>
                                            </b>
                                        </td>

                                        <!-- PHỤ HUYNH -->
                                        <td>
                                            <?= htmlspecialchars($s['parent_name']) ?>
                                        </td>

                                        <!-- SĐT -->
                                        <td>
                                            <?= htmlspecialchars($s['student_phone']) ?>
                                        </td>

                                        <!-- NGÀY SINH -->
                                        <td>
                                            <?= $s['date_of_birth'] ?>
                                        </td>

                                        <!-- TRẠNG THÁI -->
                                        <td>
                                            <?php if ($s['status'] == 1): ?>
                                                <span class="badge bg-success">Đang học</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Ngừng</span>
                                            <?php endif; ?>
                                        </td>

                                        <?php if (!$isTeacher): ?>
                                            <!-- ACTION -->
                                            <td class="text-center">

                                                <!-- EDIT -->
                                                <a href="?module=student&action=edit&id=<?= $s['student_id'] ?>"
                                                    class="btn btn-sm btn-warning">
                                                    Sửa
                                                </a>

                                                <?php if ($s['status'] == 1): ?>
                                                    <a href="?module=student&action=archive&id=<?= $s['student_id'] ?>"
                                                        class="btn btn-sm btn-secondary"
                                                        onclick="return confirm('Lưu trữ học viên này?')">
                                                        Lưu trữ
                                                    </a>
                                                <?php else: ?>
                                                    <a href="?module=student&action=restore&id=<?= $s['student_id'] ?>"
                                                        class="btn btn-sm btn-success"
                                                        onclick="return confirm('Khôi phục học viên này?')">
                                                        Khôi phục
                                                    </a>
                                                <?php endif; ?>

                                            </td>
                                        <?php endif; ?>

                                    </tr>
                                <?php endforeach; ?>
                            </tbody>

                        </table>

                    </div>
                </div>
            </div>
            <!-- CONTENT END -->

            <!-- NAVIGATION START -->
            <!-- INFO -->
            <div class="text-center mt-2">
                Hiển thị
                <?= $total > 0 ? $offset + 1 : 0 ?>
                -
                <?= min($offset + $limit, $total) ?>
                /
                <?= $total ?> học viên
            </div>

            <!-- PAGINATION START-->
            <?php if ($totalPages > 1): ?>
                <nav class="mt-3">
                    <ul class="pagination justify-content-center">

                        <?php
                        $baseQuery = [
                            'module' => 'student',
                            'keyword' => $filters['keyword'] ?? '',
                            'status' => $filters['status'] ?? ''
                        ];
                        ?>

                        <!-- PREV -->
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link"
                                href="?<?= http_build_query(array_merge($baseQuery, ['page' => $page - 1])) ?>">
                                «
                            </a>
                        </li>

                        <?php
                        $start = max(1, $page - 2);
                        $end = min($totalPages, $page + 2);
                        ?>

                        <!-- PAGE -->
                        <?php for ($i = $start; $i <= $end; $i++): ?>
                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($baseQuery, ['page' => $i])) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <!-- NEXT -->
                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link"
                                href="?<?= http_build_query(array_merge($baseQuery, ['page' => $page + 1])) ?>">
                                »
                            </a>
                        </li>

                    </ul>
                </nav>
            <?php endif; ?>
            <!-- PAGINATION END -->

        </div>
    </div>