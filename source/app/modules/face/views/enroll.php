<style>
.face-page {
    max-width: 980px;
    margin: 0 auto;
}

.face-card {
    background: #fff;
    border-radius: 8px;
    padding: 24px;
    box-shadow: 0 8px 24px rgba(15, 23, 42, .06);
}

.camera-wrapper {
    width: 100%;
    border-radius: 8px;
    overflow: hidden;
    background: #111827;
    margin-top: 18px;
    position: relative;
}

#video {
    width: 100%;
    min-height: 420px;
    display: block;
    background: #111827;
}

#canvas {
    display: none;
}

.pose-box {
    position: absolute;
    top: 14px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0, 0, 0, .68);
    color: #fff;
    padding: 8px 14px;
    border-radius: 6px;
    font-size: 16px;
    z-index: 10;
    white-space: nowrap;
}

.progress-bar-wrap {
    height: 12px;
    background: #e9ecef;
    border-radius: 999px;
    overflow: hidden;
}

.progress-fill {
    width: 0%;
    height: 100%;
    background: #198754;
    transition: .25s;
}

.face-actions {
    margin-top: 18px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

#face-status {
    margin-top: 16px;
    font-weight: 600;
    min-height: 28px;
}

.face-guide {
    margin-top: 14px;
    background: #fff3cd;
    padding: 12px;
    border-radius: 8px;
    color: #856404;
    font-size: 14px;
}
</style>

<div id="main">
    <header class="mb-3">
        <a href="#" class="burger-btn d-block d-xl-none">
            <i class="bi bi-justify fs-3"></i>
        </a>
    </header>

    <div class="page-heading">
        <div class="face-page">
            <div class="face-card">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                    <div>
                        <h3 class="mb-1">Đăng ký khuôn mặt AI</h3>
                        <div class="text-muted">
                            Thu thập 10 góc mặt để tăng độ chính xác khi điểm danh.
                        </div>
                    </div>
                    <a href="?module=face&action=attendance" class="btn btn-outline-primary">
                        Điểm danh
                    </a>
                </div>

                <div class="form-group">
                    <label class="form-label">Chọn người dùng</label>
                    <select id="user_id" class="form-select">
                        <option value="">-- Chọn giảng viên hoặc học sinh --</option>
                        <?php foreach ($users as $u): ?>
                            <option value="<?= (int) $u['user_id'] ?>">
                                <?= htmlspecialchars($u['name']) ?>
                                (<?= $u['role'] === 'teacher' ? 'Giảng viên' : 'Học sinh' ?>)
                                <?= (int) ($u['has_face'] ?? 0) ? ' - đã có khuôn mặt' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="face-guide">
                    Không đeo khẩu trang, đảm bảo đủ ánh sáng, chỉ để 1 khuôn mặt trong khung hình.
                    Nếu đăng ký lại cho người đã có dữ liệu, hệ thống sẽ ghi đè dữ liệu khuôn mặt cũ.
                    Hệ thống sẽ chặn ảnh chụp màn hình, ảnh in hoặc khuôn mặt không đạt kiểm tra chống giả mạo.
                </div>

                <div class="camera-wrapper">
                    <div class="pose-box" id="pose-box">Chưa bắt đầu</div>
                    <video id="video" autoplay playsinline muted></video>
                    <canvas id="canvas"></canvas>
                </div>

                <div class="mt-3">
                    <div class="progress-bar-wrap">
                        <div id="progress-fill" class="progress-fill"></div>
                    </div>
                    <div id="capture-count" class="small text-muted mt-2">0 / 10 ảnh</div>
                </div>

                <div class="face-actions">
                    <button class="btn btn-primary" id="camera-btn" type="button">
                        Bật camera
                    </button>
                    <button id="enroll-btn" class="btn btn-success" type="button">
                        Bắt đầu đăng ký
                    </button>
                    <button id="reset-btn" class="btn btn-outline-secondary" type="button">
                        Làm lại
                    </button>
                </div>

                <div id="face-status"></div>
            </div>
        </div>
    </div>
</div>

<script>
const video = document.getElementById("video");
const canvas = document.getElementById("canvas");
const userSelect = document.getElementById("user_id");
const enrollBtn = document.getElementById("enroll-btn");
const cameraBtn = document.getElementById("camera-btn");
const resetBtn = document.getElementById("reset-btn");
const poseBox = document.getElementById("pose-box");
const progressFill = document.getElementById("progress-fill");
const captureCount = document.getElementById("capture-count");
const statusEl = document.getElementById("face-status");

let stream = null;
let collected = 0;
let enrolling = false;

const poses = [
    "Nhìn thẳng",
    "Quay trái nhẹ",
    "Quay phải nhẹ",
    "Cúi xuống nhẹ",
    "Ngẩng lên nhẹ",
    "Lại gần camera",
    "Ra xa camera",
    "Nghiêng trái",
    "Nghiêng phải",
    "Biểu cảm tự nhiên"
];

const total = poses.length;

cameraBtn.addEventListener("click", startCamera);
enrollBtn.addEventListener("click", startEnroll);
resetBtn.addEventListener("click", resetEnroll);

async function startCamera()
{
    try {
        stream = await navigator.mediaDevices.getUserMedia({
            video: {
                width: 1280,
                height: 720,
                facingMode: "user"
            }
        });

        video.srcObject = stream;
        setStatus("Camera sẵn sàng.");
    } catch (err) {
        console.error(err);
        setStatus("Không mở được camera. Kiểm tra quyền camera của trình duyệt.", true);
    }
}

async function startEnroll()
{
    if (enrolling) {
        return;
    }

    const userId = userSelect.value;

    if (!userId) {
        alert("Chọn người dùng trước");
        return;
    }

    if (!stream) {
        alert("Bật camera trước");
        return;
    }

    await resetAiSession(userId);

    enrolling = true;
    collected = 0;
    updateProgress();
    enrollBtn.disabled = true;
    userSelect.disabled = true;

    nextStep();
}

async function resetEnroll()
{
    const userId = userSelect.value;
    collected = 0;
    enrolling = false;
    enrollBtn.disabled = false;
    userSelect.disabled = false;
    poseBox.innerText = "Chưa bắt đầu";
    updateProgress();

    if (userId) {
        await resetAiSession(userId);
    }

    setStatus("Đã đặt lại phiên đăng ký.");
}

async function nextStep()
{
    if (collected >= total) {
        enrolling = false;
        enrollBtn.disabled = false;
        userSelect.disabled = false;
        poseBox.innerText = "Hoàn tất";
        setStatus("Đăng ký khuôn mặt hoàn tất.");
        return;
    }

    const pose = poses[collected];
    let countdown = 3;
    poseBox.innerText = pose;

    const timer = setInterval(async () => {
        setStatus(`[${collected + 1}/${total}] ${pose} - ${countdown}`);
        countdown--;

        if (countdown < 0) {
            clearInterval(timer);

            const success = await captureFace();

            if (success) {
                collected++;
                updateProgress();
                setTimeout(nextStep, 800);
            } else {
                setStatus("Chụp thất bại, thử lại góc hiện tại.", true);
                setTimeout(nextStep, 1200);
            }
        }
    }, 1000);
}

async function captureFace()
{
    try {
        if (video.videoWidth < 300) {
            setStatus("Camera chưa sẵn sàng hoặc hình quá mờ.", true);
            return false;
        }

        const ctx = canvas.getContext("2d");
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        const blob = await new Promise(resolve => {
            canvas.toBlob(resolve, "image/jpeg", 0.95);
        });

        const formData = new FormData();
        formData.append("user_id", userSelect.value);
        formData.append("image", blob, "face.jpg");

        const res = await fetch("?module=face&action=enrollProxy", {
            method: "POST",
            body: formData
        });

        const data = await res.json();

        if (data.success) {
            if (data.done) {
                collected = total - 1;
            }

            setStatus(data.message || `Đã lưu ảnh ${collected + 1}`);
            return true;
        }

        setStatus(data.message || "AI không nhận được khuôn mặt hợp lệ.", true);
        return false;
    } catch (err) {
        console.error(err);
        setStatus("Không kết nối được AI server. Hãy chạy server Python trước.", true);
        return false;
    }
}

async function resetAiSession(userId)
{
    try {
        await fetch("?module=face&action=enrollResetProxy", {
            method: "POST",
            body: new URLSearchParams({ user_id: userId })
        });
    } catch (err) {
        console.warn("Không reset được AI session", err);
    }
}

function updateProgress()
{
    const percent = (collected / total) * 100;
    progressFill.style.width = percent + "%";
    captureCount.innerText = `${collected} / ${total} ảnh`;
}

function setStatus(message, isError = false)
{
    statusEl.innerText = message;
    statusEl.className = isError ? "text-danger" : "text-dark";
}
</script>
