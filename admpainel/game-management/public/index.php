<?php
require_once '../src/config/database.php';
require_once '../src/controllers/PlayerController.php';
require_once '../src/controllers/AccountController.php';

$playerController = new PlayerController();
$accountController = new AccountController();

// Simple routing logic
$requestUri = $_SERVER['REQUEST_URI'];

switch ($requestUri) {
    case '/addpontos':
        include '../src/views/addpontos.php';
        break;
    case '/ban':
        include '../src/views/ban.php';
        break;
    case '/buscachar':
        include '../src/views/buscachar.php';
        break;
    case '/buscaconta':
        include '../src/views/buscaconta.php';
        break;
    case '/editaccount':
        include '../src/views/editaccount.php';
        break;
    case '/editarchar':
        include '../src/views/editarchar.php';
        break;
    case '/editarnome':
        include '../src/views/editarnome.php';
        break;
    case '/EnviarItens':
        include '../src/views/EnviarItens.php';
        break;
    case '/playerlogs':
        include '../src/views/playerlogs.php';
        break;
    case '/ress':
        include '../src/views/ress.php';
        break;
    default:
        http_response_code(404);
        echo '404 Not Found';
        break;
}
?>