<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../painel/classe/conexao.php';

function respostaEdicao(array $dados, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $proprietario = (int) ($_SESSION['proCodigo'] ?? 0);
    $codigo = (int) ($_POST['ranCodigo'] ?? 0);
    if (!$proprietario || !$codigo) {
        respostaEdicao(['erro' => 'Sessão ou anúncio inválido.'], 401);
    }

    $conexao = new Conexao();
    $dono = $conexao->consultaPreparada(
        'SELECT 1 FROM rancho WHERE ranCodigo = :ranCodigo AND proCodigo = :proCodigo',
        ['ranCodigo' => $codigo, 'proCodigo' => $proprietario]
    )->fetchColumn();
    if (!$dono) {
        respostaEdicao(['erro' => 'Você não pode editar este anúncio.'], 403);
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
        respostaEdicao(['erro' => 'Preencha os dados do rancho corretamente.'], 422);
    }

    $ok = $conexao->executarPreparado(
        'UPDATE rancho
         SET titulo = :titulo, descricao = :descricao, cidade = :cidade,
             valorDiaria = :valorDiaria, contato = :contato,
             capacidadePessoas = :capacidade
         WHERE ranCodigo = :ranCodigo AND proCodigo = :proCodigo',
        [
            'titulo' => $titulo,
            'descricao' => $descricao,
            'cidade' => $cidade,
            'valorDiaria' => $valorDiaria,
            'contato' => $contato,
            'capacidade' => $capacidade,
            'ranCodigo' => $codigo,
            'proCodigo' => $proprietario,
        ]
    );
    $ordem = $_POST['ordemImagens'] ?? [];
    if (!is_array($ordem)) {
        $ordem = [$ordem];
    }
    $imagens = $conexao->consultaPreparada(
        'SELECT imgCodigo FROM imagensrancho
         WHERE ranCodigo = :ranCodigo ORDER BY imgCodigo',
        ['ranCodigo' => $codigo]
    )->fetchAll(PDO::FETCH_COLUMN);
    $imagens = array_map('intval', $imagens);
    $ordem = array_values(array_unique(array_map('intval', $ordem)));
    if ($ordem && count($ordem) === count($imagens) && !array_diff($ordem, $imagens)) {
        foreach ($imagens as $indice => $imagemCodigo) {
            $conexao->executarPreparado(
                'UPDATE imagensrancho SET imgCodigo = :temporario WHERE imgCodigo = :codigo',
                ['temporario' => -1000000000 - $indice, 'codigo' => $imagemCodigo]
            );
        }
        foreach ($ordem as $indice => $imagemCodigo) {
            $conexao->executarPreparado(
                'UPDATE imagensrancho SET imgCodigo = :novoCodigo WHERE imgCodigo = :temporario',
                [
                    'novoCodigo' => $imagens[$indice],
                    'temporario' => -1000000000 - array_search($imagemCodigo, $imagens, true),
                ]
            );
        }
    }
    respostaEdicao(
        $ok ? ['ok' => true] : ['erro' => 'Não foi possível editar o anúncio.'],
        $ok ? 200 : 422
    );
} catch (Throwable $erro) {
    error_log('Falha ao editar rancho: ' . $erro->getMessage());
    respostaEdicao(['erro' => 'Não foi possível editar o anúncio.'], 500);
}
