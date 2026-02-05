<?php
// Tambahkan ini di paling atas untuk log data yang masuk ke file text
// Jadi kamu bisa cek file 'debug.txt' kalau gagal terus
$debug_data = file_get_contents('php://input') . " | POST: " . json_encode($_POST);
file_put_contents('debug_log.txt', $debug_data . PHP_EOL, FILE_APPEND);

header('Content-Type: application/json');
require_once '../config/koneksi.php';

// Allow converting POST/JSON data
$input = json_decode(file_get_contents('php://input'), true);

// Get data (support JSON or Form Data)
$skor_kiri  = isset($input['skor_kiri']) ? $input['skor_kiri'] : (isset($_POST['skor_kiri']) ? $_POST['skor_kiri'] : null);
$skor_kanan = isset($input['skor_kanan']) ? $input['skor_kanan'] : (isset($_POST['skor_kanan']) ? $_POST['skor_kanan'] : null);
$durasi     = isset($input['durasi']) ? $input['durasi'] : (isset($_POST['durasi']) ? $_POST['durasi'] : null);
$pemenang   = isset($input['pemenang']) ? $input['pemenang'] : (isset($_POST['pemenang']) ? $_POST['pemenang'] : null);

// Validate
if($skor_kiri === null || $skor_kanan === null || $durasi === null || $pemenang === null) {
    echo json_encode(['status' => 'error', 'message' => 'Data incomplete']);
    exit();
}

// Convert pemenang using standard logic if needed, OR trust input.
// Assuming Arduino sends 'Kiri', 'Kanan', 'Seri' or we map it.
// Let's trust the input for now but allow mapping if raw scores provided but winner missing?
// Requirement says Arduino sends data to MySQL, so we just store it.

$query = "INSERT INTO match_data (skor_kiri, skor_kanan, durasi, pemenang) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("iiis", $skor_kiri, $skor_kanan, $durasi, $pemenang);

if($stmt->execute()) {
    echo json_encode([
        'status' => 'success', 
        'message' => 'Data saved successfully',
        'data' => [
            'id' => $conn->insert_id,
            'skor_kiri' => $skor_kiri,
            'skor_kanan' => $skor_kanan,
            'durasi' => $durasi,
            'pemenang' => $pemenang
        ]
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to save data']);
}
?>
