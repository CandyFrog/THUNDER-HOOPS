<?php
// admin/delete_game.php
session_start();
require_once '../config/koneksi.php';

// Check if admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

if(isset($_GET['id'])) {
    $game_id = (int)$_GET['id'];
    
    $query = "DELETE FROM match_data WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $game_id);
    
    if($stmt->execute()) {
        $_SESSION['delete_success'] = 'Data pertandingan berhasil dihapus!';
    } else {
        $_SESSION['delete_error'] = 'Gagal menghapus data pertandingan!';
    }
}

header("Location: games.php");
exit();
?>