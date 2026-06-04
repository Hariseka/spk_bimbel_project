<?php
include 'koneksi.php';
include 'functions.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['rows']) || !is_array($input['rows'])) {
    send_json(['status' => 'error', 'message' => 'Data Excel tidak valid.'], 400);
}

function pick_value($row, $keys, $default = null) {
    foreach ($keys as $key) {
        if (isset($row[$key]) && $row[$key] !== '') return $row[$key];
    }
    return $default;
}

mysqli_query($conn, "SET FOREIGN_KEY_CHECKS=0");
mysqli_query($conn, "TRUNCATE TABLE hasil_saw");
mysqli_query($conn, "TRUNCATE TABLE siswa");
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS=1");

$stmt = mysqli_prepare($conn, "INSERT INTO siswa (alternatif, nama_siswa, kehadiran, terlambat, pelanggaran, nilai_sikap, nilai_pas) VALUES (?, ?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    send_json(['status' => 'error', 'message' => mysqli_error($conn)], 500);
}

$count = 0;
foreach ($input['rows'] as $row) {
    $alternatif = trim((string)pick_value($row, ['Alternatif', 'alternatif'], 'a' . ($count + 1)));
    $nama = trim((string)pick_value($row, ['Nama Siswa', 'nama_siswa', 'Nama'], ''));
    if ($nama === '') continue;

    $kehadiran = (float)pick_value($row, ['Kehadiran (%)', 'Kehadiran', 'kehadiran'], 0);
    $terlambat = (int)pick_value($row, ['Terlambat', 'terlambat'], 0);
    $pelanggaran = (int)pick_value($row, ['Pelanggaran', 'pelanggaran'], 0);
    $nilai_sikap = (float)pick_value($row, ['Nilai Sikap', 'nilai_sikap'], 0);
    $nilai_pas = (float)pick_value($row, ['Nilai Pas', 'Nilai PAS', 'nilai_pas'], 0);

    mysqli_stmt_bind_param($stmt, "ssdiidd", $alternatif, $nama, $kehadiran, $terlambat, $pelanggaran, $nilai_sikap, $nilai_pas);
    if (!mysqli_stmt_execute($stmt)) {
        send_json(['status' => 'error', 'message' => mysqli_stmt_error($stmt)], 500);
    }
    $count++;
}

send_json(['status' => 'ok', 'message' => "Berhasil mengimpor $count data siswa.", 'jumlah_data' => $count]);
?>
