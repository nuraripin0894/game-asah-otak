<?php
session_start();

include('database/db_connect.php');

function getRandomWord($conn) {
    $query = "SELECT * FROM master_kata ORDER BY RANDOM() LIMIT 1";
    $result = pg_query($conn, $query);
    
    if ($result) {
        return pg_fetch_assoc($result);
    } else {
        die("Error fetching data: " . pg_last_error());
    }
}

if (!isset($_SESSION['game_state']) || isset($_POST['new_game'])) {
    $wordData = getRandomWord($conn);
    $_SESSION['game_state'] = [
        'word' => $wordData['kata'],
        'clue' => $wordData['clue'],
        'revealed' => [2, 6], 
        'score' => 0,
        'game_ended' => false
    ];
}

$gameState = &$_SESSION['game_state'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$gameState['game_ended'] && isset($_POST['guess'])) {
    $userGuess = $_POST['guess'];
    $word = $gameState['word'];
    
    for ($i = 0; $i < strlen($word); $i++) {
        if (in_array($i, $gameState['revealed'])) {
            continue; 
        }
        
        if (isset($userGuess[$i])) {
            if ($userGuess[$i] === $word[$i]) {
                $gameState['score'] += 10; 
            } else {
                $gameState['score'] -= 2;
            }
        } else {
            $gameState['score'] -= 2; 
        }
    }
    
    $gameState['game_ended'] = true;
}

$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Asah Otak</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Game Asah Otak</h1>

        <?php if (!empty($message)): ?>
        <div class="alert alert-info text-center"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if ($gameState['game_ended']): ?>
        <br>
        <div class="text-center">
            <h2>SELESAI</h2>
            <br>
            <p><strong>SCORE: </strong><?php echo $gameState['score']; ?></p>
            <p><strong>KATA: </strong> <?php echo $gameState['word']; ?></p>
            <br>
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#saveScoreModal">
                Simpan Score
            </button>
            <form method="post" class="mt-3">
                <button type="submit" name="new_game" class="btn btn-primary">Ulangi</button>
            </form>
        </div>
        <?php else: ?>
        <br>
        <p class="text-center"><strong>Clue:</strong> <?php echo $gameState['clue']; ?></p>
        <form method="post" class="mb-4">
            <div class=" d-flex justify-content-center">
                <?php for ($i = 0; $i < strlen($gameState['word']); $i++): ?>
                <input type="text" name="guess[<?php echo $i; ?>]" maxlength="1" class="form-control mx-1"
                    style="width: 40px;"
                    <?php echo in_array($i, $gameState['revealed']) ? 'value="' . $gameState['word'][$i] . '" readonly' : 'required'; ?>>
                <?php endfor; ?>
            </div>
            <br>
            <div class="text-center mt-3">
                <button type="submit" class="btn btn-primary">Jawab</button>
            </div>
        </form>
        <?php endif; ?>
    </div>

    <!-- Save Score Modal -->
    <div class="modal fade" id="saveScoreModal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel">Simpan Score</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="save_score.php">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="nama">Nama:</label>
                            <input type="text" class="form-control" id="nama" name="nama" placeholder="Nama" required>
                        </div>
                        <input type="hidden" name="score" value="<?php echo $gameState['score']; ?>">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>