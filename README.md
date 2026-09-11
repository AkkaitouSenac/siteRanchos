# Ranchos Araçatuba e Região

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

### Ainda não implementado

- Login e publicação de anúncios pelo frontend público ainda precisam ser integrados ao banco.
- Envio ou gravação do formulário de contato.
- Upload de imagens dos ranchos.
- Envio ou gravação do formulário de contato.
- Registro de todas as operações restantes no sistema de logs.

## Banco de dados

O modelo do banco está em `banco/Ranchos.mwb` e utiliza o schema `ranchos`.

Tabelas previstas:

- `adm`
- `proprietario`
- `assinatura`
- `rancho`
- `imagensrancho`
- `visitas`
- `log`
