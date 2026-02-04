<?php
// admin/delete_game.php
session_start();
require_once '../config/database.php';

// Check if admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

if(isset($_GET['id'])) {
    $game_id = (int)$_GET['id'];
    
    $query = "DELETE FROM games WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $game_id);
    
    if($stmt->execute()) {
        $_SESSION['delete_success'] = 'Game berhasil dihapus!';
    } else {
        $_SESSION['delete_error'] = 'Gagal menghapus game!';
    }
}

header("Location: games.php");
exit();
?>