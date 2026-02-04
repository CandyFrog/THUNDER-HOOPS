<?php
// admin/delete_user.php
require_once '../midleware/cek_login.php';
require_once '../config/koneksi.php';

// Check if admin
if($_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
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
    
    $query = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    
    if($stmt->execute()) {
        $_SESSION['delete_success'] = 'User berhasil dihapus!';
    } else {
        $_SESSION['delete_error'] = 'Gagal menghapus user!';
    }
}

header("Location: users.php");
exit();
?>