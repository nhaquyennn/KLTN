<?php
// Tách dữ liệu theo loại để hiển thị (Lọc từ biến $salary_levels của Controller)
$type_filter = $_GET['type'] ?? 'per_session';
$filtered_levels = array_filter($salary_levels, function ($item) use ($type_filter) {
    return $item['type'] === $type_filter;
});

// Tính tổng GV để làm progress bar
$total_gv_all = array_sum(array_column($salary_levels, 'teacher_count'));
?>
<div id="main">
    <header class="mb-3">
        <a href="#" class="burger-btn d-block d-xl-none"><i class="bi bi-justify fs-3"></i></a>
    </header>

    <div class="page-heading">
            <div class="page-heading" style="margin-bottom: 0px;padding-bottom: 0px;">

                <!-- TITLE -->
                <div class="page-title mb-3">

                    <!-- HÀNG 1 -->
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-2">

                        <!-- LEFT -->
                        <div>

                            <h3 class="mb-1">Cấu hình lương giảng viên</h3>

                            <!-- BREADCRUMB -->
                            <nav class="breadcrumb-header">

                                <ol class="breadcrumb mb-0">

                                    <li class="breadcrumb-item">
                                        <a href="<?= BASE_URL ?>?module=dashboard&action=index">
                                            Trang chủ
                                        </a>
                                    </li>

                                    <li class="breadcrumb-item active">
                                        Cấu hình lương
                                    </li>

                                </ol>

                            </nav>

                        </div>

                        <!-- RIGHT -->
                        <div class="d-flex gap-2 flex-wrap text-md-end">

                            <a href="?module=teacher&action=payroll"
                                class="btn btn-secondary">

                                <i class="bi bi-table"></i>
                                Bảng lương tháng
                            </a>

                            <form action="?module=teacher&action=payroll"
                                method="POST"
                                class="m-0">

                                <button type="submit"
                                    name="calculate"
                                    class="btn btn-primary">

                                    <i class="bi bi-calculator"></i>
                                    Tính lương tháng <?= date('m') ?>

                                </button>

                            </form>

                        </div>

                    </div>

                </div>
                <!-- TITLE END -->

            </div>

            <div class="card mt-4" style="border:none; box-shadow: 0 10px 30px rgba(0,0,0,0.05)">
                <div class="card-head"
                    style="padding: 20px; border-bottom: 1px solid #f1f1f1; display:flex; justify-content:space-between; align-items:center">
                    <div>
                        <div class="card-title" style="font-size:15px; font-weight:700">Cấu hình bậc lương</div>
                    </div>
                    <div style="display:flex;gap:12px;align-items:center">
                        <div class="type-toggle"
                            style="background:#f1f3f9; padding:4px; border-radius:10px; display:flex; gap:4px">
                            <button class="type-btn <?= ($type_filter == 'per_session') ? 'active' : '' ?>"
                                onclick="location.href='?module=teacher&action=salary_config&type=per_session'">Lương theo
                                buổi</button>
                            <button class="type-btn <?= ($type_filter == 'monthly') ? 'active' : '' ?>"
                                onclick="location.href='?module=teacher&action=salary_config&type=monthly'">Lương cố
                                định</button>
                        </div>
                        <button class="btn btn-primary" style="background:#435ebe; border:none" onclick="saveLevels()">Lưu cấu
                            hình</button>
                    </div>
                </div>
            <div class="table-responsive">
                <table class="table" style="margin:0">
                    <thead>
                        <tr style="background:#fcfcfd">
                            <th style="width:80px; padding:15px 24px; font-size:11px; color:#929292; border:none">Bậc</th>
                            <th style="border:none; font-size:11px; color:#929292">Tên bậc lương</th>
                            <th style="border:none; font-size:11px; color:#929292">Buổi tích lũy</th>
                            <th style="border:none; font-size:11px; color:#929292">
                                <?= ($type_filter == 'per_session') ? 'Lương / buổi' : 'Lương / tháng' ?>
                            </th>
                            <th style="border:none; font-size:11px; color:#929292">Số lượng giảng viên</th>
                            <th style="width:100px; border:none"></th>
                        </tr>
                    </thead>

                    <tbody id="level-tbody">
                <?php if (!empty($filtered_levels)): ?>
                    <?php foreach ($filtered_levels as $level): // Sử dụng biến đã lọc ở đây
                        $count = $level['teacher_count'] ?? 0;
                        $lvl = $level['level'];
                    ?>
        
                <tr style="vertical-align:middle; border-bottom:1px solid #f8f9fa" class="level-row"
                    data-id="<?= $level['id'] ?>"
                    data-level="<?= $level['level'] ?>">
                    <td style="padding:16px 24px">
                        <span class="level-badge l<?= $lvl ?>"><?= $lvl ?></span>
                    </td>

                    <td>
                        <input name="level_name[]" class="table-input" style="width:200px"
                            value="<?= htmlspecialchars($level['level_name']) ?>">
                    </td>

                    <td>
                        <div style="display:flex; align-items:center; gap:5px">
                            <input 
                                name="sessions[]"
                                class="table-input text-center"
                                style="
                                    width:60px;
                                    <?= ($lvl == 1) ? 'opacity:0.5;background:#f5f5f5;color:#999;' : '' ?>
                                    <?= ($type_filter == 'monthly') ? 'opacity:0.5' : '' ?>
                                "
                                value="<?= ($lvl == 1) ? 0 : $level['requirement_sessions'] ?>"
                                <?= ($lvl == 1 || $type_filter == 'monthly') ? 'readonly' : '' ?>
                            >
                            <span style="font-size:12px; color:#888">buổi</span>
                        </div>
                    </td>

                <td>
                        <div style="display:flex; align-items:center; gap:5px"> 
                            <input 
                                type="text"
                                name="amount[]"
                                class="table-input fw-bold text-end money-input"
                                style="width:110px; color:#435ebe"
                                value="<?= (int)$level['amount'] ?>"
                            >
                        
                            <span style="font-size:12px; font-weight:600; color:#435ebe">
                                vnđ
                            </span>
                        </div>
                    </td>

                    <td>
                        <div class="gv-box <?= $count > 0 ? 'gv-box-active' : 'gv-box-empty' ?>">
                            <span class="gv-number"><?= $count ?></span>
                            <span class="gv-text">Giảng viên</span>
                        </div>
                    </td>

                    <td style="text-align:right; padding-right:24px; white-space: nowrap;">
                        <a href="?module=teacher&action=index&level_id=<?= $level['id'] ?>" 
                        class="btn-sm" 
                        style="text-decoration: none; background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; color: #333; font-size: 12px; font-weight: 600; white-space: nowrap;">
                            <i class="bi bi-eye" style="font-size: 14px;"></i>
                            <span>Chi tiết</span>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center p-4 text-muted">Không có dữ liệu cho loại hình này.</td></tr>
            <?php endif; ?>
        </tbody>
        </table>
        </div>
    </div>
</div>

<script>
async function saveLevels() {
    const levels = Array.from(document.querySelectorAll('.level-row')).map(function (row) {
        const nameInput = row.querySelector('input[name="level_name[]"]');
        const sessionsInput = row.querySelector('input[name="sessions[]"]');
        const amountInput = row.querySelector('input[name="amount[]"]');

        return {
            id: row.dataset.id,
            level_name: nameInput ? nameInput.value.trim() : '',
            requirement_sessions: sessionsInput ? sessionsInput.value : 0,
            amount: amountInput ? amountInput.value.replace(/\./g, '') : 0
        };
    });

    try {
        const response = await fetch('?module=teacher&action=saveSalaryLevels', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ levels })
        });

        const data = await response.json();

        if (data.success) {
            alert('Đã lưu cấu hình bậc lương.');
            window.location.reload();
            return;
        }

        alert('Không thể lưu cấu hình lương.');
    } catch (error) {
        console.error(error);
        alert('Không thể kết nối máy chủ.');
    }
}

    // ======================
// MONEY INPUT FORMAT
// ======================
document.addEventListener('DOMContentLoaded', function () {

    function formatVND(raw) {
        if (!raw) return '';
        return raw.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function parseVND(str) {
        return str.replace(/\./g, '').replace(/\D/g, '');
    }

    function formatMoney(input) {
        const raw = parseVND(input.value);
        input.value = raw ? formatVND(raw) : '';
    }

    document.querySelectorAll('.money-input').forEach(function (input) {

        // Tránh bind 2 lần nếu script chạy lại
        if (input.dataset.moneyBound) return;
        input.dataset.moneyBound = 'true';

        // Format lúc trang load
        formatMoney(input);

        input.addEventListener('input', function () {
            const el        = this;
            const oldVal    = el.value;
            const cursorPos = el.selectionStart;

            // Đếm số ký tự THUẦN (không phải dấu chấm) trước cursor
            const rawBeforeCursor = parseVND(oldVal.slice(0, cursorPos)).length;

            // Lấy toàn bộ số thuần
            let raw = parseVND(oldVal);
            if (!raw) { el.value = ''; return; }
            if (raw.length > 15) raw = raw.slice(0, 15);

            // Format lại
            const formatted = formatVND(raw);
            el.value = formatted;

            // Tính lại vị trí cursor sau format
            let newCursor = formatted.length; // mặc định cuối chuỗi
            let count = 0;
            for (let i = 0; i < formatted.length; i++) {
                if (formatted[i] !== '.') count++;
                if (count === rawBeforeCursor) {
                    newCursor = i + 1;
                    break;
                }
            }
            el.setSelectionRange(newCursor, newCursor);
        });

        // Trước khi submit form → trả về số thuần để server nhận đúng
        const form = input.closest('form');
        if (form && !form.dataset.moneyBound) {
            form.dataset.moneyBound = 'true';
            form.addEventListener('submit', function () {
                form.querySelectorAll('.money-input').forEach(function (inp) {
                    inp.value = parseVND(inp.value);
                });
            });
        }
    });
});
</script>
