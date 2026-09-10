<?php
session_start();
$email = $_POST['admEmail'];
$senha = $_POST['admSenha'];

require_once 'classe/conexao.php';

$conexao = new Conexao();
$resultado = $conexao->login($email, $senha);

$conta = $resultado->num_rows;
if ($conta == 0) {
    session_destroy();
    header("Location: login.php?erro=1");
    exit();
} else {
$linha = $resultado->fetch_assoc();
 $_SESSION['admCodigo'] = $linha['admCodigo'];
 $_SESSION['admNome'] = $linha['admNome'];
header("Location: index.php");
}