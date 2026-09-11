# Ranchos Araçatuba e Região

## Acesso local

Com o Apache e o MySQL em execução, abra o site em:

```text
http://localhost/projeto/
```

Para acessar o painel administrativo:

```text
http://localhost/projeto/painel/login.php
```

O painel exige um administrador cadastrado na tabela `adm`. Depois de abrir a tela de login, salve o endereço nos favoritos do navegador para acessá-lo sem digitar a URL novamente.

## Situação do projeto

### Implementado

- Painel administrativo com autenticação por sessão.
- CRUD de administradores.
- CRUD de ranchos.
- CRUD de proprietários.
- CRUD de assinaturas.
- Consulta dos logs do sistema no painel.
- Conexão com o banco migrada de MySQLi para PDO.
- Login com prepared statement.
- Regeneração do ID da sessão após autenticação.
- Cabeçalho padronizado nas páginas públicas.
- Anúncios públicos carregados do banco de dados, considerando somente ranchos ativos e dentro da validade.
- API pública em `api/anuncios.php` para carregar ranchos e assinaturas do banco.

### Ainda não implementado

- Login e publicação de anúncios pelo frontend público ainda precisam ser integrados ao banco.
- Envio ou gravação do formulário de contato.
- Upload de imagens dos ranchos.
- Envio ou gravação do formulário de contato.
- Registro de todas as operações restantes no sistema de logs.

## Banco de dados

O modelo do banco está em `banco/Ranchos.mwb` e utiliza o schema `ranchos`.

A conexão local usa, por padrão, `localhost`, banco `ranchos`, usuário `root` e a senha configurada em `painel/classe/conexao.php`. Em instalações locais, mantenha essa configuração alinhada com a senha do MySQL usada no HeidiSQL.

Tabelas previstas:

- `adm`
- `proprietario`
- `assinatura`
- `rancho`
- `imagensrancho`
- `visitas`
- `log`
