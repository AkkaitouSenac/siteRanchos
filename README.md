# Ranchos Araçatuba e Região

Portal para divulgação de ranchos de aluguel em Araçatuba e região. O projeto possui uma área pública, autenticação de proprietários, publicação e edição de anúncios e painel administrativo integrado ao MySQL.

## Acesso local

Com Apache, PHP e MySQL em execução no XAMPP, acesse:

```text
http://localhost/projeto/
```

`index.php` redireciona para `anuncios.html`, que funciona como a home pública de ranchos.

O painel administrativo fica em:

```text
http://localhost/projeto/painel/login.php
```

O painel exige um administrador cadastrado na tabela `adm`.

## Funcionalidades

### Área pública

- Listagem de ranchos ativos e dentro do prazo de validade.
- Busca e filtros por cidade, capacidade e valor.
- Página individual do rancho em `rancho.php`.
- Galeria de imagens e botão de contato por WhatsApp.
- Cabeçalho com estado de autenticação persistido após atualizar a página.
- Navegação para Ranchos, Sobre e Contato.

### Área do proprietário

- Cadastro, login, logout e restauração da sessão.
- Publicação de ranchos com plano de assinatura existente.
- Upload múltiplo de imagens.
- Visualização dos próprios anúncios, inclusive para acompanhar status e validade.
- Edição de título, descrição, cidade, valor da diária, contato e capacidade.
- Validação de dados no navegador e no servidor.
- Bloqueio para que cada proprietário edite somente os próprios anúncios.

### Painel administrativo

- Autenticação por sessão.
- CRUD de administradores.
- CRUD de proprietários.
- CRUD de ranchos.
- CRUD manual de assinaturas.
- Consulta de logs do sistema.

Os planos de assinatura são criados manualmente pelo painel. O formulário público apenas lista planos cadastrados na tabela `assinatura`; nenhum plano é criado automaticamente pela API.

## APIs

| Endpoint | Função |
| --- | --- |
| `api/anuncios.php` | Retorna ranchos públicos, anúncios do proprietário autenticado e planos de assinatura. |
| `api/proprietario.php` | Realiza login, cadastro, logout e consulta da sessão do proprietário. |
| `api/publicar-rancho.php` | Publica um rancho autenticado e salva suas imagens. |
| `api/editar-rancho.php` | Edita um anúncio pertencente ao proprietário autenticado. |

As ações de proprietário são enviadas pelo campo `acao`:

- `login`: autentica por e-mail e senha.
- `cadastro`: cria uma conta com senha protegida por `password_hash`.
- `sair`: encerra a sessão.
- `sessao`: retorna os dados da sessão atual.

As APIs retornam JSON e usam códigos HTTP coerentes para sucesso, dados inválidos, falta de autenticação e conflitos.

## Validações e segurança

- Consultas ao banco com PDO e prepared statements.
- Senhas armazenadas com `password_hash` e verificadas com `password_verify`.
- Regeneração do identificador de sessão após login.
- Verificação de propriedade antes de editar anúncios.
- Validação server-side de campos obrigatórios, tamanhos, diária e capacidade.
- Bloqueio de cadastro com e-mail já existente.
- Escape de dados exibidos no HTML.
- Upload limitado a JPG, PNG e WEBP, com até 5 MB por arquivo.
- Status e validade dos anúncios não são exibidos para visitantes; ficam disponíveis para o proprietário e para o administrador.

## Banco de dados

O modelo está em `banco/Ranchos.mwb` e utiliza o schema `ranchos`.

A conexão local fica em `painel/classe/conexao.php` e usa:

```text
Host: localhost
Porta: 3306
Banco: ranchos
Usuário: root
```

Configure a senha nessa classe de acordo com a instalação local do MySQL. Em ambientes compartilhados ou de produção, prefira variáveis de ambiente em vez de manter credenciais no código.

Tabelas principais:

- `adm`
- `proprietario`
- `assinatura`
- `rancho`
- `imagensrancho`
- `visitas`
- `log`

Algumas chaves do banco atual não usam `AUTO_INCREMENT`. Por isso, os endpoints geram códigos com base no maior código existente. Essa solução mantém compatibilidade com o modelo atual, mas o schema deve ser revisado antes de uso com várias gravações simultâneas.

## Imagens

Os arquivos enviados ficam em:

```text
uploads/ranchos
```

O banco armazena o caminho de cada arquivo na tabela `imagensrancho`. A pasta precisa ter permissão de escrita para o usuário do Apache.

## Estrutura relevante

```text
/
├── anuncios.html          # Home pública
├── index.php              # Redirecionamento para a home
├── rancho.php             # Página individual
├── sobre.html
├── contato.html
├── scripts.js             # Fluxos públicos e área do proprietário
├── session-header.js      # Cabeçalho autenticado das páginas PHP/estáticas
├── styles.css
├── api/
├── painel/
├── banco/
└── uploads/ranchos/
```

## Verificação local

Com o PHP do XAMPP instalado em `C:\xampp\php`, os comandos de verificação são:

```powershell
C:\xampp\php\php.exe -l api\proprietario.php
C:\xampp\php\php.exe -l api\publicar-rancho.php
C:\xampp\php\php.exe -l api\editar-rancho.php
node --check scripts.js
node --check session-header.js
```

Também é possível testar as rotas no navegador:

```text
http://localhost/projeto/anuncios.html
http://localhost/projeto/api/anuncios.php
http://localhost/projeto/rancho.php?codigo=1
```

## Pendências conhecidas

- O formulário de contato ainda não grava ou envia mensagens.
- O registro de logs pode ser ampliado para todas as operações.
- A geração manual de códigos deve ser substituída por chaves `AUTO_INCREMENT` ou protegida por transação no banco em uma futura migração.
