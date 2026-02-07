<?php
// api/clear_command.php
require_once __DIR__ . '/../config/koneksi.php';

$query = "UPDATE settings SET value = 'idle' WHERE name = 'game_command'";
if ($conn->query($query)) {
    echo "IDLE_OK";
} else {
    echo "IDLE_FAILED";
}
?>
