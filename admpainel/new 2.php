<?php
            // Start the session and include rate limiting
            session_start();

            // Rate limiting configuration 
            $max_requests = 100;
            $time_period = 60;

            if (!isset($_SESSION['requests'])) {
                $_SESSION['requests'] = [];
            }

            $_SESSION['requests'] = array_filter($_SESSION['requests'], function($timestamp) use ($time_period) {
                return $timestamp >= time() - $time_period;
            });

            if (count($_SESSION['requests']) >= $max_requests) {
                http_response_code(429);
                die('Too many requests. Try again later.');
            }

            $_SESSION['requests'][] = time();

            // Database connection and authentication check
            include('config.php');

            if (!isset($_SESSION['UserID'])) {
                header("Location: login.php");
                exit();
            }

            $userUID = $_SESSION['UserID'];

            // Check user status
            $query = "SELECT Status FROM Users_Master WHERE UserUID = :userUID";
            $stmt = $conn1->prepare($query);
            $stmt->bindParam(':userUID', $userUID);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || $user['Status'] != 16) {
                header("Location: login.php");
                exit();
            }
			
			// Verificar si el usuario ha iniciado sesión y tiene el estado adecuado (agrega esta verificación según tu lógica de autenticación)

// Consulta para obtener los jugadores en línea
$query = "SELECT C.UserUID, C.CharName, C.Level, C.Map, C.K1, C.K2, U.UserID, U.UserIp 
          FROM PS_GameData.dbo.Chars C
          INNER JOIN PS_UserData.dbo.Users_Master U ON C.UserUID = U.UserUID
          WHERE C.LoginStatus = 1";
$stmt = $conn1->prepare($query);
$stmt->execute();
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Función para expulsar al jugador
if (isset($_GET['kick'])) {
    $UserUID = $_GET['kick'];
    $sql = "UPDATE PS_GameData.dbo.Chars SET LoginStatus = '0' WHERE UserUID = ?";
    $query = $conn1->prepare($sql);
    $query->execute([$UserUID]);
    
    $sql = "EXEC [PS_GameDefs].[dbo].[Command] @serviceName = N'ps_game', @cmmd = N'/kickuid $UserUID'";
    $query = $conn1->prepare($sql);
    $query->execute();
	
	// Redirigir después de la expulsión para evitar re-envío del formulario
    header("Location: index.php");
    exit();
}


// Función para banir al jugador
if (isset($_GET['banir'])) {
    $UserUID = $_GET['banir'];
    $sql = "UPDATE PS_GameData.dbo.Chars SET LoginStatus = '0' WHERE UserUID = ?";
    $query = $conn1->prepare($sql);
    $query->execute([$UserUID]);
	
	$sql = "UPDATE PS_UserData.dbo.Users_Master SET Status = '-5' WHERE UserUID = ?";
    $query = $conn1->prepare($sql);
    $query->execute([$UserUID]);
	
    $sql = "EXEC [PS_GameDefs].[dbo].[Command] @serviceName = N'ps_game', @cmmd = N'/kickuid $UserUID'";
    $query = $conn1->prepare($sql);
    $query->execute();
	
	// Redirigir después de la expulsión para evitar re-envío del formulario
    header("Location: index.php");
    exit();
}

$dbConfig = include('config.php');

try {

    // Consulta SQL para obter o total de contas criadas
    $sql = "SELECT COUNT(*) AS total FROM [PS_UserData].[dbo].[Users_Master]";
    $stmt = $conn1->prepare($sql);
    $stmt->execute();

    // Obter o resultado
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalAccounts = $result['total'];
	
	
	 // Consulta SQL para obter o total de Personagens criados
    $sql1 = "SELECT COUNT(*) AS totalchars FROM [PS_GameData].[dbo].[Chars] Where Del = 0";
    $stmt1 = $conn2->prepare($sql1);
    $stmt1->execute();

    // Obter o resultado
    $result1 = $stmt1->fetch(PDO::FETCH_ASSOC);
    $totalChars = $result1['totalchars'];
	
	
	 // Consulta SQL para obter o total de Personagens Online
    $sql2 = "SELECT COUNT(*) AS totalcharsOnline FROM [PS_GameData].[dbo].[Chars] Where LoginStatus = 1";
    $stmt2 = $conn2->prepare($sql2);
    $stmt2->execute();

    // Obter o resultado
    $result2 = $stmt2->fetch(PDO::FETCH_ASSOC);
    $totalCharsOnline = $result2['totalcharsOnline'];
	
	
	// Consulta SQL para obter o total de contas Banidas
    $sql3 = "SELECT COUNT(*) AS totalContasBanidas FROM [PS_UserData].[dbo].[Users_Master] Where Status = -5";
    $stmt3 = $conn1->prepare($sql3);
    $stmt3->execute();

    // Obter o resultado
    $result3 = $stmt3->fetch(PDO::FETCH_ASSOC);
    $totalAccountsBanidas = $result3['totalContasBanidas'];
	
	
	
	
	// Consulta SQL para obter o total de transaction_amount com status aprovado
$sql4 = "SELECT SUM(transaction_amount) AS totalDonates FROM [PS_UserData].[dbo].[pgtos] WHERE status IN ('approved', 'aprovado')";
$stmt4 = $conn1->prepare($sql4);
$stmt4->execute();

// Obter o resultado
$result4 = $stmt4->fetch(PDO::FETCH_ASSOC);
$totalDonates = $result4['totalDonates'];


// Consulta SQL para obter o total de dispesas
$sql5 = "SELECT SUM(valor) AS DispesarVps FROM [PS_UserData].[dbo].[DispesarVps]";
$stmt5 = $conn1->prepare($sql5);
$stmt5->execute();

// Obter o resultado
$result5 = $stmt5->fetch(PDO::FETCH_ASSOC);
$DispesarVps = $result5['DispesarVps'];



// Consulta SQL para obter o saldo total
$sql6 = "
    SELECT 
        (SELECT COALESCE(SUM(transaction_amount), 0) 
         FROM [PS_UserData].[dbo].[pgtos] 
         WHERE status IN ('approved', 'aprovado'))
        - 
        (SELECT COALESCE(SUM(valor), 0) 
         FROM [PS_UserData].[dbo].[DispesarVps]) AS saldototal
";
$stmt6 = $conn1->prepare($sql6);
$stmt6->execute();



// Obter o resultado
$result6 = $stmt6->fetch(PDO::FETCH_ASSOC);
$SaldoTotal = $result6['saldototal'];


// Consulta SQL para contar quantos jogadores estão online na facção "Light"
$sql7 = "
    SELECT COUNT(*) AS totalLightOn
    FROM PS_GameData.dbo.Chars C
    JOIN PS_GameData.dbo.UserMaxGrow UMG ON C.UserUID = UMG.UserUID
    WHERE C.LoginStatus = 1 AND UMG.Country = 0;
";

$stmt7 = $conn1->prepare($sql7);
$stmt7->execute();

// Obter o resultado
$result7 = $stmt7->fetch(PDO::FETCH_ASSOC);
$totalLightOn = $result7['totalLightOn'];


// Consulta SQL para contar quantos jogadores estão online na facção "Dark"
$sql8 = "
    SELECT COUNT(*) AS totalDarkOn
    FROM PS_GameData.dbo.Chars C
    JOIN PS_GameData.dbo.UserMaxGrow UMG ON C.UserUID = UMG.UserUID
    WHERE C.LoginStatus = 1 AND UMG.Country = 1;
";

$stmt8 = $conn1->prepare($sql8);
$stmt8->execute();

// Obter o resultado
$result8 = $stmt8->fetch(PDO::FETCH_ASSOC);
$totalDarkOn = $result8['totalDarkOn'];


// Consulta SQL para contar quantos jogadores foram criados na facção "Light"
$sql9 = "
    SELECT COUNT(*) AS totalLight
    FROM PS_GameData.dbo.Chars C
    JOIN PS_GameData.dbo.UserMaxGrow UMG ON C.UserUID = UMG.UserUID
    WHERE UMG.Country = 0 AND C.Del = 0;
";

$stmt9 = $conn1->prepare($sql9);
$stmt9->execute();

// Obter o resultado
$result9 = $stmt9->fetch(PDO::FETCH_ASSOC);
$totalLight = $result9['totalLight'];


// Consulta SQL para contar quantos jogadores criado na facção "Dark"
$sql10 = "
    SELECT COUNT(*) AS totalDark
    FROM PS_GameData.dbo.Chars C
    JOIN PS_GameData.dbo.UserMaxGrow UMG ON C.UserUID = UMG.UserUID
    WHERE UMG.Country = 1 AND C.Del = 0;
";

$stmt10 = $conn1->prepare($sql10);
$stmt10->execute();

// Obter o resultado
$result10 = $stmt10->fetch(PDO::FETCH_ASSOC);
$totalDark = $result10['totalDark'];


$valorFormatado = number_format($DispesarVps, 2, ',', '.');

$valorFormatado2 = number_format($totalDonates, 2, ',', '.');

$valorFormatado3 = number_format($SaldoTotal, 2, ',', '.');

} catch (PDOException $e) {
    echo "Erro na conexão: " . $e->getMessage();
}
			
?>
<html><head><base href="/">
  <title>Shaiya Admin Panel</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/@mdi/font@6.9.96/css/materialdesignicons.min.css" rel="stylesheet">
  <style>
    :root {
      --purple: #8f5fe8;
      --purple-light: #aa83ea;
      --white: #ffffff;
      --dark: #2a2a2a;
      --gray: #6c7293;
      --light-gray: #f2f2f2;
    }

    body {
      background: var(--light-gray);
      color: var(--dark);
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .container-scroller {
      display: flex;
      position: relative;
    }

    .horizontal-menu {
      background: var(--white);
      padding: 0 2rem;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .navbar {
      padding: 1rem 0;
    }

    .navbar-brand img {
      height: 40px;
    }

    .nav-item {
      margin: 0 0.5rem;
    }

    .nav-link {
      color: var(--gray);
      font-weight: 500;
      padding: 0.5rem 1rem;
      transition: all 0.3s ease;
    }

    .nav-link:hover,
    .nav-link.active {
      color: var(--purple);
    }

    .nav-link i {
      font-size: 1.2rem;
      margin-right: 0.5rem;
    }

    .card {
      border: none;
      border-radius: 0.5rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      margin-bottom: 1.5rem;
    }

    .card-title {
      color: var(--gray);
      font-size: 1rem;
      font-weight: 500;
      margin-bottom: 1.5rem;
    }

    .bg-gradient-primary {
      background: linear-gradient(45deg, var(--purple), var(--purple-light));
    }

    .bg-gradient-success {
      background: linear-gradient(45deg, #2dd4bf, #34d399);
    }

    .bg-gradient-info {
      background: linear-gradient(45deg, #38bdf8, #60a5fa);
    }

    .text-white {
      color: var(--white) !important;
    }

    .progress {
      height: 8px;
      border-radius: 4px;
    }

    .table td {
      padding: 1rem;
      vertical-align: middle;
      border-color: #f3f3f3;
    }

    .table td img {
      width: 36px;
      height: 36px;
      border-radius: 50%;
    }

    .badge {
      padding: 0.5rem 1rem;
      font-weight: 500;
    }

    .badge-gradient-success {
      background: linear-gradient(45deg, #2dd4bf, #34d399);
      color: white;
    }

    .badge-gradient-warning {
      background: linear-gradient(45deg, #fbbf24, #f59e0b);
      color: white;
    }

    .badge-gradient-danger {
      background: linear-gradient(45deg, #ef4444, #dc2626);
      color: white;
    }

    .todo-list {
      list-style: none;
      padding: 0;
    }

    .todo-list li {
      padding: 0.75rem;
      border-bottom: 1px solid #f3f3f3;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .form-check-label {
      margin-left: 1rem;
    }

    .footer {
      background: var(--white);
      padding: 2rem 0;
      margin-top: 2rem;
    }

    .menu-title {
      margin-left: 0.5rem;
    }

    .card-stats {
      padding: 1.5rem;
      color: white;
      position: relative;
      overflow: hidden;
    }

    .card-stats::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(255,255,255,0.1);
      transform: skewX(-20deg);
    }

    .chart-container {
      position: relative;
      height: 300px;
    }

    .dropdown-menu {
      border: none;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      border-radius: 0.5rem;
    }

    .dropdown-item {
      padding: 0.75rem 1.5rem;
      color: var(--gray);
    }

    .dropdown-item:hover {
      background: var(--light-gray);
      color: var(--purple);
    }

    .bg-gradient-danger {
      background: linear-gradient(45deg, #ef4444, #dc2626);
    }
  </style>
</head>
<body>
  <div class="container-scroller">
    <!-- Horizontal Navbar -->
    <div class="horizontal-menu">
      <nav class="navbar top-navbar">
        <div class="container">
          <a class="navbar-brand" href="/">
            <h3 class="text-purple m-0">Shaiya Admin</h3>
          </a>
          
          <div class="navbar-menu-wrapper">
            <ul class="navbar-nav">
              <li class="nav-item">
                <a class="nav-link active" href="/admpainel">
                  <i class="mdi mdi-view-dashboard"></i>
                  <span class="menu-title">Dashboard</span>
                </a>
				<li class="nav-item">
                <a class="nav-link" href="/admpainel/addpontos.php">
                  <i class="mdi mdi-clipboard-text"></i>
                  <span class="menu-title">Donates</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="/admpainel/buscaconta.php">
                  <i class="mdi mdi-account-multiple"></i>
                  <span class="menu-title">Users</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="/admpainel/buscachar.php">
                  <i class="mdi mdi-sword"></i>
                  <span class="menu-title">Characters</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="/admpainel/EnviarItens.php">
                  <i class="mdi mdi-treasure-chest"></i>
                  <span class="menu-title">Items-Enviar</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="/admpainel/editarchar.php">
                  <i class="mdi mdi-account-multiple"></i>
                  <span class="menu-title">Characters-Edit</span>
                </a>
				<li class="nav-item">
                <a class="nav-link" href="/admpainel/editarnome.php">
                 <i class="mdi mdi-transcribe"></i>
                  <span class="menu-title">Name-Edit</span>
                </a>
				
				<li class="nav-item">
                <a class="nav-link" href="/admpainel/ban.php">
                  <i class="mdi mdi-wall"></i>
                  <span class="menu-title">Ban</span>
                </a>
				<li class="nav-item">
                <a class="nav-link" href="/admpainel/ress.php">
                  <i class="mdi mdi-account-multiple"></i>
                  <span class="menu-title">Ress-Characters</span>
                </a>
				<li class="nav-item">
                <a class="nav-link" href="/admpainel/playerlogs.php">
                  <i class="mdi mdi-clipboard-text"></i>
                  <span class="menu-title">Player-Logs</span>
                </a>
              </li>
            </ul>

            <ul class="navbar-nav ml-auto">
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown">
                  <img src="https://images.websim.ai/avatar/admin" alt="Profile" class="rounded-circle" width="32">
                  <span class="menu-title ml-2">Admin</span>
                </a>
                <div class="dropdown-menu" aria-labelledby="profileDropdown">
                  <a class="dropdown-item" href="/profile">
                    <i class="mdi mdi-account mr-2"></i> Profile
                  </a>
                  <a class="dropdown-item" href="/settings">
                    <i class="mdi mdi-settings mr-2"></i> Settings
                  </a>
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item" href="/logout">
                    <i class="mdi mdi-logout mr-2"></i> Logout
                  </a>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </nav>
    </div>

    <!-- Main Content -->
    <div class="container-fluid page-body-wrapper">
      <div class="main-panel">
        <div class="content-wrapper">


          <!-- Stats Cards -->
          <div class="row">
            <div class="col-md-3">
              <div class="card bg-gradient-primary">
                <div class="card-body card-stats">
                  <h5 class="card-title text-white">Total Accounts</h5>
                  <h3 class="text-white"><?php echo number_format($totalAccounts); ?></h3>
                  <p class="text-white-50">Active accounts</p>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card bg-gradient-success">
                <div class="card-body card-stats">
                  <h5 class="card-title text-white">Online Players</h5>
                  <h3 class="text-white"><?php echo number_format($totalCharsOnline); ?></h3>
                  <p class="text-white-50">Currently active</p>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card bg-gradient-info">
                <div class="card-body card-stats">
                  <h5 class="card-title text-white">Total Characters</h5>
                  <h3 class="text-white"><?php echo number_format($totalChars); ?></h3>
                  <p class="text-white-50">Created characters</p>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card bg-gradient-danger">
                <div class="card-body card-stats">
                  <h5 class="card-title text-white">Banned Accounts</h5>
                  <h3 class="text-white"><?php echo number_format($totalAccountsBanidas); ?></h3>
                  <p class="text-white-50">Total banned accounts</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Alliance Stats -->
          <div class="row mt-4">
            <div class="col-md-3">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Light Alliance</h5>
                  <h3><?php echo number_format($totalLight); ?></h3>
                  <p>Online: <?php echo number_format($totalLightOn); ?></p>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Dark Alliance</h5>
                  <h3><?php echo number_format($totalDark); ?></h3>
                  <p>Online: <?php echo number_format($totalDarkOn); ?></p>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Total Donations</h5>
                  <h3>$<?php echo number_format($totalDonates, 2); ?></h3>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Server Balance</h5>
                  <h3>$<?php echo number_format($SaldoTotal, 2); ?></h3>
                </div>
              </div>
            </div>
          </div>
		  
		            <div class="row mt-4">
            <div class="col-12">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Detalhe do Personagem</h5>
		  
                      <form action="admpainel/buscachar.php" method="POST">
                        
						<table class="center-table">
                                <tr>
                                   <br> <br> 
									<td class="white-text">Nome do Personagem:</td>
                                    
									<td><input type="text" name="CharName" size="20"/></td>
                                </tr>
                            </table>
							<br> 
                        <center><input type="submit" style="width:150px; height:40px" value="Verificar Personagem ↺" name="submit" class="btn btn-warning"/></center>
                    </form>
                    <?php if (isset($errorMessage)) { echo "<div class='alert alert-danger'>$errorMessage</div>"; } ?>
                </div>
            </div>
        </div>
    </div>
</div>



<?php
include 'config.php'; // Inclua o arquivo de configuração do banco de dados

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($_POST['CharName'])) {
        $errorMessage = 'Você não especificou um nome de conta!';
    } else {
        $uid = $_POST['CharName'];
        try {
            $queryUserID = $conn2->prepare('
                SELECT 
                    c.CharName,
                    c.Level,
					c.UserID,
					c.UserUID,
					c.CharID,
                    c.Str + characterItems.ConstStr + lapisSum.ConstStr AS Str,
                    c.Dex + characterItems.ConstDex + lapisSum.ConstDex AS Dex,
                    c.Rec + characterItems.ConstRec + lapisSum.ConstRec AS Rec,
                    c.Int + characterItems.ConstInt + lapisSum.ConstInt AS Int,
                    c.Wis + characterItems.ConstWis + lapisSum.ConstWis AS Wis,
                    c.Luc + characterItems.ConstLuc + lapisSum.ConstLuc AS Luc,
                    c.Str + characterItems.ConstStr + lapisSum.ConstStr + c.Dex + characterItems.ConstDex + lapisSum.ConstDex + c.Rec + characterItems.ConstRec + lapisSum.ConstRec + c.Int + characterItems.ConstInt + lapisSum.ConstInt + c.Wis + characterItems.ConstWis + lapisSum.ConstWis + c.Luc + characterItems.ConstLuc + lapisSum.ConstLuc AS TotalStat,
                    c.Rec + characterItems.ConstRec + lapisSum.ConstRec + characterItems.Defense + lapisSum.Defense AS Defense,
                    c.Wis + characterItems.ConstWis + lapisSum.ConstWis + characterItems.MagicResist + lapisSum.MagicResist AS MagicResist,
                    characterItems.Absorb + lapisSum.Absorb AS Absorb,
                    CASE
                        WHEN c.Job < 3 THEN
                            (c.Str + characterItems.ConstStr + lapisSum.ConstStr) * 1.3 + (c.Dex + characterItems.ConstDex + lapisSum.ConstDex) * 0.2 + characterItems.Attack + lapisSum.Attack
                        WHEN c.Job = 3 THEN
                            (c.Str + characterItems.ConstStr + lapisSum.ConstStr) + (c.Dex + characterItems.ConstDex + lapisSum.ConstDex) * 0.2 + (c.Luc + characterItems.ConstLuc + lapisSum.ConstLuc) * 0.3 + characterItems.Attack + lapisSum.Attack
                        ELSE
                            (c.Int + characterItems.ConstInt + lapisSum.ConstInt) + (c.Wis + characterItems.ConstWis + lapisSum.ConstWis) * 0.2 + characterItems.Attack + lapisSum.Attack
                    END AS AttackMin,
                    CASE
                        WHEN c.Job < 3 THEN
                            (c.Str + characterItems.ConstStr + lapisSum.ConstStr) * 1.3 + (c.Dex + characterItems.ConstDex + lapisSum.ConstDex) * 0.2 + characterItems.Attack + lapisSum.Attack + characterItems.AttackModifier + lapisSum.AttackModifier
                        WHEN c.Job = 3 THEN
                            (c.Str + characterItems.ConstStr + lapisSum.ConstStr) + (c.Dex + characterItems.ConstDex + lapisSum.ConstDex) * 0.2 + (c.Luc + characterItems.ConstLuc + lapisSum.ConstLuc) * 0.3 + characterItems.Attack + lapisSum.Attack + characterItems.AttackModifier + lapisSum.AttackModifier
                        ELSE
                            (c.Int + characterItems.ConstInt + lapisSum.ConstInt) + (c.Wis + characterItems.ConstWis + lapisSum.ConstWis) * 0.2 + characterItems.Attack + lapisSum.Attack + characterItems.AttackModifier
                    END AS AttackMax
                FROM [Ps_GameData].[dbo].[Chars] c
                INNER JOIN [PS_UserData].[dbo].[Users_Master] u ON c.UserUID = u.UserUID
                INNER JOIN (
                    SELECT
                        ci.CharID,
                        SUM(i.ConstStr + CASE WHEN i.ReqWis > 0 AND LEN(ci.CraftName) = 20 THEN CONVERT(int, SUBSTRING(ci.CraftName, 1, 2)) ELSE 0 END) AS ConstStr,
                        SUM(i.ConstDex + CASE WHEN i.ReqWis > 0 AND LEN(ci.CraftName) = 20 THEN CONVERT(int, SUBSTRING(ci.CraftName, 3, 2)) ELSE 0 END) AS ConstDex,
                        SUM(i.ConstRec + CASE WHEN i.ReqWis > 0 AND LEN(ci.CraftName) = 20 THEN CONVERT(int, SUBSTRING(ci.CraftName, 5, 2)) ELSE 0 END) AS ConstRec,
                        SUM(i.ConstInt + CASE WHEN i.ReqWis > 0 AND LEN(ci.CraftName) = 20 THEN CONVERT(int, SUBSTRING(ci.CraftName, 7, 2)) ELSE 0 END) AS ConstInt,
                        SUM(i.ConstWis + CASE WHEN i.ReqWis > 0 AND LEN(ci.CraftName) = 20 THEN CONVERT(int, SUBSTRING(ci.CraftName, 9, 2)) ELSE 0 END) AS ConstWis,
                        SUM(i.ConstLuc + CASE WHEN i.ReqWis > 0 AND LEN(ci.CraftName) = 20 THEN CONVERT(int, SUBSTRING(ci.CraftName, 11, 2)) ELSE 0 END) AS ConstLuc,
                        SUM(i.Effect1 + CASE WHEN i.ReqWis > 0 AND LEN(ci.CraftName) = 20 AND (CONVERT(int, SUBSTRING(ci.CraftName, 19, 2)) BETWEEN 1 AND 20) THEN 
                            CASE
                                WHEN SUBSTRING(ci.CraftName, 19, 2) = \'01\' THEN 7 WHEN SUBSTRING(ci.CraftName, 19, 2) = \'02\' THEN 14 WHEN SUBSTRING(ci.CraftName, 19, 2) = \'03\' THEN 21
                                WHEN SUBSTRING(ci.CraftName, 19, 2) = \'04\' THEN 31 WHEN SUBSTRING(ci.CraftName, 19, 2) = \'05\' THEN 41 WHEN SUBSTRING(ci.CraftName, 19, 2) = \'06\' THEN 51
                                WHEN SUBSTRING(ci.CraftName, 19, 2) = \'07\' THEN 64 WHEN SUBSTRING(ci.CraftName, 19, 2) = \'08\' THEN 77 WHEN SUBSTRING(ci.CraftName, 19, 2) = \'09\' THEN 90
                                WHEN SUBSTRING(ci.CraftName, 19, 2) = \'10\' THEN 106 WHEN SUBSTRING(ci.CraftName, 19, 2) = \'11\' THEN 122 WHEN SUBSTRING(ci.CraftName, 19, 2) = \'12\' THEN 138
                                WHEN SUBSTRING(ci.CraftName, 19, 2) = \'13\' THEN 157 WHEN SUBSTRING(ci.CraftName, 19, 2) = \'14\' THEN 176 WHEN SUBSTRING(ci.CraftName, 19, 2) = \'15\' THEN 195
                                WHEN SUBSTRING(ci.CraftName, 19, 2) = \'16\' THEN 217 WHEN SUBSTRING(ci.CraftName, 19, 2) = \'17\' THEN 239 WHEN SUBSTRING(ci.CraftName, 19, 2) = \'18\' THEN 261
                                WHEN SUBSTRING(ci.CraftName, 19, 2) = \'19\' THEN 286 WHEN SUBSTRING(ci.CraftName, 19, 2) = \'20\' THEN 311 
                            END
                        ELSE 0 END) AS Attack,
                        SUM(i.Effect2) AS AttackModifier,
                        SUM(i.Effect3) AS Defense,
                        SUM(i.Effect4) AS MagicResist,
                        SUM(CASE WHEN i.ReqWis > 0 AND (CONVERT(int, SUBSTRING(ci.CraftName, 19, 2)) BETWEEN 51 AND 70) THEN ((CONVERT(int, SUBSTRING(ci.CraftName, 19, 2)) - 50) * 5) ELSE 0 END) AS Absorb
                    FROM [Ps_GameData].[dbo].[CharItems] ci
                    INNER JOIN [Ps_GameDefs].[dbo].[Items] i ON i.ItemID = ci.ItemID
                    WHERE ci.Bag = 0
                      AND ci.Slot >= 0
                      AND ci.Slot != 13
                    GROUP BY ci.CharID
                ) AS characterItems ON c.CharID = characterItems.CharID
                LEFT JOIN (
                    SELECT
                        ci.CharID,
                        SUM(ISNULL(lapis.ConstStr, 0)) AS ConstStr,
                        SUM(ISNULL(lapis.ConstDex, 0)) AS ConstDex,
                        SUM(ISNULL(lapis.ConstRec, 0)) AS ConstRec,
                        SUM(ISNULL(lapis.ConstInt, 0)) AS ConstInt,
                        SUM(ISNULL(lapis.ConstWis, 0)) AS ConstWis,
                        SUM(ISNULL(lapis.ConstLuc, 0)) AS ConstLuc,
                        SUM(ISNULL(lapis.Effect1, 0)) AS Attack,
                        SUM(ISNULL(lapis.Effect2, 0)) AS AttackModifier,
                        SUM(ISNULL(lapis.Effect3, 0)) AS Defense,
                        SUM(ISNULL(lapis.Effect4, 0)) AS MagicResist,
                        SUM(ISNULL(lapis.Exp,0)) AS Absorb
						
	
		
		
                    FROM [Ps_GameData].[dbo].[CharItems] ci
		INNER JOIN [Ps_GameDefs].[dbo].Items i ON ci.ItemID = i.ItemID
		LEFT JOIN [Ps_GameDefs].[dbo].Items lapis ON lapis.Type = 30 AND lapis.TypeID IN(ci.Gem1,ci.Gem2,ci.Gem3,ci.Gem4,ci.Gem5,ci.Gem6)
	WHERE ci.Bag = 0
		AND ci.Slot >= 0
		AND ci.Slot != 13  
					  
                    GROUP BY ci.CharID
                ) AS lapisSum ON c.CharID = lapisSum.CharID
                WHERE c.CharName = :CharName
            ');

            $queryUserID->execute([':CharName' => $uid]);
            $result = $queryUserID->fetch(PDO::FETCH_ASSOC);
            // Verifique se há resultados válidos
   if ($result && is_array($result) && !empty($result)) {
                echo '
                    <div class="product-sales-area mg-tb-30">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <div class="product-sales-chart center-table">
                                        <table class="table table-striped table-bordered">
                                            <thead class="thead-dark">
                                                <tr>
												
												    <th>Usuario</th>
													<th>CharID</th>
												    <th>UserUID</th>
                                                    <th>Personagem</th>
                                                    <th>Level</th>
                                                    <th>STR</th>
                                                    <th>DEX</th>
                                                    <th>REC</th>
                                                    <th>INT</th>
                                                    <th>WIS</th>
                                                    <th>LUC</th>
                                                    <th>Total de Status</th>
                                                    <th>Defesa</th>
                                                    <th>Resistência Mágica</th>
                                                    <th>Absorção</th>
                                                    <th>Ataque Mínimo</th>
                                                    <th>Ataque Máximo</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
												    <td>' . $result['UserID'] . '</td>
													<td>' . $result['CharID'] . '</td>
												    <td>' . $result['UserUID'] . '</td>
                                                    <td>' . $result['CharName'] . '</td>
                                                    <td>' . $result['Level'] . '</td>
                                                    <td>' . $result['Str'] . '</td>
                                                    <td>' . $result['Dex'] . '</td>
                                                    <td>' . $result['Rec'] . '</td>
                                                    <td>' . $result['Int'] . '</td>
                                                    <td>' . $result['Wis'] . '</td>
                                                    <td>' . $result['Luc'] . '</td>
                                                    <td>' . $result['TotalStat'] . '</td>
                                                    <td>' . $result['Defense'] . '</td>
                                                    <td>' . $result['MagicResist'] . '</td>
                                                    <td>' . $result['Absorb'] . '</td>
                                                    <td>' . $result['AttackMin'] . '</td>
                                                    <td>' . $result['AttackMax'] . '</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                ';
            } else {
                echo '<p>Nenhum resultado para exibir.</p>';
            }
        } catch (PDOException $e) {
            $errorMessage = 'Erro de banco de dados: ' . $e->getMessage();
        }
    }
}

if (isset($errorMessage)) {
    echo json_encode(['error' => $errorMessage]);
}
?>
 </div>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <footer class="footer">
            <div class="container">
              <div class="d-flex justify-content-between align-items-center">
                <span class="text-muted">Copyright 2023 Shaiya Admin Panel</span>
                <span class="text-muted">Version 1.0.0</span>
              </div>
            </div>
          </footer>
          
        </div>
      </div>
    </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  
  
</body></html>