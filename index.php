<?php
$ranchos = [];
try {
    require_once __DIR__ . '/painel/classe/conexao.php';
    $conexao = new Conexao();
    $ranchos = $conexao->consultaPreparada(
        'SELECT ranCodigo, titulo, descricao, valorDiaria
         FROM rancho
         WHERE ativo = 1 AND dataExpiracao >= CURDATE()
         ORDER BY ranCodigo DESC'
    )->fetchAll();
} catch (Throwable $erro) {
    error_log('Falha ao carregar ranchos em destaque: ' . $erro->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Security-Policy"
        content="default-src 'self' https://cdn.jsdelivr.net; style-src 'self' https://cdn.jsdelivr.net; script-src 'self' https://cdn.jsdelivr.net; font-src 'self' https://cdn.jsdelivr.net; object-src 'none'; base-uri 'self';">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranchos Araçatuba & Região - Aluguel Sem Cadastro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous"
        referrerpolicy="no-referrer">
    <link rel="stylesheet" href="styles.css">
</head>

<body class="bg-light text-dark">
    <header class="bg-primary text-white py-4">
        <div class="container">
            <nav class="navbar navbar-expand-lg navbar-dark">
                <div class="d-flex align-items-center w-100 flex-wrap">
                    <a class="navbar-brand fw-bold me-auto" href="index.php">Ranchos Araçatuba &
                        Região</a>
                    <button class="navbar-toggler order-3" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false"
                        aria-label="Alternar navegação">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse order-4" id="navMenu">
                        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                            <li class="nav-item"><a class="nav-link text-white" href="index.php">Início</a></li>
                            <li class="nav-item"><a class="nav-link text-white" href="sobre.html">Sobre</a></li>
                            <li class="nav-item"><a class="nav-link text-white" href="anuncios.html">Anúncios</a>
                            </li>
                            <li class="nav-item"><a class="nav-link text-white" href="contato.html">Contato</a>
                            </li>
                        </ul>
                        <div id="authArea"></div>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <main class="container my-5">
        <section class="card shadow-sm mb-5" id="sobre">
            <div class="card-body">
                <h2 class="card-title">Ranchos em destaque</h2>
                <p class="card-text">Veja os ranchos selecionados para aluguel em Araçatuba e região. Os visitantes
                    comuns visualizam somente o valor da diária; a assinatura do anúncio é fixa e contratada pelo
                    proprietário ao publicar o rancho.</p>
                <span class="badge badge-plan">Diárias visíveis para locatários</span>
            </div>
        </section>

        <section class="row g-4 mb-5" id="ranchos">
            <?php if (!$ranchos): ?>
                <div class="col-12">
                    <div class="alert alert-info">Nenhum rancho ativo disponível no momento.</div>
                </div>
            <?php else: ?>
                <?php foreach ($ranchos as $rancho): ?>
                    <div class="col-md-6">
                        <a class="card h-100 shadow-sm text-decoration-none text-reset"
                            href="rancho.php?codigo=<?php echo (int) $rancho['ranCodigo']; ?>">
                            <div class="card-body">
                                <h4 class="card-title"><?php echo htmlspecialchars($rancho['titulo'], ENT_QUOTES, 'UTF-8'); ?></h4>
                                <p class="card-text"><?php echo htmlspecialchars($rancho['descricao'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="mb-1"><strong>Diária:</strong> R$ <?php echo htmlspecialchars($rancho['valorDiaria'], ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                                </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <section class="card shadow-sm mb-5" id="como-funciona">
            <div class="card-body">
                <h2 class="card-title">Como funciona</h2>
                <p class="card-text">Buscadores podem ver os ranchos disponíveis e o preço da diária, sem
                    cadastro. Proprietários fazem login exclusivo, criam o anúncio e escolhem um plano de
                    assinatura para publicar o rancho. O pagamento da assinatura é feito pelo dono, e o visitante
                    aluga direto com o proprietário pelo contato do anúncio.</p>
            </div>
        </section>
    </main>

    <footer class="bg-white py-4">
        <div class="container text-center text-muted">
            <p class="mb-0">© 2026 Ranchos Araçatuba & Região. Divulgação de ranchos de aluguel sem cadastro para
                visitantes.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"
        referrerpolicy="no-referrer"></script>
    <script src="session-header.js"></script>
</body>

</html>
