<?php
// Configuração de limite
$max_requests = 100;
$time_period = 60; // em segundos

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Receba os dados enviados via AJAX
    $idexterno = $_POST['paymentId'];
    $newStatus = $_POST['status']; // "approved", "cancelled" ou "rejected", por exemplo

    // Conecte-se ao banco de dados
    $serverName = "158.220.115.213";
    $connectionOptions = array(
        "Database" => "PS_UserData",
        "Uid" => "Dsjui",
        "PWD" => "Sh1@9u"
    );

    $conn = sqlsrv_connect($serverName, $connectionOptions);

    if ($conn === false) {
        die('Erro ao conectar ao servidor SQL: ' . print_r(sqlsrv_errors(), true));
    }

    if ($newStatus === "approved") {
        // Atualize o status no banco de dados
        $sql = "UPDATE [PS_UserData].[dbo].[pgtos] SET status = ? WHERE idexterno = ?";
        $params = array($newStatus, $idexterno);
        $query = sqlsrv_query($conn, $sql, $params);

        if ($query === false) {
            die('Erro na atualização do status: ' . print_r(sqlsrv_errors(), true));
        }

        // Consulta para obter o UserUID relevante de [PS_UserData].[dbo].[pgtos]
        $sql_select_useruid = "SELECT UserUID FROM [PS_UserData].[dbo].[pgtos] WHERE idexterno = ?";
        $params_select_useruid = array($idexterno);
        $stmt_select_useruid = sqlsrv_query($conn, $sql_select_useruid, $params_select_useruid);

        if ($stmt_select_useruid === false) {
            die('Erro na consulta para obter o UserUID: ' . print_r(sqlsrv_errors(), true));
        }

        // Obtém o UserUID
        $row = sqlsrv_fetch_array($stmt_select_useruid, SQLSRV_FETCH_ASSOC);
        $UserUID = $row['UserUID'];

        // Consulta para obter o valor relevante de [PS_UserData].[dbo].[pgtos]
        $sql_select_valor = "SELECT transaction_amount FROM [PS_UserData].[dbo].[pgtos] WHERE idexterno = ?";
        $params_select_valor = array($idexterno);
        $stmt_select_valor = sqlsrv_query($conn, $sql_select_valor, $params_select_valor);

        if ($stmt_select_valor === false) {
            die('Erro na consulta para obter o valor: ' . print_r(sqlsrv_errors(), true));
        }

        // Obtém o valor
        $row = sqlsrv_fetch_array($stmt_select_valor, SQLSRV_FETCH_ASSOC);
        $valor = $row['transaction_amount'];

        // Adicione pontos com base no valor da compra
        $pointsToAdd = 0;
        $escalapoints = 0;

        switch ($valor) {
            case 5.00:
                    $pointsToAdd = 5000;
                    break;
                case 10.00:
                    $pointsToAdd = 10000;
                    break;
                case 20.00:
                    $pointsToAdd = 20000;
                    break;
                case 30.00:
                    $pointsToAdd = 30000;
                    break;
                case 40.00:
                    $pointsToAdd = 40000;
                    break;
                case 50.00:
                    $pointsToAdd = 50000;
                    break;
                case 60.00:
                    $pointsToAdd = 60000;
                    break;
                case 70.00:
                    $pointsToAdd = 70000;
                    break;
                case 80.00:
                    $pointsToAdd = 80000;
                    break;
                case 90.00:
                    $pointsToAdd = 90000;
                    break;
                case 100.00:
                    $pointsToAdd = 100000;
                    break;
                
            default:
                $pointsToAdd = 0;
                $escalapoints = 0;
        }

        // Atualiza os pontos do usuário na tabela PS_UserData.dbo.Users_Master
        $sql_update_points_users = "UPDATE PS_UserData.dbo.Users_Master SET Point = Point + ? WHERE UserUID = ?";
        $params_update_points_users = array($pointsToAdd, $UserUID);
        $stmt_update_points_users = sqlsrv_query($conn, $sql_update_points_users, $params_update_points_users);

        if ($stmt_update_points_users === false) {
            die('Erro ao atualizar pontos na tabela Users_Master: ' . print_r(sqlsrv_errors(), true));
        }

        echo "Status do pagamento atualizado com sucesso e pontos adicionados.";
    } elseif ($newStatus === "cancelled" || $newStatus === "rejected") {
        $sql_update_status = "UPDATE [PS_UserData].[dbo].[pgtos] SET status = ? WHERE idexterno = ?";
        $params_update_status = array($newStatus, $idexterno);
        $stmt_update_status = sqlsrv_query($conn, $sql_update_status, $params_update_status);

        if ($stmt_update_status === false) {
            die('Erro ao atualizar o estado do pagamento para "' . $newStatus . '": ' . print_r(sqlsrv_errors(), true));
        }

        echo "Estado do pagamento atualizado para '" . $newStatus . "'.";
    } else {
        // Caso o novo status não seja "approved", "cancelled" nem "rejected", você pode adicionar tratamento de erro ou lidar com outras situações aqui
        echo "Status de pagamento não suportado: $newStatus";
    }

    // Feche a conexão com o banco de dados
    sqlsrv_close($conn);

    // Responda ao AJAX com uma mensagem de sucesso
    echo "Status atualizado com sucesso para $newStatus";
} else {
    // Responda a solicitações não suportadas de outra forma
    echo "Solicitação inválida";
}
?>
