<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../painel/classe/conexao.php';

try {
    $conexao = new Conexao();
    $ranchos = $conexao->consultaPreparada(
        'SELECT ranCodigo, proCodigo, assCodigo, titulo, descricao, cidade,
                valorDiaria, contato, capacidadePessoas, endereco, qtdQuartos,
                dataExpiracao, ativo
         FROM rancho
         WHERE ativo = 1 AND dataExpiracao >= CURDATE()
         ORDER BY ranCodigo DESC'
    )->fetchAll();

    $assinaturas = $conexao->consultaPreparada(
        'SELECT assCodigo, nome, dias, valor
         FROM assinatura
         ORDER BY assCodigo'
    )->fetchAll();

    echo json_encode([
        'ranchos' => $ranchos,
        'assinaturas' => $assinaturas,
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
} catch (Throwable $erro) {
    http_response_code(500);
    error_log('Falha ao carregar anúncios públicos: ' . $erro->getMessage());
    echo json_encode([
        'erro' => 'Não foi possível carregar os anúncios.',
    ], JSON_UNESCAPED_UNICODE);
}
