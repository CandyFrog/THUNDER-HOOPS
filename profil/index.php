<?php
// profil/index.php
ob_start();
require_once '../midleware/cek_login.php';
require_once '../config/koneksi.php';

$user_id = $_SESSION['user_id'];
$page_title = "Profil Akun - Basketball Arcade";

$swal_title = '';
$swal_text = '';
$swal_icon = '';

// Ambil data user
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Folder Penyimpanan Foto
$upload_dir = '../assets/foto_profil/';

// Handle Hapus Foto
if (isset($_POST['delete_photo'])) {
    if (!empty($user['foto_profil']) && file_exists($upload_dir . $user['foto_profil'])) {
        @unlink($upload_dir . $user['foto_profil']);
    }
    
    $query = "UPDATE users SET foto_profil = NULL WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['user_success'] = 'Foto profil berhasil dihapus!';
        header("Location: index.php");
        exit;
    }
}

// Ambil pesan dari session jika ada (setelah redirect)
if (isset($_SESSION['user_success'])) {
    $swal_title = 'Berhasil!';
    $swal_text = $_SESSION['user_success'];
    $swal_icon = 'success';
    unset($_SESSION['user_success']);
} elseif (isset($_SESSION['user_error'])) {
    $swal_title = 'Gagal!';
    $swal_text = $_SESSION['user_error'];
    $swal_icon = 'error';
    unset($_SESSION['user_error']);
}

// Handle Update Profil & Upload Cropped Photo
if (isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name']);
    
    // Update Nama
    $query = "UPDATE users SET full_name = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $full_name, $user_id);
    $stmt->execute();
    $_SESSION['full_name'] = $full_name;

    // Handle Cropped Image (Base64)
    if (!empty($_POST['cropped_image'])) {
        $data = $_POST['cropped_image'];
        
        if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
            $data = substr($data, strpos($data, ',') + 1);
            $type = strtolower($type[1]);

            if (!in_array($type, ['jpg', 'jpeg', 'png', 'webp'])) {
                $_SESSION['user_error'] = 'Format file tidak didukung!';
            } else {
                $data = base64_decode($data);
                if (strlen($data) > 20 * 1024 * 1024) {
                    $_SESSION['user_error'] = 'Ukuran foto maksimal 20MB!';
                } else {
                    $new_filename = 'profile_' . $user_id . '_' . time() . '.' . ($type == 'jpeg' ? 'jpg' : $type);
                    if (file_put_contents($upload_dir . $new_filename, $data)) {
                        if (!empty($user['foto_profil']) && file_exists($upload_dir . $user['foto_profil'])) {
                            @unlink($upload_dir . $user['foto_profil']);
                        }
                        $query = "UPDATE users SET foto_profil = ? WHERE id = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("si", $new_filename, $user_id);
                        $stmt->execute();
                        $_SESSION['user_success'] = 'Profil dan foto berhasil diperbarui!';
                    }
                }
            }
        }
    } else {
        $_SESSION['user_success'] = 'Profil berhasil diperbarui!';
    }
    header("Location: index.php");
    exit;
}

// Handle Ganti Password
if (isset($_POST['change_password'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (password_verify($old_password, $user['password'])) {
        if ($new_password === $confirm_password) {
            if (strlen($new_password) >= 6) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $query = "UPDATE users SET password = ? WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("si", $hashed_password, $user_id);
                
                if ($stmt->execute()) {
                    $swal_title = 'Berhasil!';
                    $swal_text = 'Password berhasil diubah!';
                    $swal_icon = 'success';
                }
            } else {
                $swal_title = 'Gagal!';
                $swal_text = 'Password baru minimal 6 karakter!';
                $swal_icon = 'error';
            }
        } else {
            $swal_title = 'Gagal!';
            $swal_text = 'Konfirmasi password tidak cocok!';
            $swal_icon = 'error';
        }
    } else {
        $swal_title = 'Gagal!';
        $swal_text = 'Password lama salah!';
        $swal_icon = 'error';
    }
}

include '../includes/header.php';
?>
<!-- Cropper.js CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">

<?php include '../includes/navbar.php'; ?>

<div class="container-custom mt-4">
    <div class="mb-4">
        <h1 class="page-title">Profil Akun</h1>
        <p class="page-subtitle">Kelola informasi data diri dan foto profil abang</p>
    </div>

    <div class="row">
        <!-- Sidebar: Foto Profil -->
        <div class="col-lg-4 mb-4">
            <div class="card card-custom shadow-sm text-center p-4 h-100">
                <div class="mb-3 position-relative d-inline-block mx-auto">
                    <?php 
                    $foto = !empty($user['foto_profil']) ? '../assets/foto_profil/' . $user['foto_profil'] : 'https://ui-avatars.com/api/?name=' . urlencode($user['full_name']) . '&background=E8A796&color=fff&size=128';
                    ?>
                    <img src="<?php echo $foto; ?>" alt="Profile" class="rounded-circle shadow" id="profileDisplay" style="width: 160px; height: 160px; object-fit: cover; border: 5px solid white;">
                </div>
                
                <h4 class="fw-bold mb-1 mt-2"><?php echo htmlspecialchars($user['full_name']); ?></h4>
                <p class="text-muted small mb-3">#<?php echo htmlspecialchars($user['username']); ?></p>
                <div class="d-flex justify-content-center gap-2 mb-4">
                    <span class="badge bg-peach px-3 py-2 rounded-pill"><?php echo ucfirst($user['role']); ?></span>
                </div>

                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-peach py-2" onclick="document.getElementById('inputPhoto').click();">
                        <i class="bi bi-camera me-2"></i> Ganti Foto Profil
                    </button>
                    
                    <?php if (!empty($user['foto_profil'])): ?>
                    <form action="" method="POST" onsubmit="return confirm('Hapus foto profil?');">
                        <input type="hidden" name="delete_photo" value="1">
                        <button type="submit" class="btn btn-outline-danger w-100 py-2">
                            <i class="bi bi-trash me-2"></i> Hapus Foto
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
                
                <p class="small text-muted mt-3 mb-0">Hanya JPG, PNG, atau WEBP. Maksimal 20MB.</p>
                <input type="file" id="inputPhoto" class="d-none" accept="image/png, image/jpeg, image/webp">
            </div>
        </div>

        <!-- Form Updates -->
        <div class="col-lg-8">
            <div class="card card-custom shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-person-lines-fill me-2 text-peach"></i>Informasi Pribadi</h5>
                </div>
                <div class="card-body p-4 pt-0">
                    <form action="" method="POST" id="profileForm">
                        <input type="hidden" name="update_profile" value="1">
                        <input type="hidden" name="cropped_image" id="croppedImageData">
                        
                        <div class="row g-3">
                            <div class="col-md-6 text-start">
                                <label class="form-label small fw-bold text-muted mb-1">Username</label>
                                <input type="text" class="form-control form-control-custom bg-light" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                            </div>
                            <div class="col-md-6 text-start">
                                <label class="form-label small fw-bold text-muted mb-1">Nama Lengkap</label>
                                <input type="text" name="full_name" class="form-control form-control-custom" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-peach px-4 py-2">
                                    <i class="bi bi-check-circle me-1"></i> Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card card-custom shadow-sm">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-shield-lock-fill me-2 text-peach"></i>Keamanan Akun</h5>
                </div>
                <div class="card-body p-4 pt-0">
                    <form action="" method="POST">
                        <input type="hidden" name="change_password" value="1">
                        <div class="row g-3">
                            <div class="col-12 text-start">
                                <label class="form-label small fw-bold text-muted mb-1">Password Lama</label>
                                <div class="password-field-container">
                                    <input type="password" name="old_password" class="form-control form-control-custom" placeholder="Masukkan password saat ini" required>
                                    <i class="bi bi-eye password-toggle"></i>
                                </div>
                            </div>
                            <div class="col-md-6 text-start">
                                <label class="form-label small fw-bold text-muted mb-1">Password Baru</label>
                                <div class="password-field-container">
                                    <input type="password" name="new_password" class="form-control form-control-custom" placeholder="Min. 6 karakter" required>
                                    <i class="bi bi-eye password-toggle"></i>
                                </div>
                            </div>
                            <div class="col-md-6 text-start">
                                <label class="form-label small fw-bold text-muted mb-1">Konfirmasi Password</label>
                                <div class="password-field-container">
                                    <input type="password" name="confirm_password" class="form-control form-control-custom" placeholder="Ulangi password baru" required>
                                    <i class="bi bi-eye password-toggle"></i>
                                </div>
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-outline-peach px-4 py-2">
                                    <i class="bi bi-key me-1"></i> Ganti Password
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Crop -->
<div class="modal fade" id="cropModal" tabindex="-1" aria-labelledby="cropModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header border-0 bg-light p-4">
                <h5 class="modal-title fw-bold" id="cropModalLabel">Potong Foto Profil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="img-container mb-3" style="max-height: 500px;">
                    <img id="cropImage" src="" alt="To Crop" style="max-width: 100%;">
                </div>
                <div class="alert alert-info py-2 small mb-0">
                    <i class="bi bi-info-circle me-1"></i> Seret dan atur ukuran kotak untuk menentukan area foto.
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light px-4 py-2 border" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-peach px-5 py-2" id="btnApplyCrop">Potong & Gunakan</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let cropper;
        const inputPhoto = document.getElementById('inputPhoto');
        const cropImage = document.getElementById('cropImage');
        const cropModalEl = document.getElementById('cropModal');
        const cropModal = new bootstrap.Modal(cropModalEl);
        const btnApplyCrop = document.getElementById('btnApplyCrop');
        const croppedImageData = document.getElementById('croppedImageData');
        const profileForm = document.getElementById('profileForm');

        if (inputPhoto) {
            inputPhoto.addEventListener('change', function(e) {
                const files = e.target.files;
                if (files && files.length > 0) {
                    const file = files[0];
                    
                    // Validasi Ukuran (20MB)
                    if (file.size > 20 * 1024 * 1024) {
                        Swal.fire('Gagal!', 'Ukuran foto maksimal 20MB bang!', 'warning');
                        inputPhoto.value = '';
                        return;
                    }

                    // Validasi Tipe
                    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                        Swal.fire('Gagal!', 'Format file harus JPG, PNG, atau WEBP ya!', 'warning');
                        inputPhoto.value = '';
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        cropImage.src = e.target.result;
                        cropModal.show();
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        cropModalEl.addEventListener('shown.bs.modal', function() {
            cropper = new Cropper(cropImage, {
                aspectRatio: 1,
                viewMode: 2,
                dragMode: 'move',
                autoCropArea: 1,
                restore: false,
                guides: true,
                center: true,
                highlight: false,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false,
            });
        });

        cropModalEl.addEventListener('hidden.bs.modal', function() {
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
            inputPhoto.value = '';
        });

        if (btnApplyCrop) {
            btnApplyCrop.addEventListener('click', function() {
                if (!cropper) return;
                
                const canvas = cropper.getCroppedCanvas({
                    width: 600,
                    height: 600,
                });
                
                croppedImageData.value = canvas.toDataURL('image/jpeg', 0.9);
                cropModal.hide();
                
                // Langsung submit form
                profileForm.submit();
            });
        }

        <?php if ($swal_title): ?>
        Swal.fire({
            title: '<?php echo $swal_title; ?>',
            text: '<?php echo $swal_text; ?>',
            icon: '<?php echo $swal_icon; ?>',
            confirmButtonColor: '#ff9a9e'
        });
        <?php endif; ?>
    });
</script>

<style>
.bg-peach { background-color: var(--primary-peach) !important; color: white !important; }
.text-peach { color: var(--primary-peach) !important; }
.card-custom { border-radius: 20px !important; border: none; }
.form-control-custom { border-radius: 12px; height: 48px; border: 1px solid #eee; }
.form-control-custom:focus { border-color: var(--primary-peach); box-shadow: 0 0 0 0.25rem rgba(255, 154, 158, 0.1); }
.btn-peach { background: linear-gradient(135deg, var(--primary-peach), var(--secondary-peach)); border: none; color: white; border-radius: 12px; font-weight: 600; transition: all 0.3s ease; }
.btn-peach:hover { transform: translateY(-1px); box-shadow: 0 4px 15px rgba(255, 154, 158, 0.3); color: white; opacity: 0.9; }
.btn-outline-peach { border: 2px solid var(--primary-peach); color: var(--primary-peach); border-radius: 12px; font-weight: 600; }
.btn-outline-peach:hover { background-color: var(--primary-peach); color: white; }
.img-container { text-align: center; background-color: #f8f9fa; border-radius: 10px; padding: 10px; }
</style>

<?php 
ob_end_flush();
include '../includes/footer.php'; 
?>
