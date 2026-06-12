from fastapi import FastAPI, File, UploadFile
import onnxruntime as ort
import numpy as np
from PIL import Image
import io

app = FastAPI()

# Load model on startup
session = ort.InferenceSession("models/mobilevit_rawbil_hsi8.onnx")
input_name = session.get_inputs()[0].name
output_name = session.get_outputs()[0].name

@app.get("/")
def root():
    return {"status": "HortiVision AI inference server running"}

@app.post("/predict")
async def predict(file: UploadFile = File(...)):
    # Read image
    contents = await file.read()
    image = Image.open(io.BytesIO(contents)).convert("RGB")
    image = image.resize((256, 256))
    
    # Prepare input
    img_array = np.array(image).astype(np.float32)
    img_array = img_array / 255.0
    img_array = np.transpose(img_array, (2, 0, 1))
    img_array = np.expand_dims(img_array, axis=0)
    
    # Run inference
    result = session.run([output_name], {input_name: img_array})
    
    return {"result": result[0].tolist()}
