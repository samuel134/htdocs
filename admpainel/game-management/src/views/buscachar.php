<?php
// buscachar.php

require_once '../config/database.php';
require_once '../controllers/PlayerController.php';

$playerController = new PlayerController();

$searchResults = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $searchTerm = $_POST['searchTerm'];
    $searchResults = $playerController->searchCharacters($searchTerm);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Characters</title>
</head>
<body>
    <h1>Search Characters</h1>
    <form method="POST" action="">
        <input type="text" name="searchTerm" placeholder="Enter character name" required>
        <button type="submit">Search</button>
    </form>

    <?php if (!empty($searchResults)): ?>
        <h2>Search Results:</h2>
        <ul>
            <?php foreach ($searchResults as $character): ?>
                <li><?php echo htmlspecialchars($character['name']); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</body>
</html>