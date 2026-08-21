<?php
ob_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/koneksi.php';
ob_clean();

$query = "SELECT name, value FROM settings";
$result = $conn->query($query);
$settings = [];
if ($result) {
    while($row = $result->fetch_assoc()) {
        $settings[$row['name']] = $row['value'];
    }
}

$wifi_sync_pending = isset($settings['wifi_sync_pending']) ? (int)$settings['wifi_sync_pending'] : 0;

// Jika Arduino melaporkan WiFi aktif (dan tidak ada pending sync dari web admin)
if ($wifi_sync_pending == 0 && isset($_GET['active_ssid']) && !empty($_GET['active_ssid'])) {
    $act_ssid = trim($_GET['active_ssid']);
    $act_pass = trim($_GET['active_pass'] ?? '');

    $stmt1 = $conn->prepare("INSERT INTO settings (name, value) VALUES ('wifi_ssid', ?) ON DUPLICATE KEY UPDATE value = ?");
    $stmt1->bind_param("ss", $act_ssid, $act_ssid);
    $stmt1->execute();

    $stmt2 = $conn->prepare("INSERT INTO settings (name, value) VALUES ('wifi_password', ?) ON DUPLICATE KEY UPDATE value = ?");
    $stmt2->bind_param("ss", $act_pass, $act_pass);
    $stmt2->execute();

    $settings['wifi_ssid'] = $act_ssid;
    $settings['wifi_password'] = $act_pass;
}

// Jika wifi_sync_pending diset 1 oleh admin web, setelah dibaca Arduino (dengan ack=1), kita reset ke 0
if ($wifi_sync_pending == 1 && isset($_GET['ack']) && $_GET['ack'] == '1') {
    $conn->query("UPDATE settings SET value = '0' WHERE name = 'wifi_sync_pending'");
}

$match_duration = isset($settings['match_duration']) ? (int)$settings['match_duration'] : 60;
$game_command   = isset($settings['game_command']) ? $settings['game_command'] : 'idle';
$wifi_ssid      = isset($settings['wifi_ssid']) ? $settings['wifi_ssid'] : '';
$wifi_password  = isset($settings['wifi_password']) ? $settings['wifi_password'] : '';

if (isset($_GET['ack']) && $_GET['ack'] == '1') {
    $conn->query("UPDATE settings SET value = 'idle' WHERE name = 'game_command'");
}

echo json_encode([
    'status'            => 'success',
    'match_duration'    => $match_duration,
    'game_command'      => $game_command,
    'wifi_ssid'         => $wifi_ssid,
    'wifi_password'     => $wifi_password,
    'wifi_sync_pending' => $wifi_sync_pending
]);
?>
