<?php
ob_start();

require_once '../midleware/cek_login.php';
require_once '../config/koneksi.php';

if($_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$page_title = "Pengaturan - Basketball Arcade";
$message = "";
$message_type = "";

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
    } elseif (isset($_POST['update_wifi'])) {
        $wifi_ssid = trim($_POST['wifi_ssid'] ?? '');
        $wifi_pass = trim($_POST['wifi_password'] ?? '');

        if (!empty($wifi_ssid)) {
            $stmt1 = $conn->prepare("INSERT INTO settings (name, value) VALUES ('wifi_ssid', ?) ON DUPLICATE KEY UPDATE value = ?");
            $stmt1->bind_param("ss", $wifi_ssid, $wifi_ssid);
            $stmt1->execute();

            $stmt2 = $conn->prepare("INSERT INTO settings (name, value) VALUES ('wifi_password', ?) ON DUPLICATE KEY UPDATE value = ?");
            $stmt2->bind_param("ss", $wifi_pass, $wifi_pass);
            $stmt2->execute();

            $sync_val = "1";
            $stmt3 = $conn->prepare("INSERT INTO settings (name, value) VALUES ('wifi_sync_pending', ?) ON DUPLICATE KEY UPDATE value = ?");
            $stmt3->bind_param("ss", $sync_val, $sync_val);
            $stmt3->execute();

            if ($is_ajax) {
                ob_clean();
                echo json_encode(['status' => 'success', 'message' => 'Username dan Password WiFi berhasil diperbarui!']);
                exit();
            }
            $message = "Pengaturan WiFi berhasil diperbarui!";
            $message_type = "success";
        } else {
            if ($is_ajax) {
                ob_clean();
                echo json_encode(['status' => 'error', 'message' => 'SSID WiFi tidak boleh kosong!']);
                exit();
            }
            $message = "SSID WiFi tidak boleh kosong!";
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

$query = "SELECT name, value FROM settings WHERE name IN ('match_duration', 'wifi_ssid', 'wifi_password')";
$result = $conn->query($query);
$settings = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $settings[$row['name']] = $row['value'];
    }
}
$current_duration = $settings['match_duration'] ?? 60;
$current_wifi_ssid = $settings['wifi_ssid'] ?? '';
$current_wifi_pass = $settings['wifi_password'] ?? '';

include '../includes/header.php';
include '../includes/navbar.php';
?>

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
    
    <div class="row g-4">
        <div class="col-12 col-lg-6">
            <div class="card card-custom shadow-sm border-0 h-100">
                <div class="card-header-custom p-3 p-md-4 bg-white border-bottom">
                    <span class="fw-bold fs-5 text-dark"><i class="bi bi-clock-history me-2 text-peach"></i>Durasi Pertandingan</span>
                </div>
                <div class="card-body p-3 p-md-4 d-flex flex-column justify-content-between">
                    <form method="POST" action="" class="form-ajax-settings no-auto-loading h-100 d-flex flex-column justify-content-between">
                        <div class="mb-4">
                            <label for="match_duration" class="form-label fw-bold text-muted mb-2">Lama Waktu (Detik)</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light border-end-0 px-3"><i class="bi bi-stopwatch text-peach fs-4"></i></span>
                                <input type="number" class="form-control form-control-custom border-start-0 ps-0 bg-light fs-5 fw-bold" 
                                       id="match_duration" name="match_duration" 
                                       value="<?php echo htmlspecialchars($current_duration); ?>" required min="1">
                            </div>
                            <div class="form-text mt-3 text-muted">
                                <i class="bi bi-info-circle me-1 text-peach"></i> Waktu ini akan dikirimkan ke Arduino sebagai batasan waktu bermain.
                            </div>
                        </div>
                        
                        <button type="submit" name="update_duration" id="btn-save" class="btn btn-peach w-100 py-3 mt-auto fs-6">
                            <i class="bi bi-save me-2 fs-5"></i> <span id="btn-text">Simpan Perubahan</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card card-custom shadow-sm border-0 h-100">
                <div class="card-header-custom p-3 p-md-4 bg-white border-bottom">
                    <span class="fw-bold fs-5 text-dark"><i class="bi bi-wifi me-2 text-peach"></i>Pengaturan WiFi (SSID & Password)</span>
                </div>
                <div class="card-body p-3 p-md-4 d-flex flex-column justify-content-between">
                    <form method="POST" action="" class="form-ajax-wifi no-auto-loading h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="mb-3">
                                <label for="wifi_ssid" class="form-label fw-bold text-muted mb-2">Nama WiFi (SSID)</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light border-end-0 px-3"><i class="bi bi-wifi text-peach fs-4"></i></span>
                                    <input type="text" class="form-control form-control-custom border-start-0 ps-0 bg-light fs-6 fw-bold" 
                                           id="wifi_ssid" name="wifi_ssid" 
                                           value="<?php echo htmlspecialchars($current_wifi_ssid); ?>" placeholder="Nama WiFi (SSID)" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="wifi_password" class="form-label fw-bold text-muted mb-2">Password WiFi</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light border-end-0 px-3"><i class="bi bi-key text-peach fs-4"></i></span>
                                    <input type="password" class="form-control form-control-custom border-start-0 border-end-0 ps-0 bg-light fs-6 fw-bold" 
                                           id="wifi_password" name="wifi_password" 
                                           value="<?php echo htmlspecialchars($current_wifi_pass); ?>" placeholder="Password WiFi">
                                    <span class="input-group-text bg-light border-start-0 px-3" style="cursor: pointer;" onclick="toggleWifiPassword(this)">
                                        <i class="bi bi-eye text-peach fs-5" id="wifi-pass-icon"></i>
                                    </span>
                                </div>
                                <div class="form-text mt-3 text-muted">
                                    <i class="bi bi-info-circle me-1 text-peach"></i> Disimpan di database untuk referensi atau fitur sinkronisasi WiFi.
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" name="update_wifi" id="btn-save-wifi" class="btn btn-peach w-100 py-3 mt-auto fs-6">
                            <i class="bi bi-save me-2 fs-5"></i> <span id="btn-wifi-text">Simpan Pengaturan WiFi</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card card-custom shadow-sm border-0 h-100">
                <div class="card-header-custom p-3 p-md-4 bg-white border-bottom">
                    <span class="fw-bold fs-5 text-dark"><i class="bi bi-controller me-2 text-peach"></i>Kontrol Langsung</span>
                </div>
                <div class="card-body p-3 p-md-4 d-flex flex-column justify-content-between">
                    <?php
                    $q_cmd = "SELECT value FROM settings WHERE name = 'game_command'";
                    $r_cmd = $conn->query($q_cmd);
                    $curr_cmd = ($r_cmd && $row = $r_cmd->fetch_assoc()) ? $row['value'] : 'idle';
                    ?>
                    <div class="mb-4 text-center p-3 p-md-4 bg-light rounded-4 border border-1 border-light-subtle">
                        <span class="small text-muted d-block mb-2 fw-semibold fs-6">Status Perintah Saat Ini:</span>
                        <span class="badge <?php 
                            echo ($curr_cmd == 'start') ? 'bg-success' : (($curr_cmd == 'reset') ? 'bg-danger' : 'bg-secondary'); 
                        ?> p-3 px-4 rounded-pill fs-6 uppercase shadow-sm">
                            <i class="bi bi-broadcast me-2"></i> <?php echo strtoupper($curr_cmd); ?>
                        </span>
                    </div>
                    <div class="row g-3 mt-auto">
                        <div class="col-12 col-sm-6">
                            <button onclick="sendGameCommand('start')" class="btn btn-success w-100 py-3 py-md-4 rounded-4 shadow-sm border-0 btn-action">
                                <i class="bi bi-play-fill fs-2 d-block mb-1"></i>
                                <span class="fw-bold fs-6">MULAI GAME</span>
                            </button>
                        </div>
                        <div class="col-12 col-sm-6">
                            <button onclick="sendGameCommand('reset')" class="btn btn-danger w-100 py-3 py-md-4 rounded-4 shadow-sm border-0 btn-action">
                                <i class="bi bi-arrow-counterclockwise fs-2 d-block mb-1"></i>
                                <span class="fw-bold fs-6">RESET / RESTART</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleWifiPassword(element) {
    const input = document.getElementById('wifi_password');
    const icon = document.getElementById('wifi-pass-icon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

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

document.querySelector('.form-ajax-wifi').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-save-wifi');
    const originalBtnHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';

    const formData = new FormData(this);
    formData.append('ajax', '1');
    formData.append('update_wifi', '1');

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
        
        const badge = document.querySelector('.badge.uppercase');
        if(badge) {
            badge.innerText = cmd.toUpperCase();
            badge.className = `badge ${cmd === 'start' ? 'bg-success' : (cmd === 'reset' ? 'bg-danger' : 'bg-secondary')} p-3 px-4 rounded-pill fs-6 uppercase shadow-sm`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function updateStatusBadge() {
    fetch('../api/get_settings.php')
    .then(response => response.json())
    .then(data => {
        const badge = document.querySelector('.badge.uppercase');
        if(badge) {
            const cmd = data.game_command;
            badge.innerText = cmd.toUpperCase();
            badge.className = `badge ${cmd === 'start' ? 'bg-success' : (cmd === 'reset' ? 'bg-danger' : 'bg-secondary')} p-3 px-4 rounded-pill fs-6 uppercase shadow-sm`;
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
.btn-peach { background: linear-gradient(135deg, var(--primary-peach), var(--secondary-peach)); border: none; color: white; border-radius: 14px; font-weight: 600; transition: all 0.3s ease; height: 54px; }
.btn-peach:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(255, 154, 158, 0.4); color: white; opacity: 0.95; }
.form-control-custom { height: 54px; border: 1px solid #eee; }
.input-group > :first-child { border-top-left-radius: 14px !important; border-bottom-left-radius: 14px !important; }
.input-group > :last-child { border-top-right-radius: 14px !important; border-bottom-right-radius: 14px !important; }
.form-control-custom:focus { border-color: var(--primary-peach); box-shadow: 0 0 0 0.25rem rgba(255, 154, 158, 0.15); background-color: #fff !important; }
.btn-action { transition: all 0.3s ease; }
.btn-action:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.15) !important; }
</style>

<?php include '../includes/footer.php'; ?>
