<?php
// generate_password.php
$password = "admin123";
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Password: $password<br>";
echo "Hash: $hash<br>";
echo "<br>Copy hash di atas, lalu jalankan query SQL ini di phpMyAdmin:<br>";
echo "<code>UPDATE users SET password = '$hash' WHERE username = 'admin';</code>";
?>