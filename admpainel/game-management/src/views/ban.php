<?php
// ban.php

require_once '../config/database.php';
require_once '../controllers/PlayerController.php';

$playerController = new PlayerController();
$players = $playerController->getAllPlayers(); // Assuming this method retrieves all players

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ban'])) {
    $playerId = $_POST['player_id'];
    $playerController->banPlayer($playerId); // Assuming this method bans a player
    header("Location: ban.php"); // Redirect to the same page after banning
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ban Player</title>
</head>
<body>
    <h1>Ban Player</h1>
    <form method="POST" action="">
        <label for="player_id">Select Player to Ban:</label>
        <select name="player_id" id="player_id" required>
            <?php foreach ($players as $player): ?>
                <option value="<?php echo $player['id']; ?>"><?php echo htmlspecialchars($player['name']); ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="ban">Ban Player</button>
    </form>
</body>
</html>