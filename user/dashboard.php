<?php
require_once '../midleware/cek_login.php';
require_once '../config/koneksi.php';

$page_title = "Dashboard Pengguna - Basketball Arcade";

$query = "SELECT COUNT(*) as total FROM match_data";
$result = $conn->query($query);
$total_games = $result->fetch_assoc()['total'];

$query = "SELECT pemenang, COUNT(*) as total FROM match_data GROUP BY pemenang";
$result = $conn->query($query);
$wins = [];
while($row = $result->fetch_assoc()) {
    $wins[$row['pemenang']] = $row['total'];
}

$player1_wins = isset($wins['Player 1']) ? $wins['Player 1'] : (isset($wins['Kiri']) ? $wins['Kiri'] : 0);
$player2_wins = isset($wins['Player 2']) ? $wins['Player 2'] : (isset($wins['Kanan']) ? $wins['Kanan'] : 0);
$total_draws = isset($wins['Draw']) ? $wins['Draw'] : (isset($wins['Seri']) ? $wins['Seri'] : 0);

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$query = "SELECT COUNT(*) as total FROM match_data";
$result = $conn->query($query);
$total_records = $result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

$query = "SELECT * FROM match_data ORDER BY id DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
$games = $result->fetch_all(MYSQLI_ASSOC);

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container-custom mt-4">
    <div class="mb-4">
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Selamat datang, <?php echo $_SESSION['full_name']; ?>! 🎮</p>
    </div>
    
    <div class="row g-4 mb-4">
        <div class="col-6 col-md-6 col-lg-3">
            <div class="stats-card">
                <div class="stats-number"><?php echo $total_games; ?></div>
                <div class="stats-label">Total Permainan</div>
            </div>
        </div>
        <div class="col-6 col-md-6 col-lg-3">
            <div class="stats-card">
                <div class="stats-number"><?php echo $player1_wins; ?></div>
                <div class="stats-label">Kemenangan Player 1</div>
            </div>
        </div>
        <div class="col-6 col-md-6 col-lg-3">
            <div class="stats-card">
                <div class="stats-number"><?php echo $player2_wins; ?></div>
                <div class="stats-label">Kemenangan Player 2</div>
            </div>
        </div>
        <div class="col-6 col-md-6 col-lg-3">
            <div class="stats-card">
                <div class="stats-number"><?php echo $total_draws; ?></div>
                <div class="stats-label">Seri</div>
            </div>
        </div>
    </div>
    
    <div class="card card-custom">
        <div class="card-header-custom">
            <i class="bi bi-clock-history"></i> Riwayat Permainan
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-custom mb-0">
                    <thead>
                        <tr>
                            <th>ID Permainan</th>
                            <th>Skor Kiri</th>
                            <th>Skor Kanan</th>
                            <th>Pemenang</th>
                            <th>Durasi</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($games) > 0): ?>
                            <?php foreach($games as $game): ?>
                            <tr>
                                <td><strong>#<?php echo $game['id']; ?></strong></td>
                                <td>
                                    <span style="font-size: 1.2rem; font-weight: 600; color: var(--primary-peach);">
                                        <?php echo $game['skor_kiri']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="font-size: 1.2rem; font-weight: 600; color: var(--primary-peach);">
                                        <?php echo $game['skor_kanan']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if($game['pemenang'] == 'Draw' || $game['pemenang'] == 'Seri'): ?>
                                        <span class="badge-draw">Seri</span>
                                    <?php else: ?>
                                        <span class="badge-winner"><?php echo $game['pemenang']; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $game['durasi']; ?> detik</td>
                                <td><small class="text-muted"><?php echo date('d M Y, H:i', strtotime($game['created_at'])); ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="bi bi-inbox" style="font-size: 3rem; color: var(--secondary-peach);"></i>
                                    <p class="mt-2 mb-0">Belum ada riwayat permainan</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if($total_pages > 1): ?>
            <div class="p-3">
                <nav>
                    <ul class="pagination justify-content-center mb-0">
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>">Sebelumnya</a>
                        </li>
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>">Berikutnya</a>
                        </li>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>