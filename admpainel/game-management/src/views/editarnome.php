<?php
// editarnome.php

require_once '../config/database.php';
require_once '../controllers/PlayerController.php';

$playerController = new PlayerController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $playerId = $_POST['player_id'];
    $newName = $_POST['new_name'];

    if ($playerController->updatePlayerName($playerId, $newName)) {
        echo "Player name updated successfully.";
    } else {
        echo "Failed to update player name.";
    }
}

$players = $playerController->getAllPlayers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Player Name</title>
</head>
<body>
    <h1>Edit Player Name</h1>
    <form method="POST" action="editarnome.php">
        <label for="player_id">Select Player:</label>
        <select name="player_id" id="player_id" required>
            <?php foreach ($players as $player): ?>
                <option value="<?= $player['id'] ?>"><?= $player['name'] ?></option>
            <?php endforeach; ?>
        </select>
        <br>
        <label for="new_name">New Name:</label>
        <input type="text" name="new_name" id="new_name" required>
        <br>
        <input type="submit" value="Update Name">
    </form>
</body>
</html>