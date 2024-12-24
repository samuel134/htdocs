<?php
require_once '../config.php';

if (isset($_POST['search'])) {
    $searchTerm = $_POST['searchTerm'];
    $stmt = $conn1->prepare("SELECT * FROM [PS_GameData].[dbo].[Chars] WHERE CharacterName LIKE ?");
    $stmt->execute(["%$searchTerm%"]);
    $characters = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $characters = [];
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
    <form method="POST" action="buscachar.php">
        <input type="text" name="searchTerm" placeholder="Enter character name" required>
        <button type="submit" name="search">Search</button>
    </form>

    <?php if (!empty($characters)): ?>
        <h2>Search Results:</h2>
        <ul>
            <?php foreach ($characters as $character): ?>
                <li><?php echo htmlspecialchars($character['CharacterName']); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php elseif (isset($_POST['search'])): ?>
        <p>No characters found.</p>
    <?php endif; ?>
</body>
</html>