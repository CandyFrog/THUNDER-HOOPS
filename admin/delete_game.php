<?php
// admin/delete_game.php
session_start();
require_once '../config/database.php';

// Check if admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if(isset($_GET['id'])) {
    $game_id = (int)$_GET['id'];
    
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "DELETE FROM games WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $game_id);
    
    if($stmt->execute()) {
        $_SESSION['delete_success'] = 'Game berhasil dihapus!';
    } else {
        $_SESSION['delete_error'] = 'Gagal menghapus game!';
    }
}

header("Location: games.php");
exit();
?>