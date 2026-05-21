<style>
.face-attendance-wrap {
    max-width: 1100px;
    margin: 0 auto;
}

.session-card {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 18px;
    height: 100%;
    background: #fff;
}

.camera-panel {
    display: grid;
    grid-template-columns: minmax(320px, 1fr) 360px;
    gap: 20px;
    align-items: start;
}

.camera-box {
    position: relative;
    background: #111827;
    border-radius: 8px;
    overflow: hidden;
}

#video {
    width: 100%;
    min-height: 360px;
    display: block;
    background: #111827;
}

#overlay {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
}

#guide-text {
    position: absolute;
    top: 12px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0, 0, 0, .65);
    color: #fff;
    padding: 8px 14px;
    border-radius: 6px;
    font-size: 15px;
    white-space: nowrap;
}

.attendance-status {
    margin-top: 12px;
    font-size: 18px;
    font-weight: 600;
}

.roster-list {
    max-height: 520px;
    overflow: auto;
}

.roster-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    border-bottom: 1px solid #edf2f7;
    padding: 10px 0;
}

.network-status {
    border-radius: 8px;
    padding: 12px 14px;
    border: 1px solid #e9ecef;
    background: #fff;
}

#toast {
    position: fixed;
    top: 20px;
    right: 20px;
    background: #198754;
    color: white;
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 15px;
    display: none;
    z-index: 9999;
}

@media (max-width: 992px) {
    .camera-panel {
        grid-template-columns: 1fr;
    }
}
</style>

<div id="main">
    <div class="page-heading">
        <div class="face-attendance-wrap">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <div>
                    <h3 class="mb-1">Điểm danh bằng nhận diện gương mặt</h3>
                    <div class="text-muted">Chọn lớp có lịch dạy hôm nay, sau đó bắt đầu quét gương mặt.</div>
                </div>
                <?php if ($selectedSession): ?>
                    <a href="?module=face&action=attendance" class="btn btn-outline-secondary">
                        Chọn lớp khác
                    </a>
                <?php endif; ?>
            </div>

            <?php if (!$selectedSession): ?>
                <div class="row g-3">
                    <?php if (empty($sessions)): ?>
                        <div class="col-12">
                            <div class="card p-4 text-muted">
                                Hôm nay giảng viên chưa có lịch dạy.
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($sessions as $session): ?>
                            <div class="col-12 col-md-6 col-xl-4">
                                <div class="session-card shadow-sm">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                        <div>
                                            <h5 class="fw-bold mb-1"><?= htmlspecialchars($session['course_name']) ?></h5>
                                            <div class="text-muted small">
                                                <?= htmlspecialchars($session['package_name']) ?> -
                                                <?= htmlspecialchars($session['class_code']) ?>
                                            </div>
                                        </div>
                                        <span class="badge bg-primary">
                                            <?= htmlspecialchars(substr($session['start_time'] ?? '', 0, 5)) ?>
                                        </span>
                                    </div>

                                    <div class="small text-muted mb-3">
                                        <?= htmlspecialchars($session['shift_name'] ?? 'Ca học') ?>
                                        <?php if (!empty($session['end_time'])): ?>
                                            · <?= htmlspecialchars(substr($session['end_time'], 0, 5)) ?>
                                        <?php endif; ?>
                                        <?php if (!empty($session['room_name'])): ?>
                                            · <?= htmlspecialchars($session['room_name']) ?>
                                        <?php endif; ?>
                                    </div>

                                    <div class="d-flex justify-content-between mb-3">
                                        <span>Giảng viên</span>
                                        <strong><?= (int) $session['teacher_checked'] ? 'Đã điểm danh' : 'Chưa điểm danh' ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span>Học sinh</span>
                                        <strong><?= (int) $session['attended_count'] ?>/<?= (int) $session['student_count'] ?></strong>
                                    </div>

                                    <a href="?module=face&action=attendance&session_id=<?= (int) $session['session_id'] ?>"
                                        class="btn btn-primary w-100">
                                        Chọn lớp và quét
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php if (!empty($networkAccess['enabled'])): ?>
                    <div class="network-status mb-3 <?= !empty($networkAccess['allowed']) ? 'border-success' : 'border-danger' ?>">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <div class="fw-bold <?= !empty($networkAccess['allowed']) ? 'text-success' : 'text-danger' ?>">
                                    <?= !empty($networkAccess['allowed']) ? 'WiFi hợp lệ để điểm danh' : 'Không đúng WiFi điểm danh' ?>
                                </div>
                                <div class="small text-muted">
                                    IP hiện tại: <?= htmlspecialchars($networkAccess['client_ip'] ?? 'Không xác định') ?>
                                </div>
                            </div>
                            <span class="badge <?= !empty($networkAccess['allowed']) ? 'bg-success' : 'bg-danger' ?>">
                                <?= !empty($networkAccess['allowed']) ? 'Được phép' : 'Bị chặn' ?>
                            </span>
                        </div>
                        <?php if (empty($networkAccess['allowed'])): ?>
                            <div class="small text-danger mt-2">
                                <?= htmlspecialchars($networkAccess['message'] ?? 'Thiết bị không nằm trong WiFi được phép điểm danh.') ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="card p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h5 class="mb-1">
                                <?= htmlspecialchars($selectedSession['course_name']) ?> -
                                <?= htmlspecialchars($selectedSession['class_code']) ?>
                            </h5>
                            <div class="text-muted small">
                                <?= htmlspecialchars($selectedSession['package_name']) ?> ·
                                <?= htmlspecialchars(substr($selectedSession['start_time'] ?? '', 0, 5)) ?>
                                <?php if (!empty($selectedSession['end_time'])): ?>
                                    - <?= htmlspecialchars(substr($selectedSession['end_time'], 0, 5)) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <button id="startScanBtn" class="btn btn-success" <?= empty($networkAccess['allowed']) ? 'disabled' : '' ?>>
                            Bắt đầu quét
                        </button>
                    </div>
                </div>

                <div class="camera-panel">
                    <div>
                        <div class="camera-box">
                            <video id="video" autoplay playsinline muted></video>
                            <canvas id="overlay"></canvas>
                            <div id="guide-text">Nhấn bắt đầu quét để mở camera</div>
                        </div>
                        <div class="attendance-status" id="attendance-status">
                            Đang chờ bắt đầu quét.
                        </div>
                    </div>

                    <div class="card p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold mb-0">Danh sách điểm danh</h6>
                            <button class="btn btn-sm btn-outline-primary" id="refreshRosterBtn">Làm mới</button>
                        </div>
                        <div id="roster" class="roster-list text-muted">Đang tải danh sách...</div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="toast"></div>

<?php if ($selectedSession): ?>
<script>
const sessionId = <?= (int) $selectedSession['session_id'] ?>;
const networkAllowed = <?= !empty($networkAccess['allowed']) ? 'true' : 'false' ?>;
const networkMessage = <?= json_encode($networkAccess['message'] ?? 'Thiết bị không nằm trong WiFi được phép điểm danh.', JSON_UNESCAPED_UNICODE) ?>;
const video = document.getElementById("video");
const overlay = document.getElementById("overlay");
const ctx = overlay.getContext("2d");
const statusEl = document.getElementById("attendance-status");
const guideText = document.getElementById("guide-text");
const rosterEl = document.getElementById("roster");

let cameraReady = false;
let scanning = false;
let isSaving = false;
let scanTimer = null;
let lastCheck = {};

document.getElementById("startScanBtn").addEventListener("click", async () => {
    if (!networkAllowed) {
        statusEl.innerText = networkMessage;
        showToast(networkMessage, true);
        return;
    }

    if (!cameraReady) {
        await startCamera();
    }

    scanning = true;
    guideText.innerText = "Đưa gương mặt vào camera";
    statusEl.innerText = "Camera sẵn sàng, đang quét...";
});

document.getElementById("refreshRosterBtn").addEventListener("click", loadRoster);

async function startCamera()
{
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        video.srcObject = stream;

        video.onloadedmetadata = () => {
            cameraReady = true;
            resizeOverlay();
            if (!scanTimer) {
                scanTimer = setInterval(scanFrame, 1500);
            }
        };
    } catch (err) {
        console.error("CAMERA ERROR:", err);
        statusEl.innerText = "Không mở được camera. Kiểm tra quyền camera của trình duyệt.";
    }
}

function resizeOverlay()
{
    overlay.width = video.videoWidth || video.clientWidth;
    overlay.height = video.videoHeight || video.clientHeight;
}

async function scanFrame()
{
    if (!scanning || !cameraReady || video.videoWidth === 0) {
        return;
    }

    resizeOverlay();
    ctx.clearRect(0, 0, overlay.width, overlay.height);

    const tempCanvas = document.createElement("canvas");
    tempCanvas.width = video.videoWidth;
    tempCanvas.height = video.videoHeight;
    tempCanvas.getContext("2d").drawImage(video, 0, 0);

    tempCanvas.toBlob(async (blob) => {
        const formData = new FormData();
        formData.append("image", blob, "face.jpg");

        try {
            const res = await fetch("?module=face&action=recognizeProxy", {
                method: "POST",
                body: formData
            });
            const data = await res.json();
            handleAiResult(data);
        } catch (err) {
            console.error("AI ERROR:", err);
            statusEl.innerText = "Không kết nối được AI server.";
        }
    }, "image/jpeg", 0.88);
}

function handleAiResult(data)
{
    if (!data || !data.success) {
        statusEl.innerText = data?.message || "AI không trả kết quả hợp lệ.";
        return;
    }

    if (!data.face_found) {
        statusEl.innerText = "Không thấy gương mặt.";
        return;
    }

    if (data.box) {
        ctx.strokeStyle = data.matched ? "#22c55e" : "#f59e0b";
        ctx.lineWidth = 3;
        ctx.strokeRect(data.box.left, data.box.top, data.box.width, data.box.height);
        ctx.fillStyle = ctx.strokeStyle;
        ctx.font = "20px Arial";
        ctx.fillText(data.name || "Unknown", data.box.left, Math.max(22, data.box.top - 8));
    }

    if (!data.matched || !data.user_id) {
        statusEl.innerText = "Không nhận diện được người trong hệ thống.";
        return;
    }

    statusEl.innerText = "Nhận diện: " + data.name;

    const now = Date.now();
    if (lastCheck[data.user_id] && now - lastCheck[data.user_id] < 10000) {
        return;
    }

    lastCheck[data.user_id] = now;
    saveAttendance(data.user_id);
}

function saveAttendance(userId)
{
    if (!networkAllowed) {
        statusEl.innerText = networkMessage;
        showToast(networkMessage, true);
        return;
    }

    if (isSaving) {
        return;
    }

    isSaving = true;
    statusEl.innerText = "Đang lưu điểm danh...";

    const canvas = document.createElement("canvas");
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext("2d").drawImage(video, 0, 0);

    canvas.toBlob(async (blob) => {
        const formData = new FormData();
        formData.append("user_id", userId);
        formData.append("session_id", sessionId);
        formData.append("image", blob, "attendance.jpg");

        try {
            const res = await fetch("?module=face&action=checkIn", {
                method: "POST",
                body: formData
            });
            const data = await res.json();

            if (data.success) {
                statusEl.innerText = data.message;
                showToast(data.message + " (" + statusLabel(data.status) + ")");
                loadRoster();
            } else {
                statusEl.innerText = data.message;
                showToast(data.message, true);
            }
        } catch (err) {
            console.error("CHECKIN ERROR:", err);
            statusEl.innerText = "Lỗi server khi lưu điểm danh.";
        } finally {
            setTimeout(() => {
                isSaving = false;
            }, 1800);
        }
    }, "image/jpeg", 0.88);
}

async function loadRoster()
{
    try {
        const res = await fetch(`?module=face&action=roster&session_id=${sessionId}`);
        const data = await res.json();

        if (!data.success) {
            rosterEl.innerText = data.message || "Không tải được danh sách.";
            return;
        }

        rosterEl.innerHTML = "";
        rosterEl.appendChild(renderGroup("Giảng viên", data.data.teachers || []));
        rosterEl.appendChild(renderGroup("Học sinh", data.data.students || []));
    } catch (err) {
        console.error("ROSTER ERROR:", err);
        rosterEl.innerText = "Không tải được danh sách điểm danh.";
    }
}

function renderGroup(title, rows)
{
    const wrap = document.createElement("div");
    const heading = document.createElement("div");
    heading.className = "fw-bold text-dark mt-2 mb-1";
    heading.textContent = title;
    wrap.appendChild(heading);

    if (!rows.length) {
        const empty = document.createElement("div");
        empty.className = "text-muted small mb-2";
        empty.textContent = "Chưa có dữ liệu.";
        wrap.appendChild(empty);
        return wrap;
    }

    rows.forEach(row => {
        const item = document.createElement("div");
        item.className = "roster-row";
        item.dataset.userId = row.user_id;

        const name = document.createElement("div");
        name.innerHTML = `<div class="fw-semibold text-dark">${escapeHtml(row.name)}</div><div class="small text-muted">${row.checked_at ? escapeHtml(row.checked_at) : "Chưa điểm danh"}</div>`;

        const badge = document.createElement("span");
        badge.className = "badge " + badgeClass(row.status);
        badge.textContent = statusLabel(row.status);

        item.appendChild(name);
        item.appendChild(badge);
        wrap.appendChild(item);
    });

    return wrap;
}

function badgeClass(status)
{
    if (status === "present") return "bg-success";
    if (status === "late") return "bg-warning text-dark";
    if (status === "absent") return "bg-danger";
    return "bg-light text-dark";
}

function statusLabel(status)
{
    if (status === "present") return "Có mặt";
    if (status === "late") return "Đi trễ";
    if (status === "absent") return "Vắng";
    return "Chưa điểm danh";
}

function showToast(message, warning = false)
{
    const toast = document.getElementById("toast");
    toast.textContent = message;
    toast.style.background = warning ? "#dc3545" : "#198754";
    toast.style.display = "block";

    setTimeout(() => {
        toast.style.display = "none";
    }, 3200);
}

function escapeHtml(value)
{
    return String(value ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
}

loadRoster();
</script>
<?php endif; ?>
