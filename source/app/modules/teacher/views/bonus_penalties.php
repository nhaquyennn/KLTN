<div id="main">
    <header class="mb-3">
        <a href="#" class="burger-btn d-block d-xl-none">
            <i class="bi bi-justify fs-3"></i>
        </a>
    </header>

    <div class="page-heading">

        <!-- HEADER -->
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
            <div>
                <div style="font-size:18px;font-weight:500;color:var(--color-text-primary)">
                    Thưởng & Phạt giảng viên
                </div>
                <div style="font-size:13px;color:var(--color-text-secondary);margin-top:3px">
                    Tháng <?= $month ?> / <?= $year ?>
                </div>
            </div>
        </div>

        <!-- STATS -->
        <div class="card">
            <div class="stats">

                <div class="stat">
                    <div class="stat-label">Tổng phạt tháng này</div>
                    <div class="stat-num" style="color:#A32D2D">
                        <?= number_format($stats['total_penalty'] ?? 0) ?>đ
                    </div>
                    <div class="stat-sub">
                        <?= $stats['penalty_count'] ?? 0 ?> lần phạt
                    </div>
                </div>

                <div class="stat">
                    <div class="stat-label">Tổng thưởng tháng này</div>
                    <div class="stat-num" style="color:#27500A">
                        <?= number_format($stats['total_bonus'] ?? 0) ?>đ
                    </div>
                    <div class="stat-sub">
                        <?= $stats['bonus_count'] ?? 0 ?> lần thưởng
                    </div>
                </div>

                <div class="stat">
                    <div class="stat-label">Ảnh hưởng lương</div>
                    <div class="stat-num" style="color:#534AB7">
                        <?= number_format(($stats['total_bonus'] ?? 0) - ($stats['total_penalty'] ?? 0)) ?>đ
                    </div>
                    <div class="stat-sub">net tháng này</div>
                </div>

            </div>
        </div>

        <!-- MAIN -->
        <div class="card">
            <div class="tab-row">
                <button class="tab active" onclick="switchTab('penalty',this)">Thêm phạt</button>
                <button class="tab" onclick="switchTab('bonus',this)">Thêm thưởng</button>
                <button class="tab" onclick="switchTab('history',this)">Lịch sử</button>
            </div>

            <div id="toast" class="toast"></div>

            <!-- ================= TAB PHẠT ================= -->
            <div id="tab-penalty">
                <div class="form-section">
                    <!-- Vi phạm phổ biến -->
                    <div class="form-group">
                        <label class="form-label">Loại vi phạm</label>
                        <div style="
                                display:grid;
                                grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
                                gap:10px">

                            <!-- Đi trễ -->
                            <div class="quick-penalty" onclick="applyPenaltyPreset('late',50000,'Đi trễ')">

                                <div class="qp-title">Đi trễ</div>
                                <div class="qp-desc">Phạt gợi ý: 50.000đ</div>
                            </div>

                            <!-- Vắng -->
                            <div class="quick-penalty"
                                onclick="applyPenaltyPreset('absent',200000,'Vắng không báo trước')">

                                <div class="qp-title">Vắng không phép</div>
                                <div class="qp-desc">Phạt gợi ý: 200.000đ</div>
                            </div>

                            <!-- Chất lượng -->
                            <div class="quick-penalty"
                                onclick="applyPenaltyPreset('quality',100000,'Phản ánh chất lượng')">

                                <div class="qp-title">Phản ánh chất lượng</div>
                                <div class="qp-desc">Phạt gợi ý: 100.000đ</div>
                            </div>

                            <div class="quick-penalty" onclick="applyPenaltyPreset('other',0,'Vi phạm khác')">

                                <div class="qp-title">Vi phạm khác</div>
                            </div>

                        </div>
                    </div>
                    <!-- HÀNG 1 -->
                    <div class="form-row">

                        <div class="form-group">
                            <label class="form-label">Giảng viên</label>

                            <select class="form-select" id="p-teacher" onchange="updatePreview()">
                                <option value="">-- Chọn giảng viên --</option>

                                <?php foreach ($teachers as $t): ?>
                                    <option value="<?= $t['teacher_id'] ?>" data-salary="<?= $t['salary_value'] ?? 0 ?>">
                                        <?= $t['name'] ?>
                                    </option>
                                <?php endforeach; ?>

                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Buổi vi phạm</label>

                            <select class="form-select" id="p-session">
                                <option value="">-- Chọn buổi dạy --</option>

                                <?php foreach ($sessions as $s): ?>
                                    <option value="<?= $s['session_id'] ?>">
                                        <?= $s['class_name'] ?>
                                        · <?= date('d/m/Y', strtotime($s['session_date'])) ?>
                                        · <?= substr($s['start_time'], 0, 5) ?>
                                    </option>
                                <?php endforeach; ?>

                            </select>
                        </div>

                    </div>

                    <!-- HÀNG 2 -->
                    <div class="form-row">

                        <div class="form-group">
                            <label class="form-label">Mức phạt</label>
                            <input class="form-input" type="number" id="p-amount">

                        </div>

                        <div class="form-group">
                            <label class="form-label">Lý do</label>
                            <textarea class="form-input" id="p-reason"></textarea>
                        </div>

                    </div>

                    <div style="
                        display:flex;
                        align-items:center;
                        gap:8px;
                        flex-wrap:wrap;
                        margin-top:10px">

                        <span style="
                            font-size:12px;
                            color:var(--color-text-secondary)">
                            Gợi ý nhanh:
                        </span>

                        <button type="button" class="btn-sm" onclick="quickPenalty(50000)">
                            50.000đ
                        </button>

                        <button type="button" class="btn-sm" onclick="quickPenalty(100000)">
                            100.000đ
                        </button>

                        <button type="button" class="btn-sm" onclick="quickPenalty(150000)">
                            150.000đ
                        </button>

                    </div>
                    <button class="btn btn-red" onclick="submitPenalty()">Xác nhận phạt</button>

                </div>
            </div>
            <!-- ================= TAB THƯỞNG ================= -->
            <div id="tab-bonus" style="display:none">

                <div class="form-section">

                    <!-- ROW 1 -->
                    <div class="form-row">

                        <!-- loại thưởng -->
                        <div class="form-group">

                            <label class="form-label">
                                Loại thưởng
                            </label>

                            <div class="bonus-types">

                                <div class="btype selected" onclick="selectBType(this,'holiday')" id="bt-holiday">

                                    <div style="
                            display:flex;
                            align-items:center;
                            gap:8px;
                            margin-bottom:10px">

                                        <div style="
                                width:10px;
                                height:10px;
                                border-radius:50%;
                                background:#1D9E75">
                                        </div>

                                        <div class="btype-name">
                                            Dịp thưởng
                                        </div>

                                    </div>

                                    <select class="form-select" id="b-occasion">

                                        <option>
                                            Tết Nguyên Đán <?= $year + 1 ?>
                                        </option>

                                        <option>
                                            Ngày Nhà giáo 20/11
                                        </option>

                                        <option>
                                            30/4 - 1/5
                                        </option>

                                        <option>
                                            Thưởng hiệu suất
                                        </option>

                                        <option>
                                            Khác
                                        </option>

                                    </select>

                                </div>

                            </div>

                        </div>

                        <!-- giảng viên -->
                        <div class="form-group">

                            <label class="form-label">
                                Giảng viên
                            </label>

                            <select class="form-select" id="b-teacher" onchange="updateBonusPreview()">

                                <option value="">
                                    -- Chọn giảng viên --
                                </option>

                                <option value="all">
                                    Tất cả giảng viên
                                </option>

                                <?php foreach ($teachers as $t): ?>
                                    <option value="<?= $t['teacher_id'] ?>" data-base="<?= $t['salary_value'] ?? 0 ?>">

                                        <?= $t['name'] ?>

                                    </option>
                                <?php endforeach; ?>

                            </select>

                        </div>

                    </div>

                    <!-- ROW 2 -->
                    <div class="form-row">

                        <!-- tiền thưởng -->
                        <div class="form-group" id="bonus-amount-group">

                            <label class="form-label" id="bonus-amount-label">

                                Số tiền thưởng

                            </label>

                            <div style="
                    display:flex;
                    align-items:center;
                    gap:8px">

                                <input class="form-input" type="number" id="b-amount" placeholder="0" style="flex:1"
                                    oninput="updateBonusPreview()">

                                <span id="b-unit" style="
                        font-size:13px; font-weight: bolder;
                        color:var(--color-text-secondary);
                        white-space:nowrap">

                                    VNĐ

                                </span>

                            </div>

                            <!-- gợi ý nhanh -->
                            <div style="
                    display:flex;
                    align-items:center;
                    gap:8px;
                    flex-wrap:wrap;
                    margin-top:10px">

                                <span style="
                        font-size:12px;
                        color:var(--color-text-secondary)">

                                    Gợi ý nhanh:

                                </span>

                                <button type="button" class="btn-sm" onclick="quickBonus(50000)">

                                    50.000đ

                                </button>

                                <button type="button" class="btn-sm" onclick="quickBonus(100000)">

                                    100.000đ

                                </button>

                                <button type="button" class="btn-sm" onclick="quickBonus(150000)">

                                    150.000đ

                                </button>

                            </div>

                        </div>

                        <!-- ghi chú -->
                        <div class="form-group">

                            <label class="form-label">
                                Ghi chú
                            </label>

                            <textarea class="form-input" id="b-note" placeholder="Ghi chú thêm..."
                                style="min-height:92px"></textarea>

                        </div>

                    </div>

                    <!-- button -->
                    <button class="btn btn-green" style="margin-top:18px" onclick="submitBonus()">

                        Xác nhận thưởng

                    </button>

                </div>

            </div>

            <!-- ================= HISTORY ================= -->
            <div id="tab-history" style="display:none">
                <?php if (!empty($history)): ?>
                    <?php foreach ($history as $h): ?>
                        <!-- Thêm ID để JavaScript xác định dòng cần xóa -->
                        <div class="hist-item d-flex justify-content-between align-items-center mb-3 p-3 border-bottom"
                            id="penalty-row-<?= $h['id'] ?>">

                            <div class="hist-left d-flex align-items-start">
                                <div class="hist-dot mt-2 me-3"
                                    style="width:12px; height:12px; border-radius:50%; background: <?= $h['type'] == 'penalty' ? '#E24B4A' : '#1D9E75' ?>">
                                </div>

                                <div>
                                    <div class="hist-name" style="font-weight: bold; font-size: 1.1rem;">
                                        <?= htmlspecialchars($h['name']) ?>
                                    </div>

                                    <div class="hist-meta text-muted" style="font-size: 0.9rem;">
                                        <span class="badge <?= $h['type'] == 'penalty' ? 'bg-danger' : 'bg-success' ?> mb-1">
                                            <?= $h['type'] == 'penalty' ? 'Phạt' : 'Thưởng' ?>
                                        </span>
                                    </div>

                                    <div class="hist-reason text-dark italic" style="font-style: italic;">
                                        "<?= htmlspecialchars($h['reason']) ?>"
                                    </div>
                                </div>
                            </div>

                            <div class="text-end d-flex align-items-center">
                                <div class="me-4">
                                    <div class="hist-amount"
                                        style="font-weight: bold; font-size: 1.1rem; color: <?= $h['type'] == 'penalty' ? '#A32D2D' : '#27500A' ?>">
                                        <?= $h['type'] == 'penalty' ? '-' : '+' ?>
                                        <?= number_format($h['amount']) ?>đ
                                    </div>

                                    <div class="hist-date text-muted" style="font-size: 0.85rem;">
                                        <i class="bi bi-calendar3"></i> <?= date('d/m/Y H:i', strtotime($h['created_at'])) ?>
                                    </div>
                                </div>

                                <!-- NÚT XÓA PHẠT/THƯỞNG -->
                                <button class="btn btn-outline-danger btn-sm rounded-circle"
                                    onclick="deletePenalty(<?= $h['id'] ?>)" title="Xóa bản ghi này">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </div>

                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center p-5 text-muted">Chưa có lịch sử thưởng phạt nào.</div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>
<script>
    window.APP_MONTH = <?= $month ?>;
    window.APP_YEAR = <?= $year ?>;
    let currentPType = 'other';

    function switchTab(tab, button) {
        ['penalty', 'bonus', 'history'].forEach(function (name) {
            const el = document.getElementById('tab-' + name);
            if (el) {
                el.style.display = name === tab ? '' : 'none';
            }
        });

        document.querySelectorAll('.tab').forEach(function (item) {
            item.classList.remove('active');
        });

        if (button) {
            button.classList.add('active');
        }
    }

    function quickPenalty(amount) {
        document.getElementById('p-amount').value = amount;
        updatePreview();
    }

    function updatePreview() {
        return true;
    }

    function updateBonusPreview() {
        return true;
    }

    function selectBType(element) {
        document.querySelectorAll('.btype').forEach(function (item) {
            item.classList.remove('selected');
        });

        if (element) {
            element.classList.add('selected');
        }
    }

    async function submitPenalty() {
        const teacherId = document.getElementById('p-teacher').value;
        const amount = Number(document.getElementById('p-amount').value || 0);
        const reason = document.getElementById('p-reason').value.trim();

        if (!teacherId) {
            alert('Vui lòng chọn giảng viên.');
            return;
        }

        if (amount <= 0) {
            alert('Vui lòng nhập mức phạt.');
            return;
        }

        await saveTransaction({
            type: 'penalty',
            teacher_id: teacherId,
            amount: amount,
            reason: reason || 'Phạt giảng viên',
            month: window.APP_MONTH,
            year: window.APP_YEAR
        });
    }

    async function submitBonus() {
        const teacherId = document.getElementById('b-teacher').value;
        const amount = Number(document.getElementById('b-amount').value || 0);
        const note = document.getElementById('b-note').value.trim();
        const occasion = document.getElementById('b-occasion') ? document.getElementById('b-occasion').value : '';

        if (!teacherId) {
            alert('Vui lòng chọn giảng viên.');
            return;
        }

        if (amount <= 0) {
            alert('Vui lòng nhập số tiền thưởng.');
            return;
        }

        await saveTransaction({
            type: 'bonus',
            teacher_id: teacherId,
            amount: amount,
            reason: note || occasion || 'Thưởng giảng viên',
            month: window.APP_MONTH,
            year: window.APP_YEAR
        });
    }

    async function saveTransaction(payload) {
        try {
            const response = await fetch('?module=teacher&action=saveTransaction', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (data.success) {
                alert('Đã lưu thưởng/phạt.');
                window.location.reload();
                return;
            }

            alert('Không thể lưu dữ liệu.');
        } catch (error) {
            console.error(error);
            alert('Không thể kết nối máy chủ.');
        }
    }

    function applyPenaltyPreset(type, amount, reason) {

        currentPType = type;

        // chọn lại UI type
        document.querySelectorAll('.ptype').forEach(p => {
            p.classList.remove('selected');
        });

        const target = document.getElementById('pt-' + type);

        if (target) {
            target.classList.add('selected');
        }

        // set tiền
        document.getElementById('p-amount').value = amount;

        // set lý do
        document.getElementById('p-reason').value = reason;

        // hiện ô phút trễ
        const lateGroup = document.getElementById('late-min-group');

        if (lateGroup) {
            lateGroup.style.display =
                type === 'late' ? 'flex' : 'none';
        }

        updatePreview();
    }

    function quickBonus(amount) {
        document.getElementById('b-amount').value = amount;
        updateBonusPreview();
    }
    async function deletePenalty(id) {
        if (!confirm("Bạn có chắc chắn muốn xóa bản ghi thưởng/phạt này? Thao tác này sẽ không thể hoàn tác.")) return;

        try {
            // Gọi đến action deleteTransaction trong module teacher của bạn
            const response = await fetch(`?module=teacher&action=deleteTransaction&id=${id}`, {
                method: 'POST'
            });

            const data = await response.json();

            if (data.success) {
                const row = document.getElementById(`penalty-row-${id}`);
                if (row) {
                    row.style.transition = "all 0.4s ease";
                    row.style.opacity = "0";
                    row.style.transform = "translateX(20px)";
                    setTimeout(() => row.remove(), 400);
                }
            } else {
                alert("Lỗi: " + (data.message || "Không thể xóa bản ghi."));
            }
        } catch (error) {
            console.error("Lỗi kết nối:", error);
            alert("Không thể kết nối đến máy chủ.");
        }
    }
</script>
