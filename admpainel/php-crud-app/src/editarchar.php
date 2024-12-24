<?php
require_once '../config.php';

if (isset($_GET['id'])) {
    $characterId = $_GET['id'];

    // Fetch current character details
    $stmt = $conn1->prepare("SELECT * FROM [PS_GameData].[dbo].[Chars] WHERE CharacterID = ?");
    $stmt->execute([$characterId]);
    $character = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$character) {
        die("Character not found.");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $newName = $_POST['name'];
        $newLevel = $_POST['level'];
        // Add more fields as necessary

        // Update character details
        $updateStmt = $conn1->prepare("UPDATE [PS_GameData].[dbo].[Chars] SET Name = ?, Level = ? WHERE CharacterID = ?");
        $updateStmt->execute([$newName, $newLevel, $characterId]);

        header("Location: buscachar.php");
        exit();
    }
} else {
    die("Invalid request.");
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
    <form method="POST">
        <label for="name">Character Name:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($character['Name']); ?>" required>
        
        <label for="level">Character Level:</label>
        <input type="number" id="level" name="level" value="<?php echo htmlspecialchars($character['Level']); ?>" required>
        
        <!-- Add more fields as necessary -->

        <button type="submit">Update Character</button>
    </form>
    <a href="buscachar.php">Cancel</a>
</body>
</html>