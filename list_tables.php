<?php
require_once 'config/koneksi.php';

echo "Tables in " . $db . ":\n";
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch_array()) {
    echo "- " . $row[0] . "\n";
    
    // Show structure for each table
    $table = $row[0];
    echo "  Structure for $table:\n";
    $struct = $conn->query("DESCRIBE $table");
    while ($col = $struct->fetch_assoc()) {
        echo "    * " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
}
?>
