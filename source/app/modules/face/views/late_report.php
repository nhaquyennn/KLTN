<div id="main">
    <div class="page-heading">
        <h3>Báo cáo chấm công giảng viên</h3>
    </div>

    <div class="page-content">
        <div class="card">
            <div class="card-body">
                <!-- FILTER BUTTONS -->
                <div class="mb-3 d-flex justify-content-between align-items-center">
                    <div>
                        <a href="?module=face&action=lateReport&filter=all&date=<?= $_GET['date'] ?? date('Y-m-d') ?>" class="btn btn-primary">Tất cả</a>
                        <a href="?module=face&action=lateReport&filter=present&date=<?= $_GET['date'] ?? date('Y-m-d') ?>" class="btn btn-success">Đúng giờ</a>
                        <a href="?module=face&action=lateReport&filter=late&date=<?= $_GET['date'] ?? date('Y-m-d') ?>" class="btn btn-warning">Đi trễ</a>
                        <a href="?module=face&action=lateReport&filter=absent" class="btn btn-danger">Vắng</a>
                    </div>
                    
                    <!-- Nút xử lý hàng loạt -->
                    <button id="btn-bulk-penalty" class="btn btn-dark" style="display:none;" onclick="bulkPenalty()">
                        <i class="bi bi-hammer"></i> Phạt tất cả
                    </button>
                </div>

                <form method="GET" class="row g-2 mb-3">
                    <input type="hidden" name="module" value="face">
                    <input type="hidden" name="action" value="lateReport">
                    <input type="hidden" name="filter" value="<?= $_GET['filter'] ?? 'all' ?>">
                    <div class="col-md-3">
                        <input type="date" name="date" class="form-control" value="<?= $_GET['date'] ?? date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary">Lọc ngày</button>
                    </div>
                </form>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="check-all" class="form-check-input"></th>
                            <th>#</th>
                            <th>Giảng viên</th>
                            <th>Lớp</th>
                            <th>Ca học</th>
                            <th>Check-in</th>
                            <th>Trạng thái (Phút)</th>
                            <th>Ảnh</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($reports)): ?>
                            <?php foreach($reports as $index => $row): 
                            
                                $minutes = ($row['late_minutes'] !== null) ? (int)$row['late_minutes'] : null;
                                
                                // Logic màu sắc và nhãn
                                $status_text = "";
                                $badge_class = "";
                                $can_penalty = false;

                                if ($minutes === null) {
                                    $status_text = "Vắng";
                                    $badge_class = "bg-secondary";
                                } elseif ($minutes <= -10) {
                                    $status_text = "Sớm " . abs($minutes) . "p";
                                    $badge_class = "bg-success"; // Xanh lá
                                } elseif ($minutes <= 0) {
                                    $status_text = "Đúng giờ";
                                    $badge_class = "bg-info"; // Xanh dương (Bootstrap info tương đương Cyan/Blue)
                                } elseif ($minutes > 0 && $minutes < 15) {
                                    $status_text = "Trễ nhẹ " . $minutes . "p";
                                    $badge_class = "bg-warning text-dark";
                                } elseif ($minutes >= 15 && $minutes < 30) {
                                    $status_text = "Trễ " . $minutes . "p";
                                    $badge_class = "bg-warning text-dark"; // Vàng
                                    $can_penalty = true;
                                } else {
                                    $status_text = "Trễ " . $minutes . "p (Vắng)";
                                    $badge_class = "bg-danger"; // Đỏ
                                    $can_penalty = true;
                                }
                            ?>
                            <tr id="row-<?= $row['attendance_id'] ?>">
                                <td>
                                    <?php if($can_penalty): ?>
                                        <input type="checkbox" class="form-check-input check-item" value="<?= $row['attendance_id'] ?>" data-type="<?= ($minutes >= 30) ? 'absent' : 'late' ?>">
                                    <?php endif; ?>
                                </td>
                                <td><?= $index + 1 ?></td>
                                <td><strong><?= htmlspecialchars($row['teacher_name']) ?></strong></td>
                                <td><?= htmlspecialchars($row['course_name']) ?> <br><small>[<?= htmlspecialchars($row['class_code']) ?>]</small></td>
                                <td><?= $row['start_time'] ?></td>
                                <td><?= $row['check_in_time'] ?? '---' ?></td>
                                <td><span class="badge <?= $badge_class ?>"><?= $status_text ?></span></td>
                               <td>

                                            <?php if(!empty($row['face_image'])): ?>

                                            <img
                                                src="uploads/attendance/<?= htmlspecialchars($row['face_image']) ?>"
                                                width="180"
                                            >

                                            <?php endif; ?>

                                            </td>
                                <td>
                                    <?php if($can_penalty): ?>
                                        <div class="btn-group">
                                            <!-- Nút Xác nhận: Phạt và ẩn hàng -->
                                            <button class="btn <?= ($minutes >= 30) ? 'btn-danger' : 'btn-warning' ?> btn-sm" 
                                                    onclick="executePenalty(<?= $row['attendance_id'] ?>, <?= $row['teacher_id'] ?>, <?= $minutes ?? 0 ?>)">
                                                Xác nhận phạt
                                            </button>
                                            
                                            <!-- Nút Xóa: Chỉ ẩn hàng (Bỏ qua) -->
                                            <button class="btn btn-outline-secondary btn-sm" onclick="removeRow(<?= $row['attendance_id'] ?>)" title="Bỏ qua không phạt">
                                                <i class="bi bi-trash"></i> Xóa
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="9" class="text-center">Không có dữ liệu</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// 1. Hàm hỗ trợ ẩn hàng khỏi giao diện
function removeRow(attendanceId) {
    const row = document.getElementById(`row-${attendanceId}`);
    if (row) {
        row.style.transition = "all 0.3s ease";
        row.style.opacity = "0";
        setTimeout(() => row.remove(), 300);
    }
}

// 2. Hàm xử lý phạt đơn lẻ
async function executePenalty(attendanceId, teacherId, lateMinutes, className, shiftTime) {
    const now = new Date();
    const payload = {
        teacher_id: teacherId,
        type: 'penalty',
        amount: lateMinutes >= 30 ? 100000 : 50000,
        // Gộp thông tin Lớp và Ca vào lý do để hiện bên Lịch sử
        reason: `Phạt trễ ${lateMinutes}p [Lớp: ${className}] [Ca: ${shiftTime}]`,
        month: now.getMonth() + 1,
        year: now.getFullYear()
    };
}

    try {
        const res = await fetch('?module=teacher&action=saveTransaction', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if(data.success) {
            removeRow(attendanceId); // Phạt xong ẩn luôn
        }
    } catch (err) { alert("Lỗi kết nối!"); }
}

// 3. Logic Checkbox hàng loạt
document.getElementById('check-all')?.addEventListener('change', function() {
    document.querySelectorAll('.check-item').forEach(item => item.checked = this.checked);
    toggleBulkButton();
});

function toggleBulkButton() {
    const selected = document.querySelectorAll('.check-item:checked').length;
    document.getElementById('btn-bulk-penalty').style.display = selected > 0 ? 'block' : 'none';
}

// 4. Hàm xử lý phạt hàng loạt
async function bulkPenalty() {
    const selected = document.querySelectorAll('.check-item:checked');
    if(!confirm(`Phạt ${selected.length} người và ẩn khỏi danh sách?`)) return;

    for (let item of selected) {
        const attendanceId = item.value;
        const teacherId = item.closest('tr').querySelector('button').getAttribute('onclick').split(',')[1].trim(); 
        // Lấy teacher_id từ nút bấm gần nhất cho nhanh
        
        const payload = {
            teacher_id: teacherId,
            type: 'penalty',
            amount: 50000,
            reason: "Phạt đi trễ (Hàng loạt)",
            month: new Date().getMonth() + 1,
            year: new Date().getFullYear()
        };

        await fetch('?module=teacher&action=saveTransaction', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        removeRow(attendanceId);
    }
    alert("Đã xử lý xong các mục được chọn!");
    toggleBulkButton();
}
</script>
