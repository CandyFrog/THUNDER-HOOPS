<?php
require_once '../midleware/cek_login.php';
require_once '../config/koneksi.php';

if($_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token()) {
    $_SESSION['user_error'] = 'Aksi tidak diizinkan!';
    header("Location: users.php");
    exit();
}

if(isset($_POST['user_id'])) {
    $user_id = (int)$_POST['user_id'];
    
    if($user_id == $_SESSION['user_id']) {
        $_SESSION['user_error'] = 'Tidak bisa menghapus akun sendiri!';
        header("Location: users.php");
        exit();
    }
    
    $query = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    
    if($stmt->execute()) {
        $_SESSION['user_success'] = 'Pengguna berhasil dihapus!';
    } else {
        $_SESSION['user_error'] = 'Gagal menghapus pengguna!';
    }
}

header("Location: users.php");
exit();
?>