<?php
// admin/games.php
session_start();
require_once '../config/database.php';

// Check if admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$page_title = "Manage Games - Basketball Arcade";

$database = new Database();
$db = $database->getConnection();

$success = '';
$error = '';

// Handle Add Game
if(isset($_POST['add_game'])) {
    $player1_score = (int)$_POST['player1_score'];
    $player2_score = (int)$_POST['player2_score'];
    $game_duration = (int)$_POST['game_duration'];
    $notes = trim($_POST['notes']);
    
    // Determine winner
    if($player1_score > $player2_score) {
        $winner = 'Player 1';
    } elseif($player2_score > $player1_score) {
        $winner = 'Player 2';
    } else {
        $winner = 'Draw';
    }
    
    $query = "INSERT INTO games (player1_score, player2_score, winner, game_duration, notes) 
              VALUES (:p1, :p2, :winner, :duration, :notes)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':p1', $player1_score);
    $stmt->bindParam(':p2', $player2_score);
    $stmt->bindParam(':winner', $winner);
    $stmt->bindParam(':duration', $game_duration);
    $stmt->bindParam(':notes', $notes);
    
    if($stmt->execute()) {
        $success = 'Game berhasil ditambahkan!';
    } else {
        $error = 'Gagal menambahkan game!';
    }
}

// Handle Edit Game
if(isset($_POST['edit_game'])) {
    $game_id = (int)$_POST['game_id'];
    $player1_score = (int)$_POST['player1_score'];
    $player2_score = (int)$_POST['player2_score'];
    $game_duration = (int)$_POST['game_duration'];
    $notes = trim($_POST['notes']);
    
    // Determine winner
    if($player1_score > $player2_score) {
        $winner = 'Player 1';
    } elseif($player2_score > $player1_score) {
        $winner = 'Player 2';
    } else {
        $winner = 'Draw';
    }
    
    $query = "UPDATE games SET player1_score = :p1, player2_score = :p2, winner = :winner, 
              game_duration = :duration, notes = :notes WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':p1', $player1_score);
    $stmt->bindParam(':p2', $player2_score);
    $stmt->bindParam(':winner', $winner);
    $stmt->bindParam(':duration', $game_duration);
    $stmt->bindParam(':notes', $notes);
    $stmt->bindParam(':id', $game_id);
    
    if($stmt->execute()) {
        $success = 'Game berhasil diupdate!';
    } else {
        $error = 'Gagal mengupdate game!';
    }
}

// Get all games with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where_clause = '';
if(!empty($search)) {
    $where_clause = "WHERE id LIKE :search OR winner LIKE :search";
}

$query = "SELECT COUNT(*) as total FROM games $where_clause";
$stmt = $db->prepare($query);
if(!empty($search)) {
    $search_param = "%$search%";
    $stmt->bindParam(':search', $search_param);
}
$stmt->execute();
$total_records = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_records / $limit);

$query = "SELECT * FROM games $where_clause ORDER BY played_at DESC LIMIT :limit OFFSET :offset";
$stmt = $db->prepare($query);
if(!empty($search)) {
    $stmt->bindParam(':search', $search_param);
}
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$games = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container-custom mt-4">
    <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="page-title">Manage Games</h1>
            <p class="page-subtitle">Kelola semua data permainan</p>
        </div>
        <button class="btn btn-peach" data-bs-toggle="modal" data-bs-target="#addGameModal">
            <i class="bi bi-plus-circle"></i> Add New Game
        </button>
    </div>
    
    <?php if($success): ?>
    <div class="alert alert-success alert-custom alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i> <?php echo $success; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <?php if($error): ?>
    <div class="alert alert-danger alert-custom alert-dismissible fade show" role="alert">
        <i class="bi bi-x-circle"></i> <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <!-- Search and Filter -->
    <div class="card card-custom mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-10">
                    <input type="text" class="form-control form-control-custom" name="search" 
                           placeholder="Cari berdasarkan ID atau Winner..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-peach w-100">
                        <i class="bi bi-search"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Games Table -->
    <div class="card card-custom">
        <div class="card-header-custom">
            <i class="bi bi-list-ul"></i> All Games (<?php echo $total_records; ?> records)
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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($games) > 0): ?>
                            <?php foreach($games as $game): ?>
                            <tr>
                                <td><strong>#<?php echo $game['id']; ?></strong></td>
                                <td>
                                    <span style="font-size: 1.3rem; font-weight: 700; color: var(--primary-peach);">
                                        <?php echo $game['player1_score']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="font-size: 1.3rem; font-weight: 700; color: var(--primary-peach);">
                                        <?php echo $game['player2_score']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if($game['winner'] == 'Draw'): ?>
                                        <span class="badge-draw"><i class="bi bi-dash-circle"></i> Draw</span>
                                    <?php else: ?>
                                        <span class="badge-winner"><i class="bi bi-trophy"></i> <?php echo $game['winner']; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $game['game_duration']; ?>s</td>
                                <td><?php echo date('d M Y, H:i', strtotime($game['played_at'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-peach me-1" 
                                            onclick="editGame(<?php echo htmlspecialchars(json_encode($game)); ?>)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" 
                                            onclick="deleteGame(<?php echo $game['id']; ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="bi bi-inbox" style="font-size: 3rem; color: var(--secondary-peach);"></i>
                                    <p class="mt-2 mb-0">Tidak ada data game ditemukan</p>
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
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                        
                        <?php 
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        if($start_page > 1): ?>
                            <li class="page-item"><a class="page-link" href="?page=1&search=<?php echo urlencode($search); ?>">1</a></li>
                            <?php if($start_page > 2): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php for($i = $start_page; $i <= $end_page; $i++): ?>
                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if($end_page < $total_pages): ?>
                            <?php if($end_page < $total_pages - 1): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                            <li class="page-item"><a class="page-link" href="?page=<?php echo $total_pages; ?>&search=<?php echo urlencode($search); ?>"><?php echo $total_pages; ?></a></li>
                        <?php endif; ?>
                        
                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Game Modal -->
<div class="modal fade" id="addGameModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 20px; border: none;">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-peach), var(--secondary-peach)); color: white; border-radius: 20px 20px 0 0;">
                <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Add New Game</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Player 1 Score</label>
                        <input type="number" class="form-control form-control-custom" name="player1_score" required min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Player 2 Score</label>
                        <input type="number" class="form-control form-control-custom" name="player2_score" required min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Game Duration (seconds)</label>
                        <input type="number" class="form-control form-control-custom" name="game_duration" required min="1" value="120">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea class="form-control form-control-custom" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="border: none;">
                    <button type="button" class="btn btn-outline-peach" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_game" class="btn btn-peach">
                        <i class="bi bi-save"></i> Save Game
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Game Modal -->
<div class="modal fade" id="editGameModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 20px; border: none;">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-peach), var(--secondary-peach)); color: white; border-radius: 20px 20px 0 0;">
                <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit Game</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="game_id" id="edit_game_id">
                    <div class="mb-3">
                        <label class="form-label">Player 1 Score</label>
                        <input type="number" class="form-control form-control-custom" name="player1_score" id="edit_player1_score" required min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Player 2 Score</label>
                        <input type="number" class="form-control form-control-custom" name="player2_score" id="edit_player2_score" required min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Game Duration (seconds)</label>
                        <input type="number" class="form-control form-control-custom" name="game_duration" id="edit_game_duration" required min="1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea class="form-control form-control-custom" name="notes" id="edit_notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="border: none;">
                    <button type="button" class="btn btn-outline-peach" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_game" class="btn btn-peach">
                        <i class="bi bi-save"></i> Update Game
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteGameModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 20px; border: none;">
            <div class="modal-header" style="background: linear-gradient(135deg, #dc3545, #c82333); color: white; border-radius: 20px 20px 0 0;">
                <h5 class="modal-title"><i class="bi bi-trash"></i> Delete Game</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this game? This action cannot be undone.</p>
                <p class="text-danger mb-0"><strong>Game ID: #<span id="delete_game_id"></span></strong></p>
            </div>
            <div class="modal-footer" style="border: none;">
                <button type="button" class="btn btn-outline-peach" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">
                    <i class="bi bi-trash"></i> Delete
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function editGame(game) {
    document.getElementById('edit_game_id').value = game.id;
    document.getElementById('edit_player1_score').value = game.player1_score;
    document.getElementById('edit_player2_score').value = game.player2_score;
    document.getElementById('edit_game_duration').value = game.game_duration;
    document.getElementById('edit_notes').value = game.notes || '';
    
    var editModal = new bootstrap.Modal(document.getElementById('editGameModal'));
    editModal.show();
}

function deleteGame(gameId) {
    document.getElementById('delete_game_id').textContent = gameId;
    document.getElementById('confirmDeleteBtn').href = 'delete_game.php?id=' + gameId;
    
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteGameModal'));
    deleteModal.show();
}
</script>

<?php include '../includes/footer.php'; ?>