<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ban_user'])) {
    $userUID = $_POST['userUID'];
    
    $sql = "UPDATE [PS_GameData].[dbo].[Chars] SET Status = 'banned' WHERE UserUID = ?";
    $stmt = $conn1->prepare($sql);
    
    if ($stmt->execute([$userUID])) {
        echo "User banned successfully.";
    } else {
        echo "Error banning user.";
    }
}

$sql = "SELECT UserUID, Username FROM [PS_GameData].[dbo].[Chars] WHERE Status != 'banned'";
$stmt = $conn1->prepare($sql);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ban User</title>
</head>
<body>
    <h1>Ban User</h1>
    <form method="POST" action="">
        <label for="userUID">Select User to Ban:</label>
        <select name="userUID" id="userUID" required>
            <?php foreach ($users as $user): ?>
                <option value="<?= htmlspecialchars($user['UserUID']) ?>"><?= htmlspecialchars($user['Username']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="ban_user">Ban User</button>
    </form>
</body>
</html>