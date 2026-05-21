<div id="main">
    <div class="page-heading">

        <!-- TITLE START-->
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 mb-3">
                    <h3>Quản lý ghi danh</h3>
                </div>
            </div>

            <!-- BREADCRUMB START-->
            <div class="row">
                <div class="col-12">
                    <nav class="breadcrumb-header">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="<?= BASE_URL ?>?module=dashboard&action=index">Trang chủ</a>
                            </li>
                            <li class="breadcrumb-item active">Quản lý ghi danh</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <!-- BREADCRUMB END-->

        </div>
        <!-- TITLE END-->

        <!-- ADD STUDENT FORM START-->
        <div class="card mb-3">
            <div class="card-header">
                <h5>Thêm học viên vào lớp</h5>
            </div>

            <div class="card-body">
                <?php if (!empty($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($_SESSION['success']) ?>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <?php if (!empty($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($_SESSION['error']) ?>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <form method="POST" action="?module=enrollment&action=store">

                    <div class="row">
                        <div class="col-md-6">
                            <select name="class_id" id="classSelect" class="form-control" required>
                                <option value="">-- Chọn lớp --</option>
                                <?php foreach ($classes as $c): ?>
                                    <?php
                                    $studentCount = (int) ($classEnrollmentCounts[$c['class_id']] ?? 0);
                                    $maxStudents = (int) ($c['max_students'] ?? 10);
                                    $isFull = $studentCount >= $maxStudents;
                                    ?>
                                    <option value="<?= $c['class_id'] ?>" <?= $isFull ? 'disabled' : '' ?>>
                                        <?= $c['course_name'] . ' - ' . $c['package_name'] . ' - ' . $c['class_code'] ?>
                                        (<?= $studentCount ?>/<?= $maxStudents ?> học viên<?= $isFull ? ' - Đã đủ, mở thêm lớp' : '' ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">
                                Số học viên tối đa lấy theo cấu hình của từng lớp.
                            </small>
                        </div>

                        <div class="col-md-6">
                            <select name="student_id" id="studentSelect" class="form-control" required disabled>
                                <option>-- Chọn lớp trước --</option>
                            </select>
                        </div>
                    </div>

                    <div id="classCapacityNotice" class="mt-2"></div>

                    <button class="btn btn-primary mt-3">Thêm</button>
                </form>
            </div>
        </div>
        <!-- ADD STUDENT FORM END-->

        <!-- TABLE START-->
        <div class="card">
            <div class="card-header">
                <h5>Danh sách ghi danh</h5>
            </div>

            <div class="card-body">
                <form method="GET" class="row g-2 mb-3">
                    <input type="hidden" name="module" value="enrollment">
                    <input type="hidden" name="action" value="index">

                    <div class="col-md-4">
                        <input type="text" name="keyword" class="form-control"
                            placeholder="Tìm theo tên học viên"
                            value="<?= htmlspecialchars($filters['keyword'] ?? '') ?>">
                    </div>

                    <div class="col-md-4">
                        <select name="payment_filter" class="form-control">
                            <option value="">-- Tất cả học phí --</option>
                            <option value="not_full" <?= (($filters['payment_filter'] ?? '') === 'not_full') ? 'selected' : '' ?>>
                                Chưa đóng đủ học phí
                            </option>
                            <option value="unpaid" <?= (($filters['payment_filter'] ?? '') === 'unpaid') ? 'selected' : '' ?>>
                                Chưa đóng
                            </option>
                            <option value="partial" <?= (($filters['payment_filter'] ?? '') === 'partial') ? 'selected' : '' ?>>
                                Đóng một phần
                            </option>
                            <option value="paid" <?= (($filters['payment_filter'] ?? '') === 'paid') ? 'selected' : '' ?>>
                                Đã đóng đủ
                            </option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <button class="btn btn-primary">Lọc</button>
                        <a href="?module=enrollment&action=index" class="btn btn-secondary">Reset</a>
                    </div>
                </form>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Học viên</th>
                            <th>Lớp</th>
                            <th>Tiến độ</th>
                            <th>Trạng thái</th>
                            <th>Học phí</th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($enrollments)): ?>
                            <?php foreach ($enrollments as $index => $e): ?>

                                <?php
                                $status = $e['status'];

                                if ($e['attended_sessions'] >= $e['total_sessions']) {
                                    $status = 'completed';
                                }

                                $remaining = $e['final_fee'] - $e['paid_amount'];
                                $isPaid = $e['payment_status'] == 'paid';

                                $percent = $e['final_fee'] > 0
                                    ? round(($e['paid_amount'] / $e['final_fee']) * 100)
                                    : 0;
                                ?>

                                <tr>
                                    <td class="text-center fw-bold">
                                        <?= $offset + $index + 1 ?>
                                    </td>

                                    <td><?= $e['student_name'] ?></td>

                                    <td>
                                        <?= $e['course_name'] ?> -
                                        <?= $e['package_name'] ?> -
                                        <?= $e['class_code'] ?>
                                    </td>

                                    <td class="text-center">
                                        <?= $e['attended_sessions'] ?> / <?= $e['total_sessions'] ?>
                                    </td>

                                    <!-- TRẠNG THÁI -->
                                    <td class="text-center">
                                        <?php if ($status == 'studying'): ?>
                                            <span class="badge bg-primary">Đang học</span>
                                        <?php elseif ($status == 'completed'): ?>
                                            <span class="badge bg-success">Hoàn thành</span>
                                        <?php elseif ($status == 'paused'): ?>
                                            <span class="badge bg-warning text-dark">Tạm dừng</span>
                                        <?php elseif ($status == 'dropped'): ?>
                                            <span class="badge bg-danger">Nghỉ học</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- HỌC PHÍ -->
                                    <td>
                                        <div>
                                            <b><?= number_format($e['paid_amount']) ?></b> /
                                            <?= number_format($e['final_fee']) ?>
                                        </div>

                                        <small class="text-danger">
                                            Còn nợ: <?= number_format($remaining) ?>
                                        </small>

                                        <div class="progress mt-1" style="height:6px;">
                                            <div class="progress-bar <?= $percent == 100 ? 'bg-success' : 'bg-warning' ?>"
                                                style="width: <?= $percent ?>%">
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex flex-column align-items-center gap-2">

                                            <!-- NHÓM 1: QUẢN LÝ TRẠNG THÁI -->
                                            <div class="btn-group w-100">
                                                <?php if ($status == 'studying'): ?>
                                                    <button type="button" class="btn btn-sm btn-info dropdown-toggle"
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                        Trạng thái
                                                    </button>
                                                    <ul class="dropdown-menu shadow">
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="?module=enrollment&action=changeStatus&id=<?= $e['enrollment_id'] ?>&status=paused"
                                                                onclick="return confirm('Tạm dừng học viên này?')">
                                                                <i class="bi bi-pause-circle text-warning"></i> Tạm dừng học
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="?module=enrollment&action=changeStatus&id=<?= $e['enrollment_id'] ?>&status=dropped"
                                                                onclick="return confirm('Xác nhận cho học viên nghỉ học?')">
                                                                <i class="bi bi-x-circle text-danger"></i> Nghỉ luôn
                                                            </a>
                                                        </li>
                                                    </ul>
                                                <?php elseif ($status == 'paused' || $status == 'dropped'): ?>
                                                    <a href="?module=enrollment&action=changeStatus&id=<?= $e['enrollment_id'] ?>&status=studying"
                                                        class="btn btn-sm btn-success w-100"
                                                        onclick="return confirm('Cho học viên đi học lại?')">
                                                        <i class="bi bi-play-circle"></i> Tiếp tục học
                                                    </a>
                                                <?php elseif ($status == 'completed'): ?>
                                                    <span class="badge bg-light-success text-success border border-success w-100">
                                                        <i class="bi bi-check-all"></i> Hoàn thành
                                                    </span>
                                                <?php endif; ?>
                                            </div>

                                            <!-- NHÓM 2: QUẢN LÝ HỌC PHÍ (GỘP DROPDOWN) -->
                                            <div class="btn-group w-100">
                                                <?php if (!$isPaid && $remaining > 0): ?>
                                                    <button type="button" class="btn btn-sm btn-primary dropdown-toggle"
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="bi bi-credit-card"></i> Đóng học phí
                                                    </button>
                                                    <ul class="dropdown-menu shadow dropdown-menu-end">
                                                        <li>
                                                            <h6 class="dropdown-header">Phương thức thanh toán</h6>
                                                        </li>
                                                        <li>
                                                            <button class="dropdown-item" type="button"
                                                                onclick="openPayModal(<?= $e['enrollment_id'] ?>, <?= (int) $e['final_fee'] ?>, <?= (int) $e['paid_amount'] ?>)">
                                                                <i class="bi bi-cash-stack text-primary"></i> Thủ công (Tiền
                                                                mặt/Chuyển khoản)
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="?module=enrollment&action=payment&id=<?= $e['enrollment_id'] ?>">
                                                                <i class="bi bi-qr-code-scan text-success"></i> Thanh toán trực
                                                                tuyến (VNPay)
                                                            </a>
                                                        </li>
                                                    </ul>
                                                <?php else: ?>
                                                    <div class="py-1">
                                                        <span
                                                            class="badge bg-light-success text-success border border-success w-100">
                                                            <i class="bi bi-check-all"></i> Đã đủ tiền
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                        </div>
                                    </td>
                                </tr>

                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    Không có dữ liệu
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- PAGINATION -->
                <div class="text-center mt-2">
                    Hiển thị
                    <?= $total > 0 ? $offset + 1 : 0 ?> -
                    <?= min($offset + $limit, $total) ?> /
                    <?= $total ?> ghi danh
                </div>

                <?php if ($totalPages > 1): ?>
                    <nav class="mt-3">
                        <ul class="pagination justify-content-center">
                            <?php
                            $baseQuery = [
                                'module' => 'enrollment',
                                'action' => 'index',
                                'keyword' => $filters['keyword'] ?? '',
                                'payment_filter' => $filters['payment_filter'] ?? ''
                            ];
                            ?>
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($baseQuery, ['page' => $page - 1])) ?>">«</a>
                            </li>

                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($baseQuery, ['page' => $i])) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($baseQuery, ['page' => $page + 1])) ?>">»</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>

            </div>
        </div>
        <!-- TABLE END -->

    </div>
</div>

<!-- MODAL -->
<div class="modal fade" id="payModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="payForm">

                <div class="modal-header">
                    <h5 class="modal-title">Thanh toán học phí</h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                    </button>
                </div>

                <div class="modal-body">

                    <input type="hidden" name="enrollment_id" id="enrollment_id">

                    <div class="mb-2">
                        <label class="fw-bold">Tổng học phí:</label>
                        <div id="total_fee">0</div>
                    </div>

                    <div class="mb-2">
                        <label class="fw-bold">Đã thanh toán:</label>
                        <div id="paid_amount">0</div>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold text-danger">
                            Còn lại:
                        </label>

                        <div id="remaining" class="text-danger fw-bold">
                            0
                        </div>
                    </div>

                    <div class="mb-3">

                        <label class="form-label fw-bold">
                            Hình thức thanh toán
                        </label>

                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="pay_type" value="full" id="pay_full"
                                checked>

                            <label class="form-check-label" for="pay_full">
                                Thanh toán đầy đủ
                            </label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="pay_type" value="partial"
                                id="pay_partial">

                            <label class="form-check-label" for="pay_partial">
                                Thanh toán một phần
                            </label>
                        </div>

                    </div>

                    <div class="mb-3">

                        <label class="form-label fw-bold">
                            Số tiền thanh toán
                        </label>

                        <input type="number" name="amount" id="amountInput" class="form-control" min="1000" required>

                        <small class="text-muted">
                            Không được vượt quá số tiền còn lại
                        </small>

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Đóng
                    </button>

                    <button type="submit" class="btn btn-success" id="submitBtn">
                        Xác nhận thanh toán
                    </button>

                </div>

            </form>

        </div>
    </div>
</div>

<script>

    let currentRemaining = 0;

    // =============================
    // FORMAT MONEY
    // =============================
    function formatMoney(num) {

        return Number(num || 0)
            .toLocaleString('vi-VN') + ' VNĐ';
    }

    // =============================
    // OPEN PAYMENT MODAL
    // =============================
    function openPayModal(id, total, paid) {

        total = Number(total) || 0;
        paid = Number(paid) || 0;

        currentRemaining = Math.max(0, total - paid);

        // Nếu đã thanh toán đủ
        if (currentRemaining <= 0) {

            alert('Đơn ghi danh đã thanh toán đủ');
            return;
        }

        // hidden id
        document.getElementById('enrollment_id').value = id;

        // render info
        document.getElementById('total_fee').innerText =
            formatMoney(total);

        document.getElementById('paid_amount').innerText =
            formatMoney(paid);

        document.getElementById('remaining').innerText =
            formatMoney(currentRemaining);

        const amountInput =
            document.getElementById('amountInput');

        // set max
        amountInput.max = currentRemaining;

        // default full payment
        amountInput.value = currentRemaining;

        amountInput.readOnly = true;

        document.querySelector(
            'input[name="pay_type"][value="full"]'
        ).checked = true;

        let modal = new bootstrap.Modal(
            document.getElementById('payModal')
        );

        modal.show();
    }

    // =============================
    // DOM READY
    // =============================
    document.addEventListener("DOMContentLoaded", function () {

        // =============================
        // LOAD STUDENT
        // =============================
        const classSelect =
            document.getElementById('classSelect');

        const studentSelect =
            document.getElementById('studentSelect');

        const classCapacityNotice =
            document.getElementById('classCapacityNotice');

        if (classSelect && studentSelect) {

            classSelect.addEventListener('change', function () {

                let classId = this.value;

                studentSelect.innerHTML =
                    '<option>Đang tải...</option>';

                studentSelect.disabled = true;
                if (classCapacityNotice) {
                    classCapacityNotice.innerHTML = '';
                }

                if (!classId) {

                    studentSelect.innerHTML =
                        '<option>-- Chọn lớp trước --</option>';
                    if (classCapacityNotice) {
                        classCapacityNotice.innerHTML = '';
                    }

                    return;
                }

                fetch(
                    `?module=enrollment&action=getAvailableStudents&class_id=${classId}`
                )
                    .then(res => res.json())
                    .then(data => {

                        let html =
                            '<option value="">-- Chọn học viên --</option>';

                        if (data && data.full) {
                            html =
                                '<option>Lớp đã đủ học viên</option>';

                            if (classCapacityNotice) {
                                classCapacityNotice.innerHTML =
                                    `<div class="alert alert-warning mb-0">${data.message}</div>`;
                            }

                            studentSelect.disabled = true;
                            studentSelect.innerHTML = html;
                            return;
                        }

                        const students = Array.isArray(data) ? data : (data.students || []);

                        if (!Array.isArray(students) || students.length === 0) {

                            html =
                                '<option>✔ Tất cả học viên đã được ghi danh</option>';

                        } else {

                            students.forEach(s => {

                                html += `
                                <option value="${s.student_id}">
                                    ${s.student_name}
                                </option>
                            `;
                            });
                        }

                        studentSelect.innerHTML = html;
                        studentSelect.disabled = false;
                        if (classCapacityNotice) {
                            classCapacityNotice.innerHTML = '';
                        }
                    })
                    .catch(err => {

                        console.error(err);

                        studentSelect.innerHTML =
                            '<option>Lỗi load dữ liệu</option>';
                    });

            });
        }

        // =============================
        // PAYMENT LOGIC
        // =============================
        const form =
            document.getElementById('payForm');

        const amountInput =
            document.getElementById('amountInput');

        // =============================
        // RADIO CHANGE
        // =============================
        document
            .querySelectorAll('input[name="pay_type"]')
            .forEach(radio => {

                radio.addEventListener('change', function () {

                    if (this.value === 'full') {

                        amountInput.value =
                            currentRemaining;

                        amountInput.readOnly = true;

                    } else {

                        amountInput.value = '';

                        amountInput.readOnly = false;

                        amountInput.focus();
                    }
                });
            });

        // =============================
        // REALTIME VALIDATION
        // =============================
        if (amountInput) {

            amountInput.addEventListener('input', function () {

                let value =
                    parseFloat(this.value) || 0;

                // âm
                if (value < 0) {

                    this.value = 0;
                    return;
                }

                // vượt quá
                if (value > currentRemaining) {

                    alert(
                        'Không được vượt quá số tiền còn lại'
                    );

                    this.value = currentRemaining;
                }
            });
        }

        // =============================
        // SUBMIT PAYMENT
        // =============================
        if (form) {

            form.addEventListener('submit', function (e) {

                e.preventDefault();

                let formData = new FormData(this);

                let amount =
                    parseFloat(formData.get('amount')) || 0;

                // validate
                if (amount <= 0) {

                    alert('Vui lòng nhập số tiền hợp lệ');
                    return;
                }

                // chặn thanh toán dư
                if (amount > currentRemaining) {

                    alert(
                        'Số tiền vượt quá số tiền còn lại'
                    );

                    return;
                }

                const btn =
                    document.getElementById('submitBtn');

                btn.disabled = true;
                btn.innerText = 'Đang xử lý...';

                fetch('?module=enrollment&action=payAjax', {

                    method: 'POST',
                    body: formData

                })
                    .then(res => res.json())
                    .then(data => {

                        btn.disabled = false;
                        btn.innerText =
                            'Xác nhận thanh toán';

                        if (data.success) {

                            alert('Thanh toán thành công');

                            location.reload();

                        } else {

                            alert(
                                data.message ||
                                'Thanh toán thất bại'
                            );
                        }

                    })
                    .catch(err => {

                        console.error(err);

                        btn.disabled = false;

                        btn.innerText =
                            'Xác nhận thanh toán';

                        alert('Lỗi kết nối server');
                    });

            });
        }

    });

</script>
