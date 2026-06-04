<?php
include 'koneksi.php';
$res = mysqli_query($conn, "SELECT * FROM siswa ORDER BY id ASC");
$siswa = mysqli_fetch_all($res, MYSQLI_ASSOC);
$res_k = mysqli_query($conn, "SELECT * FROM kriteria ORDER BY id ASC");
$kriteria = mysqli_fetch_all($res_k, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPK Prioritas Bimbingan Belajar Tambahan</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <main class="container">
        <section class="hero">
            <div>
                <p class="badge">Sistem Pendukung Keputusan</p>
                <h1>Optimasi Penentuan Prioritas Siswa Penerima Bimbingan Belajar Tambahan</h1>
                <p class="subtitle">Metode SAW dengan kriteria kehadiran, keterlambatan, pelanggaran, nilai sikap, dan nilai PAS.</p>
            </div>
            <div class="hero-card">
                <strong><?= count($siswa) ?></strong>
                <span>Data Siswa</span>
            </div>
        </section>

        <section class="grid two">
            <div class="card">
                <h2>Bobot Kriteria</h2>
                <table>
                    <thead><tr><th>Kriteria</th><th>Bobot</th><th>Tipe</th></tr></thead>
                    <tbody>
                    <?php foreach($kriteria as $k): ?>
                        <tr>
                            <td><?= htmlspecialchars($k['nama']) ?></td>
                            <td><?= number_format((float)$k['bobot'], 2) ?></td>
                            <td><span class="pill <?= $k['tipe'] ?>"><?= htmlspecialchars($k['tipe']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="card">
                <h2>Upload Excel</h2>
                <p class="muted">Format kolom: Alternatif, Nama Siswa, Kehadiran (%), Terlambat, Pelanggaran, Nilai Sikap, Nilai Pas.</p>
                <input type="file" id="fileExcel" accept=".xlsx,.xls,.csv">
                <button class="btn secondary" onclick="importExcel()">Import Excel ke Database</button>
                <p id="importStatus" class="muted"></p>
            </div>
        </section>

        <section class="card">
            <div class="toolbar">
                <div>
                    <h2>Data Alternatif Siswa</h2>
                    <p class="muted">Data awal sudah diambil dari file Excel dan disimpan ke database.</p>
                </div>
                <div class="actions">
                    <button class="btn" onclick="hitungSAW()">Hitung SAW</button>
                    <button class="btn dark" onclick="hitungHybrid()">Ranking Hybrid SAW + ML</button>
                    <button class="btn secondary" onclick="evaluasiSPK()">Evaluasi SPK</button>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Alt</th><th>Nama Siswa</th><th>Kehadiran</th><th>Terlambat</th><th>Pelanggaran</th><th>Nilai Sikap</th><th>Nilai PAS</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach($siswa as $s): ?>
                        <tr>
                            <td><?= htmlspecialchars($s['alternatif']) ?></td>
                            <td><?= htmlspecialchars($s['nama_siswa']) ?></td>
                            <td><?= number_format((float)$s['kehadiran'], 2) ?>%</td>
                            <td><?= (int)$s['terlambat'] ?></td>
                            <td><?= (int)$s['pelanggaran'] ?></td>
                            <td><?= number_format((float)$s['nilai_sikap'], 2) ?></td>
                            <td><?= number_format((float)$s['nilai_pas'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="card" id="hasilCard" style="display:none;">
            <h2 id="hasilTitle">Hasil</h2>
            <div id="hasil"></div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script>
    function showResult(title, html) {
        document.getElementById('hasilCard').style.display = 'block';
        document.getElementById('hasilTitle').textContent = title;
        document.getElementById('hasil').innerHTML = html;
        document.getElementById('hasilCard').scrollIntoView({ behavior: 'smooth' });
    }

    function renderRanking(data, mode = 'saw') {
        const rows = data.map((item) => {
            const skor = mode === 'hybrid' ? item.skor_hybrid : item.skor_saw;
            const ranking = mode === 'hybrid' ? item.ranking_hybrid : item.ranking;
            const kategori = mode === 'hybrid' ? item.kategori_hybrid : item.kategori;
            const mlCols = mode === 'hybrid' ? `<td>${item.proba_ml}</td><td>${item.keterangan_ml}</td>` : '';
            return `
                <tr>
                    <td><strong>${ranking}</strong></td>
                    <td>${item.nama_siswa}</td>
                    <td>${item.kehadiran}%</td>
                    <td>${item.terlambat}</td>
                    <td>${item.pelanggaran}</td>
                    <td>${item.nilai_sikap}</td>
                    <td>${item.nilai_pas}</td>
                    <td><strong>${skor}</strong></td>
                    ${mlCols}
                    <td><span class="status">${kategori}</span></td>
                </tr>`;
        }).join('');

        const mlHead = mode === 'hybrid' ? '<th>Proba ML</th><th>Status ML</th>' : '';
        return `<div class="table-wrap"><table>
            <thead><tr><th>Rank</th><th>Nama Siswa</th><th>Kehadiran</th><th>Terlambat</th><th>Pelanggaran</th><th>Sikap</th><th>PAS</th><th>Skor</th>${mlHead}<th>Kategori</th></tr></thead>
            <tbody>${rows}</tbody>
        </table></div>`;
    }

    async function hitungSAW() {
        showResult('Memproses...', '<p class="muted">Sedang menghitung SAW...</p>');
        const res = await fetch('hitung_saw.php');
        const json = await res.json();
        if (json.status !== 'ok') return showResult('Error', `<pre>${JSON.stringify(json, null, 2)}</pre>`);
        showResult('Hasil Ranking SAW', renderRanking(json.data, 'saw'));
    }

    async function hitungHybrid() {
        showResult('Memproses...', '<p class="muted">Sedang menghubungkan PHP ke FastAPI ML...</p>');
        const res = await fetch('integrasi.php');
        const json = await res.json();
        if (json.status !== 'ok') {
            const fallback = json.saw_fallback ? '<h3>Fallback SAW</h3>' + renderRanking(json.saw_fallback, 'saw') : '';
            return showResult('FastAPI ML Belum Aktif', `<p class="error">${json.message}</p><pre>${json.detail || ''}</pre>${fallback}`);
        }
        showResult('Hasil Ranking Hybrid SAW + ML', renderRanking(json.data, 'hybrid'));
    }

    async function evaluasiSPK() {
        const res = await fetch('evaluasi.php');
        const json = await res.json();
        showResult('Evaluasi SPK', `<div class="metrics">
            <div><strong>${json.spearman_correlation}</strong><span>Spearman</span></div>
            <div><strong>${json.akurasi_top5}%</strong><span>Akurasi Top-5</span></div>
            <div><strong>${json.akurasi_top10}%</strong><span>Akurasi Top-10</span></div>
        </div><p class="muted">${json.catatan}</p>`);
    }

    async function importExcel() {
        const file = document.getElementById('fileExcel').files[0];
        if (!file) return alert('Pilih file Excel terlebih dahulu.');
        document.getElementById('importStatus').textContent = 'Membaca file...';

        const data = await file.arrayBuffer();
        const workbook = XLSX.read(data);
        const sheet = workbook.Sheets[workbook.SheetNames[0]];
        const rows = XLSX.utils.sheet_to_json(sheet, { defval: '' });

        const res = await fetch('import_excel.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ rows })
        });
        const json = await res.json();
        document.getElementById('importStatus').textContent = json.message || 'Import selesai.';
        if (json.status === 'ok') setTimeout(() => location.reload(), 1000);
    }
    </script>
</body>
</html>
