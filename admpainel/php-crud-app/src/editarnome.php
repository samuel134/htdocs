<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $UserUID = $_POST['UserUID'];
    $newName = $_POST['newName'];

    $sql = "UPDATE [PS_GameData].[dbo].[Chars] SET Name = ? WHERE UserUID = ?";
    $stmt = $conn1->prepare($sql);
    $stmt->execute([$newName, $UserUID]);

    header("Location: index.php");
    exit();
}

$UserUID = $_GET['UserUID'];
$sql = "SELECT Name FROM [PS_GameData].[dbo].[Chars] WHERE UserUID = ?";
$stmt = $conn1->prepare($sql);
$stmt->execute([$UserUID]);
$character = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Character Name</title>
</head>
<body>
    <h1>Edit Character Name</h1>
    <form method="POST" action="editarnome.php">
        <input type="hidden" name="UserUID" value="<?php echo htmlspecialchars($UserUID); ?>">
        <label for="newName">Current Name: <?php echo htmlspecialchars($character['Name']); ?></label><br>
        <label for="newName">New Name:</label>
        <input type="text" name="newName" required><br>
        <input type="submit" value="Update Name">
    </form>
    <a href="index.php">Cancel</a>
</body>
</html>