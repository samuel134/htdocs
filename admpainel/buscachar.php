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

$searchResults = [];
$searchPerformed = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['search'])) {
    $searchTerm = $_POST['search'];
    $searchType = $_POST['searchType'];
    $classFilter = $_POST['classFilter'];
    
    try {
        $whereClause = [];
        $params = [];
        
        if ($searchType === 'charname') {
            $whereClause[] = "C.CharName LIKE :searchTerm";
            $params[':searchTerm'] = "%$searchTerm%";
        } else {
            $whereClause[] = "C.CharID = :searchTerm";
            $params[':searchTerm'] = $searchTerm;
        }
        
        if (!empty($classFilter)) {
            $whereClause[] = "C.Class = :classFilter";
            $params[':classFilter'] = $classFilter;
        }
        
        $query = "SELECT C.*, U.UserID, U.Status as AccountStatus, U.Point as AccountPoints
                 FROM [PS_GameData].[dbo].[Chars] C
                 JOIN [PS_UserData].[dbo].[Users_Master] U ON C.UserUID = U.UserUID
                 WHERE " . implode(" AND ", $whereClause);
        
        $stmt = $conn1->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $searchPerformed = true;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Search Characters - Shaiya Admin</title>
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
                <a class="nav-link active" href="buscachar.php">
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
        <!-- Search Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Search Characters</h5>
            </div>
            <div class="card-body">
                <form method="POST" class="row g-3" id="searchForm">
                    <div class="col-md-4">
                        <label class="form-label">Search Term</label>
                        <input type="text" name="search" class="form-control" required
                               placeholder="Enter character name or ID..."
                               onkeyup="if(event.key === 'Enter') document.getElementById('searchForm').submit()">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Search Type</label>
                        <select name="searchType" class="form-select">
                            <option value="charname">Character Name</option>
                            <option value="charid">Character ID</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Class Filter</label>
                        <select name="classFilter" class="form-select">
                            <option value="">All Classes</option>
                            <option value="0">Fighter</option>
                            <option value="1">Guardian</option>
                            <option value="2">Ranger</option>
                            <option value="3">Archer</option>
                            <option value="4">Mage</option>
                            <option value="5">Priest</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="mdi mdi-magnify me-2"></i>Search
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Search Results -->
        <?php if ($searchPerformed): ?>
            <div class="card">
                <div class="card-header">
                    <h5>Search Results</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($searchResults)): ?>
                        <div class="alert alert-info">
                            <i class="mdi mdi-information me-2"></i>No characters found matching your search criteria.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Character ID</th>
                                        <th>Character Name</th>
                                        <th>Account</th>
                                        <th>Level</th>
                                        <th>Class</th>
                                        <th>Status</th>
                                        <th>Last Login</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($searchResults as $char): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($char['CharID']); ?></td>
                                            <td><?php echo htmlspecialchars($char['CharName']); ?></td>
                                            <td><?php echo htmlspecialchars($char['UserID']); ?></td>
                                            <td><?php echo htmlspecialchars($char['Level']); ?></td>
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
                                            <td><?php echo date('Y-m-d H:i', strtotime($char['LastConnected'])); ?></td>
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
                                                    <a href="editaccount.php?uid=<?php echo $char['UserUID']; ?>" 
                                                       class="btn btn-sm btn-secondary"
                                                       title="Edit Account">
                                                        <i class="mdi mdi-account-edit me-1"></i>Account
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
