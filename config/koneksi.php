<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "basketball_arcade";


$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
