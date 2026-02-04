<?php
// admin/games.php
require_once '../midleware/cek_login.php';
require_once '../config/koneksi.php';

// Check if admin
if($_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$page_title = "Manage Games - Basketball Arcade";

// Connection already included

$success = '';
$error = '';

// Handle Add Game
if(isset($_POST['add_game'])) {
    // Get form data
    $skor_kiri = (int)$_POST['skor_kiri'];
    $skor_kanan = (int)$_POST['skor_kanan'];
    $durasi = (int)$_POST['durasi'];
    $pemenang = '';
    
    if($skor_kiri > $skor_kanan) {
        $pemenang = 'Kiri';
    } elseif($skor_kanan > $skor_kiri) {
        $pemenang = 'Kanan';
    } else {
        $pemenang = 'Seri';
    }
    
    $query = "INSERT INTO match_data (skor_kiri, skor_kanan, durasi, pemenang) 
              VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiis", $skor_kiri, $skor_kanan, $durasi, $pemenang);
    
    if($stmt->execute()) {
        $success = 'Data pertandingan berhasil ditambahkan!';
    } else {
        $error = 'Gagal menambahkan data pertandingan!';
    }
}

// Handle Edit Game
if(isset($_POST['edit_game'])) {
    // Get form data
    $game_id = (int)$_POST['id'];
    $skor_kiri = (int)$_POST['skor_kiri'];
    $skor_kanan = (int)$_POST['skor_kanan'];
    $durasi = (int)$_POST['durasi'];
    $pemenang = '';
    
    if($skor_kiri > $skor_kanan) {
        $pemenang = 'Kiri';
    } elseif($skor_kanan > $skor_kiri) {
        $pemenang = 'Kanan';
    } else {
        $pemenang = 'Seri';
    }
    
    $query = "UPDATE match_data SET skor_kiri = ?, skor_kanan = ?, pemenang = ?, 
              durasi = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiisi", $skor_kiri, $skor_kanan, $pemenang, $durasi, $game_id);
    
    if($stmt->execute()) {
        $success = 'Data pertandingan berhasil diupdate!';
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
$params = [];
$types = '';

if(!empty($search)) {
    $where_clause = "WHERE id LIKE ? OR pemenang LIKE ?";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

$query = "SELECT COUNT(*) as total FROM match_data $where_clause";
$stmt = $conn->prepare($query);
if(!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$total_records = $result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

$query = "SELECT * FROM match_data $where_clause ORDER BY id DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);

// Append limit and offset to params
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$games = $result->fetch_all(MYSQLI_ASSOC);

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
                            <th>No</th>
                            <th>ID</th>
                            <th>Skor Kiri</th>
                            <th>Skor Kanan</th>
                            <th>Pemenang</th>
                            <th>Durasi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($games) > 0): ?>
                            <?php $no = $offset + 1; foreach($games as $game): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td>#<?php echo $game['id']; ?></td>
                                <td><?php echo $game['skor_kiri']; ?></td>
                                <td><?php echo $game['skor_kanan']; ?></td>
                                <td>
                                    <?php if($game['pemenang'] == 'Seri'): ?>
                                        <span class="badge bg-secondary">Seri</span>
                                    <?php else: ?>
                                        <span class="badge bg-success"><?php echo $game['pemenang']; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $game['durasi']; ?>s</td>
                                <td>
                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editGameModal<?php echo $game['id']; ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="delete_game.php?id=<?php echo $game['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus data ini?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>

                            <!-- Edit Game Modal -->
                            <div class="modal fade" id="editGameModal<?php echo $game['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Data Pertandingan</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="edit_game" value="1">
                                                <input type="hidden" name="id" value="<?php echo $game['id']; ?>">
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Skor Kiri</label>
                                                    <input type="number" class="form-control" name="skor_kiri" value="<?php echo $game['skor_kiri']; ?>" required>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Skor Kanan</label>
                                                    <input type="number" class="form-control" name="skor_kanan" value="<?php echo $game['skor_kanan']; ?>" required>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Durasi (detik)</label>
                                                    <input type="number" class="form-control" name="durasi" value="<?php echo $game['durasi']; ?>" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
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
                    <input type="hidden" name="add_game" value="1">
                
                    <div class="mb-3">
                        <label class="form-label">Skor Kiri</label>
                        <input type="number" class="form-control" name="skor_kiri" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Skor Kanan</label>
                        <input type="number" class="form-control" name="skor_kanan" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Durasi (detik)</label>
                        <input type="number" class="form-control" name="durasi" required>
                    </div>
                </div>
                <div class="modal-footer" style="border: none;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
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