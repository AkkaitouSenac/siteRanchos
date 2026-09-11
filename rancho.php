<?php
require_once __DIR__ . '/painel/classe/conexao.php';

$codigo = filter_input(INPUT_GET, 'codigo', FILTER_VALIDATE_INT);
$rancho = null;
$imagens = [];

if ($codigo) {
    try {
        $conexao = new Conexao();
        $rancho = $conexao->consultaPreparada(
            'SELECT ranCodigo, titulo, descricao, cidade, valorDiaria, contato,
                    capacidadePessoas, dataExpiracao
             FROM rancho
             WHERE ranCodigo = :codigo AND ativo = 1
                   AND dataExpiracao >= CURDATE()',
            ['codigo' => $codigo]
        )->fetch();
        if ($rancho) {
            $imagens = $conexao->consultaPreparada(
                'SELECT imagem FROM imagensrancho
                 WHERE ranCodigo = :codigo ORDER BY imgCodigo',
                ['codigo' => $codigo]
            )->fetchAll();
        }
    } catch (Throwable $erro) {
        error_log('Falha ao carregar detalhes do rancho: ' . $erro->getMessage());
    }
}

function h($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $rancho ? h($rancho['titulo']) : 'Rancho não encontrado'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="bg-primary text-white py-4">
        <div class="container">
            <a class="navbar-brand text-white fw-bold" href="index.php">Ranchos Araçatuba & Região</a>
        </div>
    </header>
    <main class="container py-5">
        <?php if (!$rancho): ?>
            <div class="alert alert-warning">Este anúncio não está disponível.</div>
            <a href="anuncios.html" class="btn btn-primary">Voltar aos anúncios</a>
        <?php else: ?>
            <a href="anuncios.html" class="btn btn-outline-primary mb-4">Voltar aos anúncios</a>
            <article class="card shadow-sm">
                <?php if ($imagens): ?>
                    <div class="row g-2 p-3">
                        <?php foreach ($imagens as $imagem): ?>
                            <div class="col-md-4">
                                <img class="img-fluid rounded w-100" src="<?php echo h($imagem['imagem']); ?>"
                                    alt="Imagem de <?php echo h($rancho['titulo']); ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <div class="card-body">
                    <h1 class="h2"><?php echo h($rancho['titulo']); ?></h1>
                    <p><?php echo nl2br(h($rancho['descricao'])); ?></p>
                    <p><strong>Localização:</strong> <?php echo h($rancho['cidade']); ?></p>
                    <p><strong>Capacidade:</strong> até <?php echo (int) $rancho['capacidadePessoas']; ?> pessoas</p>
                    <p><strong>Diária:</strong> R$ <?php echo h($rancho['valorDiaria']); ?></p>
                    <a class="btn btn-success" href="https://wa.me/<?php echo preg_replace('/\D+/', '', $rancho['contato']); ?>"
                        target="_blank" rel="noopener">Entrar em contato pelo WhatsApp</a>
                </div>
            </article>
        <?php endif; ?>
    </main>
</body>
</html>
