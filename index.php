<?php
// index.php
session_start();

// Redirect jika sudah login
if(isset($_SESSION['user_id'])) {
    if($_SESSION['role'] == 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: user/dashboard.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Basketball Arcade - IoT System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="icon" type="image/png" href="assets/logo.png">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Hero Section -->
    <div class="auth-container">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <div class="text-center text-lg-start">
                        <h1 style="font-size: 3.5rem; font-weight: 800; color: var(--text-dark); line-height: 1.2;">
                            🏀 Basketball<br>
                            <span style="color: var(--primary-peach);">Arcade System</span>
                        </h1>
                        <p style="font-size: 1.2rem; color: var(--text-dark); margin-top: 1.5rem; margin-bottom: 2rem;">
                            Sistem IoT untuk mengelola permainan basketball arcade dengan monitoring real-time dan statistik lengkap.
                        </p>
                        <div class="d-flex gap-3 justify-content-center justify-content-lg-start">
                            <a href="auth/login.php" class="btn btn-peach btn-lg">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </a>
                            <a href="auth/register.php" class="btn btn-outline-peach btn-lg">
                                <i class="bi bi-person-plus"></i> Register
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="text-center">
                        <div style="background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border-radius: 30px; padding: 3rem; box-shadow: 0 20px 60px rgba(232, 167, 150, 0.3);">
                            <img src="assets/logo.png" alt="Logo" class="rounded-circle shadow mb-4" style="width: 150px; height: 150px; object-fit: cover; border: 5px solid var(--primary-peach);">
                            <h3 style="color: var(--text-dark); margin-top: 1rem; font-weight: 700;">
                                Track Your Games
                            </h3>
                            <p style="color: var(--text-dark);">
                                Monitor statistik, score, dan history permainan dengan mudah
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Features -->
            <div class="row mt-5 g-4">
                <div class="col-md-4">
                    <div class="stats-card">
                        <i class="bi bi-speedometer2" style="font-size: 3rem; color: var(--primary-peach);"></i>
                        <h5 class="mt-3" style="color: var(--text-dark); font-weight: 600;">Real-time Monitoring</h5>
                        <p style="color: var(--text-dark); margin: 0;">Monitor permainan secara langsung</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card">
                        <i class="bi bi-graph-up" style="font-size: 3rem; color: var(--primary-peach);"></i>
                        <h5 class="mt-3" style="color: var(--text-dark); font-weight: 600;">Statistik Lengkap</h5>
                        <p style="color: var(--text-dark); margin: 0;">Analisis data permainan detail</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card">
                        <i class="bi bi-shield-check" style="font-size: 3rem; color: var(--primary-peach);"></i>
                        <h5 class="mt-3" style="color: var(--text-dark); font-weight: 600;">Secure & Reliable</h5>
                        <p style="color: var(--text-dark); margin: 0;">Data aman dan terpercaya</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <footer class="text-center py-4">
        <p class="mb-0" style="color: var(--text-dark);">
            &copy; <?php echo date('Y'); ?> Basketball Arcade IoT System. Made with <i class="bi bi-heart-fill" style="color: var(--primary-peach);"></i>
        </p>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>