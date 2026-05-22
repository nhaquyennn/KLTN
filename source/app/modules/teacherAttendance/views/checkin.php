<?php $saved = !empty($attendance); ?>
<style>
.teacher-checkin .panel{border:0;border-radius:8px;box-shadow:0 8px 22px rgba(31,45,61,.08)}
.teacher-checkin video{aspect-ratio:4/3;background:#111;border-radius:8px;object-fit:cover;width:100%}
.teacher-checkin .result{border:1px solid #dce3ef;border-radius:8px;min-height:84px;padding:16px}
</style>
<div class="teacher-checkin" id="main">
    <header class="mb-3"><a href="#" class="burger-btn d-block d-xl-none"><i class="bi bi-justify fs-3"></i></a></header>
    <div class="d-flex justify-content-between flex-wrap gap-2 mb-4"><div><h3 class="mb-1">Điểm danh giảng viên</h3><div class="text-muted"><?= htmlspecialchars(($session['course_name'] ?? '') . ' - ' . ($session['class_code'] ?? '')) ?></div></div><a class="btn btn-secondary" href="?module=teacherDashboard&action=index">Dashboard</a></div>
    <div class="row g-4">
        <div class="col-12 col-lg-4">
            <div class="card panel"><div class="card-body p-4"><h5 class="fw-bold mb-3">Buổi học</h5><div class="small text-muted">Ngày</div><div class="mb-2 fw-semibold"><?= htmlspecialchars(date('d/m/Y', strtotime($session['session_date']))) ?></div><div class="small text-muted">Ca học</div><div class="mb-2 fw-semibold"><?= htmlspecialchars(($session['shift_name'] ?? '') . ' ' . substr((string) ($session['start_time'] ?? ''), 0, 5) . ' - ' . substr((string) ($session['end_time'] ?? ''), 0, 5)) ?></div><div class="small text-muted">Phòng</div><div class="mb-2 fw-semibold"><?= htmlspecialchars($session['room_name'] ?? 'Chưa xếp') ?></div><div class="small text-muted">Trạng thái</div><div><?= $saved ? '<span class="badge bg-success">Đã điểm danh</span>' : '<span class="badge bg-primary">Chờ điểm danh</span>' ?></div></div></div>
        </div>
        <div class="col-12 col-lg-8">
            <div class="card panel"><div class="card-body p-4"><div class="row g-4"><div class="col-12 col-md-6"><video id="teacherCamera" autoplay playsinline muted></video><div class="d-flex gap-2 mt-3"><button id="openCameraBtn" class="btn btn-outline-primary"><i class="bi bi-camera-video"></i> Mở camera</button><button id="faceBtn" class="btn btn-primary" <?= $saved ? 'disabled' : '' ?>>Điểm danh bằng gương mặt</button></div></div><div class="col-12 col-md-6"><label class="form-label">Lý do điểm danh thủ công sau giờ bắt đầu</label><textarea id="manualNote" class="form-control mb-2" rows="3" maxlength="500"></textarea><button id="manualBtn" class="btn btn-warning mb-3" <?= $saved ? 'disabled' : '' ?>>Điểm danh thủ công</button><h6 class="fw-bold">Kết quả</h6><div class="result" id="attendanceResult"><?= $saved ? 'Đã lưu điểm danh: ' . htmlspecialchars($attendance['status'] ?? '') . '. Phương thức: ' . htmlspecialchars($attendance['method'] ?? '-') : 'Chưa gửi điểm danh.' ?></div></div></div></div></div>
        </div>
    </div>
</div>
<script>
const sessionId = <?= (int) $session['session_id'] ?>;
const video = document.getElementById('teacherCamera');
const result = document.getElementById('attendanceResult');
let stream;
document.getElementById('openCameraBtn').addEventListener('click', openCamera);
document.getElementById('faceBtn').addEventListener('click', faceCheckin);
document.getElementById('manualBtn').addEventListener('click', manualCheckin);
async function openCamera(){try{stream = await navigator.mediaDevices.getUserMedia({video:true});video.srcObject=stream;result.textContent='Camera đã sẵn sàng.';}catch(e){result.textContent='Không mở được camera.';}}
async function faceCheckin(){if(!video.videoWidth){await openCamera();if(!video.videoWidth){return;}}const canvas=document.createElement('canvas');canvas.width=video.videoWidth;canvas.height=video.videoHeight;canvas.getContext('2d').drawImage(video,0,0);canvas.toBlob(async blob=>{const fd=new FormData();fd.append('session_id',sessionId);fd.append('image',blob,'teacher-face.jpg');await submit('?module=teacherAttendance&action=faceCheckin',fd);},'image/jpeg',.9);}
async function manualCheckin(){const fd=new FormData();fd.append('session_id',sessionId);fd.append('note',document.getElementById('manualNote').value);await submit('?module=teacherAttendance&action=manualCheckin',fd);}
async function submit(url, body){result.textContent='Đang xử lý...';try{const res=await fetch(url,{method:'POST',body});const json=await res.json();const data=json.data||{};const confidence=data.confidence_score!==null&&data.confidence_score!==undefined?` Confidence: ${data.confidence_score}.`:'';result.innerHTML=`<div class="${json.success?'text-success':'text-danger'} fw-semibold">${escapeHtml(json.message)}</div><div class="small">Trạng thái: ${escapeHtml(data.status||'-')}. Phương thức: ${escapeHtml(data.method||'-')}.${escapeHtml(confidence)}</div>`;if(data.attendance_id){document.getElementById('faceBtn').disabled=true;document.getElementById('manualBtn').disabled=true;}}catch(e){result.textContent='Lỗi server khi lưu điểm danh.';}}
function escapeHtml(value){const div=document.createElement('div');div.textContent=value || '';return div.innerHTML;}
</script>
