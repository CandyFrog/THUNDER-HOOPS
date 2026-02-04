<?php
// user/dashboard.php
session_start();
require_once '../config/koneksi.php';

// Check if user
if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$page_title = "User Dashboard - Basketball Arcade";

// Database connection is already established in config/koneksi.php

// Get statistics
$query = "SELECT COUNT(*) as total FROM games";
$result = $conn->query($query);
$total_games = $result->fetch_assoc()['total'];

$query = "SELECT COUNT(*) as total FROM games WHERE winner = 'Player 1'";
$result = $conn->query($query);
$player1_wins = $result->fetch_assoc()['total'];

$query = "SELECT COUNT(*) as total FROM games WHERE winner = 'Player 2'";
$result = $conn->query($query);
$player2_wins = $result->fetch_assoc()['total'];

$query = "SELECT COUNT(*) as total FROM games WHERE winner = 'Draw'";
$result = $conn->query($query);
$total_draws = $result->fetch_assoc()['total'];

// Get all games with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$query = "SELECT COUNT(*) as total FROM games";
$result = $conn->query($query);
$total_records = $result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

$query = "SELECT * FROM games ORDER BY played_at DESC LIMIT ? OFFSET ?";
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
    
    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="stats-card">
                <div class="stats-number"><?php echo $total_games; ?></div>
                <div class="stats-label">Total Games</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stats-card">
                <div class="stats-number"><?php echo $player1_wins; ?></div>
                <div class="stats-label">Player 1 Wins</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stats-card">
                <div class="stats-number"><?php echo $player2_wins; ?></div>
                <div class="stats-label">Player 2 Wins</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stats-card">
                <div class="stats-number"><?php echo $total_draws; ?></div>
                <div class="stats-label">Draws</div>
            </div>
        </div>
    </div>
    
    <!-- Game History -->
    <div class="card card-custom">
        <div class="card-header-custom">
            <i class="bi bi-clock-history"></i> Game History
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-custom mb-0">
                    <thead>
                        <tr>
                            <th>Game ID</th>
                            <th>Player 1</th>
                            <th>Player 2</th>
                            <th>Winner</th>
                            <th>Duration</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($games) > 0): ?>
                            <?php foreach($games as $game): ?>
                            <tr>
                                <td><strong>#<?php echo $game['id']; ?></strong></td>
                                <td>
                                    <span style="font-size: 1.2rem; font-weight: 600; color: var(--primary-peach);">
                                        <?php echo $game['player1_score']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="font-size: 1.2rem; font-weight: 600; color: var(--primary-peach);">
                                        <?php echo $game['player2_score']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if($game['winner'] == 'Draw'): ?>
                                        <span class="badge-draw">Draw</span>
                                    <?php else: ?>
                                        <span class="badge-winner"><?php echo $game['winner']; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $game['game_duration']; ?> detik</td>
                                <td><?php echo date('d M Y, H:i', strtotime($game['played_at'])); ?></td>
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
            
            <!-- Pagination -->
            <?php if($total_pages > 1): ?>
            <div class="p-3">
                <nav>
                    <ul class="pagination justify-content-center mb-0">
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                        </li>
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>