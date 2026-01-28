<?php
// admin/delete_user.php
session_start();
require_once '../config/database.php';

// Check if admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if(isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    
    // Prevent deleting own account
    if($user_id == $_SESSION['user_id']) {
        $_SESSION['delete_error'] = 'Tidak bisa menghapus akun sendiri!';
        header("Location: users.php");
        exit();
    }
    
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "DELETE FROM users WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $user_id);
    
    if($stmt->execute()) {
        $_SESSION['delete_success'] = 'User berhasil dihapus!';
    } else {
        $_SESSION['delete_error'] = 'Gagal menghapus user!';
    }
}

header("Location: users.php");
exit();
?>