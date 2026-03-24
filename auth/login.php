<?php
// login.php
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

// Generate CSRF token jika belum ada
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';

// Brute force protection
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['login_last_attempt'] = time();
}

// Reset counter setelah 15 menit
if (time() - $_SESSION['login_last_attempt'] > 900) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['login_last_attempt'] = time();
}

$max_attempts = 10;
$locked_out = $_SESSION['login_attempts'] >= $max_attempts;

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi CSRF dulu
    $csrf_valid = isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
    if (!$csrf_valid) {
        $error = 'Request tidak valid. Silakan coba lagi.';
    } elseif ($locked_out) {
        $error = 'Terlalu banyak percobaan login. Coba lagi dalam 15 menit.';
    } else {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        
        if(empty($username) || empty($password)) {
            $error = 'Username dan password harus diisi!';
        } else {
            // Prepare statement
            $query = "SELECT * FROM users WHERE username = ? LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $_SESSION['login_attempts']++;
            $_SESSION['login_last_attempt'] = time();

            if($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                if(password_verify($password, $user['password'])) {
                    // Reset counter on success
                    $_SESSION['login_attempts'] = 0;
                    session_regenerate_id(true);

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['role'] = $user['role'];
                    
                    if($user['role'] == 'admin') {
                        header("Location: ../admin/dashboard.php");
                    } else {
                        header("Location: ../user/dashboard.php");
                    }
                    exit();
                } else {
                    // Generic error – jangan bocorkan apakah username atau password yang salah
                    $error = 'Username atau password salah!';
                }
            } else {
                $error = 'Username atau password salah!';
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
    <title>Login - Basketball Arcade</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../assets/logo.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="text-center mb-4">
                <div class="mb-3">
                    <img src="../assets/logo.png" alt="Logo" class="rounded-circle shadow auth-logo">
                </div>
                <h1 class="auth-title">Basketball Arcade</h1>
                <p class="auth-subtitle">Masuk ke akun Anda</p>
            </div>
            
            <?php if($error): ?>
            <div class="alert alert-danger alert-custom" role="alert">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <?php echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">'; ?>
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control form-control-custom" id="username" name="username" required autocomplete="username">
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="password-field-container">
                        <input type="password" class="form-control form-control-custom" id="password" name="password" required autocomplete="current-password">
                        <i class="bi bi-eye password-toggle"></i>
                    </div>
                </div>
                
                <?php if ($locked_out): ?>
                <div class="alert alert-danger alert-custom" role="alert">
                    <i class="bi bi-shield-lock me-2"></i>Akun sementara dikunci. Terlalu banyak percobaan login. Coba lagi dalam 15 menit.
                </div>
                <?php endif; ?>
                
                <button type="submit" class="btn btn-peach w-100 mb-3" <?php echo $locked_out ? 'disabled' : ''; ?>>Masuk</button>
                
                <div class="text-center">
                    <p class="mb-0">Belum punya akun? <a href="register.php" style="color: var(--primary-peach); font-weight: 600; text-decoration: none;">Daftar di sini</a></p>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js?v=1.2"></script>
</body>
</html>