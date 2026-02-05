<?php
require_once 'config/koneksi.php';

$result = $conn->query("SHOW TRIGGERS");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "No triggers found.\n";
}
?>
