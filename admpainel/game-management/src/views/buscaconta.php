<?php
// buscaconta.php

// Include database connection
require_once '../config/database.php';

// Initialize variables
$searchTerm = '';
$accounts = [];

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $searchTerm = $_POST['searchTerm'];

    // Prepare and execute the search query
    $sql = "SELECT * FROM accounts WHERE username LIKE :searchTerm";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['searchTerm' => '%' . $searchTerm . '%']);
    $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Accounts</title>
</head>
<body>
    <h1>Search Accounts</h1>
    <form method="POST" action="">
        <input type="text" name="searchTerm" placeholder="Enter username" value="<?php echo htmlspecialchars($searchTerm); ?>" required>
        <button type="submit">Search</button>
    </form>

    <?php if (!empty($accounts)): ?>
        <h2>Search Results:</h2>
        <ul>
            <?php foreach ($accounts as $account): ?>
                <li><?php echo htmlspecialchars($account['username']); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No accounts found.</p>
    <?php endif; ?>
</body>
</html>