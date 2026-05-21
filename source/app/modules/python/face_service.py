import json
import mysql.connector
import numpy as np

DB_CONFIG = {
    "host": "localhost",
    "user": "root",
    "password": "",
    "database": "merge_q"
}


# =====================================================
# DB CONNECT
# =====================================================

def get_connection():

    return mysql.connector.connect(**DB_CONFIG)


# =====================================================
# LOAD ALL EMBEDDINGS
# =====================================================

def load_all_faces():

    conn = get_connection()

    cursor = conn.cursor(dictionary=True)

    cursor.execute("""
        SELECT
            f.user_id,
            f.face_embedding,
            u.name,
            u.role
        FROM face_data f
        JOIN users u
            ON u.user_id = f.user_id
    """)

    rows = cursor.fetchall()

    result = []

    for row in rows:

        embeddings = json.loads(
            row["face_embedding"]
        )

        result.append({
            "user_id": row["user_id"],
            "name": row["name"],
            "role": row["role"],
            "embeddings": [
                np.array(e, dtype=np.float32)
                for e in embeddings
            ]
        })

    cursor.close()
    conn.close()

    return result


# =====================================================
# SAVE FACE EMBEDDING
# =====================================================

def save_face(user_id, embeddings):

    conn = get_connection()

    cursor = conn.cursor()

    embeddings_json = json.dumps([
        emb.tolist()
        for emb in embeddings
    ])

    cursor.execute("""
        SELECT face_id
        FROM face_data
        WHERE user_id = %s
    """, (user_id,))

    existing = cursor.fetchone()

    if existing:

        cursor.execute("""
            UPDATE face_data
            SET face_embedding = %s
            WHERE user_id = %s
        """, (
            embeddings_json,
            user_id
        ))

    else:

        cursor.execute("""
            INSERT INTO face_data
            (
                user_id,
                face_embedding
            )
            VALUES (%s,%s)
        """, (
            user_id,
            embeddings_json
        ))

    conn.commit()

    cursor.close()
    conn.close()


# =====================================================
# SAVE ATTENDANCE
# =====================================================

def save_attendance(user_id):

    conn = get_connection()

    cursor = conn.cursor()

    cursor.execute("""
        INSERT INTO attendance_logs
        (
            user_id,
            checkin_time,
            method,
            status
        )
        VALUES
        (
            %s,
            NOW(),
            'face',
            'success'
        )
    """, (user_id,))

    conn.commit()

    cursor.close()
    conn.close()
