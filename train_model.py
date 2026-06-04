import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import accuracy_score, classification_report
from sklearn.preprocessing import StandardScaler
import joblib

# label: 1 = perlu bimbingan belajar tambahan, 0 = belum menjadi prioritas utama.
DATASET = "data_training_siswa.csv"
FEATURES = ["kehadiran", "terlambat", "pelanggaran", "nilai_sikap", "nilai_pas"]

data = pd.read_csv(DATASET)
X = data[FEATURES]
y = data["label"]

scaler = StandardScaler()
X_scaled = scaler.fit_transform(X)

# Stratify dipakai agar data 0 dan 1 tetap seimbang pada data test.
X_train, X_test, y_train, y_test = train_test_split(
    X_scaled, y, test_size=0.25, random_state=42, stratify=y
)

model = RandomForestClassifier(
    n_estimators=200,
    random_state=42,
    class_weight="balanced"
)
model.fit(X_train, y_train)

y_pred = model.predict(X_test)
print(f"Akurasi Model: {accuracy_score(y_test, y_pred) * 100:.2f}%")
print(classification_report(y_test, y_pred, zero_division=0))

print("Feature importance:")
for name, importance in zip(FEATURES, model.feature_importances_):
    print(f"- {name}: {importance:.4f}")

joblib.dump(model, "model_siswa.pkl")
joblib.dump(scaler, "scaler_siswa.pkl")
print("Model dan scaler berhasil disimpan: model_siswa.pkl, scaler_siswa.pkl")
