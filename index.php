<?php
session_start();

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
    <title>Sistem IoT Basketball Arcade</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="icon" type="image/png" href="assets/logo.png">
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="auth-container">
        <div class="container my-auto">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="text-center text-lg-start">
                        <h1 class="hero-title">
                            🏀 Basket<br>
                            <span style="color: var(--primary-peach);">Sistem Arcade</span>
                        </h1>
                        <p class="hero-subtitle">
                            Sistem IoT untuk mengelola permainan basket arcade dengan pemantauan real-time dan statistik lengkap.
                        </p>
                        <div class="d-flex gap-3 justify-content-center justify-content-lg-start">
                            <a href="auth/login.php" class="btn btn-peach btn-lg">
                                <i class="bi bi-box-arrow-in-right"></i> Masuk
                            </a>
                            <a href="auth/register.php" class="btn btn-outline-peach btn-lg">
                                <i class="bi bi-person-plus"></i> Daftar
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="text-center">
                        <div class="track-card">
                            <img src="assets/logo.png" alt="Logo" class="rounded-circle shadow mb-3 track-logo">
                            <h3 style="color: var(--text-dark); margin-top: 0.5rem; font-weight: 700;">
                                Pantau Permainan Anda
                            </h3>
                            <p style="color: var(--text-dark); margin-bottom: 0;">
                                Pantau statistik, skor, dan riwayat permainan dengan mudah
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4 g-3">
                <div class="col-md-4">
                    <div class="stats-card">
                        <i class="bi bi-speedometer2 feature-icon"></i>
                        <h5 class="feature-title">Pemantauan Real-time</h5>
                        <p class="feature-desc">Pantau permainan secara langsung</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card">
                        <i class="bi bi-graph-up feature-icon"></i>
                        <h5 class="feature-title">Statistik Lengkap</h5>
                        <p class="feature-desc">Analisis data permainan secara detail</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card">
                        <i class="bi bi-shield-check feature-icon"></i>
                        <h5 class="feature-title">Aman & Terpercaya</h5>
                        <p class="feature-desc">Data aman dan terpercaya</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <footer class="text-center py-4">
        <p class="mb-0" style="color: var(--text-dark);">
            &copy; <?php echo date('Y'); ?> Sistem IoT Basketball Arcade. Dibuat dengan <i class="bi bi-heart-fill" style="color: var(--primary-peach);"></i>
        </p>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>