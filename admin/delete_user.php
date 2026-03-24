<?php
// admin/delete_user.php
require_once '../midleware/cek_login.php';
require_once '../config/koneksi.php';

// Check if admin
if($_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// CSRF check – require POST to avoid CSRF via GET link
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token()) {
    $_SESSION['user_error'] = 'Aksi tidak diizinkan!';
    header("Location: users.php");
    exit();
}

if(isset($_POST['user_id'])) {
    $user_id = (int)$_POST['user_id'];
    
    // Prevent deleting own account
    if($user_id == $_SESSION['user_id']) {
        $_SESSION['user_error'] = 'Tidak bisa menghapus akun sendiri!';
        header("Location: users.php");
        exit();
    }
    
    $query = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    
    if($stmt->execute()) {
        $_SESSION['user_success'] = 'User berhasil dihapus!';
    } else {
        $_SESSION['user_error'] = 'Gagal menghapus user!';
    }
}

header("Location: users.php");
exit();
?>