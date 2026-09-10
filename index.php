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
                    <a href="anuncios.html?login=1" class="btn btn-login-top order-1 me-3">Entrar</a>
                    <a class="navbar-brand fw-bold order-2 me-auto" href="index.php">Ranchos Araçatuba &
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
            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title">Rancho do Bosque</h4>
                        <p class="card-text">Beira de lago, 3 quartos, cozinha equipada, área externa para churrasco e
                            acesso fácil à cidade.</p>
                        <p class="mb-1"><strong>Diária:</strong> R$ 800</p>
                        <p class="mb-0"><strong>Status:</strong> Anúncio ativo</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title">Rancho dos Amigos</h4>
                        <p class="card-text">Áreas verdes, acomodação para 12 pessoas, banheiro com água quente e
                            estacionamento privativo.</p>
                        <p class="mb-1"><strong>Diária:</strong> R$ 1.050</p>
                        <p class="mb-0"><strong>Status:</strong> Anúncio ativo</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title">Rancho Vila Verde</h4>
                        <p class="card-text">Espaço tranquilo com fogão a lenha, rede, piscina exclusiva e suporte para
                            eventos de fim de semana.</p>
                        <p class="mb-1"><strong>Diária:</strong> R$ 950</p>
                        <p class="mb-0"><strong>Status:</strong> Anúncio ativo</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title">Rancho Recanto Sul</h4>
                        <p class="card-text">Casa com varanda ampla, rede de descanso, cozinha completa e localização
                            próxima à rodovia.</p>
                        <p class="mb-1"><strong>Diária:</strong> R$ 870</p>
                        <p class="mb-0"><strong>Status:</strong> Anúncio ativo</p>
                    </div>
                </div>
            </div>
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
</body>

</html>
