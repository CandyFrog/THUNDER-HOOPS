<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Enforce HTTPS
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    $redirect = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $redirect");
    exit();
}

if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php?pesan=belum_login");
    exit();
}
    
?>