<?php
ob_start();
session_start();
if (!isset($_SESSION['admCodigo'])) {
    header('Location: login.php');
    exit();
}
require_once 'classe/conexao.php';
$obj = new Conexao();
$arquivoAtual = basename($_SERVER['SCRIPT_NAME']);
$item = $_GET['item'] ?? 'listar';
$codigo = $_GET['codigo'] ?? null;
$camposLista = 'ranCodigo, titulo, cidade, valorDiaria, capacidadePessoas, qtdQuartos, ativo';

function escaparValor(Conexao $obj, $valor): string
{
    return $obj->escapar($valor);
}

if ($item === 'salvar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $ranCodigo = $_POST['ranCodigo'] ?? '';
    $proCodigo = (int) ($_POST['proCodigo'] ?? 0);
    $assCodigo = (int) ($_POST['assCodigo'] ?? 0);
    $proCodigoSql = $proCodigo > 0 ? (string) $proCodigo : 'NULL';
    $assCodigoSql = $assCodigo > 0 ? (string) $assCodigo : 'NULL';
    $titulo = escaparValor($obj, $_POST['titulo'] ?? '');
    $descricao = escaparValor($obj, $_POST['descricao'] ?? '');
    $cidade = escaparValor($obj, $_POST['cidade'] ?? '');
    $valorDiaria = escaparValor($obj, $_POST['valorDiaria'] ?? '0');
    $contato = escaparValor($obj, $_POST['contato'] ?? '');
    $capacidadePessoas = escaparValor($obj, $_POST['capacidadePessoas'] ?? '0');
    $endereco = escaparValor($obj, $_POST['endereco'] ?? '');
    $qtdQuartos = escaparValor($obj, $_POST['qtdQuartos'] ?? '0');
    $dataExpiracao = escaparValor($obj, $_POST['dataExpiracao'] ?? '');
    $ativo = isset($_POST['ativo']) ? '1' : '0';

    $campos = 'proCodigo, assCodigo, titulo, descricao, cidade, valorDiaria, contato, capacidadePessoas, endereco, qtdQuartos, dataExpiracao, ativo';
    $valores = "$proCodigoSql, $assCodigoSql, '$titulo', '$descricao', '$cidade', '$valorDiaria', '$contato', '$capacidadePessoas', '$endereco', '$qtdQuartos', '$dataExpiracao', $ativo";

    if ($ranCodigo === '') {
        $resposta = $obj->salvar('rancho', $campos, $valores);
        $destino = $resposta ? "$arquivoAtual?item=listar&sucesso=1" : "$arquivoAtual?item=adicionar&erro=1";
    } else {
        $ranCodigo = escaparValor($obj, $ranCodigo);
        $camposValores = "proCodigo = $proCodigoSql, assCodigo = $assCodigoSql, titulo = '$titulo', descricao = '$descricao', cidade = '$cidade', valorDiaria = '$valorDiaria', contato = '$contato', capacidadePessoas = '$capacidadePessoas', endereco = '$endereco', qtdQuartos = '$qtdQuartos', dataExpiracao = '$dataExpiracao', ativo = $ativo";
        $resposta = $obj->atualizar('rancho', $camposValores, "ranCodigo = '$ranCodigo'");
        $destino = $resposta ? "$arquivoAtual?item=listar&sucesso=2" : "$arquivoAtual?item=adicionar&erro=1&codigo=$ranCodigo";
    }
    header("Location: $destino");
    exit();
}

if ($item === 'remover' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigos = $_POST['ranCodigo'] ?? [];
    $codigos = array_filter(array_map('intval', (array) $codigos));
    if (!$codigos) {
        header("Location: $arquivoAtual?item=listar&erro=selecao");
        exit();
    }
    $resposta = $obj->remover('rancho', 'ranCodigo IN (' . implode(',', $codigos) . ')');
    header("Location: $arquivoAtual?item=listar&sucesso=" . ($resposta ? '3' : '4'));
    exit();
}

$rancho = [
    'ranCodigo' => '', 'proCodigo' => '', 'assCodigo' => '', 'titulo' => '',
    'descricao' => '', 'cidade' => '', 'valorDiaria' => '', 'contato' => '',
    'capacidadePessoas' => '', 'endereco' => '', 'qtdQuartos' => '',
    'dataExpiracao' => '', 'ativo' => '1'
];
if ($item === 'adicionar' && $codigo !== null) {
    $resultado = $obj->consultaAvancada('rancho', '*', 'ranCodigo = ' . (int) $codigo);
    if ($resultado && ($linha = $resultado->fetch_assoc())) {
        $rancho = array_merge($rancho, $linha);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar ranchos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>
<body>
<?php include 'header.php'; ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-2"><?php include 'menu.php'; ?></div>
        <main class="col-10">
            <h1>Ranchos</h1>
            <?php if (isset($_GET['erro'])): ?>
                <div class="alert alert-danger">Não foi possível concluir a operação.</div>
            <?php elseif (isset($_GET['sucesso'])): ?>
                <div class="alert alert-success">Operação concluída com sucesso.</div>
            <?php endif; ?>

            <?php if ($item === 'adicionar'): ?>
                <form method="POST" action="<?php echo $arquivoAtual; ?>?item=salvar" class="row g-3">
                    <input type="hidden" name="ranCodigo" value="<?php echo htmlspecialchars($rancho['ranCodigo']); ?>">
                    <?php
                    $campos = [
                        'proCodigo' => 'Código do proprietário', 'assCodigo' => 'Código da assinatura',
                        'titulo' => 'Título', 'cidade' => 'Cidade', 'valorDiaria' => 'Valor da diária',
                        'contato' => 'Contato', 'capacidadePessoas' => 'Capacidade de pessoas',
                        'endereco' => 'Endereço', 'qtdQuartos' => 'Quantidade de quartos',
                        'dataExpiracao' => 'Data de expiração'
                    ];
                    foreach ($campos as $nome => $rotulo):
                    ?>
                        <div class="col-md-6">
                            <label class="form-label" for="<?php echo $nome; ?>"><?php echo $rotulo; ?></label>
                            <input class="form-control" id="<?php echo $nome; ?>" name="<?php echo $nome; ?>" value="<?php echo htmlspecialchars($rancho[$nome] ?? ''); ?>" <?php echo $nome === 'titulo' ? 'required' : ''; ?> <?php echo $nome === 'dataExpiracao' ? 'type="date"' : ''; ?> >
                        </div>
                    <?php endforeach; ?>
                    <div class="col-12">
                        <label class="form-label" for="descricao">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="4"><?php echo htmlspecialchars($rancho['descricao']); ?></textarea>
                    </div>
                    <div class="col-12 form-check">
                        <input class="form-check-input" type="checkbox" id="ativo" name="ativo" <?php echo (int) $rancho['ativo'] === 1 ? 'checked' : ''; ?> >
                        <label class="form-check-label" for="ativo">Ativo</label>
                    </div>
                    <div class="col-12"><button class="btn btn-primary" type="submit">Salvar</button></div>
                </form>
            <?php else: ?>
                <p><a class="btn btn-primary" href="<?php echo $arquivoAtual; ?>?item=adicionar">Adicionar rancho</a></p>
                <form method="POST" action="<?php echo $arquivoAtual; ?>?item=remover">
                    <button class="btn btn-danger mb-3" type="submit">Remover selecionados</button>
                    <table class="table table-hover">
                        <thead><tr><th></th><th>Código</th><th>Título</th><th>Cidade</th><th>Diária</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                        <?php $resultado = $obj->consultaAvancada('rancho', $camposLista); if ($resultado): while ($linha = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><input type="checkbox" name="ranCodigo[]" value="<?php echo (int) $linha['ranCodigo']; ?>"></td>
                                <td><?php echo (int) $linha['ranCodigo']; ?></td>
                                <td><?php echo htmlspecialchars($linha['titulo'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($linha['cidade'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($linha['valorDiaria'] ?? ''); ?></td>
                                <td><?php echo (int) $linha['ativo'] === 1 ? 'Ativo' : 'Inativo'; ?></td>
                                <td><a href="<?php echo $arquivoAtual; ?>?item=adicionar&codigo=<?php echo (int) $linha['ranCodigo']; ?>">Editar</a></td>
                            </tr>
                        <?php endwhile; endif; ?>
                        </tbody>
                    </table>
                </form>
            <?php endif; ?>
        </main>
    </div>
</div>
<?php include 'footer.php'; ?>
</body>
</html>
