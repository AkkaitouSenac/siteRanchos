<?php
session_start();
$admCodigo = isset($_SESSION['admCodigo']) ? (int) $_SESSION['admCodigo'] : null;
require_once 'classe/conexao.php';
try {
    $conexao = new Conexao();
    $conexao->registrarLog($admCodigo, 'adm', 'LOGOUT', $admCodigo, 'logout');
} catch (Throwable $erro) {
    error_log('Falha ao registrar logout: ' . $erro->getMessage());
}
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();
header('Location: login.php');
exit();
