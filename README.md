# SPK Prioritas Siswa Penerima Bimbingan Belajar Tambahan

Proyek ini memakai:

- PHP + MySQL untuk antarmuka web dan perhitungan SAW.
- Metode SAW untuk ranking prioritas siswa.
- FastAPI + Random Forest untuk prediksi Machine Learning.
- Integrasi hybrid: 70% skor SAW + 30% probabilitas ML.

## Struktur Folder

```text
spk_bimbel_project/
├── spk_bimbel/
│   ├── index.php
│   ├── koneksi.php
│   ├── functions.php
│   ├── hitung_saw.php
│   ├── integrasi.php
│   ├── evaluasi.php
│   ├── import_excel.php
│   ├── data_siswa.sql
│   └── assets/style.css
└── ml_api/
    ├── api_ml.py
    ├── train_model.py
    ├── data_training_siswa.csv
    └── requirements.txt
```

## Cara Menjalankan Web PHP/MySQL

1. Buka XAMPP, jalankan Apache dan MySQL.
2. Copy folder `spk_bimbel` ke:

```text
C:\xampp\htdocs\spk_bimbel
```

3. Buka `phpMyAdmin`, lalu import file:

```text
spk_bimbel/data_siswa.sql
```

4. Buka browser:

```text
http://localhost/spk_bimbel/index.php
```

5. Klik tombol **Hitung SAW** untuk melihat ranking prioritas.

## Cara Menjalankan Machine Learning API

1. Masuk ke folder `ml_api`:

```bash
cd ml_api
```

2. Install library:

```bash
pip install -r requirements.txt
```

3. Training model:

```bash
python train_model.py
```

4. Jalankan FastAPI:

```bash
uvicorn api_ml:app --reload --port 8000
```

5. Cek dokumentasi API:

```text
http://127.0.0.1:8000/docs
```

6. Kembali ke web, klik **Ranking Hybrid SAW + ML**.

## Kriteria dan Bobot

| Kriteria | Bobot | Tipe | Makna dalam prioritas bimbel |
|---|---:|---|---|
| Kehadiran | 0.20 | Cost | Kehadiran lebih rendah berarti lebih diprioritaskan |
| Terlambat | 0.15 | Benefit | Semakin sering terlambat berarti semakin diprioritaskan |
| Pelanggaran | 0.15 | Benefit | Semakin banyak pelanggaran berarti semakin diprioritaskan |
| Nilai Sikap | 0.20 | Cost | Nilai sikap lebih rendah berarti lebih diprioritaskan |
| Nilai PAS | 0.30 | Cost | Nilai PAS lebih rendah berarti lebih diprioritaskan |

## Catatan Penting

- Label pada `data_training_siswa.csv` adalah label demonstrasi untuk kebutuhan praktikum. Untuk penelitian atau produk nyata, label sebaiknya ditentukan oleh guru BK/wali kelas.
- File `evaluasi.php` memakai rule pakar sederhana. Untuk laporan, ganti dengan ranking pakar yang divalidasi guru.
- Upload Excel di `index.php` memakai SheetJS CDN, sehingga fitur upload Excel membutuhkan internet. Jika tidak ada internet, gunakan data dari `data_siswa.sql`.
