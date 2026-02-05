<?php
ob_start();
// api/get_settings.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/koneksi.php';
ob_clean();

// Fetch all settings
$query = "SELECT name, value FROM settings";
$result = $conn->query($query);
$settings = [];
if ($result) {
    while($row = $result->fetch_assoc()) {
        $settings[$row['name']] = $row['value'];
    }
}

$match_duration = isset($settings['match_duration']) ? (int)$settings['match_duration'] : 60;
$game_command   = isset($settings['game_command']) ? $settings['game_command'] : 'idle';

// Optimasi: Jika Arduino menyertakan ?ack=1, langsung hapus command (handshake otomatis)
if (isset($_GET['ack']) && $_GET['ack'] == '1') {
    $conn->query("UPDATE settings SET value = 'idle' WHERE name = 'game_command'");
}

echo json_encode([
    'status'         => 'success',
    'match_duration' => $match_duration,
    'game_command'   => $game_command
]);
?>
