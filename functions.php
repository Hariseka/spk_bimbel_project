<?php
// functions.php
// Fungsi umum untuk SPK Bimbingan Belajar Tambahan.

function send_json($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

function get_siswa($conn) {
    $res = mysqli_query($conn, "SELECT * FROM siswa ORDER BY id ASC");
    if (!$res) {
        send_json(['status' => 'error', 'message' => mysqli_error($conn)], 500);
    }
    return mysqli_fetch_all($res, MYSQLI_ASSOC);
}

function get_kriteria($conn) {
    $res = mysqli_query($conn, "SELECT * FROM kriteria ORDER BY id ASC");
    if (!$res) {
        send_json(['status' => 'error', 'message' => mysqli_error($conn)], 500);
    }
    return mysqli_fetch_all($res, MYSQLI_ASSOC);
}

function kategori_prioritas($ranking) {
    if ($ranking <= 5) return "Sangat Prioritas";
    if ($ranking <= 10) return "Prioritas";
    return "Monitoring";
}

function calculate_saw($siswa, $kriteria) {
    if (count($siswa) === 0) {
        return [];
    }

    $min = [];
    $max = [];

    foreach ($kriteria as $k) {
        $field = $k['field_name'];
        $values = array_map(fn($row) => (float)$row[$field], $siswa);
        $min[$field] = min($values);
        $max[$field] = max($values);
    }

    $hasil = [];
    foreach ($siswa as $row) {
        $skor = 0;
        $normalisasi = [];

        foreach ($kriteria as $k) {
            $field = $k['field_name'];
            $bobot = (float)$k['bobot'];
            $tipe = $k['tipe'];
            $x = (float)$row[$field];

            if ($tipe === 'benefit') {
                // Benefit: semakin besar nilai, semakin tinggi prioritas.
                $r = ($max[$field] > 0) ? ($x / $max[$field]) : 0;
            } else {
                // Cost: semakin kecil nilai, semakin tinggi prioritas.
                // Contoh: nilai PAS rendah atau kehadiran rendah berarti siswa lebih diprioritaskan.
                $r = ($x > 0) ? ($min[$field] / $x) : 0;
            }

            $normalisasi[$field] = round($r, 4);
            $skor += $bobot * $r;
        }

        $hasil[] = [
            'id' => (int)$row['id'],
            'alternatif' => $row['alternatif'],
            'nama_siswa' => $row['nama_siswa'],
            'kehadiran' => (float)$row['kehadiran'],
            'terlambat' => (int)$row['terlambat'],
            'pelanggaran' => (int)$row['pelanggaran'],
            'nilai_sikap' => (float)$row['nilai_sikap'],
            'nilai_pas' => (float)$row['nilai_pas'],
            'r_kehadiran' => $normalisasi['kehadiran'] ?? 0,
            'r_terlambat' => $normalisasi['terlambat'] ?? 0,
            'r_pelanggaran' => $normalisasi['pelanggaran'] ?? 0,
            'r_nilai_sikap' => $normalisasi['nilai_sikap'] ?? 0,
            'r_nilai_pas' => $normalisasi['nilai_pas'] ?? 0,
            'skor_saw' => round($skor, 4),
        ];
    }

    usort($hasil, fn($a, $b) => $b['skor_saw'] <=> $a['skor_saw']);

    foreach ($hasil as $i => &$item) {
        $item['ranking'] = $i + 1;
        $item['kategori'] = kategori_prioritas($i + 1);
    }

    return $hasil;
}

function save_hasil_saw($conn, $hasil) {
    $stmt = mysqli_prepare($conn, "
        INSERT INTO hasil_saw
        (siswa_id, r_kehadiran, r_terlambat, r_pelanggaran, r_nilai_sikap, r_nilai_pas, skor_saw, ranking, kategori)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            r_kehadiran = VALUES(r_kehadiran),
            r_terlambat = VALUES(r_terlambat),
            r_pelanggaran = VALUES(r_pelanggaran),
            r_nilai_sikap = VALUES(r_nilai_sikap),
            r_nilai_pas = VALUES(r_nilai_pas),
            skor_saw = VALUES(skor_saw),
            ranking = VALUES(ranking),
            kategori = VALUES(kategori),
            created_at = CURRENT_TIMESTAMP
    ");

    if (!$stmt) {
        send_json(['status' => 'error', 'message' => mysqli_error($conn)], 500);
    }

    foreach ($hasil as $h) {
        $id = (int)$h['id'];
        $rk = (float)$h['r_kehadiran'];
        $rt = (float)$h['r_terlambat'];
        $rp = (float)$h['r_pelanggaran'];
        $rns = (float)$h['r_nilai_sikap'];
        $rnp = (float)$h['r_nilai_pas'];
        $skor = (float)$h['skor_saw'];
        $ranking = (int)$h['ranking'];
        $kategori = $h['kategori'];

        mysqli_stmt_bind_param($stmt, "iddddddis", $id, $rk, $rt, $rp, $rns, $rnp, $skor, $ranking, $kategori);
        if (!mysqli_stmt_execute($stmt)) {
            send_json(['status' => 'error', 'message' => mysqli_stmt_error($stmt)], 500);
        }
    }
}
?>
