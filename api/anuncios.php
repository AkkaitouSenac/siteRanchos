<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../painel/classe/conexao.php';

try {
    $conexao = new Conexao();
    $ranchos = $conexao->consultaPreparada(
        'SELECT r.ranCodigo, r.proCodigo, r.assCodigo, r.titulo, r.descricao, r.cidade,
                valorDiaria, contato, capacidadePessoas, endereco, qtdQuartos,
                dataExpiracao, ativo,
                (SELECT i.imagem FROM imagensrancho i
                 WHERE i.ranCodigo = r.ranCodigo ORDER BY i.imgCodigo DESC LIMIT 1) AS imagem
         FROM rancho r
         WHERE r.ativo = 1 AND r.dataExpiracao >= CURDATE()
         ORDER BY r.ranCodigo DESC'
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
