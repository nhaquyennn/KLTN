<div id="main">
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-7 mb-3">
                    <h3>Cấu hình WiFi điểm danh</h3>
                    <div class="text-muted">
                        Chỉ các thiết bị nằm trong IP/subnet bên dưới mới được lưu điểm danh gương mặt.
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <nav class="breadcrumb-header">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="<?= BASE_URL ?>?module=dashboard&action=index">Trang chủ</a>
                            </li>
                            <li class="breadcrumb-item active">Cấu hình WiFi điểm danh</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= nl2br(htmlspecialchars(str_replace('<br>', "\n", $_SESSION['error']))) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="row g-3">
            <div class="col-12 col-xl-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Mạng được phép điểm danh</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="?module=face&action=wifiConfig">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" role="switch" id="enabledSwitch"
                                    name="enabled" <?= !empty($config['enabled']) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-bold" for="enabledSwitch">
                                    Bật giới hạn WiFi khi điểm danh
                                </label>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold" for="allowedCidrs">
                                    IP/subnet được phép
                                </label>
                                <textarea id="allowedCidrs" name="allowed_cidrs" class="form-control" rows="8" maxlength="5000"
                                    placeholder="192.168.1.0/24&#10;127.0.0.1/32"><?= htmlspecialchars(implode("\n", $config['allowed_cidrs'] ?? [])) ?></textarea>
                                <small class="text-muted">
                                    Nhập mỗi dòng một IP hoặc CIDR. Ví dụ: 192.168.1.0/24, 10.0.0.15, ::1/128.
                                </small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold" for="blockedMessage">
                                    Thông báo khi không đúng WiFi
                                </label>
                                <textarea id="blockedMessage" name="blocked_message" class="form-control" rows="3" maxlength="500"><?= htmlspecialchars($config['blocked_message'] ?? '') ?></textarea>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i>
                                    Lưu cấu hình
                                </button>
                                <a href="?module=face&action=attendance" class="btn btn-outline-secondary">
                                    Quay lại điểm danh
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Trạng thái hiện tại</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="text-muted small">IP thiết bị hiện tại</div>
                            <div class="fw-bold"><?= htmlspecialchars($currentAccess['client_ip'] ?? 'Không xác định') ?></div>
                        </div>

                        <div class="mb-3">
                            <div class="text-muted small">Kết quả kiểm tra</div>
                            <span class="badge <?= !empty($currentAccess['allowed']) ? 'bg-success' : 'bg-danger' ?>">
                                <?= !empty($currentAccess['allowed']) ? 'Được phép điểm danh' : 'Không được phép' ?>
                            </span>
                        </div>

                        <div class="alert alert-light border mb-0">
                            <?= htmlspecialchars($currentAccess['message'] ?? '') ?>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Gợi ý</h5>
                    </div>
                    <div class="card-body text-muted">
                        <p class="mb-2">Nếu WiFi trung tâm cấp IP dạng 192.168.0.x, nhập 192.168.0.0/24.</p>
                        <p class="mb-0">Nếu chỉ cho phép một máy cụ thể, nhập IP đầy đủ của máy đó.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
