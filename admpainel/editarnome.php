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
    $currentCharName = $_POST['currentCharName'] ?? '';
    $newCharName = $_POST['newCharName'] ?? '';

    if (empty($currentCharName) || empty($newCharName)) {
        $message = "<div class='alert alert-danger'>
            <i class='mdi mdi-alert me-2'></i>All fields are required!
        </div>";
    } else {
        try {
            // Check if current character name exists
            $stmt = $conn1->prepare("
                SELECT C.*, U.UserID 
                FROM [PS_GameData].[dbo].[Chars] C
                JOIN [PS_UserData].[dbo].[Users_Master] U ON C.UserUID = U.UserUID
                WHERE C.CharName = ?
            ");
            $stmt->execute([$currentCharName]);
            $char = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($char) {
                // Check if new name is already taken
                $stmt = $conn1->prepare("SELECT COUNT(*) as count FROM [PS_GameData].[dbo].[Chars] WHERE CharName = ?");
                $stmt->execute([$newCharName]);
                $nameExists = $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;

                if ($nameExists) {
                    $message = "<div class='alert alert-danger'>
                        <i class='mdi mdi-alert me-2'></i>The new character name is already taken!
                    </div>";
                } else {
                    // Update character name
                    $stmt = $conn1->prepare("UPDATE [PS_GameData].[dbo].[Chars] SET CharName = ? WHERE CharID = ?");
                    $stmt->execute([$newCharName, $char['CharID']]);

                    $message = "<div class='alert alert-success'>
                        <i class='mdi mdi-check-circle me-2'></i>Character name successfully changed!
                        <div class='mt-2 small text-muted'>
                            Account: {$char['UserID']}<br>
                            Old Name: {$currentCharName}<br>
                            New Name: {$newCharName}
                        </div>
                    </div>";
                }
            } else {
                $message = "<div class='alert alert-danger'>
                    <i class='mdi mdi-alert me-2'></i>Current character name not found!
                </div>";
            }
        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'>
                <i class='mdi mdi-alert me-2'></i>Database error: " . $e->getMessage() . "
            </div>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Character Name - Shaiya Admin</title>
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
                <a class="nav-link active" href="editarnome.php">
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
            <div class="col-md-6 mx-auto">
                <!-- Edit Name Form -->
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <i class="mdi mdi-rename-box me-2"></i>
                        <h5 class="mb-0">Change Character Name</h5>
                    </div>
                    <div class="card-body">
                        <?php echo $message; ?>
                        
                        <form method="POST" class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Current Character Name</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="mdi mdi-account"></i>
                                    </span>
                                    <input type="text" name="currentCharName" class="form-control" required
                                           placeholder="Enter current character name">
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">New Character Name</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="mdi mdi-account-edit"></i>
                                    </span>
                                    <input type="text" name="newCharName" class="form-control" required
                                           placeholder="Enter new character name">
                                </div>
                                <div class="form-text text-muted">
                                    <i class="mdi mdi-information-outline me-1"></i>
                                    The new name must be unique and follow the game's naming rules.
                                </div>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="mdi mdi-content-save me-2"></i>Change Name
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Name Change Rules -->
                <div class="card mt-4">
                    <div class="card-header d-flex align-items-center">
                        <i class="mdi mdi-information me-2"></i>
                        <h5 class="mb-0">Name Change Rules</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="mdi mdi-check-circle text-success me-2"></i>
                                Names must be between 3 and 16 characters long
                            </li>
                            <li class="mb-2">
                                <i class="mdi mdi-check-circle text-success me-2"></i>
                                Only letters (A-Z, a-z) and numbers (0-9) are allowed
                            </li>
                            <li class="mb-2">
                                <i class="mdi mdi-check-circle text-success me-2"></i>
                                Names must start with a letter
                            </li>
                            <li class="mb-2">
                                <i class="mdi mdi-close-circle text-danger me-2"></i>
                                No special characters or spaces allowed
                            </li>
                            <li>
                                <i class="mdi mdi-close-circle text-danger me-2"></i>
                                No offensive or inappropriate names
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
