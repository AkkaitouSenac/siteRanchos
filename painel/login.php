<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login do painel administrativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>

<body class="d-flex align-items-center justify-content-center min-vh-100">
    <form style="border: 1px solid #ccc; padding: 20px; border-radius: 5px;"
        action="acesso_login.php" method="POST">
    <?php
    if (isset($_GET['erro']) and $_GET['erro'] == 1) {
    ?>
        <div class="alert alert-warning" role="alert">
            Dados incorretos! Tente novamente.
        </div>
<?php
    } ?>
        <div class="container" style="max-width: 450px;">
            <div class="mb-3">
                <label for="exampleInputEmail1" class="form-label">Email </label>
                <input required placeholder="Insira um e-mail válido" type="email" class="form-control" id="exampleInputEmail1" name="admEmail">
            </div>
            <div class="mb-3">
                <label for="exampleInputPassword1" class="form-label">Senha</label>
                <input required type="password" name="admSenha" class="form-control" id="exampleInputPassword1">
            </div>
            <button type="submit" class="btn btn-primary">Logar no painel</button>
        </div>
    </form>
</body>

</html>