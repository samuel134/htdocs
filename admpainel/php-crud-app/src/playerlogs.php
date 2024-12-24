<?php
require_once '../config.php';

$conn = new PDO($dsn, $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$query = "SELECT * FROM player_logs ORDER BY log_time DESC";
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
</head>
<body>
    <h1>Player Activity Logs</h1>
    <table border="1">
        <thead>
            <tr>
                <th>Log ID</th>
                <th>User UID</th>
                <th>Action</th>
                <th>Log Time</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?php echo htmlspecialchars($log['log_id']); ?></td>
                    <td><?php echo htmlspecialchars($log['user_uid']); ?></td>
                    <td><?php echo htmlspecialchars($log['action']); ?></td>
                    <td><?php echo htmlspecialchars($log['log_time']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>