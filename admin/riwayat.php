<?php
require_once '../midleware/cek_login.php';
require_once '../config/koneksi.php';

if($_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$page_title = "Riwayat Permainan - Basketball Arcade";

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$winner_filter = isset($_GET['winner']) ? trim($_GET['winner']) : '';

$where_clauses = [];
$params = [];
$types = '';

if(!empty($search)) {
    $where_clauses[] = "(id LIKE ? OR pemenang LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

if(!empty($winner_filter)) {
    $where_clauses[] = "pemenang = ?";
    $params[] = $winner_filter;
    $types .= 's';
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

$query_total = "SELECT COUNT(*) as total FROM match_data $where_sql";
$stmt_total = $conn->prepare($query_total);
if(!empty($params)) {
    $stmt_total->bind_param($types, ...$params);
}
$stmt_total->execute();
$total_records = $stmt_total->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

$query_games = "SELECT * FROM match_data $where_sql ORDER BY id DESC LIMIT ? OFFSET ?";
$stmt_games = $conn->prepare($query_games);

$final_params = array_merge($params, [$limit, $offset]);
$final_types = $types . "ii";

$stmt_games->bind_param($final_types, ...$final_params);
$stmt_games->execute();
$games = $stmt_games->get_result()->fetch_all(MYSQLI_ASSOC);

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container-custom mt-4">
    <div class="mb-4">
        <h1 class="page-title">Riwayat Permainan</h1>
        <p class="page-subtitle">Riwayat seluruh hasil pertandingan THUNDER-HOOPS</p>
    </div>
    
    <div class="card card-custom mb-4 shadow-sm border-0">
        <div class="card-body">
            <form method="GET" action="" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small fw-bold text-muted mb-2">Cari ID / Status</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 px-3"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control form-control-custom border-start-0 ps-0 bg-light" name="search" 
                               placeholder="Cari ID Pertandingan..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted mb-2">Filter Pemenang</label>
                    <select class="form-select form-select-custom bg-light" name="winner">
                        <option value="">Semua Hasil</option>
                        <option value="Kiri" <?php echo $winner_filter == 'Kiri' ? 'selected' : ''; ?>>Kiri Menang</option>
                        <option value="Kanan" <?php echo $winner_filter == 'Kanan' ? 'selected' : ''; ?>>Kanan Menang</option>
                        <option value="Seri" <?php echo $winner_filter == 'Seri' ? 'selected' : ''; ?>>Seri / Draw</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-peach w-100 py-2">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        <?php if(!empty($search) || !empty($winner_filter)): ?>
                            <a href="riwayat.php" class="btn btn-light py-2 border">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card card-custom shadow-sm border-0 overflow-hidden">
        <div class="card-header-custom p-3 bg-white border-bottom d-flex align-items-center justify-content-between">
            <span class="fw-bold"><i class="bi bi-trophy-fill me-2 text-peach"></i>Daftar Pertandingan</span>
            <span class="badge bg-light text-muted border"><?php echo $total_records; ?> Total Data</span>
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
                    <tbody>
                        <?php if(count($games) > 0): ?>
                            <?php foreach($games as $game): ?>
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
            
            <?php if($total_pages > 1): ?>
            <div class="p-4 border-top">
                <nav>
                    <ul class="pagination justify-content-end mb-0 gap-2">
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link border rounded-3 shadow-none px-3" href="riwayat.php?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&winner=<?php echo urlencode($winner_filter); ?>">
                                <i class="bi bi-chevron-left small"></i>
                            </a>
                        </li>
                        
                        <?php 
                        $start = max(1, $page - 2);
                        $end = min($total_pages, $page + 2);
                        for($i = $start; $i <= $end; $i++): 
                        ?>
                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                            <a class="page-link border rounded-3 shadow-none <?php echo $page == $i ? 'bg-peach text-white border-peach' : 'text-dark bg-white'; ?>" 
                               href="riwayat.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&winner=<?php echo urlencode($winner_filter); ?>">
                               <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link border rounded-3 shadow-none px-3" href="riwayat.php?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&winner=<?php echo urlencode($winner_filter); ?>">
                                <i class="bi bi-chevron-right small"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.bg-peach { background-color: var(--primary-peach) !important; }
.border-peach { border-color: var(--primary-peach) !important; }
.text-peach { color: var(--primary-peach) !important; }
.card-custom { border-radius: 20px !important; }
.table-hover tbody tr:hover { background-color: rgba(255, 154, 158, 0.02) !important; cursor: default; }
.page-link { min-width: 40px; text-align: center; color: var(--primary-peach); }
.page-link:hover { background-color: #f8f9fa; color: var(--primary-peach); }
.form-control-custom, .form-select-custom { border-radius: 12px; height: 48px; border: 1px solid #eee; }
.form-control-custom:focus, .form-select-custom:focus { border-color: var(--primary-peach); box-shadow: 0 0 0 0.25rem rgba(255, 154, 158, 0.1); background-color: #fff !important; }
.btn-peach { background: linear-gradient(135deg, var(--primary-peach), var(--secondary-peach)); border: none; color: white; border-radius: 12px; font-weight: 600; transition: all 0.3s ease; height: 48px; }
.btn-peach:hover { transform: translateY(-1px); box-shadow: 0 4px 15px rgba(255, 154, 158, 0.3); color: white; opacity: 0.9; }
.badge { font-weight: 600; letter-spacing: 0.3px; }
</style>

<?php include '../includes/footer.php'; ?>