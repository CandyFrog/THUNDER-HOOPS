<?php
require_once '../midleware/cek_login.php';
require_once '../config/koneksi.php';

if($_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$page_title = "Dashboard Admin - Basketball Arcade";

$query = "SELECT COUNT(*) as total FROM match_data";
$result = $conn->query($query);
$total_games = $result->fetch_assoc()['total'];

$query = "SELECT COUNT(*) as total FROM users WHERE role = 'user'";
$result = $conn->query($query);
$total_users = $result->fetch_assoc()['total'];

$query = "SELECT pemenang, COUNT(*) as total FROM match_data GROUP BY pemenang";
$result = $conn->query($query);
$wins = [];
while($row = $result->fetch_assoc()) {
    $wins[$row['pemenang']] = $row['total'];
}

$player1_wins = 0;
$player2_wins = 0;
$total_draws = 0;

foreach ($wins as $key => $count) {
    if (strpos(strtoupper($key), 'PLAYER 1') !== false || strtoupper($key) == 'KIRI') {
        $player1_wins += $count;
    } elseif (strpos(strtoupper($key), 'PLAYER 2') !== false || strtoupper($key) == 'KANAN') {
        $player2_wins += $count;
    } elseif (strpos(strtoupper($key), 'DRAW') !== false || strtoupper($key) == 'SERI') {
        $total_draws += $count;
    }
}

$query = "SELECT * FROM match_data ORDER BY id DESC LIMIT 5";
$result = $conn->query($query);
$recent_games = $result->fetch_all(MYSQLI_ASSOC);

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container-custom mt-4">
    <div class="mb-4">
        <h1 class="page-title">Dashboard Admin</h1>
        <p class="page-subtitle">Selamat datang kembali, <?php echo $_SESSION['full_name']; ?>! 👋</p>
    </div>
    
    <div id="stats-container">
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-5 g-4 mb-4">
            <div class="col">
                <div class="stats-card h-100">
                    <div class="stats-number" id="stat-total-games"><?php echo $total_games; ?></div>
                    <div class="stats-label">Total Permainan</div>
                </div>
            </div>
            <div class="col">
                <div class="stats-card h-100">
                    <div class="stats-number" id="stat-total-users"><?php echo $total_users; ?></div>
                    <div class="stats-label">Total Pengguna</div>
                </div>
            </div>
            <div class="col">
                <div class="stats-card h-100">
                    <div class="stats-number" id="stat-player1-wins"><?php echo $player1_wins; ?></div>
                    <div class="stats-label">Kemenangan Player 1</div>
                </div>
            </div>
            <div class="col">
                <div class="stats-card h-100">
                    <div class="stats-number" id="stat-total-draws"><?php echo $total_draws; ?></div>
                    <div class="stats-label">Draws</div>
                </div>
            </div>
            <div class="col">
                <div class="stats-card h-100">
                    <div class="stats-number" id="stat-player2-wins"><?php echo $player2_wins; ?></div>
                    <div class="stats-label">Kemenangan Player 2</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card card-custom">
        <div class="card-header-custom d-flex justify-content-between align-items-center">
            <span><i class="bi bi-clock-history"></i> Permainan Terkini</span>
            <span class="badge bg-soft-peach text-peach" id="live-indicator">
                <span class="spinner-grow spinner-grow-sm me-1" role="status"></span> LIVE
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-custom mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th style="width: 15%; text-align: center;">Skor Kiri</th>
                            <th style="width: 15%; text-align: center;">Skor Kanan</th>
                            <th>Pemenang</th>
                            <th>Durasi</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody id="recent-games-table">
                        <?php if(count($recent_games) > 0): ?>
                            <?php foreach($recent_games as $game): ?>
                            <tr>
                                <td>#<?php echo $game['id']; ?></td>
                                <td class="text-center"><strong><?php echo $game['skor_kiri']; ?></strong></td>
                                <td class="text-center"><strong><?php echo $game['skor_kanan']; ?></strong></td>
                                <td>
                                    <?php if($game['pemenang'] == 'Draw' || $game['pemenang'] == 'Seri'): ?>
                                        <span class="badge-draw">Seri</span>
                                    <?php else: ?>
                                        <span class="badge-winner"><?php echo $game['pemenang']; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $game['durasi']; ?>s</td>
                                <td><?php echo date('d M Y H:i', strtotime($game['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">Belum ada data permainan</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function refreshDashboard() {
    fetch('../api/dashboard_stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('stat-total-games').innerText = data.stats.total_games;
                document.getElementById('stat-total-users').innerText = data.stats.total_users;
                document.getElementById('stat-player1-wins').innerText = data.stats.player1_wins;
                document.getElementById('stat-player2-wins').innerText = data.stats.player2_wins;
                document.getElementById('stat-total-draws').innerText = data.stats.total_draws;

                const tbody = document.getElementById('recent-games-table');
                let tableHtml = '';
                
                if (data.recent_games.length > 0) {
                    data.recent_games.forEach(game => {
                        const badgeClass = (game.pemenang === 'Draw' || game.pemenang === 'Seri') ? 'badge-draw' : 'badge-winner';
                        const badgeText = (game.pemenang === 'Draw' || game.pemenang === 'Seri') ? 'Seri' : game.pemenang;
                        
                        const date = new Date(game.created_at);
                        const formattedDate = date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) + ' ' + 
                                            date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

                        tableHtml += `
                            <tr>
                                <td>#${game.id}</td>
                                <td class="text-center"><strong>${game.skor_kiri}</strong></td>
                                <td class="text-center"><strong>${game.skor_kanan}</strong></td>
                                <td><span class="${badgeClass}">${badgeText}</span></td>
                                <td>${game.durasi}s</td>
                                <td>${formattedDate}</td>
                            </tr>
                        `;
                    });
                } else {
                    tableHtml = '<tr><td colspan="6" class="text-center py-4">Belum ada data permainan</td></tr>';
                }
                
                if (tbody.innerHTML !== tableHtml) {
                    tbody.innerHTML = tableHtml;
                }
            }
        })
        .catch(error => console.error('Gagal memperbarui dashboard:', error));
}

setInterval(refreshDashboard, 5000);
</script>

<style>
.bg-soft-peach { background-color: rgba(255, 154, 158, 0.1); }
</style>

<?php include '../includes/footer.php'; ?>