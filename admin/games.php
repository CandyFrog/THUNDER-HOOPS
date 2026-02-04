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
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
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



<?php include '../includes/footer.php'; ?>