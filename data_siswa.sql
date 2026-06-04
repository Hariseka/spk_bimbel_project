-- Database SPK Prioritas Siswa Penerima Bimbingan Belajar Tambahan
-- Import file ini melalui phpMyAdmin atau MySQL CLI.
CREATE DATABASE IF NOT EXISTS spk_bimbel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE spk_bimbel;

DROP TABLE IF EXISTS hasil_saw;
DROP TABLE IF EXISTS kriteria;
DROP TABLE IF EXISTS siswa;

CREATE TABLE siswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alternatif VARCHAR(20) NOT NULL,
    nama_siswa VARCHAR(150) NOT NULL,
    kehadiran DECIMAL(5,2) NOT NULL,
    terlambat INT NOT NULL,
    pelanggaran INT NOT NULL,
    nilai_sikap DECIMAL(5,2) NOT NULL,
    nilai_pas DECIMAL(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE kriteria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    field_name VARCHAR(50) NOT NULL,
    bobot DECIMAL(5,2) NOT NULL,
    tipe ENUM('benefit','cost') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE hasil_saw (
    id INT AUTO_INCREMENT PRIMARY KEY,
    siswa_id INT NOT NULL,
    r_kehadiran DECIMAL(10,4) NOT NULL,
    r_terlambat DECIMAL(10,4) NOT NULL,
    r_pelanggaran DECIMAL(10,4) NOT NULL,
    r_nilai_sikap DECIMAL(10,4) NOT NULL,
    r_nilai_pas DECIMAL(10,4) NOT NULL,
    skor_saw DECIMAL(10,4) NOT NULL,
    ranking INT NOT NULL,
    kategori VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_siswa (siswa_id),
    CONSTRAINT fk_hasil_siswa FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO kriteria (nama, field_name, bobot, tipe) VALUES
('Kehadiran', 'kehadiran', 0.20, 'cost'),
('Terlambat', 'terlambat', 0.15, 'benefit'),
('Pelanggaran', 'pelanggaran', 0.15, 'benefit'),
('Nilai Sikap', 'nilai_sikap', 0.20, 'cost'),
('Nilai PAS', 'nilai_pas', 0.30, 'cost');

INSERT INTO siswa (alternatif, nama_siswa, kehadiran, terlambat, pelanggaran, nilai_sikap, nilai_pas) VALUES
('a1', 'AHMAD ABDILLAH ARAYYAN', 100.00, 0, 0, 90.00, 82.33),
('a2', 'AKHMAD ALIF AL AYDRUS', 95.00, 2, 0, 80.00, 81.33),
('a3', 'ALPINO DZAKA OKTAVIAN', 97.00, 1, 0, 90.00, 83.67),
('a4', 'ARFAN AKBAR DWINARTA', 92.00, 3, 1, 80.00, 87.50),
('a5', 'ARSYA PUTRA PRATAMA', 96.00, 1, 0, 90.00, 84.17),
('a6', 'BAGAS ACHMAD PRATAMA', 93.00, 2, 0, 80.00, 83.83),
('a7', 'BARAKA MAULANA IBRAHIM', 100.00, 0, 0, 90.00, 85.17),
('a8', 'CINTA PRAJNA ANINDITA', 98.00, 1, 0, 90.00, 85.83),
('a9', 'DEVEN NARENDRA SHALFIE', 95.00, 2, 0, 80.00, 81.33),
('a10', 'EDGARD MAHARDIKA', 96.00, 1, 0, 90.00, 84.67),
('a11', 'EL NINO MANGGALA SAKHIY', 90.00, 4, 2, 80.00, 81.00),
('a12', 'ESTER CHELSYATAMA WIJAYA', 99.00, 0, 0, 90.00, 87.50),
('a13', 'FARANISHA AURELIA', 97.00, 1, 0, 90.00, 84.50),
('a14', 'GABRIELLO PURWANDA WIJAYA', 94.00, 2, 0, 80.00, 87.33),
('a15', 'HAFIDZ REIHAN ARDI NUGROHO', 96.00, 1, 0, 90.00, 84.33),
('a16', 'HERLINA DIAN CHRISTABELL', 95.00, 2, 0, 80.00, 85.50),
('a17', 'IKA AULIANA SETYOWATI', 97.00, 1, 0, 90.00, 85.33),
('a18', 'JABBAR ALMER DZAKY', 91.00, 3, 1, 80.00, 84.17),
('a19', 'KHAIREN ASYA AZ ZAHRA', 100.00, 0, 0, 90.00, 85.67),
('a20', 'KHANZA ARINA', 96.00, 1, 0, 90.00, 82.67),
('a21', 'MARITZA FAWWAZ RAFA WIGUNA', 95.00, 2, 0, 80.00, 83.83),
('a22', 'MASTITA ROBIATUL ADAWIYAH', 97.00, 1, 0, 90.00, 83.50),
('a23', 'MUHAMMAD BAGAS IRBANY SHAUQY', 94.00, 2, 0, 80.00, 84.67),
('a24', 'MUHAMMAD FAUZAN IRFAN YA AFI', 96.00, 1, 0, 90.00, 88.17),
('a25', 'MUHAMMAD FIKRI AL KHAKIM', 88.00, 5, 3, 70.00, 84.50),
('a26', 'MUHAMMAD HANZHALAH FIRMANSYAH', 97.00, 1, 0, 90.00, 79.33),
('a27', 'MUHAMMAD RAMADHIANSYAH', 95.00, 2, 0, 80.00, 88.50),
('a28', 'MUHAMMAD RAYYAN FAZA', 100.00, 0, 0, 90.00, 84.00),
('a29', 'RAFA FIKAN ALAMSYAH PUTRA', 94.00, 2, 0, 80.00, 85.33),
('a30', 'REVAN YULIAN PERMANA P.W.', 96.00, 1, 0, 90.00, 85.17),
('a31', 'RIFKY AZIDAN ALANSYAH', 92.00, 3, 1, 80.00, 88.83),
('a32', 'SALMA NURIL RIZKY AZZAHRO', 97.00, 1, 0, 90.00, 87.00),
('a33', 'SATRIA WIJAYA', 95.00, 2, 0, 80.00, 84.33),
('a34', 'SEVIAN THALITA MEYCCA', 98.00, 1, 0, 90.00, 87.00),
('a35', 'VALENANDRA SATYA ADELAID PRIBADI', 96.00, 1, 0, 90.00, 85.83),
('a36', 'ZAHWA ARUM RAMADHANI', 97.00, 1, 0, 80.00, 84.67);
