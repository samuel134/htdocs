<?php
// ress.php - View for resurrecting players

// Include database connection
require_once '../config/database.php';

// Initialize variables
$players = [];

// Fetch players from the database for resurrection
try {
    $stmt = $conn->prepare("SELECT UserUID, PlayerName FROM Players WHERE IsDead = 1");
    $stmt->execute();
    $players = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching players: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resurrect Players</title>
</head>
<body>
    <h1>Resurrect Players</h1>
    <form action="ress_action.php" method="POST">
        <label for="player">Select Player to Resurrect:</label>
        <select name="player" id="player" required>
            <?php foreach ($players as $player): ?>
                <option value="<?php echo htmlspecialchars($player['UserUID']); ?>">
                    <?php echo htmlspecialchars($player['PlayerName']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Resurrect</button>
    </form>
</body>
</html>