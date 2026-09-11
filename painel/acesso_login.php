<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}
$email = $_POST['admEmail'] ?? '';
$senha = $_POST['admSenha'] ?? '';

require_once 'classe/conexao.php';

$conexao = new Conexao();
$administrador = $conexao->login($email, $senha);

if (!$administrador) {
    $conexao->registrarLog(null, 'adm', 'LOGIN_FALHA', null, 'login');
    session_destroy();
    header("Location: login.php?erro=1");
    exit();
} else {
    session_regenerate_id(true);
    $_SESSION['admCodigo'] = $administrador['admCodigo'];
    $_SESSION['admNome'] = $administrador['admNome'];
    $conexao->registrarLog((int) $administrador['admCodigo'], 'adm', 'LOGIN', $administrador['admCodigo'], 'login');
    header("Location: index.php");
    exit();
}