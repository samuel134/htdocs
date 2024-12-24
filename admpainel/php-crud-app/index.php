<?php
require 'config.php';

// Fetch all users or characters for display
$query = "SELECT * FROM [PS_GameData].[dbo].[Chars]";
$stmt = $conn1->prepare($query);
$stmt->execute();
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP CRUD Application</title>
</head>
<body>
    <h1>Welcome to the PHP CRUD Application</h1>
    <nav>
        <ul>
            <li><a href="src/addpontos.php">Add Points</a></li>
            <li><a href="src/ban.php">Ban User</a></li>
            <li><a href="src/buscachar.php">Search Character</a></li>
            <li><a href="src/buscaconta.php">Search Account</a></li>
            <li><a href="src/editaccount.php">Edit Account</a></li>
            <li><a href="src/editarchar.php">Edit Character</a></li>
            <li><a href="src/editarnome.php">Rename Character</a></li>
            <li><a href="src/EnviarItens.php">Send Items</a></li>
            <li><a href="src/playerlogs.php">Player Logs</a></li>
            <li><a href="src/ress.php">Resurrect Character</a></li>
        </ul>
    </nav>

    <h2>Members List</h2>
    <table>
        <tr>
            <th>UserUID</th>
            <th>Name</th>
            <th>Login Status</th>
        </tr>
        <?php foreach ($members as $member): ?>
        <tr>
            <td><?php echo htmlspecialchars($member['UserUID']); ?></td>
            <td><?php echo htmlspecialchars($member['Name']); ?></td>
            <td><?php echo htmlspecialchars($member['LoginStatus']); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>