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
    
    try {
        if ($searchType === 'userid') {
            $query = "SELECT UserUID, UserID, Status, Point, JoinDate, LeaveDate 
                     FROM [PS_UserData].[dbo].[Users_Master] 
                     WHERE UserID LIKE :searchTerm";
        } else {
            $query = "SELECT UserUID, UserID, Status, Point, JoinDate, LeaveDate 
                     FROM [PS_UserData].[dbo].[Users_Master] 
                     WHERE UserUID = :searchTerm";
        }
        
        $stmt = $conn1->prepare($query);
        
        if ($searchType === 'userid') {
            $stmt->bindValue(':searchTerm', "%$searchTerm%", PDO::PARAM_STR);
        } else {
            $stmt->bindValue(':searchTerm', $searchTerm, PDO::PARAM_INT);
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
    <title>Search Accounts - Shaiya Admin</title>
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
        <!-- Search Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Search Accounts</h5>
            </div>
            <div class="card-body">
                <form method="POST" class="row g-3" id="searchForm">
                    <div class="col-md-6">
                        <label class="form-label">Search Term</label>
                        <input type="text" name="search" class="form-control" required
                               placeholder="Enter username or ID to search..."
                               onkeyup="if(event.key === 'Enter') document.getElementById('searchForm').submit()">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Search Type</label>
                        <select name="searchType" class="form-select">
                            <option value="userid">Username</option>
                            <option value="useruid">User ID</option>
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
                            <i class="mdi mdi-information me-2"></i>No accounts found matching your search criteria.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>User ID</th>
                                        <th>Username</th>
                                        <th>Status</th>
                                        <th>Points</th>
                                        <th>Join Date</th>
                                        <th>Last Login</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($searchResults as $account): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($account['UserUID']); ?></td>
                                            <td><?php echo htmlspecialchars($account['UserID']); ?></td>
                                            <td>
                                                <?php if ($account['Status'] == -5): ?>
                                                    <span class="badge bg-danger">Banned</span>
                                                <?php elseif ($account['Status'] == 16): ?>
                                                    <span class="badge bg-success">Admin</span>
                                                <?php else: ?>
                                                    <span class="badge bg-primary">Active</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo number_format($account['Point']); ?></td>
                                            <td><?php echo date('Y-m-d H:i', strtotime($account['JoinDate'])); ?></td>
                                            <td><?php echo $account['LeaveDate'] ? date('Y-m-d H:i', strtotime($account['LeaveDate'])) : 'Never'; ?></td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <a href="editaccount.php?uid=<?php echo $account['UserUID']; ?>" 
                                                       class="btn btn-sm btn-primary"
                                                       title="Edit Account">
                                                        <i class="mdi mdi-pencil me-1"></i>Edit
                                                    </a>
                                                    <?php if ($account['Status'] != -5): ?>
                                                        <a href="ban.php?uid=<?php echo $account['UserUID']; ?>" 
                                                           class="btn btn-sm btn-danger"
                                                           onclick="return confirm('Are you sure you want to ban this account?')"
                                                           title="Ban Account">
                                                            <i class="mdi mdi-block-helper me-1"></i>Ban
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="buscachar.php?uid=<?php echo $account['UserUID']; ?>" 
                                                       class="btn btn-sm btn-info"
                                                       title="View Characters">
                                                        <i class="mdi mdi-account-group me-1"></i>Chars
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
