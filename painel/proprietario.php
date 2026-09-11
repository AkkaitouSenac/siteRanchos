<?php
ob_start();
session_start();
if (!isset($_SESSION['admCodigo'])) { header('Location: login.php'); exit; }
require_once 'classe/conexao.php';
$obj = new Conexao();
$item = $_GET['item'] ?? 'listar';
$codigo = filter_input(INPUT_GET, 'codigo', FILTER_VALIDATE_INT);
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !Conexao::validarCsrf($_POST['_csrf'] ?? null)) {
    $erro = 'Sessão expirada. Recarregue a página e tente novamente.';
    $item = 'listar';
}
if (!$erro && $item === 'salvar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['proCodigo'] ?? 0);
    $dados = [
        'nome' => trim((string) ($_POST['nome'] ?? '')),
        'email' => trim((string) ($_POST['email'] ?? '')),
        'telefone' => trim((string) ($_POST['telefone'] ?? '')),
        'dataCadastro' => $_POST['dataCadastro'] ?: date('Y-m-d'),
    ];
    $senha = (string) ($_POST['senha'] ?? '');
    if ($dados['nome'] === '' || !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
        $erro = 'Informe nome e e-mail válido.';
    } elseif ($id > 0) {
        $sql = 'UPDATE proprietario SET nome=:nome,email=:email,telefone=:telefone,dataCadastro=:dataCadastro' .
            ($senha !== '' ? ',senha=:senha' : '') . ' WHERE proCodigo=:id';
        if ($senha !== '') $dados['senha'] = password_hash($senha, PASSWORD_DEFAULT);
        $dados['id'] = $id;
        $ok = $obj->executarPreparado($sql, $dados);
        if ($ok) { $obj->registrarLog((int) $_SESSION['admCodigo'], 'proprietario', 'UPDATE', $id, $sql); header('Location: proprietario.php?sucesso=2'); exit; }
        $erro = 'Não foi possível atualizar o proprietário.';
    } else {
        $dados['senha'] = password_hash($senha, PASSWORD_DEFAULT);
        $sql = 'INSERT INTO proprietario (nome,email,telefone,senha,dataCadastro) VALUES (:nome,:email,:telefone,:senha,:dataCadastro)';
        $ok = $obj->executarPreparado($sql, $dados);
        if ($ok) { $obj->registrarLog((int) $_SESSION['admCodigo'], 'proprietario', 'INSERT', null, $sql); header('Location: proprietario.php?sucesso=1'); exit; }
        $erro = 'Não foi possível cadastrar o proprietário.';
    }
}
if (!$erro && $item === 'remover' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $ids = array_values(array_filter(array_map('intval', (array) ($_POST['proCodigo'] ?? []))));
    if ($ids) {
        $marks = implode(',', array_fill(0, count($ids), '?'));
        $ok = $obj->executarPreparado("DELETE FROM proprietario WHERE proCodigo IN ($marks)", $ids);
        if ($ok) { foreach ($ids as $id) $obj->registrarLog((int) $_SESSION['admCodigo'], 'proprietario', 'DELETE', $id); }
        header('Location: proprietario.php?sucesso=' . ($ok ? '3' : '4')); exit;
    }
    $erro = 'Selecione ao menos um registro.';
}
$registro = ['proCodigo'=>'','nome'=>'','email'=>'','telefone'=>'','senha'=>'','dataCadastro'=>date('Y-m-d')];
if ($item === 'adicionar' && $codigo) {
    $r = $obj->consultaPreparada('SELECT proCodigo,nome,email,telefone,dataCadastro FROM proprietario WHERE proCodigo=:id', ['id'=>$codigo])->fetch();
    if ($r) $registro = array_merge($registro, $r);
}
function h($v): string { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Proprietários</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"></head><body>
<?php include 'header.php'; ?><div class="container-fluid"><div class="row"><aside class="col-2"><?php include 'menu.php'; ?></aside><main class="col-10"><h1>Proprietários</h1>
<?php if ($erro): ?><div class="alert alert-danger"><?php echo h($erro); ?></div><?php endif; ?>
<?php if ($item === 'adicionar'): ?><form method="post" action="proprietario.php?item=salvar" class="row g-3"><input type="hidden" name="_csrf" value="<?php echo h(Conexao::csrfToken()); ?>"><input type="hidden" name="proCodigo" value="<?php echo h($registro['proCodigo']); ?>">
<?php foreach (['nome'=>'Nome','email'=>'E-mail','telefone'=>'Telefone','dataCadastro'=>'Data de cadastro'] as $campo=>$rotulo): ?><div class="col-md-6"><label class="form-label" for="<?php echo $campo; ?>"><?php echo $rotulo; ?></label><input class="form-control" id="<?php echo $campo; ?>" name="<?php echo $campo; ?>" value="<?php echo h($registro[$campo]); ?>" <?php echo $campo==='email'?'type="email"':''; ?> <?php echo $campo==='dataCadastro'?'type="date"':''; ?> required></div><?php endforeach; ?>
<div class="col-md-6"><label class="form-label" for="senha">Senha <?php echo $registro['proCodigo'] ? '(deixe em branco para manter)' : ''; ?></label><input class="form-control" type="password" id="senha" name="senha" <?php echo $registro['proCodigo']?'':'required'; ?>></div><div class="col-12"><button class="btn btn-primary">Salvar</button></div></form>
<?php else: ?><p><a class="btn btn-primary" href="proprietario.php?item=adicionar">Adicionar proprietário</a></p><form method="post" action="proprietario.php?item=remover"><input type="hidden" name="_csrf" value="<?php echo h(Conexao::csrfToken()); ?>"><button class="btn btn-danger mb-3">Remover selecionados</button><table class="table table-hover"><thead><tr><th></th><th>Código</th><th>Nome</th><th>E-mail</th><th>Telefone</th><th></th></tr></thead><tbody>
<?php $r=$obj->consultaPreparada('SELECT proCodigo,nome,email,telefone FROM proprietario ORDER BY proCodigo DESC'); while($l=$r->fetch()): ?><tr><td><input type="checkbox" name="proCodigo[]" value="<?php echo (int)$l['proCodigo']; ?>"></td><td><?php echo (int)$l['proCodigo']; ?></td><td><?php echo h($l['nome']); ?></td><td><?php echo h($l['email']); ?></td><td><?php echo h($l['telefone']); ?></td><td><a href="proprietario.php?item=adicionar&codigo=<?php echo (int)$l['proCodigo']; ?>">Editar</a></td></tr><?php endwhile; ?></tbody></table></form><?php endif; ?></main></div></div><?php include 'footer.php'; ?></body></html>
