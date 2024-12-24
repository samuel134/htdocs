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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $charName = $_POST['charName'] ?? '';
    $itemType = $_POST['itemType'] ?? '';
    $itemId = $_POST['itemId'] ?? '';
    $quantity = $_POST['quantity'] ?? 1;

    try {
        // First, get character info
        $stmt = $conn1->prepare("SELECT C.CharID, C.UserUID, C.CharName, U.UserID 
                                FROM [PS_GameData].[dbo].[Chars] C
                                JOIN [PS_UserData].[dbo].[Users_Master] U ON C.UserUID = U.UserUID 
                                WHERE C.CharName = ?");
        $stmt->execute([$charName]);
        $char = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($char) {
            // Insert item into warehouse
            $stmt = $conn1->prepare("
                INSERT INTO [PS_GameData].[dbo].[UserStoredItems] 
                (UserUID, ItemID, Type, Quantity, Slot, Date) 
                VALUES (?, ?, ?, ?, 
                    (SELECT ISNULL(MAX(Slot), 0) + 1 
                     FROM [PS_GameData].[dbo].[UserStoredItems] 
                     WHERE UserUID = ?),
                    GETDATE())
            ");
            
            if ($stmt->execute([$char['UserUID'], $itemId, $itemType, $quantity, $char['UserUID']])) {
                $message = "<div class='alert alert-success'>
                    <i class='mdi mdi-check-circle me-2'></i>
                    Item successfully sent to {$charName}'s warehouse!
                    <div class='mt-2 small text-muted'>
                        Account: {$char['UserID']} | Character: {$char['CharName']}
                    </div>
                </div>";
            } else {
                $message = "<div class='alert alert-danger'>
                    <i class='mdi mdi-alert me-2'></i>Error sending item.
                </div>";
            }
        } else {
            $message = "<div class='alert alert-danger'>
                <i class='mdi mdi-alert me-2'></i>Character not found.
            </div>";
        }
    } catch (PDOException $e) {
        $message = "<div class='alert alert-danger'>
            <i class='mdi mdi-alert me-2'></i>Database error: " . $e->getMessage() . "
        </div>";
    }
}

// Get recent transactions with more details
$recentTransactions = [];
try {
    $stmt = $conn1->query("
        SELECT TOP 10 
            c.CharName, 
            c.Level,
            u.UserID,
            usi.ItemID, 
            usi.Type,
            usi.Quantity, 
            usi.Date
        FROM [PS_GameData].[dbo].[UserStoredItems] usi
        JOIN [PS_GameData].[dbo].[Chars] c ON usi.UserUID = c.UserUID
        JOIN [PS_UserData].[dbo].[Users_Master] u ON c.UserUID = u.UserUID
        ORDER BY usi.Date DESC
    ");
    $recentTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Silently handle error
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Send Items - Shaiya Admin</title>
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
                <a class="nav-link active" href="EnviarItens.php">
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
        <!-- Send Items Form -->
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center">
                        <i class="mdi mdi-gift-outline me-2"></i>
                        <h5 class="mb-0">Send Items to Character</h5>
                    </div>
                    <div class="card-body">
                        <?php echo $message; ?>
                        
                        <form method="POST" class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Character Name</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="mdi mdi-account"></i>
                                    </span>
                                    <input type="text" name="charName" class="form-control" required
                                           placeholder="Enter character name">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Item Type</label>
                                <select name="itemType" class="form-select" required>
                                    <option value="">Select Type</option>
                                    <option value="1">Weapons</option>
                                    <option value="2">Armor</option>
                                    <option value="3">Jewelry</option>
                                    <option value="4">Consumables</option>
                                    <option value="5">Quest Items</option>
                                    <option value="6">Misc Items</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Quantity</label>
                                <input type="number" name="quantity" class="form-control" 
                                       value="1" min="1" max="999" required>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Item ID</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="mdi mdi-cube-outline"></i>
                                    </span>
                                    <input type="number" name="itemId" class="form-control" required
                                           placeholder="Enter item ID">
                                </div>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="mdi mdi-send me-2"></i>Send Item
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Common Items Reference -->
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <i class="mdi mdi-format-list-bulleted me-2"></i>
                        <h5 class="mb-0">Common Items</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Item ID</th>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1001</td>
                                        <td>Health Potion</td>
                                        <td>Consumable</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary"
                                                    onclick="fillItemDetails(1001, 4)">
                                                <i class="mdi mdi-plus-circle"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>1002</td>
                                        <td>Mana Potion</td>
                                        <td>Consumable</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary"
                                                    onclick="fillItemDetails(1002, 4)">
                                                <i class="mdi mdi-plus-circle"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>2001</td>
                                        <td>Basic Sword</td>
                                        <td>Weapon</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary"
                                                    onclick="fillItemDetails(2001, 1)">
                                                <i class="mdi mdi-plus-circle"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <i class="mdi mdi-history me-2"></i>
                        <h5 class="mb-0">Recent Transactions</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Character</th>
                                        <th>Account</th>
                                        <th>Item</th>
                                        <th>Type</th>
                                        <th>Qty</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentTransactions as $tx): ?>
                                        <tr>
                                            <td>
                                                <?php echo htmlspecialchars($tx['CharName']); ?>
                                                <small class="text-muted d-block">Level <?php echo $tx['Level']; ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($tx['UserID']); ?></td>
                                            <td><?php echo htmlspecialchars($tx['ItemID']); ?></td>
                                            <td>
                                                <?php 
                                                $types = [
                                                    1 => 'Weapon',
                                                    2 => 'Armor',
                                                    3 => 'Jewelry',
                                                    4 => 'Consumable',
                                                    5 => 'Quest',
                                                    6 => 'Misc'
                                                ];
                                                echo $types[$tx['Type']] ?? 'Unknown';
                                                ?>
                                            </td>
                                            <td><?php echo number_format($tx['Quantity']); ?></td>
                                            <td><?php echo date('Y-m-d H:i', strtotime($tx['Date'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function fillItemDetails(itemId, itemType) {
            document.querySelector('input[name="itemId"]').value = itemId;
            document.querySelector('select[name="itemType"]').value = itemType;
        }
    </script>
</body>
</html>
