from fastapi import FastAPI, File, UploadFile, HTTPException
from fastapi.responses import FileResponse
import onnxruntime as ort
import spectral
import numpy as np
import cv2
import tempfile
import os
import shutil

app = FastAPI(title="HortiVision AI Inference Server")

# ── Constants from model documentation ──────────────────────
CLASS_NAMES      = ["muscadine_copper", "muscadine_darkpurple", "muscadine_purple"]
CLASSIFIER_SCALE = 24066.9
TARGET_SIZE      = 224

# 8 band indices (0-based) selected for classification
BAND_INDICES = [88, 124, 144, 167, 189, 207, 234, 262]

# Segmentation band (860nm) for adaptive bounding box
SEG_BAND_INDEX = 234

# ── Load ONNX model once at startup ─────────────────────────
MODEL_PATH  = "models/mobilevit_rawbil_hsi8.onnx"
session     = ort.InferenceSession(MODEL_PATH)
input_name  = session.get_inputs()[0].name    # "hsi8_input"
output_name = session.get_outputs()[0].name   # "logits"

print(f"Model loaded: {MODEL_PATH}")
print(f"Input : {input_name} {session.get_inputs()[0].shape}")
print(f"Output: {output_name} {session.get_outputs()[0].shape}")


def softmax_fn(logits: np.ndarray) -> np.ndarray:
    e = np.exp(logits - np.max(logits))
    return e / e.sum()


def segment_bbox(band_image: np.ndarray):
    """
    Adaptive HSI segmentation using the 860nm band.
    Returns (x1, y1, x2, y2) with padding.
    Falls back to full image if segmentation fails.
    """
    h, w = band_image.shape
    norm    = cv2.normalize(band_image, None, 0, 255, cv2.NORM_MINMAX)
    gray    = norm.astype(np.uint8)
    blurred = cv2.GaussianBlur(gray, (5, 5), 0)
    _, thresh = cv2.threshold(blurred, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
    kernel  = cv2.getStructuringElement(cv2.MORPH_ELLIPSE, (3, 3))
    opened  = cv2.morphologyEx(thresh, cv2.MORPH_OPEN, kernel)
    closed  = cv2.morphologyEx(
        opened, cv2.MORPH_CLOSE,
        cv2.getStructuringElement(cv2.MORPH_ELLIPSE, (9, 9))
    )
    contours, _ = cv2.findContours(closed, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
    if not contours:
        return 0, 0, w, h
    largest      = max(contours, key=cv2.contourArea)
    x, y, bw, bh = cv2.boundingRect(largest)
    pad_x = int(bw * 0.15)
    pad_y = int(bh * 0.15)
    x1 = max(0, x - pad_x)
    y1 = max(0, y - pad_y)
    x2 = min(w, x + bw + pad_x)
    y2 = min(h, y + bh + pad_y)
    return x1, y1, x2, y2

def preprocess(hdr_path: str, bil_path: str):
    img = spectral.open_image(hdr_path)
    
    # Only load the 9 bands we need — saves ~97% memory
    needed_bands = sorted(set(BAND_INDICES + [SEG_BAND_INDEX]))  
    # [88, 124, 144, 167, 189, 207, 234, 262] + [234] = 8 unique bands
    
    # Load only needed bands
    partial = img.read_bands(needed_bands)  # shape (500, 900, 9)
    
    # Remap indices to new positions in partial array
    band_map = {b: i for i, b in enumerate(needed_bands)}
    
    seg_band = partial[:, :, band_map[SEG_BAND_INDEX]]
    x1, y1, x2, y2 = segment_bbox(seg_band)
    
    crop = partial[y1:y2, x1:x2, :]
    
    # Select 8 classifier bands using remapped indices
    hsi8 = np.stack([crop[:, :, band_map[b]] for b in BAND_INDICES], axis=-1)
    
    resized = np.zeros((TARGET_SIZE, TARGET_SIZE, 8), dtype=np.float32)
    for i in range(8):
        resized[:, :, i] = cv2.resize(
            hsi8[:, :, i].astype(np.float32),
            (TARGET_SIZE, TARGET_SIZE),
            interpolation=cv2.INTER_LINEAR
        )
    
    resized = resized / CLASSIFIER_SCALE
    chw  = np.transpose(resized, (2, 0, 1))
    nchw = np.expand_dims(chw, axis=0)
    
    return nchw, (x1, y1, x2, y2)
# ── Routes ───────────────────────────────────────────────────

@app.get("/")
def root():
    return {
        "status"  : "HortiVision AI inference server running",
        "model"   : "MobileViT HSI8 — Muscadine Grape Classifier",
        "classes" : CLASS_NAMES
    }


@app.post("/predict")
async def predict(
    bil_file: UploadFile = File(..., description="ENVI .bil hyperspectral file"),
    hdr_file: UploadFile = File(..., description="Matching .hdr header file")
):
    tmp_dir = tempfile.mkdtemp()
    try:
        bil_path = os.path.join(tmp_dir, "input.bil")
        hdr_path = os.path.join(tmp_dir, "input.bil.hdr")

        with open(bil_path, "wb") as f:
            f.write(await bil_file.read())
        with open(hdr_path, "wb") as f:
            f.write(await hdr_file.read())

        input_tensor, bbox = preprocess(hdr_path, bil_path)

        logits = session.run([output_name], {input_name: input_tensor})[0][0]
        probs  = softmax_fn(logits)

        pred_index    = int(np.argmax(probs))
        prediction    = CLASS_NAMES[pred_index]
        confidence    = float(probs[pred_index])
        probabilities = {CLASS_NAMES[i]: float(probs[i]) for i in range(len(CLASS_NAMES))}

        return {
            "ok"           : True,
            "prediction"   : prediction,
            "confidence"   : confidence,
            "probabilities": probabilities,
            "bbox_xyxy"    : list(bbox)
        }

    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

    finally:
        shutil.rmtree(tmp_dir, ignore_errors=True)


@app.get("/health")
def health():
    return {"status": "ok"}