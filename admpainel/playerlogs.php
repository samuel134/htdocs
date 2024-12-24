<?php
session_start();
include('config.php');

if (!isset($_SESSION['UserID'])) {
    header("Location: login.php");
    exit();
}

$userUID = $_SESSION['UserID'];

// Check user status
$query = "SELECT Status FROM [PS_UserData].[dbo].[Users_Master] WHERE UserUID = :userUID";
$stmt = $conn1->prepare($query);
$stmt->bindParam(':userUID', $userUID);
$stmt->execute();

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['Status'] != 16) {
    header("Location: login.php");
    exit();
}

$message = '';
$logs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $charName = $_POST['charName'] ?? '';
    $actionType = $_POST['actionType'] ?? '';

    try {
        $stmt = $conn1->prepare("
            SELECT c.CharName, cl.*
            FROM [PS_GameLog].[dbo].[ActionLog] cl
            INNER JOIN PS_GameData.dbo.Chars c ON cl.CharID = c.CharID
            WHERE c.CharName = :charName AND cl.ActionType = :actionType
            ORDER BY cl.ActionTime ASC
        ");
        $stmt->bindParam(':charName', $charName);
        $stmt->bindParam(':actionType', $actionType);
        $stmt->execute();
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $message = "<div class='alert alert-danger'>
            <i class='mdi mdi-alert me-2'></i>Database error: " . $e->getMessage() . "
        </div>";
    }
}

// Get total counts
$totalAccounts = $conn1->query("SELECT COUNT(*) AS total FROM [PS_UserData].[dbo].[Users_Master]")->fetchColumn();
$totalChars = $conn1->query("SELECT COUNT(*) AS totalchars FROM [PS_GameData].[dbo].[Chars] WHERE Del = 0")->fetchColumn();
$totalCharsOnline = $conn1->query("SELECT COUNT(*) AS totalcharsOnline FROM [PS_GameData].[dbo].[Chars] WHERE LoginStatus = 1")->fetchColumn();
$totalAccountsBanned = $conn1->query("SELECT COUNT(*) AS totalContasBanidas FROM [PS_UserData].[dbo].[Users_Master] WHERE Status = -5")->fetchColumn();
$totalDonations = $conn1->query("SELECT SUM(transaction_amount) AS totalDonates FROM [PS_UserData].[dbo].[pgtos] WHERE status = 'approved'")->fetchColumn();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Player Logs - Shaiya Admin</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@6.9.96/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="css/modern-admin.css" rel="stylesheet">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="brand">
            <h3>Shaiya Admin</h3>
        </div>
        
        <ul class="nav-menu">
            <li class="nav-item">
                <a class="nav-link" href="index.php">
                    <i class="mdi mdi-view-dashboard"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="addpontos.php">
                    <i class="mdi mdi-clipboard-text"></i>
                    <span>Donates</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="buscaconta.php">
                    <i class="mdi mdi-account-multiple"></i>
                    <span>Users</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="buscachar.php">
                    <i class="mdi mdi-sword"></i>
                    <span>Characters</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="EnviarItens.php">
                    <i class="mdi mdi-treasure-chest"></i>
                    <span>Items-Enviar</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="editarchar.php">
                    <i class="mdi mdi-account-edit"></i>
                    <span>Characters-Edit</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="editarnome.php">
                    <i class="mdi mdi-transcribe"></i>
                    <span>Name-Edit</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="ban.php">
                    <i class="mdi mdi-wall"></i>
                    <span>Ban</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="playerlogs.php">
                    <i class="mdi mdi-clipboard-text"></i>
                    <span>Player-Logs</span>
                </a>
            </li>
        </ul>
        
        <div class="user-profile">
            <img src="https://images.websim.ai/avatar/admin" alt="Profile">
            <span>Admin</span>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="row">
            <div class="col-md-6">
                <!-- Search Player Logs Form -->
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center">
                        <i class="mdi mdi-account-search me-2"></i>
                        <h5 class="mb-0">Search Player Logs</h5>
                    </div>
                    <div class="card-body">
                        <?php echo $message; ?>
                        
                        <form method="POST" class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Character Name</label>
                                <input type="text" name="charName" class="form-control" required
                                       placeholder="Enter character name">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Action Type</label>
                                <select name="actionType" class="form-select" required>
                                    <option value="115">Received Player Items</option>
                                    <option value="116">Sell to a Player</option>
                                    <option value="117">Trade between players</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="mdi mdi-magnify me-2"></i>Search
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Player Logs Table -->
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <i class="mdi mdi-history me-2"></i>
                        <h5 class="mb-0">Player Logs</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Character</th>
                                        <th>Map</th>
                                        <th>Level</th>
                                        <th>Date and Time</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($logs)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center">No logs found for the selected criteria.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($logs as $log): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($log['CharName']); ?></td>
                                                <td><?php echo htmlspecialchars($log['Map']); ?></td>
                                                <td><?php echo htmlspecialchars($log['Level']); ?></td>
                                                <td><?php echo date('Y-m-d H:i', strtotime($log['ActionTime'])); ?></td>
                                                <td><?php echo htmlspecialchars($log['ActionType']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <!-- Summary Stats -->
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <i class="mdi mdi-chart-bar me-2"></i>
                        <h5 class="mb-0">Summary Stats</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <h6 class="text-muted mb-1">Total Accounts</h6>
                                    <h4><?php echo htmlspecialchars($totalAccounts); ?></h4>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <h6 class="text-muted mb-1">Total Characters</h6>
                                    <h4><?php echo htmlspecialchars($totalChars); ?></h4>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <h6 class="text-muted mb-1">Total Online</h6>
                                    <h4><?php echo htmlspecialchars($totalCharsOnline); ?></h4>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <h6 class="text-muted mb-1">Total Banned</h6>
                                    <h4><?php echo htmlspecialchars($totalAccountsBanned); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
