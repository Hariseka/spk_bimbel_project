<?php
include 'koneksi.php';
include 'functions.php';

$siswa = get_siswa($conn);
$kriteria = get_kriteria($conn);
$hasil = calculate_saw($siswa, $kriteria);
save_hasil_saw($conn, $hasil);

send_json([
    'status' => 'ok',
    'message' => 'Perhitungan SAW berhasil dilakukan.',
    'data' => $hasil
]);
?>
