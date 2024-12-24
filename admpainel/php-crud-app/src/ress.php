<?php
// ress.php - Handles the resurrection of characters

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $characterId = $_POST['character_id'];

    // Validate input
    if (!empty($characterId)) {
        // Update character status to alive
        $sql = "UPDATE [PS_GameData].[dbo].[Chars] SET Status = 'alive' WHERE CharacterID = ?";
        $stmt = $conn1->prepare($sql);
        $stmt->execute([$characterId]);

        // Redirect after processing
        header("Location: index.php?message=Character resurrected successfully");
        exit();
    } else {
        $error = "Character ID is required.";
    }
}

// Fetch characters for selection
$sql = "SELECT CharacterID, Name FROM [PS_GameData].[dbo].[Chars] WHERE Status = 'dead'";
$stmt = $conn1->prepare($sql);
$stmt->execute();
$deadCharacters = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resurrect Character</title>
</head>
<body>
    <h1>Resurrect Character</h1>
    <?php if (isset($error)): ?>
        <p style="color:red;"><?php echo $error; ?></p>
    <?php endif; ?>
    <form method="POST" action="">
        <label for="character_id">Select Character to Resurrect:</label>
        <select name="character_id" id="character_id" required>
            <option value="">--Select Character--</option>
            <?php foreach ($deadCharacters as $character): ?>
                <option value="<?php echo $character['CharacterID']; ?>"><?php echo $character['Name']; ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Resurrect</button>
    </form>
</body>
</html>