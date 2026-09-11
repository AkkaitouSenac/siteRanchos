# Ranchos Araçatuba e Região

## Situação do projeto

### Implementado

- Painel administrativo com autenticação por sessão.
- CRUD de administradores.
- CRUD de ranchos.
- Conexão com o banco migrada de MySQLi para PDO.
- Login com prepared statement.
- Regeneração do ID da sessão após autenticação.
- Cabeçalho padronizado nas páginas públicas.

### Ainda não implementado

- Persistência do frontend público no banco.
- Envio ou gravação do formulário de contato.
- CRUD de proprietários.
- CRUD de assinaturas.
- Upload de imagens dos ranchos.
- Sistema completo de logs usando a tabela `log`.
- Migração dos métodos genéricos de CRUD para prepared statements com parâmetros.

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
