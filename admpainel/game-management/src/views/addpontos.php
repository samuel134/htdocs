<?php
// addpontos.php

require_once '../config/database.php';
require_once '../controllers/PlayerController.php';

$playerController = new PlayerController();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $playerId = $_POST['player_id'];
    $points = $_POST['points'];

    if ($playerController->addPoints($playerId, $points)) {
        echo "Points added successfully!";
    } else {
        echo "Failed to add points.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Points</title>
</head>
<body>
    <h1>Add Points to Player</h1>
    <form method="POST" action="addpontos.php">
        <label for="player_id">Player ID:</label>
        <input type="text" id="player_id" name="player_id" required>
        <br>
        <label for="points">Points:</label>
        <input type="number" id="points" name="points" required>
        <br>
        <input type="submit" value="Add Points">
    </form>
</body>
</html>