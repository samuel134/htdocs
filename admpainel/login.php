<?php
session_start(); // Iniciar la sesión

// Configuración de límite
$max_requests = 100;
$time_period = 60; // en segundos

// Inicializar contagem de solicitações
if (!isset($_SESSION['requests'])) {
    $_SESSION['requests'] = [];
}

// Remover solicitações antigas
$_SESSION['requests'] = array_filter($_SESSION['requests'], function($timestamp) use ($time_period) {
    return $timestamp >= time() - $time_period;
});

// Verificar se o limite foi excedido
if (count($_SESSION['requests']) >= $max_requests) {
    http_response_code(429); // Too Many Requests
    die('Muitas solicitações. Tente novamente mais tarde.');
}

// Adicionar solicitação atual
$_SESSION['requests'][] = time();

include('config.php'); // Incluir el archivo de configuración de la base de datos

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['password'])) {
    // El formulario fue enviado y las variables POST están definidas

    // Validar y autenticar al usuario
    $username = htmlspecialchars($_POST['username']); // Escapar datos para seguridad
    $password = $_POST['password'];

    // Verificar credenciales en la base de datos
    $query = "SELECT UserID, UserUID, Pw FROM Users_Master WHERE UserID = :username AND Status = 16";

    $stmt = $conn1->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $password === $user['Pw']) { // Comparar contraseñas en texto plano
        // Autenticación exitosa, establecer la sesión del usuario y redirigir
        $_SESSION['UserID'] = $user['UserUID'];

        // Redireccionar a la página de usuario después del inicio de sesión
        header("Location: index.php");
        exit();
    } else {
        // Autenticación fallida, mostrar un mensaje de error
        echo '<div class="alert alert-danger" role="alert">';
        echo "Você não tem Acesso devido não ser Admin.";
        echo '</div>';
    }
}
?>

<!doctype html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Login | Shaiya Elementos Admin Painel</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- favicon ============================================ -->
    <link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico">
    <!-- Google Fonts ============================================ -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,700,900" rel="stylesheet">
    <!-- Bootstrap CSS ============================================ -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <!-- Bootstrap CSS ============================================ -->
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <!-- owl.carousel CSS ============================================ -->
    <link rel="stylesheet" href="css/owl.carousel.css">
    <link rel="stylesheet" href="css/owl.theme.css">
    <link rel="stylesheet" href="css/owl.transitions.css">
    <!-- animate CSS ============================================ -->
    <link rel="stylesheet" href="css/animate.css">
    <!-- normalize CSS ============================================ -->
    <link rel="stylesheet" href="css/normalize.css">
    <!-- main CSS ============================================ -->
    <link rel="stylesheet" href="css/main.css">
    <!-- morrisjs CSS ============================================ -->
    <link rel="stylesheet" href="css/morrisjs/morris.css">
    <!-- mCustomScrollbar CSS ============================================ -->
    <link rel="stylesheet" href="css/scrollbar/jquery.mCustomScrollbar.min.css">
    <!-- metisMenu CSS ============================================ -->
    <link rel="stylesheet" href="css/metisMenu/metisMenu.min.css">
    <link rel="stylesheet" href="css/metisMenu/metisMenu-vertical.css">
    <!-- calendar CSS ============================================ -->
    <link rel="stylesheet" href="css/calendar/fullcalendar.min.css">
    <link rel="stylesheet" href="css/calendar/fullcalendar.print.min.css">
    <!-- forms CSS ============================================ -->
    <link rel="stylesheet" href="css/form/all-type-forms.css">
    <!-- style CSS ============================================ -->
    <link rel="stylesheet" href="style.css">
    <!-- responsive CSS ============================================ -->
    <link rel="stylesheet" href="css/responsive.css">
    <!-- modernizr JS ============================================ -->
    <script src="js/vendor/modernizr-2.8.3.min.js"></script>
</head>

<body>
    <div class="color-line"></div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="back-link back-backend">
                    
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12"></div>
            <div class="col-md-4 col-md-4 col-sm-4 col-xs-12">
                <div class="text-center m-b-md custom-login">
                    <h3>FAÇA LOGIN</h3>
                    <p>Este é o melhor Painel de todos os tempos!</p>
                </div>
                <div class="hpanel">
                    <div class="panel-body">
                        <form action="login.php" id="loginForm" method="POST">
                            <div class="form-group">
                                <label class="control-label" for="username">Nome de usuário</label>
                                <input type="text" placeholder="example@gmail.com" title="Please enter you username" required="" value="" name="username" id="username" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="control-label" for="password">Senha</label>
                                <input type="password" title="Please enter your password" placeholder="******" required="" value="" name="password" id="password" class="form-control">
                            </div>
                            <center><div class="checkbox login-checkbox">
                                <label>
                                    <input type="checkbox" class="i-checks">Lembre de mim</label>
                            </div></center>
                            <button class="btn btn-success btn-block loginbtn">Login</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12"></div>
        </div>
        <div class="row">
            <div class="col-md-12 col-md-12 col-sm-12 col-xs-12 text-center">
                <br>
                <p>Copyright © 2024 Shaiya Elementos Ep4.5 BR</a> Todos os direitos reservados.</p>
            </div>
        </div>
    </div>

    <!-- jquery ============================================ -->
    <script src="js/vendor/jquery-1.11.3.min.js"></script>
    <!-- bootstrap JS ============================================ -->
    <script src="js/bootstrap.min.js"></script>
    <!-- wow JS ============================================ -->
    <script src="js/wow.min.js"></script>
    <!-- price-slider JS ============================================ -->
    <script src="js/jquery-price-slider.js"></script>
    <!-- meanmenu JS ============================================ -->
    <script src="js/jquery.meanmenu.js"></script>
    <!-- owl.carousel JS ============================================ -->
    <script src="js/owl.carousel.min.js"></script>
    <!-- sticky JS ============================================ -->
    <script src="js/jquery.sticky.js"></script>
    <!-- scrollUp JS ============================================ -->
    <script src="js/jquery.scrollUp.min.js"></script>
    <!-- mCustomScrollbar JS ============================================ -->
    <script src="js/scrollbar/jquery.mCustomScrollbar.concat.min.js"></script>
    <script src="js/scrollbar/mCustomScrollbar-active.js"></script>
    <!-- metisMenu JS ============================================ -->
    <script src="js/metisMenu/metisMenu.min.js"></script>
    <script src="js/metisMenu/metisMenu-active.js"></script>
    <!-- tab JS ============================================ -->
    <script src="js/tab.js"></script>
    <!-- icheck JS ============================================ -->
    <script src="js/icheck/icheck.min.js"></script>
    <script src="js/icheck/icheck-active.js"></script>
    <!-- plugins JS ============================================ -->
    <script src="js/plugins.js"></script>
    <!-- main JS ============================================ -->
    <script src="js/main.js"></script>
</body>

</html>