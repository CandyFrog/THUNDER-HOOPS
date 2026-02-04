<?php
// admin/dashboard.php
require_once '../midleware/cek_login.php';
require_once '../config/koneksi.php';

// Check if admin
if($_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php"); // Or unauthorized page? Login is fine.
    exit();
}

$page_title = "Admin Dashboard - Basketball Arcade";

// Connection is already established in config/koneksi.php

// Get statistics
$query = "SELECT COUNT(*) as total FROM match_data";
$result = $conn->query($query);
$total_games = $result->fetch_assoc()['total'];

$query = "SELECT COUNT(*) as total FROM users WHERE role = 'user'";
$result = $conn->query($query);
$total_users = $result->fetch_assoc()['total'];

// Statistik Pemenang (Asumsi data dari receive.php)
// Kita hitung jumlah kemenangan masing-masing
$query = "SELECT pemenang, COUNT(*) as total FROM match_data GROUP BY pemenang";
$result = $conn->query($query);
$wins = [];
while($row = $result->fetch_assoc()) {
    $wins[$row['pemenang']] = $row['total'];
}

// Mapping pemenang (sesuaikan dengan data yang dikirim receive.php)
// Jika receive.php mengirim 'Kiri'/'Kanan' atau 'Player 1'/'Player 2'
$player1_wins = isset($wins['Player 1']) ? $wins['Player 1'] : (isset($wins['Kiri']) ? $wins['Kiri'] : 0);
$player2_wins = isset($wins['Player 2']) ? $wins['Player 2'] : (isset($wins['Kanan']) ? $wins['Kanan'] : 0);
$total_draws = isset($wins['Draw']) ? $wins['Draw'] : (isset($wins['Seri']) ? $wins['Seri'] : 0);

// Get recent games
$query = "SELECT * FROM match_data ORDER BY id DESC LIMIT 5";
$result = $conn->query($query);
$recent_games = $result->fetch_all(MYSQLI_ASSOC);

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container-custom mt-4">
    <div class="mb-4">
        <h1 class="page-title">Admin Dashboard</h1>
        <p class="page-subtitle">Selamat datang kembali, <?php echo $_SESSION['full_name']; ?>! 👋</p>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="stats-card">
                <div class="stats-number"><?php echo $total_games; ?></div>
                <div class="stats-label">Total Games</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stats-card">
                <div class="stats-number"><?php echo $total_users; ?></div>
                <div class="stats-label">Total Users</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stats-card">
                <div class="stats-number"><?php echo $player1_wins; ?></div>
                <div class="stats-label">Player 1 Wins</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stats-card">
                <div class="stats-number"><?php echo $player2_wins; ?></div>
                <div class="stats-label">Player 2 Wins</div>
            </div>
        </div>
    </div>
    
    <!-- Recent Games -->
    <div class="card card-custom">
        <div class="card-header-custom">
            <i class="bi bi-clock-history"></i> Recent Games
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-custom mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Skor Kiri</th>
                            <th>Skor Kanan</th>
                            <th>Pemenang</th>
                            <th>Durasi</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($recent_games) > 0): ?>
                            <?php foreach($recent_games as $game): ?>
                            <tr>
                                <td>#<?php echo $game['id']; ?></td>
                                <td><strong><?php echo $game['skor_kiri']; ?></strong></td>
                                <td><strong><?php echo $game['skor_kanan']; ?></strong></td>
                                <td>
                                    <?php if($game['pemenang'] == 'Draw' || $game['pemenang'] == 'Seri'): ?>
                                        <span class="badge-draw">Seri</span>
                                    <?php else: ?>
                                        <span class="badge-winner"><?php echo $game['pemenang']; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $game['durasi']; ?>s</td>
                                <td>-</td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">Belum ada data game</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>