<?php
// api.php - API untuk menerima data dari Arduino
header('Content-Type: application/json');
require_once 'config/database.php';

// Fungsi untuk send response
function sendResponse($success, $message, $data = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit();
}

// Hanya terima POST request
if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Method not allowed. Only POST is accepted.');
}

// Get POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Jika data bukan JSON, coba ambil dari POST biasa
if($data === null) {
    $data = $_POST;
}

// Validasi data yang diperlukan
if(!isset($data['player1_score']) || !isset($data['player2_score']) || !isset($data['game_duration'])) {
    sendResponse(false, 'Missing required fields: player1_score, player2_score, game_duration');
}

// Ambil data
$player1_score = (int)$data['player1_score'];
$player2_score = (int)$data['player2_score'];
$game_duration = (int)$data['game_duration'];
$notes = isset($data['notes']) ? trim($data['notes']) : 'From Arduino';

// Validasi nilai
if($player1_score < 0 || $player2_score < 0) {
    sendResponse(false, 'Scores cannot be negative');
}

if($game_duration <= 0) {
    sendResponse(false, 'Game duration must be greater than 0');
}

// Tentukan pemenang
if($player1_score > $player2_score) {
    $winner = 'Player 1';
} elseif($player2_score > $player1_score) {
    $winner = 'Player 2';
} else {
    $winner = 'Draw';
}

// Simpan ke database
try {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "INSERT INTO games (player1_score, player2_score, winner, game_duration, notes) 
              VALUES (:p1, :p2, :winner, :duration, :notes)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':p1', $player1_score);
    $stmt->bindParam(':p2', $player2_score);
    $stmt->bindParam(':winner', $winner);
    $stmt->bindParam(':duration', $game_duration);
    $stmt->bindParam(':notes', $notes);
    
    if($stmt->execute()) {
        $game_id = $db->lastInsertId();
        sendResponse(true, 'Game data saved successfully', [
            'game_id' => $game_id,
            'player1_score' => $player1_score,
            'player2_score' => $player2_score,
            'winner' => $winner,
            'game_duration' => $game_duration
        ]);
    } else {
        sendResponse(false, 'Failed to save game data');
    }
} catch(PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?>