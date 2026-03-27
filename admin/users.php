<?php
require_once '../midleware/cek_login.php';
require_once '../config/koneksi.php';

if($_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$page_title = "Kelola Pengguna - Basketball Arcade";

$success = '';
$error = '';

if(isset($_POST['add_user'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $full_name = trim($_POST['full_name']);
    $role = $_POST['role'];
    
    if(empty($username) || empty($password) || empty($full_name)) {
        $swal_title = 'Gagal!';
        $swal_text = 'Semua field harus diisi!';
        $swal_icon = 'error';
    } elseif(strlen($username) < 4) {
        $swal_title = 'Gagal!';
        $swal_text = 'Username minimal 4 karakter!';
        $swal_icon = 'error';
    } elseif(strlen($password) < 6) {
        $swal_title = 'Gagal!';
        $swal_text = 'Password minimal 6 karakter!';
        $swal_icon = 'error';
    } else {
        $query = "SELECT id FROM users WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
            $swal_title = 'Gagal!';
            $swal_text = 'Username sudah digunakan!';
            $swal_icon = 'error';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssss", $username, $hashed_password, $full_name, $role);
            
            if($stmt->execute()) {
                $swal_title = 'Berhasil!';
                $swal_text = 'Pengguna berhasil ditambahkan!';
                $swal_icon = 'success';
            } else {
                $swal_title = 'Error!';
                $swal_text = 'Gagal menambahkan pengguna: ' . $conn->error;
                $swal_icon = 'error';
            }
        }
    }
}

if(isset($_POST['edit_user'])) {
    $user_id = (int)$_POST['user_id'];
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $role = $_POST['role'];
    $password = $_POST['password'];
    
    if(empty($username) || empty($full_name)) {
        $swal_title = 'Gagal!';
        $swal_text = 'Username dan nama lengkap harus diisi!';
        $swal_icon = 'error';
    } else {
        $query = "SELECT id FROM users WHERE username = ? AND id != ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $username, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
            $swal_title = 'Gagal!';
            $swal_text = 'Username sudah digunakan oleh pengguna lain!';
            $swal_icon = 'error';
        } else {
            $updated = false;
            
            if(!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $query = "UPDATE users SET username = ?, password = ?, full_name = ?, role = ? WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ssssi", $username, $hashed_password, $full_name, $role, $user_id);
                $updated = $stmt->execute();
            } else {
                $query = "UPDATE users SET username = ?, full_name = ?, role = ? WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("sssi", $username, $full_name, $role, $user_id);
                $updated = $stmt->execute();
            }
            
            if($updated) {
                $swal_title = 'Berhasil!';
                $swal_text = 'Pengguna berhasil diperbarui!';
                $swal_icon = 'success';
            } else {
                $swal_title = 'Error!';
                $swal_text = 'Gagal memperbarui pengguna: ' . $conn->error;
                $swal_icon = 'error';
            }
        }
    }
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

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

$query = "SELECT * FROM users $where_clause ORDER BY id ASC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);

$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);

include '../includes/header.php';
include '../includes/navbar.php';

if(isset($_SESSION['user_success'])) {
    $swal_title = 'Berhasil!';
    $swal_text = $_SESSION['user_success'];
    $swal_icon = 'success';
    unset($_SESSION['user_success']);
} elseif(isset($_SESSION['user_error'])) {
    $swal_title = 'Gagal!';
    $swal_text = $_SESSION['user_error'];
    $swal_icon = 'error';
    unset($_SESSION['user_error']);
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container-custom mt-4">
    <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="page-title">Kelola Pengguna</h1>
            <p class="page-subtitle">Kelola semua pengguna sistem</p>
        </div>
        <button class="btn btn-peach" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="bi bi-person-plus"></i> Tambah Pengguna Baru
        </button>
    </div>
    
    <?php if(isset($swal_title)): ?>
    <script>
        Swal.fire({
            title: '<?php echo $swal_title; ?>',
            text: '<?php echo $swal_text; ?>',
            icon: '<?php echo $swal_icon; ?>',
            confirmButtonColor: '#ff9a9e',
            border: 'none'
        }).then(() => {
            if (window.history.replaceState) {
                window.history.replaceState( null, null, window.location.href );
            }
        });
    </script>
    <?php endif; ?>
    
    <div class="card card-custom mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-10">
                    <input type="text" class="form-control form-control-custom" name="search" 
                           placeholder="Cari berdasarkan username, nama, atau role..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-peach w-100">
                        <i class="bi bi-search"></i> Cari
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card card-custom">
        <div class="card-header-custom">
            <i class="bi bi-people"></i> Semua Pengguna (<?php echo $total_records; ?> orang)
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-custom mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Foto</th>
                            <th>Username</th>
                            <th>Nama Lengkap</th>
                            <th>Role</th>
                            <th>Dibuat Pada</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($users) > 0): ?>
                            <?php foreach($users as $user): ?>
                            <tr>
                                <td><strong>#<?php echo $user['id']; ?></strong></td>
                                <td>
                                    <?php 
                                    $foto = !empty($user['foto_profil']) ? '../assets/foto_profil/' . $user['foto_profil'] : 'https://ui-avatars.com/api/?name=' . urlencode($user['full_name']) . '&background=E8A796&color=fff';
                                    ?>
                                    <img src="<?php echo $foto; ?>" alt="Foto" class="rounded-circle shadow-sm" style="width: 40px; height: 40px; object-fit: cover; border: 2px solid white;">
                                </td>
                                <td>
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
                                    <button class="btn btn-sm btn-outline-peach me-1 btn-edit-user" 
                                            data-user='<?php echo htmlspecialchars(json_encode($user), ENT_QUOTES, 'UTF-8'); ?>'>
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
                                    <p class="mt-2 mb-0">Tidak ada data pengguna ditemukan</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
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

<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 20px; border: none;">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-peach), var(--secondary-peach)); color: white; border-radius: 20px 20px 0 0;">
                <h5 class="modal-title"><i class="bi bi-person-plus"></i> Tambah Pengguna Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="users.php">
                <div class="modal-body">
                    <input type="hidden" name="add_user" value="1">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control form-control-custom" name="username" required minlength="4">
                        <small class="text-muted">Minimal 4 karakter</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control form-control-custom" name="full_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="password-field-container">
                            <input type="password" class="form-control form-control-custom" name="password" required minlength="6">
                            <i class="bi bi-eye password-toggle"></i>
                        </div>
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
                    <button type="button" class="btn btn-outline-peach" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-peach">
                        <i class="bi bi-save"></i> Simpan Pengguna
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 20px; border: none;">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-peach), var(--secondary-peach)); color: white; border-radius: 20px 20px 0 0;">
                <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit Pengguna</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="users.php">
                <div class="modal-body">
                    <input type="hidden" name="edit_user" value="1">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control form-control-custom" name="username" id="edit_username" required minlength="4">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control form-control-custom" name="full_name" id="edit_full_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="password-field-container">
                            <input type="password" class="form-control form-control-custom" name="password" id="edit_password" minlength="6">
                            <i class="bi bi-eye password-toggle"></i>
                        </div>
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
                    <button type="button" class="btn btn-outline-peach" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-peach">
                        <i class="bi bi-save"></i> Perbarui Pengguna
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 20px; border: none;">
            <div class="modal-header" style="background: linear-gradient(135deg, #dc3545, #c82333); color: white; border-radius: 20px 20px 0 0;">
                <h5 class="modal-title"><i class="bi bi-trash"></i> Hapus Pengguna</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus pengguna ini?</p>
                <p class="text-danger mb-0"><strong>Username: <span id="delete_user_name"></span></strong></p>
            </div>
            <div class="modal-footer" style="border: none;">
                <button type="button" class="btn btn-outline-peach" data-bs-dismiss="modal">Batal</button>
                <form method="POST" action="delete_user.php" id="deleteUserForm">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="user_id" id="delete_user_id_input">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.btn-edit-user');
    editButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            try {
                const user = JSON.parse(this.getAttribute('data-user'));
                
                document.getElementById('edit_user_id').value = user.id;
                document.getElementById('edit_username').value = user.username;
                document.getElementById('edit_full_name').value = user.full_name;
                document.getElementById('edit_role').value = user.role;
                document.getElementById('edit_password').value = '';
                
                var editModal = new bootstrap.Modal(document.getElementById('editUserModal'));
                editModal.show();
            } catch (e) {
                console.error("Error mengambil data pengguna:", e);
                Swal.fire('Error', 'Gagal mengambil data pengguna', 'error');
            }
        });
    });
});

function deleteUser(userId, username) {
    document.getElementById('delete_user_name').textContent = username;
    document.getElementById('delete_user_id_input').value = userId;
    
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
    deleteModal.show();
}
</script>

<?php include '../includes/footer.php'; ?>