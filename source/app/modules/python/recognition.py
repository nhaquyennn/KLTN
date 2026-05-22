import cv2
import json
import time
import numpy as np
import mysql.connector

from insightface.app import FaceAnalysis
from sklearn.preprocessing import normalize
from anti_spoofing import check_liveness, decode_image, verify_head_turn_challenge

# =========================================================
# INSIGHTFACE
# =========================================================

app = FaceAnalysis(
    name="buffalo_l",
    providers=["CPUExecutionProvider"]
)

app.prepare(
    ctx_id=0,
    det_size=(640, 640)
)

# =========================================================
# DB CONFIG
# =========================================================

DB_CONFIG = {
    "host": "localhost",
    "user": "root",
    "password": "",
    "database": "merge_q"
}

# =========================================================
# CACHE
# =========================================================

temp_embeddings = {}
last_attendance = {}

def reset_enroll_session(user_id):
    temp_embeddings.pop(user_id, None)
    return {
        "success": True,
        "message": "Enrollment session reset"
    }


async def verify_liveness_challenge(front_bytes, left_bytes, right_bytes):

    frames = [
        decode_image(front_bytes),
        decode_image(left_bytes),
        decode_image(right_bytes)
    ]

    if any(frame is None for frame in frames):
        return {
            "success": False,
            "spoof_detected": True,
            "message": "Không đọc được ảnh kiểm tra chống gian lận."
        }

    result = verify_head_turn_challenge(frames, app)

    return {
        "success": result["is_live"],
        "spoof_detected": not result["is_live"],
        "liveness": result,
        "message": result["message"]
    }

# =========================================================
# DB
# =========================================================

def get_db():

    return mysql.connector.connect(
        **DB_CONFIG
    )

# =========================================================
# COSINE SIMILARITY
# =========================================================

def cosine_similarity(a, b):

    a = np.array(a)
    b = np.array(b)

    return np.dot(a, b)

# =========================================================
# ENROLL
# =========================================================

async def enroll_face(user_id, image_bytes):

    try:

        np_arr = np.frombuffer(
            image_bytes,
            np.uint8
        )

        frame = cv2.imdecode(
            np_arr,
            cv2.IMREAD_COLOR
        )

        if frame is None:

            return {
                "success": False,
                "message": "Cannot decode image"
            }

        # =====================================
        # DETECT
        # =====================================

        faces = app.get(frame)

        if len(faces) != 1:

            return {
                "success": False,
                "message":
                    f"Cần đúng 1 khuôn mặt (hiện có {len(faces)})"
            }

        liveness = check_liveness(frame, faces[0])

        if not liveness["is_live"]:

            return {
                "success": False,
                "spoof_detected": True,
                "liveness": liveness,
                "message": liveness["message"]
            }

        # =====================================
        # EMBEDDING
        # =====================================

        embedding = faces[0].embedding

        embedding = normalize(
            [embedding]
        )[0]

        embedding = embedding.tolist()

        # =====================================
        # CACHE
        # =====================================

        if user_id not in temp_embeddings:

            temp_embeddings[user_id] = []

        temp_embeddings[user_id].append(
            embedding
        )

        collected = len(
            temp_embeddings[user_id]
        )

        # =====================================
        # CHƯA ĐỦ 10
        # =====================================

        if collected < 10:

            return {
                "success": True,
                "done": False,
                "count": collected,
                "message":
                    f"Đã chụp {collected}/10"
            }

        # =====================================
        # MEAN EMBEDDING
        # =====================================

        embeddings = np.array(
            temp_embeddings[user_id]
        )

        mean_embedding = np.mean(
            embeddings,
            axis=0
        )

        mean_embedding = normalize(
            [mean_embedding]
        )[0]

        # =====================================
        # SAVE DB
        # =====================================

        conn = get_db()

        cursor = conn.cursor()

        embedding_json = json.dumps(
            mean_embedding.tolist()
        )

        cursor.execute("""
            SELECT face_id
            FROM face_data
            WHERE user_id = %s
            LIMIT 1
        """, (user_id,))

        existing = cursor.fetchone()

        if existing:
            cursor.execute("""
                UPDATE face_data
                SET face_embedding = %s,
                    is_active = 1,
                    created_at = NOW()
                WHERE face_id = %s
            """, (
                embedding_json,
                existing[0]
            ))
        else:
            cursor.execute("""
                INSERT INTO face_data
                (
                    user_id,
                    face_embedding,
                    is_active,
                    created_at
                )
                VALUES
                (
                    %s,
                    %s,
                    1,
                    NOW()
                )
            """, (
                user_id,
                embedding_json
            ))

        conn.commit()

        cursor.close()
        conn.close()

        del temp_embeddings[user_id]

        return {
            "success": True,
            "done": True,
            "message":
                "Đăng ký khuôn mặt thành công"
        }

    except Exception as e:

        print(e)

        return {
            "success": False,
            "message": str(e)
        }

# =========================================================
# RECOGNIZE
# =========================================================

async def recognize_face(image_bytes):

    conn = None
    cursor = None

    try:

        np_arr = np.frombuffer(
            image_bytes,
            np.uint8
        )

        frame = cv2.imdecode(
            np_arr,
            cv2.IMREAD_COLOR
        )

        if frame is None:

            return {
                "success": False,
                "message": "Cannot decode image"
            }

        # =====================================
        # DETECT
        # =====================================

        faces = app.get(frame)

        if len(faces) == 0:

            return {
                "success": True,
                "face_found": False
            }

        # lấy mặt lớn nhất
        face = max(
            faces,
            key=lambda f:
                (f.bbox[2]-f.bbox[0]) *
                (f.bbox[3]-f.bbox[1])
        )

        liveness = check_liveness(frame, face)

        if not liveness["is_live"]:

            x1, y1, x2, y2 = map(
                int,
                face.bbox
            )

            return {
                "success": True,
                "face_found": True,
                "matched": False,
                "spoof_detected": True,
                "liveness": liveness,
                "message": liveness["message"],
                "box": {
                    "top": y1,
                    "left": x1,
                    "width": x2 - x1,
                    "height": y2 - y1
                }
            }

        unknown = normalize(
            [face.embedding]
        )[0]

        # =====================================
        # LOAD DB
        # =====================================

        conn = get_db()

        cursor = conn.cursor(
            dictionary=True
        )

        cursor.execute("""
            SELECT
                fd.user_id,
                fd.face_embedding,
                u.name
            FROM face_data fd
            JOIN users u
                ON u.user_id = fd.user_id
        """)

        rows = cursor.fetchall()

        best_score = -1
        best_user = None

        # =====================================
        # COMPARE
        # =====================================

        for row in rows:

            known = np.array(
                json.loads(
                    row["face_embedding"]
                )
            )

            score = cosine_similarity(
                known,
                unknown
            )

            if score > best_score:

                best_score = score
                best_user = row

        # =====================================
        # THRESHOLD
        # =====================================

        if best_score < 0.55:

            return {
                "success": True,
                "face_found": True,
                "matched": False,
                "name": "Unknown"
            }

        x1, y1, x2, y2 = map(
            int,
            face.bbox
        )

        return {

            "success": True,
            "face_found": True,
            "matched": True,

            "user_id":
                best_user["user_id"],

            "name":
                best_user["name"],

            "score":
                float(best_score),

            "liveness":
                liveness,

            "box": {

                "top": y1,
                "left": x1,

                "width":
                    x2 - x1,

                "height":
                    y2 - y1
            }
        }

    except Exception as e:

        print(e)

        return {
            "success": False,
            "message": str(e)
        }

    finally:

        if cursor:
            cursor.close()

        if conn:
            conn.close()
