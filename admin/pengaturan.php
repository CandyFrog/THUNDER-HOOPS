<?php
ob_start();
// admin/pengaturan.php

require_once '../midleware/cek_login.php';
require_once '../config/koneksi.php';

// Check if admin
if($_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$page_title = "Pengaturan - Basketball Arcade";
$message = "";
$message_type = "";

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $is_ajax = isset($_POST['ajax']) && $_POST['ajax'] == 1;

    if (isset($_POST['update_duration'])) {
        $new_duration = (int)$_POST['match_duration'];
        if ($new_duration > 0) {
            $query = "UPDATE settings SET value = ? WHERE name = 'match_duration'";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $new_duration);
            
            if ($stmt->execute()) {
                if ($is_ajax) {
                    ob_clean();
                    echo json_encode(['status' => 'success', 'message' => 'Durasi pertandingan berhasil diperbarui!']);
                    exit();
                }
                $message = "Durasi pertandingan berhasil diperbarui!";
                $message_type = "success";
            } else {
                if ($is_ajax) {
                    ob_clean();
                    echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan ke database: ' . $conn->error]);
                    exit();
                }
                $message = "Gagal menyimpan ke database!";
                $message_type = "danger";
            }
        } else {
            if ($is_ajax) {
                ob_clean();
                echo json_encode(['status' => 'error', 'message' => 'Durasi harus lebih dari 0 detik!']);
                exit();
            }
            $message = "Durasi harus lebih dari 0 detik!";
            $message_type = "warning";
        }
    } elseif (isset($_POST['game_command'])) {
        $cmd = $_POST['game_command'];
        $query = "UPDATE settings SET value = ? WHERE name = 'game_command'";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $cmd);
        
        if ($stmt->execute()) {
            if ($is_ajax) {
                ob_clean();
                $msg = ($cmd == 'start') ? "Sinyal MULAI dikirim ke Arduino!" : "Sinyal RESET dikirim ke Arduino!";
                echo json_encode(['status' => 'success', 'message' => $msg]);
                exit();
            }
        }
    }
}

// Get current duration
$query = "SELECT value FROM settings WHERE name = 'match_duration'";
$result = $conn->query($query);
$settings = $result->fetch_assoc();
$current_duration = $settings['value'] ?? 60;

include '../includes/header.php';
include '../includes/navbar.php';
?>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container-custom mt-4">
    <div class="mb-4">
        <h1 class="page-title">Pengaturan Game</h1>
        <p class="page-subtitle">Atur parameter teknis untuk alat Arduino THUNDER-HOOPS</p>
    </div>

    <div id="ajax-alert" style="display: none;"></div>

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
                    <form method="POST" action="" class="form-ajax-settings no-auto-loading">
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

            <!-- Kontrol Game Interaktif -->
            <div class="card card-custom shadow-sm border-0 mt-4">
                <div class="card-header-custom p-3 bg-white border-bottom">
                    <span class="fw-bold"><i class="bi bi-controller me-2 text-peach"></i>Kontrol Langsung</span>
                </div>
                <div class="card-body p-4">
                    <?php
                    $q_cmd = "SELECT value FROM settings WHERE name = 'game_command'";
                    $r_cmd = $conn->query($q_cmd);
                    $curr_cmd = ($r_cmd && $row = $r_cmd->fetch_assoc()) ? $row['value'] : 'idle';
                    ?>
                    <div class="mb-3 text-center">
                        <span class="small text-muted d-block mb-1">Status Perintah Saat Ini:</span>
                        <span class="badge <?php 
                            echo ($curr_cmd == 'start') ? 'bg-success' : (($curr_cmd == 'reset') ? 'bg-danger' : 'bg-secondary'); 
                        ?> p-2 px-3 rounded-pill uppercase">
                            <i class="bi bi-broadcast me-1"></i> <?php echo strtoupper($curr_cmd); ?>
                        </span>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <button onclick="sendGameCommand('start')" class="btn btn-success w-100 py-3 rounded-4 shadow-sm border-0">
                                <i class="bi bi-play-fill fs-4 d-block"></i>
                                <span class="fw-bold">MULAI GAME</span>
                            </button>
                        </div>
                        <div class="col-6">
                            <button onclick="sendGameCommand('reset')" class="btn btn-danger w-100 py-3 rounded-4 shadow-sm border-0">
                                <i class="bi bi-arrow-counterclockwise fs-4 d-block"></i>
                                <span class="fw-bold">RESET / RESTART</span>
                            </button>
                        </div>
                    </div>
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
// Handle Update Durasi
document.querySelector('.form-ajax-settings').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-save');
    const originalBtnHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';

    const formData = new FormData(this);
    formData.append('ajax', '1');
    formData.append('update_duration', '1');

    fetch('pengaturan.php', {
        method: 'POST',
        body: formData
    })
    .then(async response => {
        const text = await response.text();
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Raw Response:', text);
            throw new Error('Terjadi kesalahan pada format data server.');
        }
    })
    .then(data => {
        Swal.fire({
            title: data.status === 'success' ? 'Berhasil!' : 'Gagal!',
            text: data.message,
            icon: data.status,
            confirmButtonColor: '#ff9a9e'
        }).then(() => {
            btn.disabled = false;
            btn.innerHTML = originalBtnHTML;
        });
    })
    .catch(error => {
        Swal.fire({
            title: 'Error!',
            text: error.message,
            icon: 'error',
            confirmButtonColor: '#ff9a9e'
        }).then(() => {
            btn.disabled = false;
            btn.innerHTML = originalBtnHTML;
        });
    });
});

// Handle Game Commands (Start/Reset)
function sendGameCommand(cmd) {
    const formData = new FormData();
    formData.append('ajax', '1');
    formData.append('game_command', cmd);

    fetch('pengaturan.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        Swal.fire({
            title: 'Sinyal Terkirim!',
            text: data.message,
            icon: 'success',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
        
        // Update badge status di UI
        const badge = document.querySelector('.badge.uppercase');
        if(badge) {
            badge.innerText = cmd.toUpperCase();
            badge.className = `badge ${cmd === 'start' ? 'bg-success' : (cmd === 'reset' ? 'bg-danger' : 'bg-secondary')} p-2 px-3 rounded-pill uppercase`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Poll status perintah setiap 3 detik
function updateStatusBadge() {
    fetch('../api/get_settings.php')
    .then(response => response.json())
    .then(data => {
        const badge = document.querySelector('.badge.uppercase');
        if(badge) {
            const cmd = data.game_command;
            badge.innerText = cmd.toUpperCase();
            badge.className = `badge ${cmd === 'start' ? 'bg-success' : (cmd === 'reset' ? 'bg-danger' : 'bg-secondary')} p-2 px-3 rounded-pill uppercase`;
        }
    });
}
setInterval(updateStatusBadge, 3000);

function copyApiUrl() {
    const url = document.getElementById('api-url').innerText;
    navigator.clipboard.writeText(url).then(() => {
        Swal.fire({
            title: 'Tersalin!',
            text: 'URL API berhasil disalin ke clipboard.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
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

<?php include '../includes/footer.php'; ?>
