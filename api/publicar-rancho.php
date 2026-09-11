<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../painel/classe/conexao.php';

function respostaPublicacao(array $dados, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    if (empty($_SESSION['proCodigo'])) {
        respostaPublicacao(['erro' => 'Faça login como proprietário para publicar.'], 401);
    }

    $nomeImagem = null;
    if (!empty($_FILES['imagem']['tmp_name'])) {
        if ($_FILES['imagem']['error'] !== UPLOAD_ERR_OK || $_FILES['imagem']['size'] > 5 * 1024 * 1024) {
            respostaPublicacao(['erro' => 'A imagem deve ter no máximo 5 MB.'], 422);
        }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES['imagem']['tmp_name']);
        $extensoes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($extensoes[$mime])) {
            respostaPublicacao(['erro' => 'Use uma imagem JPG, PNG ou WEBP.'], 422);
        }
        $nomeImagem = bin2hex(random_bytes(16)) . '.' . $extensoes[$mime];
    }

    $conexao = new Conexao();
    $plano = $conexao->consultaPreparada(
        'SELECT dias FROM assinatura WHERE assCodigo = :id',
        ['id' => (int) ($_POST['assCodigo'] ?? 0)]
    )->fetch();
    if (!$plano) {
        respostaPublicacao(['erro' => 'Plano de assinatura inválido.'], 422);
    }

    $dataExpiracao = (new DateTimeImmutable('today'))
        ->modify('+' . (int) $plano['dias'] . ' days')
        ->format('Y-m-d');
    $sql = 'INSERT INTO rancho
        (proCodigo, assCodigo, titulo, descricao, cidade, valorDiaria, contato,
         capacidadePessoas, endereco, qtdQuartos, dataExpiracao, ativo)
        VALUES (:proCodigo, :assCodigo, :titulo, :descricao, :cidade, :valorDiaria,
         :contato, :capacidade, NULL, NULL, :dataExpiracao, 1)';
    $ok = $conexao->executarPreparado($sql, [
        'proCodigo' => (int) $_SESSION['proCodigo'],
        'assCodigo' => (int) $_POST['assCodigo'],
        'titulo' => trim((string) ($_POST['titulo'] ?? '')),
        'descricao' => trim((string) ($_POST['descricao'] ?? '')),
        'cidade' => trim((string) ($_POST['cidade'] ?? '')),
        'valorDiaria' => (float) ($_POST['valorDiaria'] ?? 0),
        'contato' => trim((string) ($_POST['contato'] ?? '')),
        'capacidade' => (int) ($_POST['capacidade'] ?? 0),
        'dataExpiracao' => $dataExpiracao,
    ]);
    if (!$ok) {
        respostaPublicacao(['erro' => 'Não foi possível publicar o rancho.'], 422);
    }

    $ranCodigo = (int) $conexao->consultaPreparada(
        'SELECT ranCodigo FROM rancho WHERE proCodigo = :proCodigo ORDER BY ranCodigo DESC LIMIT 1',
        ['proCodigo' => (int) $_SESSION['proCodigo']]
    )->fetchColumn();

    if ($nomeImagem !== null) {
        $diretorio = __DIR__ . '/../uploads/ranchos';
        if (!is_dir($diretorio) && !mkdir($diretorio, 0755, true) && !is_dir($diretorio)) {
            respostaPublicacao(['erro' => 'Não foi possível criar o diretório de imagens.'], 500);
        }
        if (!move_uploaded_file($_FILES['imagem']['tmp_name'], $diretorio . '/' . $nomeImagem)) {
            respostaPublicacao(['erro' => 'Não foi possível salvar a imagem.'], 500);
        }
        $conexao->executarPreparado(
            'INSERT INTO imagensrancho (imagem, ranCodigo) VALUES (:imagem, :ranCodigo)',
            ['imagem' => 'uploads/ranchos/' . $nomeImagem, 'ranCodigo' => $ranCodigo]
        );
    }

    respostaPublicacao(['ok' => true, 'ranCodigo' => $ranCodigo], 201);
} catch (Throwable $erro) {
    error_log('Falha ao publicar rancho: ' . $erro->getMessage());
    respostaPublicacao(['erro' => 'Não foi possível publicar o rancho.'], 500);
}
