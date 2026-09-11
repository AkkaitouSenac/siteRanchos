(() => {
    const authArea = document.getElementById('authArea');
    if (!authArea || document.getElementById('authModal')) return;

    const render = (owner) => {
        authArea.textContent = '';
        if (!owner) {
            const link = document.createElement('a');
            link.className = 'btn-login-top';
            link.href = 'anuncios.html?login=1';
            link.textContent = 'Entrar';
            authArea.appendChild(link);
            return;
        }

        const chip = document.createElement('div');
        chip.className = 'auth-chip';
        const name = document.createElement('span');
        name.className = 'auth-name';
        name.textContent = owner.nome.split(' ')[0];

        const area = document.createElement('a');
        area.className = 'btn-chip-link';
        area.href = 'anuncios.html?area=1';
        area.textContent = 'Minha área';

        const sair = document.createElement('button');
        sair.className = 'btn-chip-link';
        sair.type = 'button';
        sair.textContent = 'Sair';
        sair.addEventListener('click', async () => {
            await fetch('api/proprietario.php?acao=sair', { method: 'POST' });
            render(null);
        });

        chip.append(name, area, sair);
        authArea.appendChild(chip);
    };

    fetch('api/proprietario.php?acao=sessao', { headers: { Accept: 'application/json' } })
        .then((resposta) => resposta.ok ? resposta.json() : null)
        .then((dados) => render(dados && dados.proprietario ? dados.proprietario : null))
        .catch(() => render(null));
})();
