<?php
// editaccount.php

require_once '../config/database.php';
require_once '../controllers/AccountController.php';

$accountController = new AccountController();
$accountId = $_GET['id'] ?? null;

if ($accountId) {
    $account = $accountController->getAccountById($accountId);
} else {
    // Handle error: account ID not provided
    header("Location: buscaconta.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updatedData = [
        'username' => $_POST['username'],
        'email' => $_POST['email'],
        'status' => $_POST['status'],
    ];
    
    $accountController->updateAccount($accountId, $updatedData);
    header("Location: buscaconta.php");
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
    <form method="POST">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($account['username']); ?>" required>
        
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($account['email']); ?>" required>
        
        <label for="status">Status:</label>
        <select id="status" name="status">
            <option value="active" <?php echo $account['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
            <option value="banned" <?php echo $account['status'] === 'banned' ? 'selected' : ''; ?>>Banned</option>
        </select>
        
        <button type="submit">Update Account</button>
    </form>
    <a href="buscaconta.php">Cancel</a>
</body>
</html>