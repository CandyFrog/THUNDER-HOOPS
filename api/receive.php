<?php
require_once __DIR__ . '/../config/koneksi.php';

// Validate & sanitize inputs
$skor_kiri  = isset($_GET['skor_kiri'])  ? (int)$_GET['skor_kiri']  : 0;
$skor_kanan = isset($_GET['skor_kanan']) ? (int)$_GET['skor_kanan'] : 0;
$durasi     = isset($_GET['durasi'])     ? (int)$_GET['durasi']     : 0;
$pemenang   = isset($_GET['pemenang'])   ? trim($_GET['pemenang'])   : '-';

// Whitelist pemenang values
$allowed_pemenang = ['Kiri', 'Kanan', 'Seri', 'KIRI', 'KANAN', 'SERI', '-'];
if (!in_array($pemenang, $allowed_pemenang)) {
    $pemenang = '-';
}

$query = "INSERT INTO match_data (skor_kiri, skor_kanan, durasi, pemenang) VALUES (?, ?, ?, ?)";
$stmt  = $conn->prepare($query);
$stmt->bind_param("iiis", $skor_kiri, $skor_kanan, $durasi, $pemenang);

if ($stmt->execute()) {

    echo "DATA_OK";
} else {
    echo "DATA_GAGAL";
}
?>
