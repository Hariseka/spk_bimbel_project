<?php
include 'koneksi.php';
include 'functions.php';

$siswa = get_siswa($conn);
$kriteria = get_kriteria($conn);
$hasil_saw = calculate_saw($siswa, $kriteria);
save_hasil_saw($conn, $hasil_saw);

$payload = array_map(function($row) {
    return [
        'nama_siswa' => $row['nama_siswa'],
        'kehadiran' => (float)$row['kehadiran'],
        'terlambat' => (int)$row['terlambat'],
        'pelanggaran' => (int)$row['pelanggaran'],
        'nilai_sikap' => (float)$row['nilai_sikap'],
        'nilai_pas' => (float)$row['nilai_pas'],
    ];
}, $siswa);

$api_url = 'http://127.0.0.1:8000/prediksi-batch';
$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 8);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($response === false || $http_code !== 200) {
    send_json([
        'status' => 'error',
        'message' => 'FastAPI ML belum aktif atau model belum tersedia. Jalankan: uvicorn api_ml:app --reload --port 8000',
        'detail' => $error ?: "HTTP $http_code",
        'saw_fallback' => $hasil_saw
    ], 503);
}

$prediksi = json_decode($response, true);
if (!isset($prediksi['hasil'])) {
    send_json(['status' => 'error', 'message' => 'Format respons API ML tidak valid.', 'raw' => $response], 500);
}

// Gabungkan SAW 70% dan probabilitas ML 30%.
$ml_by_name = [];
foreach ($prediksi['hasil'] as $p) {
    $ml_by_name[$p['nama_siswa']] = $p;
}

$hasil_hybrid = [];
foreach ($hasil_saw as $row) {
    $p = $ml_by_name[$row['nama_siswa']] ?? ['label' => 0, 'proba_butuh_bimbel' => 0, 'keterangan' => 'Tidak Ada Prediksi'];
    $proba = (float)$p['proba_butuh_bimbel'];
    $skor_hybrid = (0.70 * (float)$row['skor_saw']) + (0.30 * $proba);

    $hasil_hybrid[] = array_merge($row, [
        'prediksi_ml' => (int)$p['label'],
        'proba_ml' => round($proba, 4),
        'skor_hybrid' => round($skor_hybrid, 4),
        'keterangan_ml' => $p['keterangan']
    ]);
}

usort($hasil_hybrid, fn($a, $b) => $b['skor_hybrid'] <=> $a['skor_hybrid']);
foreach ($hasil_hybrid as $i => &$row) {
    $row['ranking_hybrid'] = $i + 1;
    $row['kategori_hybrid'] = kategori_prioritas($i + 1);
}

send_json([
    'status' => 'ok',
    'message' => 'Ranking hybrid SAW + ML berhasil dihitung.',
    'data' => $hasil_hybrid
]);
?>
