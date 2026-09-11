<?php
session_start();
if (!isset($_SESSION['admCodigo'])) {
    header("Location: login.php");
    exit();
} ?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Controle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>

<body>
    <?php include 'header.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-2" style="text-align: left;">
                <?php include 'menu.php'; ?>
            </div>
            <div class="col-10" style="text-align: center;">
                Selecione uma opção no menu lateral para gerenciar os dados do sistema.
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>