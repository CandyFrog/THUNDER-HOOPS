<?php
// admin/users.php
require_once '../midleware/cek_login.php';
require_once '../config/koneksi.php';

// Check if admin
if($_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$page_title = "Manage Users - Basketball Arcade";

// Connection already included

$success = '';
$error = '';

// Handle Add User
if(isset($_POST['add_user'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $full_name = trim($_POST['full_name']);
    $role = $_POST['role'];
    
    // Validasi
    if(empty($username) || empty($password) || empty($full_name)) {
        $error = 'Semua field harus diisi!';
    } elseif(strlen($username) < 4) {
        $error = 'Username minimal 4 karakter!';
    } elseif(strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } else {
        // Cek username sudah ada atau belum
        $query = "SELECT id FROM users WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
            $error = 'Username sudah digunakan!';
        } else {
            // Insert user baru
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssss", $username, $hashed_password, $full_name, $role);
            
            if($stmt->execute()) {
                $success = 'User berhasil ditambahkan!';
            } else {
                $error = 'Gagal menambahkan user!';
            }
        }
    }
}

// Handle Edit User
if(isset($_POST['edit_user'])) {
    $user_id = (int)$_POST['user_id'];
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $role = $_POST['role'];
    $password = $_POST['password'];
    
    // Validasi
    if(empty($username) || empty($full_name)) {
        $error = 'Username dan nama lengkap harus diisi!';
    } else {
        // Cek username conflict (kecuali untuk user yang sama)
        $query = "SELECT id FROM users WHERE username = ? AND id != ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $username, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
            $error = 'Username sudah digunakan oleh user lain!';
        } else {
            // Update user
            if(!empty($password)) {
                // Update dengan password baru
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $query = "UPDATE users SET username = ?, password = ?, full_name = ?, role = ? WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ssssi", $username, $hashed_password, $full_name, $role, $user_id);
            } else {
                // Update tanpa password
                $query = "UPDATE users SET username = ?, full_name = ?, role = ? WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("sssi", $username, $full_name, $role, $user_id);
            }
            
            if($stmt->execute()) {
                $success = 'User berhasil diupdate!';
            } else {
                $error = 'Gagal mengupdate user!';
            }
        }
    }
}

// Get all users with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where_clause = '';
$params = [];
$types = '';

if(!empty($search)) {
    $where_clause = "WHERE username LIKE ? OR full_name LIKE ? OR role LIKE ?";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

$query = "SELECT COUNT(*) as total FROM users $where_clause";
$stmt = $conn->prepare($query);
if(!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$total_records = $result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

$query = "SELECT * FROM users $where_clause ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);

// Append limit and offset to params
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container-custom mt-4">
    <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="page-title">Manage Users</h1>
            <p class="page-subtitle">Kelola semua pengguna sistem</p>
        </div>
        <button class="btn btn-peach" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="bi bi-person-plus"></i> Add New User
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
    
    <!-- Search -->
    <div class="card card-custom mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-10">
                    <input type="text" class="form-control form-control-custom" name="search" 
                           placeholder="Cari berdasarkan username, nama, atau role..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-peach w-100">
                        <i class="bi bi-search"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Users Table -->
    <div class="card card-custom">
        <div class="card-header-custom">
            <i class="bi bi-people"></i> All Users (<?php echo $total_records; ?> users)
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-custom mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Role</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($users) > 0): ?>
                            <?php foreach($users as $user): ?>
                            <tr>
                                <td><strong>#<?php echo $user['id']; ?></strong></td>
                                <td>
                                    <i class="bi bi-person-circle" style="color: var(--primary-peach);"></i>
                                    <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td>
                                    <?php if($user['role'] == 'admin'): ?>
                                        <span class="badge" style="background: linear-gradient(135deg, #dc3545, #c82333); color: white; padding: 0.5rem 1rem; border-radius: 50px;">
                                            <i class="bi bi-shield-fill"></i> Admin
                                        </span>
                                    <?php else: ?>
                                        <span class="badge" style="background: linear-gradient(135deg, #28a745, #20c997); color: white; padding: 0.5rem 1rem; border-radius: 50px;">
                                            <i class="bi bi-person"></i> User
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d M Y, H:i', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-peach me-1" 
                                            onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <?php if($user['id'] != $_SESSION['user_id']): ?>
                                    <button class="btn btn-sm btn-danger" 
                                            onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="bi bi-inbox" style="font-size: 3rem; color: var(--secondary-peach);"></i>
                                    <p class="mt-2 mb-0">Tidak ada data user ditemukan</p>
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
                        
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                        
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

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 20px; border: none;">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-peach), var(--secondary-peach)); color: white; border-radius: 20px 20px 0 0;">
                <h5 class="modal-title"><i class="bi bi-person-plus"></i> Add New User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control form-control-custom" name="username" required minlength="4">
                        <small class="text-muted">Minimal 4 karakter</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control form-control-custom" name="full_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control form-control-custom" name="password" required minlength="6">
                        <small class="text-muted">Minimal 6 karakter</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-control form-control-custom" name="role" required>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer" style="border: none;">
                    <button type="button" class="btn btn-outline-peach" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_user" class="btn btn-peach">
                        <i class="bi bi-save"></i> Save User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 20px; border: none;">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-peach), var(--secondary-peach)); color: white; border-radius: 20px 20px 0 0;">
                <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control form-control-custom" name="username" id="edit_username" required minlength="4">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control form-control-custom" name="full_name" id="edit_full_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control form-control-custom" name="password" id="edit_password" minlength="6">
                        <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-control form-control-custom" name="role" id="edit_role" required>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer" style="border: none;">
                    <button type="button" class="btn btn-outline-peach" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_user" class="btn btn-peach">
                        <i class="bi bi-save"></i> Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 20px; border: none;">
            <div class="modal-header" style="background: linear-gradient(135deg, #dc3545, #c82333); color: white; border-radius: 20px 20px 0 0;">
                <h5 class="modal-title"><i class="bi bi-trash"></i> Delete User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this user?</p>
                <p class="text-danger mb-0"><strong>Username: <span id="delete_user_name"></span></strong></p>
            </div>
            <div class="modal-footer" style="border: none;">
                <button type="button" class="btn btn-outline-peach" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDeleteUserBtn" class="btn btn-danger">
                    <i class="bi bi-trash"></i> Delete
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function editUser(user) {
    document.getElementById('edit_user_id').value = user.id;
    document.getElementById('edit_username').value = user.username;
    document.getElementById('edit_full_name').value = user.full_name;
    document.getElementById('edit_role').value = user.role;
    document.getElementById('edit_password').value = '';
    
    var editModal = new bootstrap.Modal(document.getElementById('editUserModal'));
    editModal.show();
}

function deleteUser(userId, username) {
    document.getElementById('delete_user_name').textContent = username;
    document.getElementById('confirmDeleteUserBtn').href = 'delete_user.php?id=' + userId;
    
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
    deleteModal.show();
}
</script>

<?php include '../includes/footer.php'; ?>