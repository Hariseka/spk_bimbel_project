<?php
// koneksi.php
// Sesuaikan $user, $password, dan $database jika konfigurasi XAMPP/MySQL berbeda.
$host = "localhost";
$user = "root";
$password = "";
$database = "spk_bimbel";

$conn = mysqli_connect($host, $user, $password, $database);
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");
?>
