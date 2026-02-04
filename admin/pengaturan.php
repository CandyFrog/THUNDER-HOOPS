<?php
// admin/pengaturan.php
require_once __DIR__ . '/../midleware/cek_login.php';
require_once __DIR__ . '/../config/koneksi.php';

// Check if admin
if($_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$page_title = "Pengaturan - Basketball Arcade";
$message = "";
$message_type = "";

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_duration'])) {
    $new_duration = (int)$_POST['match_duration'];
    $is_ajax = isset($_POST['ajax']) && $_POST['ajax'] == 1;
    
    if ($new_duration > 0) {
        $query = "UPDATE settings SET value = ? WHERE name = 'match_duration'";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $new_duration);
        
        if ($stmt->execute()) {
            if ($is_ajax) {
                echo json_encode(['status' => 'success', 'message' => 'Durasi pertandingan berhasil diperbarui!']);
                exit();
            }
            $message = "Durasi pertandingan berhasil diperbarui!";
            $message_type = "success";
        } else {
            if ($is_ajax) {
                echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui durasi.']);
                exit();
            }
            $message = "Gagal memperbarui durasi.";
            $message_type = "danger";
        }
    } else {
        if ($is_ajax) {
            echo json_encode(['status' => 'error', 'message' => 'Durasi harus lebih dari 0.']);
            exit();
        }
        $message = "Durasi harus lebih dari 0.";
        $message_type = "warning";
    }
}

// Get current duration
$query = "SELECT value FROM settings WHERE name = 'match_duration'";
$result = $conn->query($query);
$settings = $result->fetch_assoc();
$current_duration = $settings['value'] ?? 60;

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
?>

<div class="container-custom mt-4">
    <div class="mb-4">
        <h1 class="page-title">Pengaturan Game</h1>
        <p class="page-subtitle">Atur parameter teknis untuk alat Arduino THUNDER-HOOPS</p>
    </div>

    <div id="ajax-alert" style="display: none;">
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <span id="ajax-message"></span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>

    <?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
        <i class="bi bi-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?>-fill me-2"></i>
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card card-custom shadow-sm border-0">
                <div class="card-header-custom p-3 bg-white border-bottom">
                    <span class="fw-bold"><i class="bi bi-clock-history me-2 text-peach"></i>Durasi Pertandingan</span>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="">
                        <div class="mb-4">
                            <label for="match_duration" class="form-label small fw-bold text-muted mb-2">Lama Waktu (Detik)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 px-3"><i class="bi bi-stopwatch text-muted"></i></span>
                                <input type="number" class="form-control form-control-custom border-start-0 ps-0 bg-light" 
                                       id="match_duration" name="match_duration" 
                                       value="<?php echo htmlspecialchars($current_duration); ?>" required min="1">
                            </div>
                            <div class="form-text mt-2">
                                <i class="bi bi-info-circle me-1"></i> Waktu ini akan dikirimkan ke Arduino sebagai batasan waktu bermain.
                            </div>
                        </div>
                        
                        <button type="submit" name="update_duration" id="btn-save" class="btn btn-peach w-100 py-2">
                            <i class="bi bi-save me-1"></i> <span id="btn-text">Simpan Perubahan</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-custom shadow-sm border-0">
                <div class="card-header-custom p-3 bg-white border-bottom">
                    <span class="fw-bold"><i class="bi bi-cpu me-2 text-peach"></i>Informasi Integrasi</span>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted">Gunakan endpoint berikut di Arduino untuk mengambil pengaturan terbaru:</p>
                    <div class="bg-light p-3 rounded-4 border">
                        <code id="api-url" class="text-peach fw-bold"><?php 
                            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                            $host = $_SERVER['HTTP_HOST'];
                            $uri = str_replace('admin/pengaturan.php', 'api/get_settings.php', $_SERVER['REQUEST_URI']);
                            echo $protocol . "://" . $host . $uri;
                        ?></code>
                        <button class="btn btn-sm btn-light ms-2" onclick="copyApiUrl()"><i class="bi bi-clipboard"></i></button>
                    </div>
                    <ul class="mt-3 small text-muted">
                        <li>Arduino harus melakukan polling ke URL ini sebelum game dimulai.</li>
                        <li>Format data yang dikembalikan adalah teks mentah (angka) atau JSON.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelector('form').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-save');
    const btnText = document.getElementById('btn-text');
    const alertDiv = document.getElementById('ajax-alert');
    const messageSpan = document.getElementById('ajax-message');
    const formData = new FormData(this);
    formData.append('ajax', '1');
    formData.append('update_duration', '1');

    btn.disabled = true;
    btnText.innerText = 'Menyimpan...';

    fetch('pengaturan.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        alertDiv.style.display = 'block';
        messageSpan.innerText = data.message;
        const alertInner = alertDiv.querySelector('.alert');
        alertInner.className = `alert alert-${data.status === 'success' ? 'success' : 'danger'} alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4`;
        
        btn.disabled = false;
        btnText.innerText = 'Simpan Perubahan';
        
        if(data.status === 'success') {
            setTimeout(() => {
                alertDiv.style.display = 'none';
            }, 3000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        btn.disabled = false;
        btnText.innerText = 'Simpan Perubahan';
    });
});

function copyApiUrl() {
    const url = document.getElementById('api-url').innerText;
    navigator.clipboard.writeText(url).then(() => {
        alert('URL API berhasil disalin!');
    });
}
</script>

<style>
.bg-peach { background-color: var(--primary-peach) !important; }
.border-peach { border-color: var(--primary-peach) !important; }
.text-peach { color: var(--primary-peach) !important; }
.card-custom { border-radius: 20px !important; }
.btn-peach { background: linear-gradient(135deg, var(--primary-peach), var(--secondary-peach)); border: none; color: white; border-radius: 12px; font-weight: 600; transition: all 0.3s ease; height: 48px; }
.btn-peach:hover { transform: translateY(-1px); box-shadow: 0 4px 15px rgba(255, 154, 158, 0.3); color: white; opacity: 0.9; }
.form-control-custom { border-radius: 12px; height: 48px; border: 1px solid #eee; }
.form-control-custom:focus { border-color: var(--primary-peach); box-shadow: 0 0 0 0.25rem rgba(255, 154, 158, 0.1); background-color: #fff !important; }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
