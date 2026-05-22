<?php
$roleLabels = [
    'admin' => 'Admin',
    'teacher' => 'Giảng viên',
    'parent' => 'Phụ huynh',
    'student' => 'Học viên'
];
$showDeleted = !empty($showDeleted);
?>

<div id="main">
    <header class="mb-3">
        <a href="#" class="burger-btn d-block d-xl-none">
            <i class="bi bi-justify fs-3"></i>
        </a>
    </header>

    <div class="page-heading">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
            <div>
                <h3 class="mb-1"><?= $showDeleted ? 'Tài khoản đã xóa' : 'Quản lý tài khoản' ?></h3>
                <nav class="breadcrumb-header">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="?module=dashboard&action=index">Trang chủ</a></li>
                        <?php if ($showDeleted): ?>
                            <li class="breadcrumb-item"><a href="?module=account&action=index">Tài khoản</a></li>
                            <li class="breadcrumb-item active">Đã xóa</li>
                        <?php else: ?>
                            <li class="breadcrumb-item active">Tài khoản</li>
                        <?php endif; ?>
                    </ol>
                </nav>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <?php if ($showDeleted): ?>
                    <a href="?module=account&action=index" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Danh sách tài khoản
                    </a>
                <?php else: ?>
                    <a href="?module=account&action=deleted" class="btn btn-outline-danger">
                        <i class="bi bi-trash"></i>
                        Xem tài khoản đã xóa
                    </a>
                    <a href="?module=account&action=create" class="btn btn-primary">
                        <i class="bi bi-person-plus"></i>
                        Tạo tài khoản
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-2 mb-3">
                    <input type="hidden" name="module" value="account">
                    <input type="hidden" name="action" value="<?= $showDeleted ? 'deleted' : 'index' ?>">

                    <div class="col-md-4">
                        <input type="text" name="keyword" class="form-control" maxlength="100"
                            placeholder="Tên / email / số điện thoại"
                            value="<?= htmlspecialchars($filters['keyword'] ?? '') ?>">
                    </div>

                    <div class="col-md-3">
                        <select name="role" class="form-control">
                            <option value="">-- Vai trò --</option>
                            <?php foreach ($roleLabels as $value => $label): ?>
                                <option value="<?= $value ?>" <?= (($filters['role'] ?? '') === $value) ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <select name="status" class="form-control">
                            <option value="">-- Trạng thái --</option>
                            <option value="1" <?= (($filters['status'] ?? '') === '1') ? 'selected' : '' ?>>Hoạt động</option>
                            <option value="0" <?= (($filters['status'] ?? '') === '0') ? 'selected' : '' ?>>Khóa</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <button class="btn btn-primary">Lọc</button>
                        <a href="?module=account&action=<?= $showDeleted ? 'deleted' : 'index' ?>" class="btn btn-secondary">Đặt lại</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Họ tên</th>
                                <th>Email</th>
                                <th>SĐT</th>
                                <th>Vai trò</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($accounts)): ?>
                                <?php foreach ($accounts as $index => $account): ?>
                                    <tr>
                                        <td><?= $offset + $index + 1 ?></td>
                                        <td><?= htmlspecialchars($account['name'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($account['email'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($account['phone'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($roleLabels[$account['role']] ?? $account['role']) ?></td>
                                        <td>
                                            <?php if ($showDeleted): ?>
                                                <span class="badge bg-danger">Đã xóa tài khoản</span>
                                            <?php elseif ((int) ($account['status'] ?? 0) === 1): ?>
                                                <span class="badge bg-success">Hoạt động</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Khóa</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($account['created_at'] ?? '') ?></td>
                                        <td class="text-center">
                                            <?php if ($showDeleted): ?>
                                                <a href="?module=account&action=restore&id=<?= (int) $account['user_id'] ?>"
                                                    class="btn btn-sm btn-success"
                                                    onclick="return confirm('Khôi phục tài khoản này?')">
                                                    Khôi phục
                                                </a>
                                                <a href="?module=account&action=forceDelete&id=<?= (int) $account['user_id'] ?>"
                                                    class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Xóa hẳn tài khoản này? Thao tác này không thể hoàn tác.')">
                                                    Xóa hẳn
                                                </a>
                                            <?php else: ?>
                                                <a href="?module=account&action=edit&id=<?= (int) $account['user_id'] ?>"
                                                    class="btn btn-sm btn-warning">
                                                    Sửa
                                                </a>
                                                <?php if ((int) ($account['status'] ?? 0) === 1): ?>
                                                    <a href="?module=account&action=lock&id=<?= (int) $account['user_id'] ?>"
                                                        class="btn btn-sm btn-secondary"
                                                        onclick="return confirm('Khóa tài khoản này?')">
                                                        Khóa
                                                    </a>
                                                <?php else: ?>
                                                    <a href="?module=account&action=unlock&id=<?= (int) $account['user_id'] ?>"
                                                        class="btn btn-sm btn-success"
                                                        onclick="return confirm('Mở khóa tài khoản này?')">
                                                        Mở khóa
                                                    </a>
                                                <?php endif; ?>
                                                <?php if ((int) $account['user_id'] !== (int) ($_SESSION['user']['id'] ?? 0)): ?>
                                                    <a href="?module=account&action=delete&id=<?= (int) $account['user_id'] ?>"
                                                        class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Xóa tài khoản đăng nhập này? Tài khoản sẽ chuyển sang danh sách đã xóa.')">
                                                        Xóa tài khoản
                                                    </a>
                                                <?php endif; ?>
                                                <?php if ((int) ($account['has_password'] ?? 1) === 0): ?>
                                                    <div class="text-muted small mt-1">Cần cập nhật mật khẩu để đăng nhập.</div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">Không có tài khoản nào.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPages > 1): ?>
                    <nav class="mt-3">
                        <ul class="pagination justify-content-center">
                            <?php
                            $baseQuery = [
                                'module' => 'account',
                                'action' => $showDeleted ? 'deleted' : 'index',
                                'keyword' => $filters['keyword'] ?? '',
                                'role' => $filters['role'] ?? '',
                                'status' => $filters['status'] ?? ''
                            ];
                            ?>

                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($baseQuery, ['page' => $i])) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
