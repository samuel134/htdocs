<?php
session_start();
include('config.php');

if (!isset($_SESSION['UserID'])) {
    header("Location: login.php");
    exit();
}

$userUID = $_SESSION['UserID'];
$message = '';

// Initialize variables
$totalAccounts = 0;
$totalChars = 0;
$totalCharsOnline = 0;
$totalAccountsBanidas = 0;
$totalDonates = 0;
$DispesarVps = 0;
$SaldoTotal = 0;
$totalLightOn = 0;
$totalDarkOn = 0;
$totalLight = 0;
$totalDark = 0;
$members = [];

// Check user status
try {
    $query = "SELECT Status FROM [PS_UserData].[dbo].[Users_Master] WHERE UserUID = :userUID";
    $stmt = $conn1->prepare($query);
    $stmt->bindParam(':userUID', $userUID);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || $user['Status'] != 16) {
        header("Location: login.php");
        exit();
    }

    // Get online players
    $query = "SELECT C.UserUID, C.CharName, C.Level, C.Map, C.K1, C.K2, U.UserID, U.UserIp 
              FROM [PS_GameData].[dbo].[Chars] C
              INNER JOIN [PS_UserData].[dbo].[Users_Master] U ON C.UserUID = U.UserUID
              WHERE C.LoginStatus = 1";
    $stmt = $conn1->prepare($query);
    $stmt->execute();
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Handle kick player
    if (isset($_GET['kick'])) {
        $UserUID = $_GET['kick'];
        $sql = "UPDATE [PS_GameData].[dbo].[Chars] SET LoginStatus = '0' WHERE UserUID = ?";
        $query = $conn1->prepare($sql);
        $query->execute([$UserUID]);
        
        $sql = "EXEC [PS_GameDefs].[dbo].[Command] @serviceName = N'ps_game', @cmmd = N'/kickuid $UserUID'";
        $query = $conn1->prepare($sql);
        $query->execute();
        
        header("Location: index.php");
        exit();
    }

    // Handle ban player
    if (isset($_GET['banir'])) {
        $UserUID = $_GET['banir'];
        $sql = "UPDATE [PS_GameData].[dbo].[Chars] SET LoginStatus = '0' WHERE UserUID = ?";
        $query = $conn1->prepare($sql);
        $query->execute([$UserUID]);
        
        $sql = "UPDATE [PS_UserData].[dbo].[Users_Master] SET Status = '-5' WHERE UserUID = ?";
        $query = $conn1->prepare($sql);
        $query->execute([$UserUID]);
        
        $sql = "EXEC [PS_GameDefs].[dbo].[Command] @serviceName = N'ps_game', @cmmd = N'/kickuid $UserUID'";
        $query = $conn1->prepare($sql);
        $query->execute();
        
        header("Location: index.php");
        exit();
    }

    // Get statistics
    $totalAccounts = $conn1->query("SELECT COUNT(*) FROM [PS_UserData].[dbo].[Users_Master]")->fetchColumn();
    $totalChars = $conn1->query("SELECT COUNT(*) FROM [PS_GameData].[dbo].[Chars] WHERE Del = 0")->fetchColumn();
    $totalCharsOnline = $conn1->query("SELECT COUNT(*) FROM [PS_GameData].[dbo].[Chars] WHERE LoginStatus = 1")->fetchColumn();
    $totalAccountsBanidas = $conn1->query("SELECT COUNT(*) FROM [PS_UserData].[dbo].[Users_Master] WHERE Status = -5")->fetchColumn();
    
    // Get donations and expenses
    $totalDonates = $conn1->query("SELECT COALESCE(SUM(transaction_amount), 0) FROM [PS_UserData].[dbo].[pgtos] WHERE status IN ('approved', 'aprovado')")->fetchColumn();
    $DispesarVps = $conn1->query("SELECT COALESCE(SUM(valor), 0) FROM [PS_UserData].[dbo].[DispesarVps]")->fetchColumn();
    $SaldoTotal = $totalDonates - $DispesarVps;
    
    // Get alliance statistics
    $totalLightOn = $conn1->query("
        SELECT COUNT(*) FROM [PS_GameData].[dbo].[Chars] C
        JOIN [PS_GameData].[dbo].[UserMaxGrow] UMG ON C.UserUID = UMG.UserUID
        WHERE C.LoginStatus = 1 AND UMG.Country = 0
    ")->fetchColumn();
    
    $totalDarkOn = $conn1->query("
        SELECT COUNT(*) FROM [PS_GameData].[dbo].[Chars] C
        JOIN [PS_GameData].[dbo].[UserMaxGrow] UMG ON C.UserUID = UMG.UserUID
        WHERE C.LoginStatus = 1 AND UMG.Country = 1
    ")->fetchColumn();
    
    $totalLight = $conn1->query("
        SELECT COUNT(*) FROM [PS_GameData].[dbo].[Chars] C
        JOIN [PS_GameData].[dbo].[UserMaxGrow] UMG ON C.UserUID = UMG.UserUID
        WHERE UMG.Country = 0 AND C.Del = 0
    ")->fetchColumn();
    
    $totalDark = $conn1->query("
        SELECT COUNT(*) FROM [PS_GameData].[dbo].[Chars] C
        JOIN [PS_GameData].[dbo].[UserMaxGrow] UMG ON C.UserUID = UMG.UserUID
        WHERE UMG.Country = 1 AND C.Del = 0
    ")->fetchColumn();

} catch (PDOException $e) {
    $message = "<div class='alert alert-danger'>Database error: " . $e->getMessage() . "</div>";
}

// Continue with the rest of your HTML code, which looks good
?>

<!DOCTYPE html>
<html>
<head>
  <title>Dashboard - Shaiya Admin</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/@mdi/font@6.9.96/css/materialdesignicons.min.css" rel="stylesheet">
  <link href="css/modern-admin.css" rel="stylesheet">
  <style>
    /* Additional styles specific to dashboard */
    .stats-card {
      position: relative;
      overflow: hidden;
    }
    
    .stats-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 4px;
      background: var(--primary-gradient);
      transition: all 0.3s ease;
    }
    
    .stats-card.primary::before { background: var(--primary-gradient); }
    .stats-card.success::before { background: var(--success-gradient); }
    .stats-card.info::before { background: var(--info-gradient); }
    .stats-card.danger::before { background: var(--danger-gradient); }
    
    .stats-card i {
      font-size: 2rem;
      opacity: 0.1;
      position: absolute;
      right: 1rem;
      bottom: 1rem;
    }
    
    .alliance-card {
      position: relative;
      overflow: hidden;
    }
    
    .alliance-card::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 4px;
      background: linear-gradient(45deg, #4e73df 0%, #224abe 100%);
      opacity: 0;
      transition: all 0.3s ease;
    }
    
    .alliance-card:hover::after {
      opacity: 1;
    }
    
    .table td {
      vertical-align: middle;
    }
    
    .btn-group-sm > .btn {
      padding: 0.25rem 0.5rem;
      font-size: 0.875rem;
      border-radius: 0.2rem;
      margin: 0 0.25rem;
    }
    
    .btn i {
      margin-right: 0.25rem;
    }
  </style>
</head>
<body>
  <!-- Keep the rest of your HTML code unchanged as it's working well -->
  <?php if ($message): ?>
    <div class="alert-container">
      <?php echo $message; ?>
    </div>
  <?php endif; ?>
  
  <!-- Sidebar -->
  <div class="sidebar">
    <div class="brand">
      <h3>Shaiya Admin</h3>
    </div>
    
    <ul class="nav-menu">
      <li class="nav-item">
        <a class="nav-link active" href="index.php">
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
    <!-- Stats Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="card text-center mb-4">
                <div class="card-body">
                    <h5 class="card-title">Total Accounts</h5>
                    <h3 class="card-text"><?php echo number_format($totalAccounts); ?></h3>
                    <p class="text-muted">Active accounts</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card text-center mb-4">
                <div class="card-body">
                    <h5 class="card-title">Online Players</h5>
                    <h3 class="card-text"><?php echo number_format($totalCharsOnline); ?></h3>
                    <p class="text-muted">Currently active</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card text-center mb-4">
                <div class="card-body">
                    <h5 class="card-title">Total Characters</h5>
                    <h3 class="card-text"><?php echo number_format($totalChars); ?></h3>
                    <p class="text-muted">Created characters</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card text-center mb-4">
                <div class="card-body">
                    <h5 class="card-title">Banned Accounts</h5>
                    <h3 class="card-text"><?php echo number_format($totalAccountsBanidas); ?></h3>
                    <p class="text-muted">Total banned accounts</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card text-center mb-4">
                <div class="card-body">
                    <h5 class="card-title">Light Alliance</h5>
                    <h3 class="card-text"><?php echo number_format($totalLight); ?></h3>
                    <p class="text-muted"><?php echo number_format($totalLightOn); ?> Online</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card text-center mb-4">
                <div class="card-body">
                    <h5 class="card-title">Dark Alliance</h5>
                    <h3 class="card-text"><?php echo number_format($totalDark); ?></h3>
                    <p class="text-muted"><?php echo number_format($totalDarkOn); ?> Online</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card text-center mb-4">
                <div class="card-body">
                    <h5 class="card-title">Total Donations</h5>
                    <h3 class="card-text">$<?php echo number_format($totalDonates, 2); ?></h3>
                    <p class="text-muted">All time</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card text-center mb-4">
                <div class="card-body">
                    <h5 class="card-title">Server Balance</h5>
                    <h3 class="card-text">$<?php echo number_format($SaldoTotal, 2); ?></h3>
                    <p class="text-muted">Current</p>
                </div>
            </div>
        </div>
    </div>

    <style>
        .card {
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            background: linear-gradient(145deg, #ffffff, #f3f4f6);
            border-radius: 15px;
            overflow: hidden;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #4e73df, #224abe);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .card:hover::before {
            opacity: 1;
        }
        .card-title {
            color: #6c757d;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1rem;
        }
        .card-text {
            color: #2c3e50;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(45deg, #1a237e, #4e73df);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .text-muted {
            font-size: 0.875rem;
            font-weight: 500;
            color: #6c757d !important;
        }
        .badge {
            padding: 0.5rem 1rem;
            font-weight: 500;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .card-body {
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }
        .card-body::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, transparent 50%, rgba(78, 115, 223, 0.1) 50%);
            border-radius: 0 0 15px 0;
        }
    </style>

    <!-- Online Players Table -->
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
          <i class="mdi mdi-account-group me-2"></i>
          <h5 class="mb-0">Online Players</h5>
        </div>
        <span class="badge bg-primary">
          <?php echo count($members); ?> Active
        </span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table mb-0">
            <thead>
              <tr>
                <th>#</th>
                <th>Account</th>
                <th>Character Name</th>
                <th>Level</th>
                <th>Map</th>
                <th>Kills</th>
                <th>Deaths</th>
                <th>IP</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($members)): 
                $count = 0;
                foreach ($members as $row):
                  $count++; ?>
                <tr>
                  <td><?php echo $count; ?></td>
                  <td>
                    <a href="buscaconta.php?search=<?php echo urlencode($row['UserID']); ?>" 
                       class="text-decoration-none">
                      <?php echo htmlspecialchars($row['UserID']); ?>
                    </a>
                  </td>
                  <td>
                    <a href="buscachar.php?search=<?php echo urlencode($row['CharName']); ?>" 
                       class="text-decoration-none">
                      <?php echo htmlspecialchars($row['CharName']); ?>
                    </a>
                  </td>
                  <td>
                    <span class="badge bg-info">
                      Level <?php echo htmlspecialchars($row['Level']); ?>
                    </span>
                  </td>
                  <td>
                    <span class="badge bg-secondary">
                      Map <?php echo htmlspecialchars($row['Map']); ?>
                    </span>
                  </td>
                  <td>
                    <span class="text-success">
                      <?php echo number_format($row['K1']); ?>
                    </span>
                  </td>
                  <td>
                    <span class="text-danger">
                      <?php echo number_format($row['K2']); ?>
                    </span>
                  </td>
                  <td>
                    <span class="badge bg-light text-dark">
                      <?php echo htmlspecialchars($row['UserIp']); ?>
                    </span>
                  </td>
                  <td>
                    <div class="btn-group btn-group-sm">
                      <a href="index.php?kick=<?php echo $row['UserUID']; ?>" 
                         class="btn btn-danger"
                         onclick="return confirm('Are you sure you want to kick this player?');"
                         title="Kick Player">
                        <i class="mdi mdi-exit-run"></i>Kick
                      </a>
                      <a href="index.php?banir=<?php echo $row['UserUID']; ?>" 
                         class="btn btn-warning"
                         onclick="return confirm('Are you sure you want to ban this player?');"
                         title="Ban Player">
                        <i class="mdi mdi-block-helper"></i>Ban
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endforeach;
              else: ?>
                <tr>
                  <td colspan="9" class="text-center py-4">
                    <i class="mdi mdi-information me-2"></i>
                    No players online...
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
