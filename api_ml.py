from fastapi import FastAPI
from pydantic import BaseModel, Field
import joblib
import numpy as np
from typing import List
from pathlib import Path

MODEL_PATH = Path("model_siswa.pkl")
SCALER_PATH = Path("scaler_siswa.pkl")
FEATURES = ["kehadiran", "terlambat", "pelanggaran", "nilai_sikap", "nilai_pas"]

app = FastAPI(title="SPK Prioritas Bimbingan Belajar ML API")

model = None
scaler = None
if MODEL_PATH.exists() and SCALER_PATH.exists():
    model = joblib.load(MODEL_PATH)
    scaler = joblib.load(SCALER_PATH)

class SiswaInput(BaseModel):
    nama_siswa: str
    kehadiran: float = Field(..., ge=0, le=100)
    terlambat: int = Field(..., ge=0)
    pelanggaran: int = Field(..., ge=0)
    nilai_sikap: float = Field(..., ge=0, le=100)
    nilai_pas: float = Field(..., ge=0, le=100)

class PrediksiOutput(BaseModel):
    nama_siswa: str
    label: int
    proba_butuh_bimbel: float
    keterangan: str

@app.get("/")
def root():
    return {
        "message": "API ML SPK Bimbingan Belajar aktif",
        "model_loaded": model is not None and scaler is not None,
        "features": FEATURES,
    }

def predict_one(data: SiswaInput):
    if model is None or scaler is None:
        raise RuntimeError("Model belum tersedia. Jalankan: python train_model.py")

    X = np.array([[data.kehadiran, data.terlambat, data.pelanggaran, data.nilai_sikap, data.nilai_pas]])
    X_scaled = scaler.transform(X)
    label = int(model.predict(X_scaled)[0])

    proba_all = model.predict_proba(X_scaled)[0]
    class_to_index = {int(cls): idx for idx, cls in enumerate(model.classes_)}
    proba_butuh = float(proba_all[class_to_index.get(1, 0)]) if 1 in class_to_index else 0.0
    keterangan = "Perlu Bimbingan Tambahan" if label == 1 else "Belum Prioritas Utama"

    return {
        "nama_siswa": data.nama_siswa,
        "label": label,
        "proba_butuh_bimbel": round(proba_butuh, 4),
        "keterangan": keterangan,
    }

@app.post("/prediksi", response_model=PrediksiOutput)
def prediksi(data: SiswaInput):
    return predict_one(data)

@app.post("/prediksi-batch")
def prediksi_batch(items: List[SiswaInput]):
    hasil = [predict_one(item) for item in items]
    return {"hasil": hasil}
