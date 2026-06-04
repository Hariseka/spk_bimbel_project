<?php
include 'koneksi.php';
include 'functions.php';

// Evaluasi ini memakai aturan pakar sederhana sebagai pembanding demonstrasi.
// Dalam penggunaan nyata, ganti aturan ini dengan ranking dari guru BK/wali kelas.
function skor_pakar($row) {
    $skor = 0;
    if ((float)$row['kehadiran'] < 95) $skor += 3;
    if ((int)$row['terlambat'] >= 3) $skor += 3;
    if ((int)$row['pelanggaran'] >= 1) $skor += 3;
    if ((float)$row['nilai_sikap'] <= 80) $skor += 2;
    if ((float)$row['nilai_pas'] < 85) $skor += 3;
    return $skor;
}

$siswa = get_siswa($conn);
$kriteria = get_kriteria($conn);
$hasil = calculate_saw($siswa, $kriteria);
save_hasil_saw($conn, $hasil);

// Ranking pakar berbasis rule.
$pakar = [];
foreach ($siswa as $row) {
    $pakar[] = [
        'id' => (int)$row['id'],
        'nama_siswa' => $row['nama_siswa'],
        'skor_pakar' => skor_pakar($row),
    ];
}
usort($pakar, function($a, $b) {
    if ($b['skor_pakar'] == $a['skor_pakar']) return $a['id'] <=> $b['id'];
    return $b['skor_pakar'] <=> $a['skor_pakar'];
});

$rank_pakar = [];
foreach ($pakar as $i => $p) {
    $rank_pakar[$p['id']] = $i + 1;
}

$n = count($hasil);
$sum_d2 = 0;
foreach ($hasil as $h) {
    $d = (int)$h['ranking'] - (int)$rank_pakar[$h['id']];
    $sum_d2 += $d * $d;
}
$spearman = ($n > 1) ? 1 - ((6 * $sum_d2) / ($n * (($n * $n) - 1))) : 0;

$top5_spk = array_slice(array_column($hasil, 'id'), 0, 5);
$top5_pakar = array_slice(array_column($pakar, 'id'), 0, 5);
$top10_spk = array_slice(array_column($hasil, 'id'), 0, 10);
$top10_pakar = array_slice(array_column($pakar, 'id'), 0, 10);

$akurasi_top5 = count(array_intersect($top5_spk, $top5_pakar)) / 5 * 100;
$akurasi_top10 = count(array_intersect($top10_spk, $top10_pakar)) / 10 * 100;

send_json([
    'status' => 'ok',
    'message' => 'Evaluasi SPK berhasil dilakukan.',
    'spearman_correlation' => round($spearman, 4),
    'akurasi_top5' => round($akurasi_top5, 2),
    'akurasi_top10' => round($akurasi_top10, 2),
    'catatan' => 'Nilai evaluasi memakai rule pakar demonstrasi. Untuk laporan, sebaiknya validasi ranking pakar dari guru/wali kelas.',
]);
?>
