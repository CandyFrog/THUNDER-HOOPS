<?php
// api/get_settings.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/koneksi.php';

$query = "SELECT value FROM settings WHERE name = 'match_duration'";
$result = $conn->query($query);

if ($result && $row = $result->fetch_assoc()) {
    echo json_encode([
        'status' => 'success',
        'match_duration' => (int)$row['value']
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Setting not found',
        'default_duration' => 60
    ]);
}
?>
