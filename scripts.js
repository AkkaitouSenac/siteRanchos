const assinaturas = [
    { assCodigo: 1, nome: 'Bimestral', dias: 60, valor: 129 },
    { assCodigo: 2, nome: 'Trimestral', dias: 90, valor: 179 },
    { assCodigo: 3, nome: 'Semestral', dias: 180, valor: 319 },
    { assCodigo: 4, nome: 'Anual', dias: 365, valor: 549 },
    { assCodigo: 5, nome: 'Trienal', dias: 1095, valor: 1299 }
];

const diasAtras = (n) => {
    const d = new Date();
    d.setDate(d.getDate() - n);
    return d;
};

const somarDias = (data, dias) => {
    const d = new Date(data);
    d.setDate(d.getDate() + dias);
    return d;
};

const owners = [
    { proCodigo: 1, nome: 'Carlos Mendes', email: 'carlos@exemplo.com', telefone: '(18) 99999-1111', senha: '1234' },
    { proCodigo: 2, nome: 'Fernanda Silva', email: 'fernanda@exemplo.com', telefone: '(18) 99999-2222', senha: '1234' }
];

const ranchos = [
    {
        ranCodigo: 1,
        proCodigo: 1,
        titulo: 'Rancho do Bosque',
        descricao: 'Beira de lago, 3 quartos, cozinha equipada, área externa para churrasco e acesso fácil à cidade.',
        cidade: 'Araçatuba',
        valorDiaria: 800,
        contato: '(18) 99888-1010',
        capacidadePessoas: 8,
        endereco: 'Estrada Municipal, km 4',
        qtdQuartos: 3,
        assCodigo: 4,
        dataCriacao: diasAtras(100),
        ativo: true
    },
    {
        ranCodigo: 2,
        proCodigo: 1,
        titulo: 'Rancho dos Amigos',
        descricao: 'Áreas verdes, acomodação para 12 pessoas, banheiro com água quente e estacionamento privativo.',
        cidade: 'Bento de Abreu',
        valorDiaria: 1050,
        contato: '(18) 99888-2020',
        capacidadePessoas: 12,
        endereco: 'Rodovia Vicinal, s/n',
        qtdQuartos: 4,
        assCodigo: 1,
        dataCriacao: diasAtras(55),
        ativo: true
    },
    {
        ranCodigo: 3,
        proCodigo: 2,
        titulo: 'Rancho Vila Verde',
        descricao: 'Espaço tranquilo com fogão a lenha, rede, piscina exclusiva e suporte para eventos de fim de semana.',
        cidade: 'Valparaíso',
        valorDiaria: 950,
        contato: '(18) 99888-3030',
        capacidadePessoas: 10,
        endereco: 'Chácara Vila Verde, lote 12',
        qtdQuartos: 3,
        assCodigo: 5,
        dataCriacao: diasAtras(30),
        ativo: true
    },
    {
        ranCodigo: 4,
        proCodigo: 2,
        titulo: 'Rancho Recanto Sul',
        descricao: 'Casa com varanda ampla, rede de descanso, cozinha completa e localização próxima à rodovia.',
        cidade: 'Birigui',
        valorDiaria: 870,
        contato: '(18) 99888-4040',
        capacidadePessoas: 6,
        endereco: 'Av. Recanto Sul, 220',
        qtdQuartos: 2,
        assCodigo: 2,
        dataCriacao: diasAtras(100),
        ativo: true
    }
];

ranchos.forEach(r => {
    const plano = assinaturas.find(a => a.assCodigo === r.assCodigo);
    r.dataExpiracao = somarDias(r.dataCriacao, plano ? plano.dias : 0);
});

let nextRanCodigo = ranchos.length + 1;
let nextProCodigo = owners.length + 1;
let activeOwner = null;

const listings = document.getElementById('listings');
const searchText = document.getElementById('searchText');
const locationSelect = document.getElementById('locationSelect');
const priceRange = document.getElementById('priceRange');
const priceValue = document.getElementById('priceValue');
const capacitySelect = document.getElementById('capacitySelect');
const filterForm = document.getElementById('filter-form');

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

let authModal, listingModal;
if (window.bootstrap) {
    authModal = bootstrap.Modal.getOrCreateInstance(authModalEl);
    listingModal = bootstrap.Modal.getOrCreateInstance(listingModalEl);
}

const createListingCard = (item) => {
    const col = document.createElement('div');
    col.className = 'col-12';

    const card = document.createElement('div');
    card.className = 'card h-100 shadow-sm';

    const body = document.createElement('div');
    body.className = 'card-body';

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
        const matchesCapacity = capacity === 'all' || item.capacidadePessoas <= Number(capacity);
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
});
priceRange.addEventListener('input', () => {
    priceValue.textContent = `R$ ${priceRange.value}`;
    updateFilters();
});
searchText.addEventListener('input', updateFilters);
locationSelect.addEventListener('change', updateFilters);
capacitySelect.addEventListener('change', updateFilters);

const findOwnerByEmail = (email) => owners.find(o => o.email.toLowerCase() === email.toLowerCase());

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
    activeOwner = null;
    renderAuthArea();
    painelTabItem.classList.add('d-none');
    if (window.bootstrap) {
        bootstrap.Tab.getOrCreateInstance(document.getElementById('browse-tab')).show();
    }
};

loginForm.addEventListener('submit', (event) => {
    event.preventDefault();
    const email = document.getElementById('loginEmail').value.trim();
    const senha = document.getElementById('loginSenha').value;
    const owner = findOwnerByEmail(email);

    if (!owner || owner.senha !== senha) {
        loginFeedback.textContent = 'Email ou senha inválidos.';
        return;
    }

    loginFeedback.textContent = '';
    loginForm.reset();
    if (authModal) authModal.hide();
    setActiveOwner(owner);
});

registerForm.addEventListener('submit', (event) => {
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
    if (findOwnerByEmail(email)) {
        registerFeedback.textContent = 'Este email já está cadastrado. Faça login.';
        return;
    }

    const newOwner = { proCodigo: nextProCodigo++, nome, email, telefone, senha };
    owners.push(newOwner);

    registerFeedback.textContent = '';
    registerForm.reset();
    if (authModal) authModal.hide();
    setActiveOwner(newOwner);
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

    body.append(title, location, badges, criado);
    card.appendChild(body);
    col.appendChild(card);
    return col;
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
    listingStatus.textContent = '';
    listingForm.reset();
    renderPlanOptions();
});

listingForm.addEventListener('submit', (event) => {
    event.preventDefault();

    if (!activeOwner) {
        listingStatus.textContent = 'Você precisa estar logado como proprietário para publicar um anúncio.';
        listingStatus.className = 'mt-3 text-danger';
        return;
    }

    const selected = document.querySelector('input[name="subscriptionPlan"]:checked');
    const plano = selected ? assinaturas.find(a => a.assCodigo === Number(selected.value)) : null;

    if (!plano) {
        listingStatus.textContent = 'Escolha um plano de assinatura antes de publicar o anúncio.';
        listingStatus.className = 'mt-3 text-danger';
        return;
    }

    const agora = new Date();
    const newListing = {
        ranCodigo: nextRanCodigo++,
        proCodigo: activeOwner.proCodigo,
        titulo: listingName.value.trim(),
        descricao: listingDescription.value.trim(),
        cidade: listingLocation.value,
        valorDiaria: Number(listingPrice.value),
        contato: listingContato.value.trim(),
        capacidadePessoas: Number(listingCapacity.value),
        endereco: '',
        qtdQuartos: null,
        assCodigo: plano.assCodigo,
        dataCriacao: agora,
        dataExpiracao: somarDias(agora, plano.dias),
        ativo: true
    };

    ranchos.unshift(newListing);

    listingForm.reset();
    if (listingModal) listingModal.hide();
    renderPainel();
    updateFilters();
});

renderAuthArea();
updateFilters();

const params = new URLSearchParams(window.location.search);
if (params.get('login') === '1' && authModal) {
    authModal.show();
}
