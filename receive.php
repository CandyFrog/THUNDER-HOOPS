<?php
require_once __DIR__ . '/config/koneksi.php';

$skor_kiri  = $_GET['skor_kiri'] ?? 0;
$skor_kanan = $_GET['skor_kanan'] ?? 0;
$durasi     = $_GET['durasi'] ?? 0;
$pemenang   = $_GET['pemenang'] ?? '-';

$query = "INSERT INTO match_data 
          (skor_kiri, skor_kanan, durasi, pemenang)
          VALUES 
          ('$skor_kiri', '$skor_kanan', '$durasi', '$pemenang')";

if (mysqli_query($conn, $query)) {
    echo "DATA_OK";
} else {
    echo "DATA_GAGAL";
}
?>
