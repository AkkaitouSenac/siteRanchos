<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../painel/classe/conexao.php';

function respostaJson(array $dados, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $conexao = new Conexao();
    $acao = $_POST['acao'] ?? $_GET['acao'] ?? '';

    if ($acao === 'login') {
        $stmt = $conexao->consultaPreparada(
            'SELECT proCodigo, nome, email, telefone, senha
             FROM proprietario WHERE email = :email LIMIT 1',
            ['email' => trim((string) ($_POST['email'] ?? ''))]
        );
        $proprietario = $stmt->fetch();

        if (!$proprietario || !password_verify((string) ($_POST['senha'] ?? ''), $proprietario['senha'])) {
            respostaJson(['erro' => 'E-mail ou senha inválidos.'], 401);
        }

        session_regenerate_id(true);
        $_SESSION['proCodigo'] = (int) $proprietario['proCodigo'];
        respostaJson([
            'proprietario' => [
                'proCodigo' => (int) $proprietario['proCodigo'],
                'nome' => $proprietario['nome'],
                'email' => $proprietario['email'],
                'telefone' => $proprietario['telefone'],
            ],
        ]);
    }

    if ($acao === 'cadastrar') {
        $nome = trim((string) ($_POST['nome'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $telefone = trim((string) ($_POST['telefone'] ?? ''));
        $senha = (string) ($_POST['senha'] ?? '');
        if ($nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $telefone === '' || strlen($senha) < 4) {
            respostaJson(['erro' => 'Preencha os dados corretamente.'], 422);
        }

        $novoCodigo = (int) $conexao->consultaPreparada(
            'SELECT COALESCE(MAX(proCodigo), 0) + 1 FROM proprietario'
        )->fetchColumn();
        $ok = $conexao->executarPreparado(
            'INSERT INTO proprietario
                (proCodigo, nome, email, telefone, senha, dataCadastro)
             VALUES
                (:proCodigo, :nome, :email, :telefone, :senha, NOW())',
            [
                'proCodigo' => $novoCodigo,
                'nome' => $nome,
                'email' => $email,
                'telefone' => $telefone,
                'senha' => password_hash($senha, PASSWORD_DEFAULT),
            ]
        );
        if (!$ok) {
            respostaJson(['erro' => 'Não foi possível criar a conta.'], 409);
        }

        $id = (int) $conexao->consultaPreparada(
            'SELECT proCodigo FROM proprietario WHERE email = :email LIMIT 1',
            ['email' => $email]
        )->fetchColumn();
        $_SESSION['proCodigo'] = $id;
        respostaJson(['proprietario' => [
            'proCodigo' => $id, 'nome' => $nome, 'email' => $email, 'telefone' => $telefone,
        ]], 201);
    }

    if ($acao === 'sair') {
        unset($_SESSION['proCodigo']);
        respostaJson(['ok' => true]);
    }

    respostaJson(['erro' => 'Ação inválida.'], 400);
} catch (Throwable $erro) {
    error_log('Falha na autenticação do proprietário: ' . $erro->getMessage());
    respostaJson(['erro' => 'Não foi possível concluir a operação.'], 500);
}
