<?php
// register.php
session_start();
require_once '../config/koneksi.php';

// Redirect jika sudah login
if(isset($_SESSION['user_id'])) {
    if($_SESSION['role'] == 'admin') {
        header("Location: ../admin/dashboard.php");
    } else {
        header("Location: ../user/dashboard.php");
    }
    exit();
}

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name']);
    
    // Validasi
    if(empty($username) || empty($password) || empty($confirm_password) || empty($full_name)) {
        $error = 'Semua field harus diisi!';
    } elseif(strlen($username) < 4) {
        $error = 'Username minimal 4 karakter!';
    } elseif(strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } elseif($password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak cocok!';
    } else {
        // Cek username sudah ada atau belum
        $query = "SELECT id FROM users WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
            $error = 'Username sudah digunakan!';
        } else {
            // Insert user baru
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, 'user')";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sss", $username, $hashed_password, $full_name);
            
            if($stmt->execute()) {
                // Auto login
                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['username'] = $username;
                $_SESSION['full_name'] = $full_name;
                $_SESSION['role'] = 'user';
                
                header("Location: ../user/dashboard.php");
                exit();
            } else {
                $error = 'Terjadi kesalahan. Silakan coba lagi.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Basketball Arcade</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../assets/logo.png">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="text-center mb-4">
                <div class="mb-3">
                    <img src="../assets/logo.png" alt="Logo" class="rounded-circle shadow" style="width: 80px; height: 80px; object-fit: cover; border: 3px solid var(--primary-peach);">
                </div>
                <h1 class="auth-title">Basketball Arcade</h1>
                <p class="auth-subtitle">Buat akun baru</p>
            </div>
            
            <?php if($error): ?>
            <div class="alert alert-danger alert-custom" role="alert">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <?php if($success): ?>
            <div class="alert alert-success alert-custom" role="alert">
                <?php echo $success; ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="full_name" class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control form-control-custom" id="full_name" name="full_name" required value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                </div>
                
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control form-control-custom" id="username" name="username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    <small class="text-muted">Minimal 4 karakter</small>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control form-control-custom" id="password" name="password" required>
                    <small class="text-muted">Minimal 6 karakter</small>
                </div>
                
                <div class="mb-4">
                    <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                    <input type="password" class="form-control form-control-custom" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn btn-peach w-100 mb-3">Daftar</button>
                
                <div class="text-center">
                    <p class="mb-0">Sudah punya akun? <a href="login.php" style="color: var(--primary-peach); font-weight: 600; text-decoration: none;">Login di sini</a></p>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>