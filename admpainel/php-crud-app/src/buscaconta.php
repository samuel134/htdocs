<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];

    $stmt = $conn1->prepare("SELECT * FROM Users WHERE username = ?");
    $stmt->execute([$username]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search User Account</title>
</head>
<body>
    <h1>Search User Account</h1>
    <form method="POST" action="buscaconta.php">
        <input type="text" name="username" placeholder="Enter username" required>
        <button type="submit">Search</button>
    </form>

    <?php if (isset($account)): ?>
        <h2>Account Details</h2>
        <p>Username: <?php echo htmlspecialchars($account['username']); ?></p>
        <p>Email: <?php echo htmlspecialchars($account['email']); ?></p>
        <p>Status: <?php echo htmlspecialchars($account['status']); ?></p>
    <?php endif; ?>
</body>
</html>