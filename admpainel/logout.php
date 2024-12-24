<?php
session_start();

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

unset($_SESSION['UserID']);
session_destroy();

header("Location: Login.php");
exit;
?>