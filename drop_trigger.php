<?php
require_once 'config/koneksi.php';

$sql = "DROP TRIGGER IF EXISTS sync_to_games";
if ($conn->query($sql) === TRUE) {
    echo "Trigger sync_to_games dropped successfully.\n";
} else {
    echo "Error dropping trigger: " . $conn->error . "\n";
}
?>
