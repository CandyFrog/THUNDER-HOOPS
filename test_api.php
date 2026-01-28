<?php
// test_api.php - File untuk testing API tanpa Arduino
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test API - Basketball Arcade</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card card-custom">
                    <div class="card-header-custom">
                        🧪 Test API Arduino
                    </div>
                    <div class="card-body">
                        <form id="testForm">
                            <div class="mb-3">
                                <label class="form-label">Player 1 Score</label>
                                <input type="number" class="form-control form-control-custom" id="player1" value="15" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Player 2 Score</label>
                                <input type="number" class="form-control form-control-custom" id="player2" value="12" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Game Duration (seconds)</label>
                                <input type="number" class="form-control form-control-custom" id="duration" value="120" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <input type="text" class="form-control form-control-custom" id="notes" value="Test from web">
                            </div>
                            <button type="submit" class="btn btn-peach w-100">
                                <i class="bi bi-send"></i> Send to API
                            </button>
                        </form>
                        
                        <div id="result" class="mt-4" style="display: none;">
                            <h5>Response:</h5>
                            <pre id="responseText" style="background: #f5f5f5; padding: 1rem; border-radius: 10px; overflow-x: auto;"></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('testForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const data = {
                player1_score: parseInt(document.getElementById('player1').value),
                player2_score: parseInt(document.getElementById('player2').value),
                game_duration: parseInt(document.getElementById('duration').value),
                notes: document.getElementById('notes').value
            };
            
            try {
                const response = await fetch('api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                document.getElementById('result').style.display = 'block';
                document.getElementById('responseText').textContent = JSON.stringify(result, null, 2);
            } catch(error) {
                document.getElementById('result').style.display = 'block';
                document.getElementById('responseText').textContent = 'Error: ' + error.message;
            }
        });
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>