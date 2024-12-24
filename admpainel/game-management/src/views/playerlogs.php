<?php
// playerlogs.php

// Include database connection
require_once '../config/database.php';

// Fetch player logs from the database
$query = "SELECT * FROM player_logs ORDER BY log_date DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Logs</title>
    <link rel="stylesheet" href="../public/styles.css">
</head>
<body>
    <h1>Player Logs</h1>
    <table>
        <thead>
            <tr>
                <th>Log ID</th>
                <th>Player UID</th>
                <th>Action</th>
                <th>Log Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?php echo htmlspecialchars($log['log_id']); ?></td>
                    <td><?php echo htmlspecialchars($log['player_uid']); ?></td>
                    <td><?php echo htmlspecialchars($log['action']); ?></td>
                    <td><?php echo htmlspecialchars($log['log_date']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>