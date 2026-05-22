import cv2
import numpy as np


DEFAULT_THRESHOLDS = {
    "min_score": 0.58,
    "min_face_ratio": 0.035,
    "max_face_ratio": 0.72,
    "min_laplacian": 18.0,
    "min_color_std": 9.0,
    "min_texture": 7.0,
    "min_brightness": 35.0,
    "max_brightness": 225.0,
    "max_specular_ratio": 0.18,
}


def check_liveness(frame, face, thresholds=None):
    thresholds = {**DEFAULT_THRESHOLDS, **(thresholds or {})}
    h, w = frame.shape[:2]
    x1, y1, x2, y2 = _clamp_bbox(face.bbox, w, h)

    if x2 <= x1 or y2 <= y1:
        return _result(False, 0.0, "Không xác định được vùng khuôn mặt.", {})

    face_area = (x2 - x1) * (y2 - y1)
    frame_area = max(1, w * h)
    face_ratio = face_area / frame_area

    crop = frame[y1:y2, x1:x2]
    gray = cv2.cvtColor(crop, cv2.COLOR_BGR2GRAY)
    hsv = cv2.cvtColor(crop, cv2.COLOR_BGR2HSV)

    brightness = float(np.mean(gray))
    laplacian = float(cv2.Laplacian(gray, cv2.CV_64F).var())
    color_std = float(np.mean(np.std(crop.reshape(-1, 3), axis=0)))
    texture = float(np.std(gray))
    specular_ratio = float(np.mean(hsv[:, :, 2] > 245))

    checks = {
        "face_size": thresholds["min_face_ratio"] <= face_ratio <= thresholds["max_face_ratio"],
        "sharpness": laplacian >= thresholds["min_laplacian"],
        "color_variation": color_std >= thresholds["min_color_std"],
        "texture": texture >= thresholds["min_texture"],
        "brightness": thresholds["min_brightness"] <= brightness <= thresholds["max_brightness"],
        "specular": specular_ratio <= thresholds["max_specular_ratio"],
    }

    metrics = {
        "face_ratio": round(face_ratio, 4),
        "laplacian": round(laplacian, 2),
        "color_std": round(color_std, 2),
        "texture": round(texture, 2),
        "brightness": round(brightness, 2),
        "specular_ratio": round(specular_ratio, 4),
        "checks": checks,
    }

    score_parts = [
        _range_score(face_ratio, thresholds["min_face_ratio"], thresholds["max_face_ratio"]),
        _min_score(laplacian, thresholds["min_laplacian"] * 0.75, thresholds["min_laplacian"] * 3),
        _min_score(color_std, thresholds["min_color_std"] * 0.75, thresholds["min_color_std"] * 2.5),
        _min_score(texture, thresholds["min_texture"] * 0.75, thresholds["min_texture"] * 3),
        _range_score(brightness, thresholds["min_brightness"], thresholds["max_brightness"]),
        1.0 - _min_score(specular_ratio, thresholds["max_specular_ratio"], thresholds["max_specular_ratio"] * 2),
    ]
    score = float(np.clip(np.mean(score_parts), 0.0, 1.0))

    is_live = all(checks.values()) and score >= thresholds["min_score"]
    if is_live:
        return _result(True, score, "Khuôn mặt thật hợp lệ.", metrics)

    failed = [name for name, ok in checks.items() if not ok]
    message = _message_for_failed_checks(failed)
    return _result(False, score, message, metrics)


def verify_head_turn_challenge(frames, face_detector):
    if len(frames) != 3:
        return _result(False, 0.0, "Thiếu ảnh kiểm tra chống gian lận.", {})

    samples = []

    for index, frame in enumerate(frames):
        faces = face_detector.get(frame)

        if len(faces) != 1:
            return _result(False, 0.0, f"Bước {index + 1} cần đúng 1 khuôn mặt.", {
                "face_count": len(faces),
            })

        face = faces[0]
        passive = check_liveness(frame, face)

        if not passive["is_live"]:
            return _result(False, passive["score"], passive["message"], {
                "step": index + 1,
                "passive": passive,
            })

        samples.append(_pose_sample(frame, face))

    front, left, right = samples
    yaw_left_delta = left["yaw"] - front["yaw"]
    yaw_right_delta = right["yaw"] - front["yaw"]
    center_shift_left = abs(left["center_x"] - front["center_x"])
    center_shift_right = abs(right["center_x"] - front["center_x"])
    area_change_left = abs(left["area_ratio"] - front["area_ratio"])
    area_change_right = abs(right["area_ratio"] - front["area_ratio"])

    checks = {
        "front_centered": abs(front["yaw"]) <= 0.10,
        "left_turn": abs(yaw_left_delta) >= 0.055,
        "right_turn": abs(yaw_right_delta) >= 0.055,
        "opposite_direction": yaw_left_delta * yaw_right_delta < 0,
        "center_stable": center_shift_left <= 0.14 and center_shift_right <= 0.14,
        "area_stable": area_change_left <= 0.12 and area_change_right <= 0.12,
    }

    metrics = {
        "front": front,
        "left": left,
        "right": right,
        "yaw_left_delta": round(yaw_left_delta, 4),
        "yaw_right_delta": round(yaw_right_delta, 4),
        "center_shift_left": round(center_shift_left, 4),
        "center_shift_right": round(center_shift_right, 4),
        "area_change_left": round(area_change_left, 4),
        "area_change_right": round(area_change_right, 4),
        "checks": checks,
    }

    score = sum(1 for ok in checks.values() if ok) / len(checks)

    if all(checks.values()):
        return _result(True, score, "Đã vượt qua kiểm tra quay đầu chống gian lận.", metrics)

    return _result(
        False,
        score,
        "Chưa vượt qua chống gian lận. Vui lòng nhìn thẳng, quay trái rồi quay phải bằng khuôn mặt thật.",
        metrics,
    )


def decode_image(image_bytes):
    np_arr = np.frombuffer(image_bytes, np.uint8)
    return cv2.imdecode(np_arr, cv2.IMREAD_COLOR)


def _pose_sample(frame, face):
    h, w = frame.shape[:2]
    x1, y1, x2, y2 = map(float, face.bbox)
    kps = np.asarray(face.kps, dtype=np.float32)

    left_eye = kps[0]
    right_eye = kps[1]
    nose = kps[2]
    mouth_left = kps[3]
    mouth_right = kps[4]

    eye_center_x = float((left_eye[0] + right_eye[0]) / 2)
    mouth_center_x = float((mouth_left[0] + mouth_right[0]) / 2)
    face_width = max(1.0, x2 - x1)
    yaw = ((float(nose[0]) - ((eye_center_x + mouth_center_x) / 2)) / face_width)

    return {
        "yaw": round(float(yaw), 4),
        "center_x": round(float(((x1 + x2) / 2) / max(1, w)), 4),
        "center_y": round(float(((y1 + y2) / 2) / max(1, h)), 4),
        "area_ratio": round(float(((x2 - x1) * (y2 - y1)) / max(1, w * h)), 4),
    }


def _clamp_bbox(bbox, width, height):
    x1, y1, x2, y2 = map(int, bbox)
    pad_x = int((x2 - x1) * 0.08)
    pad_y = int((y2 - y1) * 0.08)
    return (
        max(0, x1 - pad_x),
        max(0, y1 - pad_y),
        min(width, x2 + pad_x),
        min(height, y2 + pad_y),
    )


def _min_score(value, low, high):
    if high <= low:
        return 1.0
    return float(np.clip((value - low) / (high - low), 0.0, 1.0))


def _range_score(value, low, high):
    if low <= value <= high:
        return 1.0
    if value < low:
        return _min_score(value, low * 0.4, low)
    return 1.0 - _min_score(value, high, min(1.0, high * 1.35))


def _message_for_failed_checks(failed):
    if "face_size" in failed:
        return "Vui lòng đưa mặt vào đúng khung, không quá xa hoặc quá gần camera."
    if "brightness" in failed:
        return "Ánh sáng chưa phù hợp, vui lòng đổi góc hoặc bật thêm đèn."
    if "sharpness" in failed:
        return "Ảnh khuôn mặt bị mờ, vui lòng giữ yên camera."
    if "color_variation" in failed or "texture" in failed:
        return "Hệ thống nghi ngờ ảnh/chụp màn hình. Vui lòng dùng khuôn mặt thật trước camera."
    if "specular" in failed:
        return "Ảnh có vùng phản sáng bất thường, vui lòng tránh màn hình hoặc ảnh in."
    return "Không vượt qua kiểm tra chống giả mạo khuôn mặt."


def _result(is_live, score, message, metrics):
    return {
        "is_live": bool(is_live),
        "score": round(float(score), 4),
        "message": message,
        "metrics": metrics,
    }
