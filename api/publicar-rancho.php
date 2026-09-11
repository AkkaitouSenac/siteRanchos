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

    $arquivosImagem = [];
    if (isset($_FILES['imagem'])) {
        $arquivosRecebidos = $_FILES['imagem'];
        $quantidade = is_array($arquivosRecebidos['tmp_name'])
            ? count($arquivosRecebidos['tmp_name'])
            : 1;
        $extensoes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

        for ($indice = 0; $indice < $quantidade; $indice++) {
            $arquivo = [
                'tmp_name' => is_array($arquivosRecebidos['tmp_name'])
                    ? $arquivosRecebidos['tmp_name'][$indice]
                    : $arquivosRecebidos['tmp_name'],
                'error' => is_array($arquivosRecebidos['error'])
                    ? $arquivosRecebidos['error'][$indice]
                    : $arquivosRecebidos['error'],
                'size' => is_array($arquivosRecebidos['size'])
                    ? $arquivosRecebidos['size'][$indice]
                    : $arquivosRecebidos['size'],
            ];
            if ($arquivo['error'] !== UPLOAD_ERR_OK || $arquivo['size'] > 5 * 1024 * 1024) {
                respostaPublicacao(['erro' => 'Cada imagem deve ter no máximo 5 MB.'], 422);
            }
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($arquivo['tmp_name']);
            if (!isset($extensoes[$mime])) {
                respostaPublicacao(['erro' => 'Use imagens JPG, PNG ou WEBP.'], 422);
            }
            $arquivosImagem[] = [
                'tmp_name' => $arquivo['tmp_name'],
                'nome' => bin2hex(random_bytes(16)) . '.' . $extensoes[$mime],
            ];
        }
    }

    $conexao = new Conexao();
    $plano = $conexao->consultaPreparada(
        'SELECT dias FROM assinatura WHERE assCodigo = :id',
        ['id' => (int) ($_POST['assCodigo'] ?? 0)]
    )->fetch();
    if (!$plano) {
        respostaPublicacao(['erro' => 'Plano de assinatura inválido.'], 422);
    }

    $titulo = trim((string) ($_POST['titulo'] ?? ''));
    $descricao = trim((string) ($_POST['descricao'] ?? ''));
    $cidade = trim((string) ($_POST['cidade'] ?? ''));
    $contato = trim((string) ($_POST['contato'] ?? ''));
    $valorDiaria = (float) ($_POST['valorDiaria'] ?? 0);
    $capacidade = (int) ($_POST['capacidade'] ?? 0);
    if ($titulo === '' || strlen($titulo) > 100 || $descricao === '' || $cidade === ''
        || strlen($cidade) > 45 || $contato === '' || strlen($contato) > 20
        || $valorDiaria <= 0 || $capacidade <= 0) {
        respostaPublicacao(['erro' => 'Preencha os dados do rancho corretamente.'], 422);
    }

    $dataExpiracao = (new DateTimeImmutable('today'))
        ->modify('+' . (int) $plano['dias'] . ' days')
        ->format('Y-m-d');
    $ranCodigo = (int) $conexao->consultaPreparada(
        'SELECT COALESCE(MAX(ranCodigo), 0) + 1 FROM rancho'
    )->fetchColumn();
    $sql = 'INSERT INTO rancho
        (ranCodigo, proCodigo, assCodigo, titulo, descricao, cidade, valorDiaria, contato,
         capacidadePessoas, dataExpiracao, ativo)
        VALUES (:ranCodigo, :proCodigo, :assCodigo, :titulo, :descricao, :cidade, :valorDiaria,
         :contato, :capacidade, :dataExpiracao, 1)';
    $ok = $conexao->executarPreparado($sql, [
        'ranCodigo' => $ranCodigo,
        'proCodigo' => (int) $_SESSION['proCodigo'],
        'assCodigo' => (int) $_POST['assCodigo'],
        'titulo' => $titulo,
        'descricao' => $descricao,
        'cidade' => $cidade,
        'valorDiaria' => $valorDiaria,
        'contato' => $contato,
        'capacidade' => $capacidade,
        'dataExpiracao' => $dataExpiracao,
    ]);
    if (!$ok) {
        respostaPublicacao(['erro' => 'Não foi possível publicar o rancho.'], 422);
    }

    if ($arquivosImagem) {
        $diretorio = __DIR__ . '/../uploads/ranchos';
        if (!is_dir($diretorio) && !mkdir($diretorio, 0755, true) && !is_dir($diretorio)) {
            respostaPublicacao(['erro' => 'Não foi possível criar o diretório de imagens.'], 500);
        }
        $imgCodigo = (int) $conexao->consultaPreparada(
            'SELECT COALESCE(MAX(imgCodigo), 0) + 1 FROM imagensrancho'
        )->fetchColumn();
        foreach ($arquivosImagem as $arquivo) {
            if (!move_uploaded_file($arquivo['tmp_name'], $diretorio . '/' . $arquivo['nome'])) {
                respostaPublicacao(['erro' => 'Não foi possível salvar uma das imagens.'], 500);
            }
            $conexao->executarPreparado(
                'INSERT INTO imagensrancho (imgCodigo, imagem, ranCodigo)
                 VALUES (:imgCodigo, :imagem, :ranCodigo)',
                [
                    'imgCodigo' => $imgCodigo++,
                    'imagem' => 'uploads/ranchos/' . $arquivo['nome'],
                    'ranCodigo' => $ranCodigo,
                ]
            );
        }
    }

    respostaPublicacao(['ok' => true, 'ranCodigo' => $ranCodigo], 201);
} catch (Throwable $erro) {
    error_log('Falha ao publicar rancho: ' . $erro->getMessage());
    respostaPublicacao(['erro' => 'Não foi possível publicar o rancho.'], 500);
}
