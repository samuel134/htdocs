<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = $_POST['user_id'];
    $pointsToAdd = $_POST['points'];

    // Validate input
    if (!empty($userId) && is_numeric($pointsToAdd)) {
        $sql = "UPDATE [PS_GameData].[dbo].[Users] SET Points = Points + ? WHERE UserID = ?";
        $stmt = $conn1->prepare($sql);
        $stmt->execute([$pointsToAdd, $userId]);

        header("Location: addpontos.php?success=1");
        exit();
    } else {
        $error = "Invalid input.";
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
    <h1>Add Points to User</h1>
    <?php if (isset($error)): ?>
        <p style="color:red;"><?php echo $error; ?></p>
    <?php endif; ?>
    <?php if (isset($_GET['success'])): ?>
        <p style="color:green;">Points added successfully!</p>
    <?php endif; ?>
    <form method="POST" action="addpontos.php">
        <label for="user_id">User ID:</label>
        <input type="text" name="user_id" required>
        <br>
        <label for="points">Points to Add:</label>
        <input type="number" name="points" required>
        <br>
        <input type="submit" value="Add Points">
    </form>
</body>
</html>