<?php
// api/dashboard_stats.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/koneksi.php';

// Check session (optional but recommended for security)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

// 1. Get total games
$query = "SELECT COUNT(*) as total FROM match_data";
$result = $conn->query($query);
$total_games = (int)$result->fetch_assoc()['total'];

// 2. Get total users
$query = "SELECT COUNT(*) as total FROM users WHERE role = 'user'";
$result = $conn->query($query);
$total_users = (int)$result->fetch_assoc()['total'];

// 3. Stats Wins
$query = "SELECT pemenang, COUNT(*) as total FROM match_data GROUP BY pemenang";
$result = $conn->query($query);
$wins = [];
while($row = $result->fetch_assoc()) {
    $wins[$row['pemenang']] = (int)$row['total'];
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

// 4. Recent Games
$query = "SELECT * FROM match_data ORDER BY id DESC LIMIT 5";
$result = $conn->query($query);
$recent_games = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode([
    'status' => 'success',
    'stats' => [
        'total_games' => $total_games,
        'total_users' => $total_users,
        'player1_wins' => $player1_wins,
        'player2_wins' => $player2_wins,
        'total_draws' => $total_draws
    ],
    'recent_games' => $recent_games
]);
?>
