<?php
ob_start();
session_start();
if (!isset($_SESSION['admCodigo'])) {
    header("Location: login.php");
    exit();
}
$titulo = "Administrador";
$table = "adm";
$colunas = "admCodigo, admNome, admEmail, admSenha";
$cabecalhoLista = ["Código", "Nome", "Email"];
$chavePrimaria = "admCodigo";
include 'classe/conexao.php';
$obj = new Conexao();
$arquivoAtual = basename($_SERVER['SCRIPT_NAME']);
$item = $_GET['item'] ?? 'listar';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Controle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="container-fluid" style>
        <div class="row">
            <div class="col-2" style="text-align: left;">
                <?php include 'menu.php'; ?>
            </div>
            <div class="col-10" style="text-align: left;">
                <h1><?php echo $titulo; ?></h1>
                <?php
                if ($item == 'listar') {
                ?>
                    <form action="administrador.php?item=remover" method="POST">
                        <!-- <i title="Remover <?php echo $titulo; ?>" class="bi bi-dash-square-fill" style="font-size: 30px; color: red;"></i> -->
                        <button type="submit" class="btn btn-danger">-</button>
                        <i onclick="location.href='<?php echo $arquivoAtual; ?>?item=adicionar'" title="Adicionar <?php echo $titulo; ?>" class="bi bi-plus-square-fill" style="font-size: 30px; color: green;"></i>



                        <?php 
                        switch ($_GET['sucesso'] ?? null) {
                            case 1:
                                $condicao = "cadastrado";
                                break;
                            case 2:
                                $condicao = "atualizado";
                                break;
                            case 3:
                                $condicao = "removido";
                                break;
                            default:
                                unset($_GET['sucesso']);
                        }
                        if (isset($_GET['sucesso'])) { ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo $titulo . " " . $condicao; ?> com sucesso!
                            </div>
                        <?php }  ?>



                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <?php
                                    for ($i = 0; $i < count($cabecalhoLista); $i++) {
                                        echo "<th scope='col'>" . $cabecalhoLista[$i] . "</th>";
                                    } ?>
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // A Instancia da classe Conexao
                                // está criado no topo do arquivo, então podemos usar a variável $obj para acessar os métodos da classe Conexao
                                $table = "adm";
                                $colunas = 'admCodigo, admNome, admEmail';
                                $resultado = $obj->consultaAvancada($table, $colunas);
                                while ($linha = $resultado->fetch(PDO::FETCH_ASSOC)) {
                                ?>
                                    <tr title="Administrador: <?php echo htmlspecialchars($linha['admNome'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <th scope="row"><input type="checkbox" value="<?php echo (int) $linha['admCodigo']; ?>" name="admCodigo[]"></th>
                                        <td><?php echo (int) $linha['admCodigo']; ?></td>
                                        <td><?php echo htmlspecialchars($linha['admNome'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($linha['admEmail'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <a href="<?php echo htmlspecialchars($arquivoAtual, ENT_QUOTES, 'UTF-8'); ?>?item=adicionar&codigo=<?php echo (int) $linha['admCodigo']; ?>">
                                                <i class="bi bi-pencil-fill" title="Editar"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php } ?>

                            </tbody>
                        </table>
                    </form>
                <?php } else if ($item == 'adicionar') { ?>
                    <div class="container-fluid">
                        <?php if (isset($_GET['erro']) and $_GET['erro'] == 1) { ?>
                            <div class="alert alert-danger" role="alert">
                                Erro ao cadastrar o administrador. Por favor, tente novamente.
                            </div>
                        <?php }
                        if (isset($_GET['codigo'])) {
                            $codigo = $obj->escapar($_GET['codigo']);
                            $resultado = $obj->consultaAvancada($table, $colunas, "$chavePrimaria = '$codigo'");
                            if ($linha = $resultado->fetch(PDO::FETCH_ASSOC)) {
                                $admEmail = $linha['admEmail'];
                                $admSenha = $linha['admSenha'];
                                $admNome = $linha['admNome'];
                            }
                        } else {
                            $admEmail = '';
                            $admSenha = '';
                            $admNome = '';
                        }
                        ?>
                        <form class="row g-3" method="POST" action="<?php echo $arquivoAtual; ?>?item=salvar">
                            <input type="hidden" name="<?php echo $chavePrimaria; ?>" value="<?php echo isset($codigo) ? $codigo : ''; ?>">
                            <div class="col-md-6">
                                <label for="inputEmail4" class="form-label">Email</label>
                                <input value="<?php echo htmlspecialchars($admEmail, ENT_QUOTES, 'UTF-8'); ?>" type="email" class="form-control" name="admEmail" id="inputEmail4">
                            </div>
                            <div class="col-md-6">
                                <label for="inputPassword4" class="form-label">Senha</label>
                                <input type="password" name="admSenha" class="form-control" id="inputPassword4" autocomplete="new-password">
                            </div>
                            <div class="col-12">
                                <label for="nome" class="form-label">Nome</label>
                                <input value="<?php echo htmlspecialchars($admNome, ENT_QUOTES, 'UTF-8'); ?>" type="text" name="admNome" class="form-control" id="nome" placeholder="Seu Nome">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Cadastrar</button>
                            </div>
                        </form>
                    </div>
                <?php } else if ($item == 'salvar') {
                    $admEmail = $obj->escapar($_POST['admEmail'] ?? '');
                    $senhaRecebida = $_POST['admSenha'] ?? '';
                    $admNome = $obj->escapar($_POST['admNome'] ?? '');
                    $admSenha = $senhaRecebida !== '' ? $obj->escapar(password_hash($senhaRecebida, PASSWORD_DEFAULT)) : '';
                    if (isset($_POST[$chavePrimaria])) {
                        $codigo = $obj->escapar($_POST[$chavePrimaria]);
                    }
                    if (!isset($codigo)) {
                        $campos = "admEmail, admSenha, admNome";
                        $valores = "'$admEmail', '$admSenha', '$admNome'";
                        $resposta = $obj->salvar($table, $campos, $valores);
                        if ($resposta) {
                            header("Location: $arquivoAtual?item=listar&sucesso=1");
                            exit();
                        } else {
                            header("Location: $arquivoAtual?item=adicionar&erro=1");
                            exit();
                        }
                    } else {
                        $codigo = $obj->escapar($_POST[$chavePrimaria] ?? '');
                        $camposValores = "admEmail = '$admEmail', admNome = '$admNome'";
                        if ($admSenha !== '') {
                            $camposValores .= ", admSenha = '$admSenha'";
                        }
                        $condicao = "$chavePrimaria = '$codigo'";
                        $resposta = $obj->atualizar($table, $camposValores, $condicao);

                        if ($resposta) {
                            header("Location: $arquivoAtual?item=listar&sucesso=2");
                            exit();
                        } else {
                            header("Location: $arquivoAtual?item=adicionar&erro=1&codigo=$codigo");
                            exit();
                        }
                    }
                } else if ($item == 'remover') {
                    $codigosSelecionados = $_POST['admCodigo'] ?? [];
                    if (!$codigosSelecionados) {
                        header("Location: $arquivoAtual?item=listar&erro=selecao");
                        exit();
                    }
                    $codigosChave = implode(',', array_map('intval', $codigosSelecionados));
                    $resposta = $obj->consultaAvancada($table, "admCodigo, admNome", "$chavePrimaria IN ($codigosChave)");
                    ?>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col">Código</th>
                                <th scope="col">Nome</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                        $_SESSION['CodigoParaRemover'] = $codigosChave;
                    while ($linha = $resposta->fetch(PDO::FETCH_ASSOC)) { ?>
                            <tr>
                                <td><?php echo (int) $linha['admCodigo']; ?></td>
                                <td><?php echo htmlspecialchars($linha['admNome'], ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                            <?php
                        } ?>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-danger" onclick="location.href='<?php echo $arquivoAtual; ?>?item=confirmarRemover'">Confirmar Remoção</button>
<?php
                } else if ($item == 'confirmarRemover' && !empty($_SESSION['CodigoParaRemover'])) {
                    $resposta = $obj->remover($table, "$chavePrimaria IN ({$_SESSION['CodigoParaRemover']})");
                    unset($_SESSION['CodigoParaRemover']);
                    if ($resposta) {
                        header("Location: $arquivoAtual?item=listar&sucesso=3");
                        exit();
                    } else {
                        echo "Erro ao remover o administrador. Por favor, tente novamente.";
                    }
                } 
                
                ?>
            </div>
        </div>

    </div>
    <?php include 'footer.php'; ?>
</body>

</html>