<?php
// midleware/cek_login.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Regenerate session ID periodically to prevent session fixation
if (!isset($_SESSION['last_regenerated'])) {
    session_regenerate_id(true);
    $_SESSION['last_regenerated'] = time();
} elseif (time() - $_SESSION['last_regenerated'] > 900) { // every 15 minutes
    session_regenerate_id(true);
    $_SESSION['last_regenerated'] = time();
}

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Validate CSRF token for POST requests.
 * Returns true if valid, false if not.
 */
function verify_csrf_token(): bool {
    $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (empty($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }
    return true;
}

/**
 * Get the CSRF token HTML hidden input
 */
function csrf_input(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
}
?>