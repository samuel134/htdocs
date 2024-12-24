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
$character = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $charName = $_POST['charName'] ?? '';
    
    try {
        // Get character info
        $stmt = $conn1->prepare("
            SELECT C.*, U.UserID 
            FROM [PS_GameData].[dbo].[Chars] C
            JOIN [PS_UserData].[dbo].[Users_Master] U ON C.UserUID = U.UserUID
            WHERE C.CharName = ?
        ");
        $stmt->execute([$charName]);
        $character = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($character) {
            // Update character stats if provided
            if (isset($_POST['level'])) {
                $stmt = $conn1->prepare("
                    UPDATE [PS_GameData].[dbo].[Chars]
                    SET Level = ?, Str = ?, Dex = ?, Int = ?, StatPoint = ?
                    WHERE CharID = ?
                ");
                $stmt->execute([
                    $_POST['level'],
                    $_POST['str'],
                    $_POST['dex'],
                    $_POST['int'],
                    $_POST['statpoints'],
                    $character['CharID']
                ]);
                
                $message = "<div class='alert alert-success'>
                    <i class='mdi mdi-check-circle me-2'></i>Character stats updated successfully!
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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Character - Shaiya Admin</title>
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
                <a class="nav-link active" href="editarchar.php">
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
        <div class="row">
            <div class="col-md-6">
                <!-- Search Character Form -->
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center">
                        <i class="mdi mdi-account-search me-2"></i>
                        <h5 class="mb-0">Search Character</h5>
                    </div>
                    <div class="card-body">
                        <?php echo $message; ?>
                        
                        <form method="POST" class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Character Name</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="mdi mdi-account"></i>
                                    </span>
                                    <input type="text" name="charName" class="form-control" required
                                           placeholder="Enter character name"
                                           value="<?php echo htmlspecialchars($character['CharName'] ?? ''); ?>">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="mdi mdi-magnify me-2"></i>Search
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if ($character): ?>
                <!-- Edit Character Form -->
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <i class="mdi mdi-account-edit me-2"></i>
                        <h5 class="mb-0">Edit Character</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="row g-3">
                            <input type="hidden" name="charName" value="<?php echo htmlspecialchars($character['CharName']); ?>">
                            
                            <div class="col-md-6">
                                <label class="form-label">Account</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($character['UserID']); ?>" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Level</label>
                                <input type="number" name="level" class="form-control" 
                                       value="<?php echo $character['Level']; ?>" min="1" max="60">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Strength</label>
                                <input type="number" name="str" class="form-control" 
                                       value="<?php echo $character['Str']; ?>" min="0">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Dexterity</label>
                                <input type="number" name="dex" class="form-control" 
                                       value="<?php echo $character['Dex']; ?>" min="0">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Intelligence</label>
                                <input type="number" name="int" class="form-control" 
                                       value="<?php echo $character['Int']; ?>" min="0">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Stat Points</label>
                                <input type="number" name="statpoints" class="form-control" 
                                       value="<?php echo $character['StatPoint']; ?>" min="0">
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="mdi mdi-content-save me-2"></i>Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($character): ?>
            <div class="col-md-6">
                <!-- Character Stats -->
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <i class="mdi mdi-information me-2"></i>
                        <h5 class="mb-0">Character Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <h6 class="text-muted mb-1">Class</h6>
                                    <h4>
                                        <?php 
                                        $classes = [
                                            0 => 'Fighter',
                                            1 => 'Guardian',
                                            2 => 'Ranger',
                                            3 => 'Archer',
                                            4 => 'Mage',
                                            5 => 'Priest'
                                        ];
                                        echo $classes[$character['Class']] ?? 'Unknown';
                                        ?>
                                    </h4>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <h6 class="text-muted mb-1">Status</h6>
                                    <h4>
                                        <?php if ($character['LoginStatus'] == 1): ?>
                                            <span class="badge bg-success">Online</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Offline</span>
                                        <?php endif; ?>
                                    </h4>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <h6 class="text-muted mb-1">Map</h6>
                                    <h4><?php echo $character['Map']; ?></h4>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <h6 class="text-muted mb-1">Last Login</h6>
                                    <h4><?php echo date('Y-m-d H:i', strtotime($character['LastConnected'])); ?></h4>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <h6 class="text-muted mb-1">Kills</h6>
                                    <h4><?php echo number_format($character['K1']); ?></h4>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <h6 class="text-muted mb-1">Deaths</h6>
                                    <h4><?php echo number_format($character['K2']); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
