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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($_POST['UserID'])) {
        $message = '<div class="alert alert-danger">Please specify an account name!</div>';
    } else {
        $uid = $_POST['UserID'];
        try {
            $queryUserID = $conn1->prepare('SELECT * FROM [PS_UserData].[dbo].[Users_Master] WHERE UserID = ?');
            $queryUserID->bindParam(1, $uid, PDO::PARAM_STR);
            $queryUserID->execute();
            $userDetails = $queryUserID->fetch(PDO::FETCH_ASSOC);

            if ($userDetails === false) {
                $message = '<div class="alert alert-danger">Account not found!</div>';
            } else {
                $UserUID = $userDetails['UserUID'];
                if (!empty($_POST['action'])) {
                    $action = $_POST['action'];
                    $points = 0;
                    $valor = 0;

                    switch ($action) {
                        case 1: $points = 5000; $Valor = 5.00; break;
                        case 2: $points = 10000; $Valor = 10.00; break;
                        case 3: $points = 20000; $Valor = 20.00; break;
                        case 4: $points = 30000; $Valor = 30.00; break;
                        case 5: $points = 40000; $Valor = 40.00; break;
                        case 6: $points = 50000; $Valor = 50.00; break;
                        case 7: $points = 60000; $Valor = 60.00; break;
                        case 8: $points = 70000; $Valor = 70.00; break;
                        case 9: $points = 80000; $Valor = 80.00; break;
                        case 10: $points = 90000; $Valor = 90.00; break;
                        case 11: $points = 100000; $Valor = 100.00; break;
                        case 12: $points = 5000; $Valor = 5.00; break;
                        case 13: $points = 10000; $Valor = 10.00; break;
                    }

                    $data_hora_venda = date('Y-m-d H:i:s');

                    $queryAction1 = $conn1->prepare("INSERT INTO [PS_UserData].[dbo].[pgtos] (UserUID, UserID, point, status, payment_method_id, transaction_amount, idexterno, data_hora_venda) VALUES (?, ?, ?, 'Aprovado', ?, ?, '123456', ?)");
                    $queryAction1->execute([$UserUID, $uid, $points, $userUID, $Valor, $data_hora_venda]);

                    $queryAction2 = $conn1->prepare("UPDATE [PS_UserData].[dbo].[Users_Master] SET point = point + ? WHERE UserUID = ?");
                    $queryAction2->execute([$points, $UserUID]);

                    $message = '<div class="alert alert-success">Points added successfully! ☑</div>';
                }
            }
        } catch (Exception $e) {
            $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
        }
    }
}

// Get transactions history
try {
    $query = $conn1->query("SELECT * FROM [PS_UserData].[dbo].[pgtos] ORDER BY data_hora_venda DESC");
    $PS_GameDatas = $query->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    $PS_GameDatas = [];
    $message .= '<div class="alert alert-danger">Error loading transactions: ' . $e->getMessage() . '</div>';
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Points - Shaiya Admin</title>
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
                <a class="nav-link active" href="addpontos.php">
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
        <!-- Add Points Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Add Points</h5>
            </div>
            <div class="card-body">
                <?php echo $message; ?>
                
                <form method="POST" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Username</label>
                        <input type="text" name="UserID" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Points Package</label>
                        <select name="action" class="form-select" required>
                            <option value="">Select Package</option>
                            <option value="1">5,000 Points - R$5.00</option>
                            <option value="2">10,000 Points - R$10.00</option>
                            <option value="3">20,000 Points - R$20.00</option>
                            <option value="4">30,000 Points - R$30.00</option>
                            <option value="5">40,000 Points - R$40.00</option>
                            <option value="6">50,000 Points - R$50.00</option>
                            <option value="7">60,000 Points - R$60.00</option>
                            <option value="8">70,000 Points - R$70.00</option>
                            <option value="9">80,000 Points - R$80.00</option>
                            <option value="10">90,000 Points - R$90.00</option>
                            <option value="11">100,000 Points - R$100.00</option>
                            <option value="12">5,000 Points - Staff</option>
                            <option value="13">10,000 Points - Staff</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-plus-circle me-2"></i>Add Points
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="card">
            <div class="card-header">
                <h5>Transaction History</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Points</th>
                                <th>Status</th>
                                <th>Method</th>
                                <th>Amount</th>
                                <th>External ID</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($PS_GameDatas)): ?>
                                <?php foreach ($PS_GameDatas as $PS_GameData): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($PS_GameData->UserID); ?></td>
                                        <td><?php echo number_format($PS_GameData->point, 0); ?></td>
                                        <td>
                                            <?php if (in_array($PS_GameData->status, ['approved', 'Aprovado', 'Completed'])): ?>
                                                <span class="badge bg-success">Approved</span>
                                            <?php elseif ($PS_GameData->status === 'cancelled'): ?>
                                                <span class="badge bg-danger">Not Approved</span>
                                            <?php elseif ($PS_GameData->status === 'rejected'): ?>
                                                <span class="badge bg-warning">Rejected</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($PS_GameData->payment_method_id); ?></td>
                                        <td>R$<?php echo number_format($PS_GameData->transaction_amount, 2); ?></td>
                                        <td><?php echo htmlspecialchars($PS_GameData->idexterno); ?></td>
                                        <td><?php echo date('d/m/Y H:i:s', strtotime($PS_GameData->data_hora_venda)); ?></td>
                                        <td>
                                            <?php if (!in_array($PS_GameData->status, ['approved', 'Aprovado', 'Completed', 'cancelled', 'rejected'])): ?>
                                                <button class="btn btn-sm btn-primary" onclick="updateStatus('<?php echo $PS_GameData->idexterno; ?>')">
                                                    <i class="mdi mdi-refresh me-1"></i>Verify
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">No transactions found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function updateStatus(paymentId) {
            var accessToken = 'APP_USR-6127177828065605-011921-e211c428eccbcc526175e1ae05b1e6c7-377194942';
            var apiUrl = 'https://api.mercadopago.com/v1/payments/' + paymentId + '?access_token=' + accessToken;

            $.ajax({
                url: apiUrl,
                method: 'GET',
                success: function(data) {
                    $.ajax({
                        url: 'process.php',
                        type: 'POST',
                        data: {
                            paymentId: paymentId,
                            status: data.status,
                        },
                        success: function(response) {
                            window.location.reload();
                        },
                        error: function(error) {
                            console.error(error);
                        }
                    });
                },
                error: function(error) {
                    console.error('Error checking payment status: ' + JSON.stringify(error));
                }
            });
        }
    </script>
</body>
</html>
