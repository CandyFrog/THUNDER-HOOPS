<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/koneksi.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$limit = isset($_GET['limit']) ? max(1, (int) $_GET['limit']) : 10;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$winner_filter = isset($_GET['winner']) ? trim($_GET['winner']) : '';

$where_clauses = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where_clauses[] = "(id LIKE ? OR pemenang LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

if (!empty($winner_filter)) {
    $where_clauses[] = "pemenang = ?";
    $params[] = $winner_filter;
    $types .= 's';
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

$stmt_total = $conn->prepare("SELECT COUNT(*) as total FROM match_data $where_sql");
if (!empty($params)) {
    $stmt_total->bind_param($types, ...$params);
}
$stmt_total->execute();
$total_records = (int) $stmt_total->get_result()->fetch_assoc()['total'];
$total_pages = (int) ceil($total_records / $limit);

$final_params = array_merge($params, [$limit, $offset]);
$final_types = $types . "ii";

$stmt_games = $conn->prepare("SELECT id, skor_kiri, skor_kanan, durasi, pemenang, created_at FROM match_data $where_sql ORDER BY id DESC LIMIT ? OFFSET ?");
$stmt_games->bind_param($final_types, ...$final_params);
$stmt_games->execute();
$games = $stmt_games->get_result()->fetch_all(MYSQLI_ASSOC);

$query_stats = "SELECT pemenang, COUNT(*) as total FROM match_data GROUP BY pemenang";
$result_stats = $conn->query($query_stats);
$wins = [];
while ($row = $result_stats->fetch_assoc()) {
    $wins[$row['pemenang']] = (int) $row['total'];
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

$stmt_total_all = $conn->query("SELECT COUNT(*) as total FROM match_data");
$total_games = (int) $stmt_total_all->fetch_assoc()['total'];

echo json_encode([
    'status' => 'success',
    'total_records' => $total_records,
    'total_pages' => $total_pages,
    'current_page' => $page,
    'stats' => [
        'total_games' => $total_games,
        'player1_wins' => $player1_wins,
        'player2_wins' => $player2_wins,
        'total_draws' => $total_draws,
    ],
    'games' => $games,
]);
