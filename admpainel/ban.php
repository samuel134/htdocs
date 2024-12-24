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
$account = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $searchTerm = $_POST['searchTerm'] ?? '';
    $searchType = $_POST['searchType'] ?? 'userid';
    $banReason = $_POST['banReason'] ?? '';
    
    try {
        // Search for account
        if ($searchType === 'userid') {
            $stmt = $conn1->prepare("
                SELECT U.*, 
                       (SELECT COUNT(*) FROM [PS_GameData].[dbo].[Chars] WHERE UserUID = U.UserUID AND Del = 0) as CharCount
                FROM [PS_UserData].[dbo].[Users_Master] U 
                WHERE U.UserID = ?
            ");
        } else {
            $stmt = $conn1->prepare("
                SELECT U.*, 
                       (SELECT COUNT(*) FROM [PS_GameData].[dbo].[Chars] WHERE UserUID = U.UserUID AND Del = 0) as CharCount
                FROM [PS_UserData].[dbo].[Users_Master] U 
                WHERE U.UserUID = ?
            ");
        }
        $stmt->execute([$searchTerm]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($account) {
            if (isset($_POST['action']) && $_POST['action'] === 'ban') {
                // Ban account
                $stmt = $conn1->prepare("UPDATE [PS_UserData].[dbo].[Users_Master] SET Status = -5 WHERE UserUID = ?");
                $stmt->execute([$account['UserUID']]);

                // Log ban
                $stmt = $conn1->prepare("
                    INSERT INTO [PS_UserData].[dbo].[BanLog] 
                    (UserUID, BannedBy, BanReason, BanDate) 
                    VALUES (?, ?, ?, GETDATE())
                ");
                $stmt->execute([$account['UserUID'], $userUID, $banReason]);

                // Kick all characters
                $stmt = $conn1->prepare("UPDATE [PS_GameData].[dbo].[Chars] SET LoginStatus = 0 WHERE UserUID = ?");
                $stmt->execute([$account['UserUID']]);

                $message = "<div class='alert alert-success'>
                    <i class='mdi mdi-check-circle me-2'></i>Account successfully banned!
                    <div class='mt-2 small text-muted'>
                        Account: {$account['UserID']}<br>
                        Reason: {$banReason}
                    </div>
                </div>";

                // Refresh account data
                $stmt = $conn1->prepare("SELECT * FROM [PS_UserData].[dbo].[Users_Master] WHERE UserUID = ?");
                $stmt->execute([$account['UserUID']]);
                $account = $stmt->fetch(PDO::FETCH_ASSOC);
            } elseif (isset($_POST['action']) && $_POST['action'] === 'unban') {
                // Unban account
                $stmt = $conn1->prepare("UPDATE [PS_UserData].[dbo].[Users_Master] SET Status = 0 WHERE UserUID = ?");
                $stmt->execute([$account['UserUID']]);

                // Log unban
                $stmt = $conn1->prepare("
                    INSERT INTO [PS_UserData].[dbo].[BanLog] 
                    (UserUID, BannedBy, BanReason, BanDate, UnbanDate) 
                    VALUES (?, ?, 'Account unbanned', GETDATE(), GETDATE())
                ");
                $stmt->execute([$account['UserUID'], $userUID]);

                $message = "<div class='alert alert-success'>
                    <i class='mdi mdi-check-circle me-2'></i>Account successfully unbanned!
                    <div class='mt-2 small text-muted'>
                        Account: {$account['UserID']}
                    </div>
                </div>";

                // Refresh account data
                $stmt = $conn1->prepare("SELECT * FROM [PS_UserData].[dbo].[Users_Master] WHERE UserUID = ?");
                $stmt->execute([$account['UserUID']]);
                $account = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } else {
            $message = "<div class='alert alert-danger'>
                <i class='mdi mdi-alert me-2'></i>Account not found!
            </div>";
        }
    } catch (PDOException $e) {
        $message = "<div class='alert alert-danger'>
            <i class='mdi mdi-alert me-2'></i>Database error: " . $e->getMessage() . "
        </div>";
    }
}

// Get recent bans
try {
    $stmt = $conn1->prepare("
        SELECT TOP 10 
            B.*, 
            U.UserID as BannedUserID,
            A.UserID as AdminUserID
        FROM [PS_UserData].[dbo].[BanLog] B
        JOIN [PS_UserData].[dbo].[Users_Master] U ON B.UserUID = U.UserUID
        JOIN [PS_UserData].[dbo].[Users_Master] A ON B.BannedBy = A.UserUID
        ORDER BY B.BanDate DESC
    ");
    $stmt->execute();
    $recentBans = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recentBans = [];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ban Management - Shaiya Admin</title>
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
                <a class="nav-link active" href="ban.php">
                    <i class="mdi mdi-wall"></i>
                    <span>Ban</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="ress.php">
                    <i class="mdi mdi-account-convert"></i>
                    <span>Ress-Characters</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="playerlogs.php">
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
                <!-- Search Account Form -->
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center">
                        <i class="mdi mdi-account-search me-2"></i>
                        <h5 class="mb-0">Search Account</h5>
                    </div>
                    <div class="card-body">
                        <?php echo $message; ?>
                        
                        <form method="POST" class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Search Term</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="mdi mdi-account"></i>
                                    </span>
                                    <input type="text" name="searchTerm" class="form-control" required
                                           placeholder="Enter username or ID"
                                           value="<?php echo htmlspecialchars($_POST['searchTerm'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Search Type</label>
                                <select name="searchType" class="form-select">
                                    <option value="userid" <?php echo ($_POST['searchType'] ?? '') === 'userid' ? 'selected' : ''; ?>>Username</option>
                                    <option value="useruid" <?php echo ($_POST['searchType'] ?? '') === 'useruid' ? 'selected' : ''; ?>>User ID</option>
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

                <?php if ($account): ?>
                <!-- Account Actions -->
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <i class="mdi mdi-account-details me-2"></i>
                        <h5 class="mb-0">Account Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <h6 class="text-muted mb-1">Username</h6>
                                    <h4><?php echo htmlspecialchars($account['UserID']); ?></h4>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <h6 class="text-muted mb-1">Status</h6>
                                    <h4>
                                        <?php if ($account['Status'] == -5): ?>
                                            <span class="badge bg-danger">Banned</span>
                                        <?php elseif ($account['Status'] == 16): ?>
                                            <span class="badge bg-success">Admin</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary">Active</span>
                                        <?php endif; ?>
                                    </h4>
                                </div>
                            </div>
                        </div>

                        <?php if ($account['Status'] != -5): ?>
                        <form method="POST" class="mb-3">
                            <input type="hidden" name="searchTerm" value="<?php echo htmlspecialchars($_POST['searchTerm'] ?? ''); ?>">
                            <input type="hidden" name="searchType" value="<?php echo htmlspecialchars($_POST['searchType'] ?? ''); ?>">
                            <input type="hidden" name="action" value="ban">
                            
                            <div class="mb-3">
                                <label class="form-label">Ban Reason</label>
                                <textarea name="banReason" class="form-control" rows="3" required
                                          placeholder="Enter reason for ban"></textarea>
                            </div>

                            <button type="submit" class="btn btn-danger" 
                                    onclick="return confirm('Are you sure you want to ban this account?')">
                                <i class="mdi mdi-block-helper me-2"></i>Ban Account
                            </button>
                        </form>
                        <?php else: ?>
                        <form method="POST">
                            <input type="hidden" name="searchTerm" value="<?php echo htmlspecialchars($_POST['searchTerm'] ?? ''); ?>">
                            <input type="hidden" name="searchType" value="<?php echo htmlspecialchars($_POST['searchType'] ?? ''); ?>">
                            <input type="hidden" name="action" value="unban">
                            
                            <button type="submit" class="btn btn-success"
                                    onclick="return confirm('Are you sure you want to unban this account?')">
                                <i class="mdi mdi-account-check me-2"></i>Unban Account
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="col-md-6">
                <!-- Recent Bans -->
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <i class="mdi mdi-history me-2"></i>
                        <h5 class="mb-0">Recent Bans</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Account</th>
                                        <th>Admin</th>
                                        <th>Reason</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentBans as $ban): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($ban['BannedUserID']); ?></td>
                                            <td><?php echo htmlspecialchars($ban['AdminUserID']); ?></td>
                                            <td><?php echo htmlspecialchars($ban['BanReason']); ?></td>
                                            <td><?php echo date('Y-m-d H:i', strtotime($ban['BanDate'])); ?></td>
                                            <td>
                                                <?php if ($ban['UnbanDate']): ?>
                                                    <span class="badge bg-success">Unbanned</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Banned</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($recentBans)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center">No recent bans</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
