<?php
// Configuração do banco de dados
$dbConfig = [
    'host' => '127.0.0.1',
    'database1' => 'PS_UserData',
    'database2' => 'PS_GameData',
    'database3' => 'PS_GameDefs',
    'database4' => 'PS_GameData',
    'database5' => 'PS_GameLog',
    'username' => 'sa',
    'password' => '123456'
];

try {
    // Conexões com os bancos de dados
    $conn1 = new PDO("sqlsrv:server={$dbConfig['host']};Database={$dbConfig['database1']}", $dbConfig['username'], $dbConfig['password']);
    $conn1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $conn2 = new PDO("sqlsrv:server={$dbConfig['host']};Database={$dbConfig['database2']}", $dbConfig['username'], $dbConfig['password']);
    $conn2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $conn3 = new PDO("sqlsrv:server={$dbConfig['host']};Database={$dbConfig['database3']}", $dbConfig['username'], $dbConfig['password']);
    $conn3->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $conn4 = new PDO("sqlsrv:server={$dbConfig['host']};Database={$dbConfig['database4']}", $dbConfig['username'], $dbConfig['password']);
    $conn4->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $conn5 = new PDO("sqlsrv:server={$dbConfig['host']};Database={$dbConfig['database5']}", $dbConfig['username'], $dbConfig['password']);
    $conn5->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Aqui você pode usar $conn1, $conn2, $conn3, $conn4 e $conn5 para interagir com os bancos de dados

} catch (PDOException $e) {
    echo "Erro na conexão: " . $e->getMessage();
    exit;
}
?>
