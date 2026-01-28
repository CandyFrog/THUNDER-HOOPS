<?php
// admin/dashboard.php
session_start();
require_once '../config/database.php';

// Check if admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$page_title = "Admin Dashboard - Basketball Arcade";

$database = new Database();
$db = $database->getConnection();

// Get statistics
$query = "SELECT COUNT(*) as total FROM games";
$stmt = $db->prepare($query);
$stmt->execute();
$total_games = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM users WHERE role = 'user'";
$stmt = $db->prepare($query);
$stmt->execute();
$total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM games WHERE winner = 'Player 1'";
$stmt = $db->prepare($query);
$stmt->execute();
$player1_wins = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM games WHERE winner = 'Player 2'";
$stmt = $db->prepare($query);
$stmt->execute();
$player2_wins = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM games WHERE winner = 'Draw'";
$stmt = $db->prepare($query);
$stmt->execute();
$total_draws = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get recent games
$query = "SELECT * FROM games ORDER BY played_at DESC LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$recent_games = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
                            <th>Player 1 Score</th>
                            <th>Player 2 Score</th>
                            <th>Winner</th>
                            <th>Duration</th>
                            <th>Played At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($recent_games) > 0): ?>
                            <?php foreach($recent_games as $game): ?>
                            <tr>
                                <td>#<?php echo $game['id']; ?></td>
                                <td><strong><?php echo $game['player1_score']; ?></strong></td>
                                <td><strong><?php echo $game['player2_score']; ?></strong></td>
                                <td>
                                    <?php if($game['winner'] == 'Draw'): ?>
                                        <span class="badge-draw">Draw</span>
                                    <?php else: ?>
                                        <span class="badge-winner"><?php echo $game['winner']; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $game['game_duration']; ?>s</td>
                                <td><?php echo date('d M Y, H:i', strtotime($game['played_at'])); ?></td>
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