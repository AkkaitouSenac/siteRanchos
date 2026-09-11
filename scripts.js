let assinaturas = [];

let ranchos = [];
let nextRanCodigo = 1;
let activeOwner = null;

const somarDias = (data, dias) => {
    const resultado = new Date(data);
    resultado.setDate(resultado.getDate() + Number(dias));
    return resultado;
};

const listings = document.getElementById('listings');
const featuredListings = document.getElementById('featuredListings');
const searchText = document.getElementById('searchText');
const locationSelect = document.getElementById('locationSelect');
const priceRange = document.getElementById('priceRange');
const priceValue = document.getElementById('priceValue');
const capacitySelect = document.getElementById('capacitySelect');
const filterForm = document.getElementById('filter-form');
const clearFilters = document.getElementById('clear-filters');

const authArea = document.getElementById('authArea');
const authModalEl = document.getElementById('authModal');
const loginForm = document.getElementById('loginForm');
const registerForm = document.getElementById('registerForm');
const loginFeedback = document.getElementById('loginFeedback');
const registerFeedback = document.getElementById('registerFeedback');

const painelTabItem = document.getElementById('painelTabItem');
const painelTabButton = document.getElementById('painel-tab');
const meusAnuncios = document.getElementById('meusAnuncios');
const painelSubtitle = document.getElementById('painelSubtitle');
const btnNovoAnuncio = document.getElementById('btnNovoAnuncio');

const listingModalEl = document.getElementById('listingModal');
const listingForm = document.getElementById('listing-form');
const listingStatus = document.getElementById('listingStatus');
const planOptions = document.getElementById('planOptions');
const listingName = document.getElementById('listingName');
const listingDescription = document.getElementById('listingDescription');
const listingLocation = document.getElementById('listingLocation');
const listingPrice = document.getElementById('listingPrice');
const listingCapacity = document.getElementById('listingCapacity');
const listingContato = document.getElementById('listingContato');
const listingImagem = document.getElementById('listingImagem');
let anuncioEmEdicao = null;

let authModal, listingModal;
if (window.bootstrap) {
    authModal = bootstrap.Modal.getOrCreateInstance(authModalEl);
    listingModal = bootstrap.Modal.getOrCreateInstance(listingModalEl);
}

const createListingCard = (item) => {
    const col = document.createElement('div');
    col.className = 'col-md-6';

    const card = document.createElement('div');
    card.className = 'card public-listing-card h-100 shadow-sm';
    card.addEventListener('click', () => {
        window.location.href = `rancho.php?codigo=${encodeURIComponent(item.ranCodigo)}`;
    });
    card.style.cursor = 'pointer';

    const body = document.createElement('div');
    body.className = 'card-body';

    if (item.imagem) {
        const image = document.createElement('img');
        image.className = 'card-img-top public-listing-image';
        image.src = item.imagem;
        image.alt = `Imagem de ${item.titulo}`;
        card.appendChild(image);
    }

    const title = document.createElement('h4');
    title.className = 'card-title';
    title.textContent = item.titulo;

    const description = document.createElement('p');
    description.className = 'card-text';
    description.textContent = item.descricao;

    const createDataLine = (label, value) => {
        const line = document.createElement('p');
        line.className = 'mb-1';
        const strong = document.createElement('strong');
        strong.textContent = label;
        line.appendChild(strong);
        line.appendChild(document.createTextNode(' ' + value));
        return line;
    };

    body.append(
        title,
        description,
        createDataLine('Localização:', item.cidade),
        createDataLine('Preço por diária:', item.valorDiaria === '-' ? item.valorDiaria : `R$ ${item.valorDiaria}`),
        createDataLine('Capacidade:', item.capacidadePessoas === '-' ? item.capacidadePessoas : `até ${item.capacidadePessoas} pessoas`),
        createDataLine('Contato:', item.contato || '-')
    );

    card.appendChild(body);
    col.appendChild(card);
    return col;
};

const renderListings = (items) => {
    listings.textContent = '';
    items.forEach(item => listings.appendChild(createListingCard(item)));
};

const renderFeaturedListings = () => {
    if (!featuredListings) return;
    featuredListings.textContent = '';
    ranchos
        .filter((rancho) => rancho.ativo && rancho.dataExpiracao > new Date())
        .slice(0, 3)
        .forEach((rancho) => featuredListings.appendChild(createListingCard(rancho)));
};

const updateFilters = () => {
    const search = searchText.value.toLowerCase();
    const location = locationSelect.value;
    const maxPrice = Number(priceRange.value);
    const capacity = capacitySelect.value;
    const hoje = new Date();

    const publicos = ranchos.filter(r => r.ativo && r.dataExpiracao > hoje);

    const filtered = publicos.filter(item => {
        const matchesSearch = item.titulo.toLowerCase().includes(search) || item.descricao.toLowerCase().includes(search);
        const matchesLocation = location === 'all' || item.cidade === location;
        const matchesPrice = item.valorDiaria <= maxPrice;
        const matchesCapacity = capacity === 'all'
            || (capacity === '18+' && item.capacidadePessoas >= 18)
            || (capacity !== '18+' && item.capacidadePessoas <= Number(capacity));
        return matchesSearch && matchesLocation && matchesPrice && matchesCapacity;
    });

    renderListings(filtered.length ? filtered : [{
        titulo: 'Nenhum anúncio encontrado',
        descricao: 'Ajuste os filtros para ver mais opções de ranchos disponíveis.',
        cidade: '',
        valorDiaria: '-',
        capacidadePessoas: '-',
        contato: ''
    }]);
};

filterForm.addEventListener('submit', (event) => {
    event.preventDefault();
    updateFilters();
    renderFeaturedListings();
});
priceRange.addEventListener('input', () => {
    priceValue.textContent = `R$ ${priceRange.value}`;
});
clearFilters.addEventListener('click', () => {
    filterForm.reset();
    priceValue.textContent = `R$ ${priceRange.value}`;
    updateFilters();
});

const carregarDadosPublicos = async () => {
    try {
        const resposta = await fetch('api/anuncios.php', {
            headers: { Accept: 'application/json' }
        });
        if (!resposta.ok) {
            throw new Error('Falha ao carregar os anúncios.');
        }

        const dados = await resposta.json();
        ranchos = Array.isArray(dados.ranchos) ? dados.ranchos.map((rancho) => ({
            ...rancho,
            ranCodigo: Number(rancho.ranCodigo),
            proCodigo: rancho.proCodigo === null ? null : Number(rancho.proCodigo),
            assCodigo: rancho.assCodigo === null ? null : Number(rancho.assCodigo),
            valorDiaria: Number(rancho.valorDiaria),
            capacidadePessoas: Number(rancho.capacidadePessoas),
            ativo: Number(rancho.ativo) === 1,
            dataCriacao: new Date(String(rancho.dataCriacao).replace(' ', 'T')),
            dataExpiracao: new Date(String(rancho.dataExpiracao).replace(' ', 'T'))
        })) : [];
        assinaturas = Array.isArray(dados.assinaturas) ? dados.assinaturas.map((plano) => ({
            ...plano,
            assCodigo: Number(plano.assCodigo),
            dias: Number(plano.dias),
            valor: Number(plano.valor)
        })) : [];
        renderPlanOptions();
        nextRanCodigo = ranchos.reduce((maior, rancho) => Math.max(maior, rancho.ranCodigo), 0) + 1;
        updateFilters();
        if (activeOwner) renderPainel();
    } catch (erro) {
        console.error(erro);
        renderListings([{
            titulo: 'Não foi possível carregar os anúncios',
            descricao: 'Verifique a conexão com o banco de dados e tente novamente.',
            cidade: '',
            valorDiaria: '-',
            capacidadePessoas: '-',
            contato: ''
        }]);
    }
};

const renderAuthArea = () => {
    authArea.textContent = '';
    if (!activeOwner) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn-login-top';
        btn.textContent = 'Entrar';
        btn.setAttribute('data-bs-toggle', 'modal');
        btn.setAttribute('data-bs-target', '#authModal');
        authArea.appendChild(btn);
        return;
    }

    const chip = document.createElement('div');
    chip.className = 'auth-chip';

    const name = document.createElement('span');
    name.className = 'auth-name';
    name.textContent = activeOwner.nome.split(' ')[0];

    const painelLink = document.createElement('button');
    painelLink.type = 'button';
    painelLink.className = 'btn-chip-link';
    painelLink.textContent = 'Minha área';
    painelLink.addEventListener('click', irParaPainel);

    const logoutLink = document.createElement('button');
    logoutLink.type = 'button';
    logoutLink.className = 'btn-chip-link';
    logoutLink.textContent = 'Sair';
    logoutLink.addEventListener('click', fazerLogout);

    chip.append(name, painelLink, logoutLink);
    authArea.appendChild(chip);
};

const irParaPainel = () => {
    painelTabItem.classList.remove('d-none');
    if (window.bootstrap) {
        bootstrap.Tab.getOrCreateInstance(painelTabButton).show();
    }
};

const setActiveOwner = (owner) => {
    activeOwner = owner;
    renderAuthArea();
    renderPainel();
    irParaPainel();
};

const fazerLogout = () => {
    fetch('api/proprietario.php?acao=sair', { method: 'POST' })
        .finally(() => {
            activeOwner = null;
            renderAuthArea();
            painelTabItem.classList.add('d-none');
            if (window.bootstrap) {
                bootstrap.Tab.getOrCreateInstance(document.getElementById('browse-tab')).show();
            }
        });
};

const restaurarSessao = async () => {
    try {
        const resposta = await fetch('api/proprietario.php?acao=sessao', {
            headers: { Accept: 'application/json' }
        });
        if (!resposta.ok) return;
        const dados = await resposta.json();
        if (dados.proprietario) {
            setActiveOwner(dados.proprietario);
        } else {
            const params = new URLSearchParams(window.location.search);
            if (params.get('login') === '1' && authModal) authModal.show();
        }
        const params = new URLSearchParams(window.location.search);
        if (params.get('area') === '1' && activeOwner) irParaPainel();
    } catch (erro) {
        console.error('Não foi possível restaurar a sessão do proprietário.', erro);
    }
};

loginForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    const email = document.getElementById('loginEmail').value.trim();
    const senha = document.getElementById('loginSenha').value;
    try {
        const resposta = await fetch('api/proprietario.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ acao: 'login', email, senha })
        });
        const dados = await resposta.json();
        if (!resposta.ok) throw new Error(dados.erro);
        loginFeedback.textContent = '';
        loginForm.reset();
        if (authModal) authModal.hide();
        setActiveOwner(dados.proprietario);
    } catch (erro) {
        loginFeedback.textContent = erro.message || 'Não foi possível entrar.';
    }
});

registerForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    const nome = document.getElementById('regNome').value.trim();
    const email = document.getElementById('regEmail').value.trim();
    const telefone = document.getElementById('regTelefone').value.trim();
    const senha = document.getElementById('regSenha').value;
    const confirma = document.getElementById('regSenhaConfirma').value;

    if (senha !== confirma) {
        registerFeedback.textContent = 'As senhas não coincidem.';
        return;
    }
    try {
        const resposta = await fetch('api/proprietario.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ acao: 'cadastrar', nome, email, telefone, senha })
        });
        const dados = await resposta.json();
        if (!resposta.ok) throw new Error(dados.erro);
        registerFeedback.textContent = '';
        registerForm.reset();
        if (authModal) authModal.hide();
        setActiveOwner(dados.proprietario);
    } catch (erro) {
        registerFeedback.textContent = erro.message || 'Não foi possível criar a conta.';
    }
});

const formatarData = (data) => data.toLocaleDateString('pt-BR');

const tempoRestanteTexto = (dataExpiracao) => {
    const hoje = new Date();
    const diffMs = dataExpiracao - hoje;
    const dias = Math.ceil(diffMs / (1000 * 60 * 60 * 24));
    if (dias > 1) return `${dias} dias restantes`;
    if (dias === 1) return '1 dia restante';
    if (dias === 0) return 'Expira hoje';
    return `Expirado há ${Math.abs(dias)} dia(s)`;
};

const createOwnerListingCard = (item) => {
    const plano = assinaturas.find(a => a.assCodigo === item.assCodigo);
    const hoje = new Date();
    const expirado = item.dataExpiracao <= hoje;
    const ativo = item.ativo && !expirado;

    const col = document.createElement('div');
    col.className = 'col-md-6';

    const card = document.createElement('div');
    card.className = 'card listing-card h-100' + (ativo ? '' : ' is-expired');

    const body = document.createElement('div');
    body.className = 'card-body';

    const title = document.createElement('h5');
    title.className = 'listing-title';
    title.textContent = item.titulo;

    const location = document.createElement('p');
    location.className = 'text-muted small mb-2';
    location.textContent = `${item.cidade} · R$ ${item.valorDiaria}/diária · até ${item.capacidadePessoas} pessoas`;

    const badges = document.createElement('div');
    badges.className = 'd-flex flex-wrap gap-2 mb-3';

    const statusBadge = document.createElement('span');
    statusBadge.className = 'badge ' + (ativo ? 'bg-success' : 'bg-secondary');
    statusBadge.textContent = ativo ? 'Ativo' : 'Expirado';

    const planBadge = document.createElement('span');
    planBadge.className = 'badge badge-plan';
    planBadge.textContent = plano ? plano.nome : 'Sem plano';

    const timeBadge = document.createElement('span');
    const diasRestantes = Math.ceil((item.dataExpiracao - hoje) / (1000 * 60 * 60 * 24));
    timeBadge.className = 'badge ' + (ativo && diasRestantes <= 7 ? 'badge-expiring' : 'badge-plan');
    timeBadge.textContent = tempoRestanteTexto(item.dataExpiracao);

    badges.append(statusBadge, planBadge, timeBadge);

    const criado = document.createElement('p');
    criado.className = 'small text-muted mb-0';
    criado.textContent = `Criado em ${formatarData(item.dataCriacao)} · Expira em ${formatarData(item.dataExpiracao)}`;

    const editar = document.createElement('button');
    editar.type = 'button';
    editar.className = 'btn btn-outline-primary btn-sm me-2';
    editar.textContent = 'Editar anúncio';
    editar.addEventListener('click', () => abrirEdicao(item));

    const destaque = document.createElement('button');
    destaque.type = 'button';
    destaque.className = 'btn btn-outline-warning btn-sm';
    destaque.textContent = 'Destacar por R$ 20,00 / 7 dias';
    destaque.addEventListener('click', () => {
        window.alert('A taxa de destaque custa R$ 20,00 por 7 dias. O pagamento ainda não está integrado.');
    });

    body.append(title, location, badges, criado, editar, destaque);
    card.appendChild(body);
    col.appendChild(card);
    return col;
};

const abrirEdicao = (item) => {
    anuncioEmEdicao = item;
    listingForm.reset();
    document.getElementById('listingModalLabel').textContent = 'Editar anúncio';
    listingName.value = item.titulo;
    listingDescription.value = item.descricao;
    listingLocation.value = item.cidade;
    listingPrice.value = item.valorDiaria;
    listingCapacity.value = item.capacidadePessoas;
    listingContato.value = item.contato;
    planOptions.closest('.col-12').classList.add('d-none');
    listingImagem.closest('.col-12').classList.add('d-none');
    listingStatus.textContent = '';
    listingModal.show();
};

const renderPainel = () => {
    if (!activeOwner) return;

    meusAnuncios.textContent = '';
    const meus = ranchos.filter(r => r.proCodigo === activeOwner.proCodigo);

    painelSubtitle.textContent = `${activeOwner.nome} · ${meus.length} anúncio(s)`;

    if (!meus.length) {
        const empty = document.createElement('div');
        empty.className = 'col-12';
        empty.innerHTML = `
            <div class="empty-state">
                <p class="mb-2 fw-semibold">Você ainda não tem anúncios publicados.</p>
                <p class="mb-0">Clique em "Publicar novo anúncio" para cadastrar seu primeiro rancho.</p>
            </div>`;
        meusAnuncios.appendChild(empty);
        return;
    }

    meus
        .slice()
        .sort((a, b) => b.dataCriacao - a.dataCriacao)
        .forEach(item => meusAnuncios.appendChild(createOwnerListingCard(item)));
};

const renderPlanOptions = () => {
    planOptions.textContent = '';
    assinaturas.forEach((plano, index) => {
        const wrapper = document.createElement('div');
        wrapper.className = 'form-check';

        const input = document.createElement('input');
        input.className = 'form-check-input';
        input.type = 'radio';
        input.name = 'subscriptionPlan';
        input.id = `plan-${plano.assCodigo}`;
        input.value = plano.assCodigo;
        if (index === 0) input.checked = true;

        const label = document.createElement('label');
        label.className = 'form-check-label';
        label.setAttribute('for', `plan-${plano.assCodigo}`);
        label.textContent = `${plano.nome} (${plano.dias} dias) — R$ ${plano.valor}`;

        wrapper.append(input, label);
        planOptions.appendChild(wrapper);
    });
};

btnNovoAnuncio.addEventListener('click', () => {
    anuncioEmEdicao = null;
    document.getElementById('listingModalLabel').textContent = 'Publicar novo anúncio';
    listingStatus.textContent = '';
    listingForm.reset();
    planOptions.closest('.col-12').classList.remove('d-none');
    listingImagem.closest('.col-12').classList.remove('d-none');
    renderPlanOptions();
});

listingForm.addEventListener('submit', async (event) => {
    event.preventDefault();

    if (!activeOwner) {
        listingStatus.textContent = 'Você precisa estar logado como proprietário para publicar um anúncio.';
        listingStatus.className = 'mt-3 text-danger';
        return;
    }

    if (anuncioEmEdicao) {
        const dados = new URLSearchParams({
            ranCodigo: String(anuncioEmEdicao.ranCodigo),
            titulo: listingName.value.trim(),
            descricao: listingDescription.value.trim(),
            cidade: listingLocation.value,
            valorDiaria: listingPrice.value,
            contato: listingContato.value.trim(),
            capacidade: listingCapacity.value,
        });
        try {
            const resposta = await fetch('api/editar-rancho.php', { method: 'POST', body: dados });
            const resultado = await resposta.json();
            if (!resposta.ok) throw new Error(resultado.erro);
            listingStatus.textContent = 'Anúncio atualizado com sucesso.';
            listingStatus.className = 'mt-3 text-success';
            listingModal.hide();
            await carregarDadosPublicos();
        } catch (erro) {
            listingStatus.textContent = erro.message || 'Não foi possível editar o anúncio.';
            listingStatus.className = 'mt-3 text-danger';
        }
        return;
    }

    const selected = document.querySelector('input[name="subscriptionPlan"]:checked');
    const plano = selected ? assinaturas.find(a => a.assCodigo === Number(selected.value)) : null;

    if (!plano) {
        listingStatus.textContent = 'Escolha um plano de assinatura antes de publicar o anúncio.';
        listingStatus.className = 'mt-3 text-danger';
        return;
    }

    const dados = new FormData();
    dados.append('titulo', listingName.value.trim());
    dados.append('descricao', listingDescription.value.trim());
    dados.append('cidade', listingLocation.value);
    dados.append('valorDiaria', listingPrice.value);
    dados.append('contato', listingContato.value.trim());
    dados.append('capacidade', listingCapacity.value);
    dados.append('assCodigo', String(plano.assCodigo));
    Array.from(listingImagem.files).forEach((arquivo) => {
        dados.append('imagem[]', arquivo);
    });

    try {
        const resposta = await fetch('api/publicar-rancho.php', {
            method: 'POST',
            body: dados
        });
        const resultado = await resposta.json();
        if (!resposta.ok) throw new Error(resultado.erro);

        listingStatus.textContent = 'Anúncio publicado com sucesso.';
        listingStatus.className = 'mt-3 text-success';
        listingForm.reset();
        if (listingModal) listingModal.hide();
        await carregarDadosPublicos();
        renderPainel();
    } catch (erro) {
        listingStatus.textContent = erro.message || 'Não foi possível publicar o anúncio.';
        listingStatus.className = 'mt-3 text-danger';
    }
});

renderAuthArea();
updateFilters();
carregarDadosPublicos();
restaurarSessao();

const params = new URLSearchParams(window.location.search);
if (params.get('area') === '1' && activeOwner) {
    irParaPainel();
}
