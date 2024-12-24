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
$accountData = null;

// Get account data
if (isset($_GET['uid'])) {
    $editUID = $_GET['uid'];
    try {
        // Get account data
        $stmt = $conn1->prepare("
            SELECT U.*, 
                   (SELECT COUNT(*) FROM [PS_GameData].[dbo].[Chars] WHERE UserUID = U.UserUID AND Del = 0) as CharCount,
                   (SELECT SUM(point) FROM [PS_UserData].[dbo].[pgtos] WHERE UserUID = U.UserUID AND status IN ('approved', 'Aprovado')) as TotalDonations
            FROM [PS_UserData].[dbo].[Users_Master] U 
            WHERE U.UserUID = ?
        ");
        $stmt->execute([$editUID]);
        $accountData = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get character data
        $stmt = $conn1->prepare("
            SELECT CharID, CharName, Level, Class, LoginStatus, Map, K1, K2, LastConnected
            FROM [PS_GameData].[dbo].[Chars]
            WHERE UserUID = ? AND Del = 0
            ORDER BY Level DESC
        ");
        $stmt->execute([$editUID]);
        $characters = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">Error loading account data: ' . $e->getMessage() . '</div>';
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $editUID = $_POST['UserUID'];
        
        // Update points if changed
        if (isset($_POST['Points']) && $_POST['Points'] !== '') {
            $stmt = $conn1->prepare("UPDATE [PS_UserData].[dbo].[Users_Master] SET Point = ? WHERE UserUID = ?");
            $stmt->execute([$_POST['Points'], $editUID]);
        }

        // Update status if changed
        if (isset($_POST['Status'])) {
            $stmt = $conn1->prepare("UPDATE [PS_UserData].[dbo].[Users_Master] SET Status = ? WHERE UserUID = ?");
            $stmt->execute([$_POST['Status'], $editUID]);
        }

        // Update password if provided
        if (!empty($_POST['NewPassword'])) {
            $newPass = $_POST['NewPassword'];
            $stmt = $conn1->prepare("UPDATE [PS_UserData].[dbo].[Users_Master] SET Password = ? WHERE UserUID = ?");
            $stmt->execute([strtoupper(hash('sha256', $newPass)), $editUID]);
        }

        $message = '<div class="alert alert-success">Account updated successfully!</div>';
        
        // Refresh account data
        $stmt = $conn1->prepare("
            SELECT U.*, 
                   (SELECT COUNT(*) FROM [PS_GameData].[dbo].[Chars] WHERE UserUID = U.UserUID AND Del = 0) as CharCount,
                   (SELECT SUM(point) FROM [PS_UserData].[dbo].[pgtos] WHERE UserUID = U.UserUID AND status IN ('approved', 'Aprovado')) as TotalDonations
            FROM [PS_UserData].[dbo].[Users_Master] U 
            WHERE U.UserUID = ?
        ");
        $stmt->execute([$editUID]);
        $accountData = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">Error updating account: ' . $e->getMessage() . '</div>';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Account - Shaiya Admin</title>
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
                <a class="nav-link active" href="buscaconta.php">
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
        <?php if ($accountData): ?>
            <!-- Account Overview -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Account Information</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" class="row g-3">
                                <input type="hidden" name="UserUID" value="<?php echo $accountData['UserUID']; ?>">
                                
                                <div class="col-md-6">
                                    <label class="form-label">Username</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($accountData['UserID']); ?>" readonly>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Points</label>
                                    <input type="number" name="Points" class="form-control" value="<?php echo $accountData['Point']; ?>">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Status</label>
                                    <select name="Status" class="form-select">
                                        <option value="0" <?php echo $accountData['Status'] == 0 ? 'selected' : ''; ?>>Normal</option>
                                        <option value="16" <?php echo $accountData['Status'] == 16 ? 'selected' : ''; ?>>Admin</option>
                                        <option value="-5" <?php echo $accountData['Status'] == -5 ? 'selected' : ''; ?>>Banned</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">New Password</label>
                                    <input type="password" name="NewPassword" class="form-control" placeholder="Leave empty to keep current">
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="mdi mdi-content-save me-2"></i>Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Account Stats</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="border rounded p-3">
                                        <h6 class="text-muted mb-1">Characters</h6>
                                        <h4><?php echo $accountData['CharCount']; ?></h4>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded p-3">
                                        <h6 class="text-muted mb-1">Total Donations</h6>
                                        <h4>$<?php echo number_format($accountData['TotalDonations'] ?? 0, 2); ?></h4>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded p-3">
                                        <h6 class="text-muted mb-1">Join Date</h6>
                                        <h4><?php echo date('Y-m-d', strtotime($accountData['JoinDate'])); ?></h4>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded p-3">
                                        <h6 class="text-muted mb-1">Last Login</h6>
                                        <h4><?php echo $accountData['LeaveDate'] ? date('Y-m-d', strtotime($accountData['LeaveDate'])) : 'Never'; ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Characters List -->
            <div class="card">
                <div class="card-header">
                    <h5>Characters</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Level</th>
                                    <th>Class</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($characters as $char): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($char['CharName']); ?></td>
                                        <td><?php echo $char['Level']; ?></td>
                                        <td>
                                            <?php 
                                            $classes = [
                                                0 => 'Fighter',
                                                1 => 'Guardian',
                                                2 => 'Ranger',
                                                3 => 'Archer',
                                                4 => 'Mage',
                                                5 => 'Priest'
                                            ];
                                            echo $classes[$char['Class']] ?? 'Unknown';
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($char['LoginStatus'] == 1): ?>
                                                <span class="badge bg-success">Online</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Offline</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="editchar.php?charid=<?php echo $char['CharID']; ?>" 
                                                   class="btn btn-sm btn-primary"
                                                   title="Edit Character">
                                                    <i class="mdi mdi-pencil me-1"></i>Edit
                                                </a>
                                                <a href="inventory.php?charid=<?php echo $char['CharID']; ?>" 
                                                   class="btn btn-sm btn-info"
                                                   title="View Inventory">
                                                    <i class="mdi mdi-bag-personal me-1"></i>Items
                                                </a>
                                                <?php if ($char['LoginStatus'] == 1): ?>
                                                    <a href="kick.php?charid=<?php echo $char['CharID']; ?>" 
                                                       class="btn btn-sm btn-warning"
                                                       onclick="return confirm('Are you sure you want to kick this character?')"
                                                       title="Kick Character">
                                                        <i class="mdi mdi-exit-run me-1"></i>Kick
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger">
                <i class="mdi mdi-alert me-2"></i>Account not found or invalid UID provided.
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
