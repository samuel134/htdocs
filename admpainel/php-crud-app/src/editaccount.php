<?php
require_once '../config.php';

if (isset($_GET['id'])) {
    $userId = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['id'];
    $username = $_POST['username'];
    $email = $_POST['email'];

    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
    $stmt->execute([$username, $email, $userId]);

    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Account</title>
</head>
<body>
    <h1>Edit Account</h1>
    <form action="editaccount.php" method="POST">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($user['id']); ?>">
        <label for="username">Username:</label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
        <br>
        <label for="email">Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        <br>
        <input type="submit" value="Update Account">
    </form>
    <a href="index.php">Cancel</a>
</body>
</html>