<?php
// editarchar.php

require_once '../config/database.php';
require_once '../controllers/PlayerController.php';

$playerController = new PlayerController();
$player = null;

if (isset($_GET['id'])) {
    $player = $playerController->getPlayerById($_GET['id']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $playerController->updatePlayer($_POST);
    header("Location: /game-management/src/views/buscachar.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Character</title>
</head>
<body>
    <h1>Edit Character</h1>
    <?php if ($player): ?>
        <form action="editarchar.php" method="POST">
            <input type="hidden" name="id" value="<?php echo $player['id']; ?>">
            <label for="name">Character Name:</label>
            <input type="text" name="name" value="<?php echo $player['name']; ?>" required>
            <br>
            <label for="level">Level:</label>
            <input type="number" name="level" value="<?php echo $player['level']; ?>" required>
            <br>
            <input type="submit" value="Update Character">
        </form>
    <?php else: ?>
        <p>Character not found.</p>
    <?php endif; ?>
</body>
</html>