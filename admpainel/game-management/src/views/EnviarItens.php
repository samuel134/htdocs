<?php
// EnviarItens.php

// Include database connection
require_once '../config/database.php';

// Initialize variables
$players = [];

// Fetch players from the database
try {
    $stmt = $conn->prepare("SELECT UserUID, PlayerName FROM Players");
    $stmt->execute();
    $players = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Itens</title>
</head>
<body>
    <h1>Enviar Itens para Jogadores</h1>
    <form action="processar_envio.php" method="POST">
        <label for="player">Selecionar Jogador:</label>
        <select name="player" id="player" required>
            <?php foreach ($players as $player): ?>
                <option value="<?php echo $player['UserUID']; ?>"><?php echo $player['PlayerName']; ?></option>
            <?php endforeach; ?>
        </select>

        <label for="item">Item:</label>
        <input type="text" name="item" id="item" required>

        <input type="submit" value="Enviar Item">
    </form>
</body>
</html>