from fastapi import FastAPI, UploadFile, File, Form
from fastapi.middleware.cors import CORSMiddleware

from recognition import (
    enroll_face,    
    recognize_face,
    reset_enroll_session
)

app = FastAPI()

# =====================================================
# CORS
# =====================================================

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# =====================================================
# ROOT
# =====================================================

@app.get("/")
def root():

    return {
        "message": "3C Face AI Running"
    }

# =====================================================
# ENROLL
# =====================================================

@app.post("/enroll")
async def enroll(
    user_id: int = Form(...),
    image: UploadFile = File(...)
):

    image_bytes = await image.read()

    result = await enroll_face(
        user_id,
        image_bytes
    )

    return result

@app.post("/enroll/reset")
async def reset_enroll(
    user_id: int = Form(...)
):
    return reset_enroll_session(user_id)

# RECOGNIZE
# =====================================================
@app.post("/recognize")
async def recognize(
    image: UploadFile = File(...)
):

    image_bytes = await image.read()

    result = await recognize_face(image_bytes)

    return result
