<?php
// admin/delete_game.php
require_once '../midleware/cek_login.php';
require_once '../config/koneksi.php';

// Check if admin
if($_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

if(isset($_GET['id'])) {
    $game_id = (int)$_GET['id'];
    
    $query = "DELETE FROM match_data WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $game_id);
    
    $stmt->execute();
}

header("Location: games.php");
exit();
?>