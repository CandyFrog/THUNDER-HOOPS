<?php
// includes/navbar.php
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Tentukan base path untuk link
$base_path = ($current_dir == 'admin' || $current_dir == 'user' || $current_dir == 'profil') ? '../' : '';
$admin_path = ($current_dir == 'admin') ? '' : $base_path . 'admin/';
$user_path = ($current_dir == 'user') ? '' : $base_path . 'user/';
$profil_path = ($current_dir == 'profil') ? '' : $base_path . 'profil/';
$auth_path = $base_path . 'auth/';

// Fetch user data for profile picture
$user_id_nav = $_SESSION['user_id'];
$query_nav = "SELECT foto_profil, full_name FROM users WHERE id = ?";
$stmt_nav = $conn->prepare($query_nav);
$stmt_nav->bind_param("i", $user_id_nav);
$stmt_nav->execute();
$res_nav = $stmt_nav->get_result()->fetch_assoc();
$nav_photo = !empty($res_nav['foto_profil']) ? $base_path . 'assets/foto_profil/' . $res_nav['foto_profil'] : 'https://ui-avatars.com/api/?name=' . urlencode($res_nav['full_name']) . '&background=E8A796&color=fff';
?>
<nav class="navbar navbar-expand-lg navbar-custom sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?php echo $_SESSION['role'] == 'admin' ? $admin_path . 'dashboard.php' : $user_path . 'dashboard.php'; ?>">
            <img src="<?php echo $base_path; ?>assets/logo.png" alt="Logo" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover; border: 2px solid var(--primary-peach);">
            <span>Basketball Arcade</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <?php if($_SESSION['role'] == 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'dashboard.php' && $current_dir == 'admin' ? 'active' : ''; ?>" href="<?php echo $admin_path; ?>dashboard.php">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'riwayat.php' ? 'active' : ''; ?>" href="<?php echo $admin_path; ?>riwayat.php">
                        <i class="bi bi-controller"></i> Games
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'users.php' ? 'active' : ''; ?>" href="<?php echo $admin_path; ?>users.php">
                        <i class="bi bi-people"></i> Users
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'pengaturan.php' ? 'active' : ''; ?>" href="<?php echo $admin_path; ?>pengaturan.php">
                        <i class="bi bi-gear"></i> Pengaturan
                    </a>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'dashboard.php' && $current_dir == 'user' ? 'active' : ''; ?>" href="<?php echo $user_path; ?>dashboard.php">
                        <i class="bi bi-house-door"></i> Dashboard
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item dropdown ms-lg-3">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
                        <img src="<?php echo $nav_photo; ?>" alt="Avatar" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover; border: 2px solid var(--primary-peach);">
                        <span><?php echo explode(' ', $res_nav['full_name'])[0]; ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="border-radius: 12px; min-width: 200px;">
                        <li class="px-3 py-2 border-bottom mb-2 bg-light rounded-top">
                            <small class="text-muted d-block">Login sebagai:</small>
                            <span class="fw-bold text-dark"><?php echo $res_nav['full_name']; ?></span>
                        </li>
                        <li><a class="dropdown-item py-2" href="<?php echo $profil_path; ?>index.php"><i class="bi bi-person me-2"></i> Profil Akun</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger py-2" href="<?php echo $auth_path; ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>