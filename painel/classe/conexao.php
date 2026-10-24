<?php

class Conexao
{
    private string $host = 'localhost';
    private string $usuario = 'root';
    private string $senha = '1234';
    private string $banco = 'ranchos';
    private PDO $conn;

    public static function csrfToken(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public static function validarCsrf(?string $token): bool
    {
        return !empty($_SESSION['_csrf']) && is_string($token)
            && hash_equals($_SESSION['_csrf'], $token);
    }

    private array $tabelasPermitidas = [
        'adm',
        'assinatura',
        'imagensrancho',
        'log',
        'proprietario',
        'rancho',
        'visitas',
    ];

    public function __construct()
    {
        $dsn = "mysql:host={$this->host};dbname={$this->banco};charset=utf8mb4";
        $this->conn = new PDO($dsn, $this->usuario, $this->senha, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    public function login(string $email, string $senha): ?array
    {
        $stmt = $this->conn->prepare(
            'SELECT admCodigo, admNome, admSenha FROM adm WHERE admEmail = :email LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        $administrador = $stmt->fetch();

        if (!$administrador) {
            return null;
        }

        $senhaValida = password_verify($senha, $administrador['admSenha']);
        if (!$senhaValida && hash_equals((string) $administrador['admSenha'], $senha)) {
            $novoHash = password_hash($senha, PASSWORD_DEFAULT);
            $atualizacao = $this->conn->prepare(
                'UPDATE adm SET admSenha = :senha WHERE admCodigo = :codigo'
            );
            $atualizacao->execute([
                'senha' => $novoHash,
                'codigo' => (int) $administrador['admCodigo'],
            ]);
            $senhaValida = true;
        }

        if (!$senhaValida) {
            return null;
        }

        return [
            'admCodigo' => (int) $administrador['admCodigo'],
            'admNome' => $administrador['admNome'],
        ];
    }

    public function consulta(string $sql): PDOStatement
    {
        return $this->conn->query($sql);
    }

    public function consultaPreparada(string $sql, array $parametros = []): PDOStatement
    {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($parametros);
        return $stmt;
    }

    public function executarPreparado(string $sql, array $parametros = []): bool
    {
        try {
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute($parametros);
        } catch (Throwable $erro) {
            error_log('Falha na operação do banco: ' . $erro->getMessage());
            return false;
        }
    }

    public function registrarLog(?int $admCodigo, string $tabela, string $operacao, $chave = null, string $sql = ''): void
    {
        try {
            $this->executarPreparado(
                'INSERT INTO log (admCodigo, logDatahora, logTabela, logOperacao, logChave, logSql, logUrl, logIp)
                 VALUES (:admCodigo, NOW(), :tabela, :operacao, :chave, :sql, :url, :ip)',
                [
                    'admCodigo' => $admCodigo ?: null,
                    'tabela' => $tabela,
                    'operacao' => $operacao,
                    'chave' => $chave === null ? null : (string) $chave,
                    'sql' => $sql,
                    'url' => (string) ($_SERVER['REQUEST_URI'] ?? ''),
                    'ip' => (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
                ]
            );
        } catch (Throwable $erro) {
            error_log('Falha ao registrar log: ' . $erro->getMessage());
        }
    }

    public function escapar($valor): string
    {
        return str_replace(
            ["\\", "'"],
            ["\\\\", "''"],
            (string) $valor
        );
    }

    public function consultaAvancada(string $tabela, string $colunas, ?string $condicao = null): PDOStatement
    {
        $this->validarTabela($tabela);
        $this->validarListaSql($colunas);

        if ($condicao !== null && trim($condicao) !== '') {
            $this->validarCondicaoSql($condicao);
        }

        $sql = "SELECT {$colunas} FROM {$tabela}";
        if ($condicao !== null && trim($condicao) !== '') {
            $sql .= " WHERE {$condicao}";
        }

        return $this->conn->query($sql);
    }

    public function salvar(string $tabela, string $campos, string $valores): bool
    {
        $this->validarTabela($tabela);
        $this->validarListaSql($campos);
        $this->validarListaSql($valores);

        return $this->executarTransacao(
            "INSERT INTO {$tabela} ({$campos}) VALUES ({$valores})"
        );
    }

    public function atualizar(string $tabela, string $camposValores, string $condicao): bool
    {
        $this->validarTabela($tabela);
        $this->validarListaSql($camposValores);
        $this->validarCondicaoSql($condicao);

        return $this->executarTransacao(
            "UPDATE {$tabela} SET {$camposValores} WHERE {$condicao}"
        );
    }

    public function remover(string $tabela, string $condicao): bool
    {
        $this->validarTabela($tabela);
        $this->validarCondicaoSql($condicao);

        return $this->executarTransacao(
            "DELETE FROM {$tabela} WHERE {$condicao}"
        );
    }

    private function executarTransacao(string $sql): bool
    {
        try {
            $this->conn->beginTransaction();
            $this->conn->exec($sql);
            $this->conn->commit();
            return true;
        } catch (Throwable $erro) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log('Falha na operação do banco: ' . $erro->getMessage());
            return false;
        }
    }

    private function validarTabela(string $tabela): void
    {
        if (!in_array($tabela, $this->tabelasPermitidas, true)) {
            throw new InvalidArgumentException('Tabela não permitida.');
        }
    }

    private function validarListaSql(string $sql): void
    {
        $sqlSemEspacos = trim($sql);
        if ($sqlSemEspacos === '') {
            throw new InvalidArgumentException('SQL inválido.');
        }

        if (preg_match('/(;|--|\/\*|\*\/|#|`|\\0)/', $sqlSemEspacos)) {
            throw new InvalidArgumentException('SQL inválido.');
        }

        $tokensBloqueados = [
            'SELECT', 'INSERT', 'UPDATE', 'DELETE', 'DROP', 'ALTER', 'CREATE',
            'TRUNCATE', 'REPLACE', 'EXEC', 'CALL', 'UNION', 'JOIN', 'INTO',
            'LOAD_FILE', 'OUTFILE', 'INFORMATION_SCHEMA', 'SCHEMA', 'TABLE',
        ];
        foreach ($tokensBloqueados as $token) {
            if (preg_match('/\b' . preg_quote($token, '/') . '\b/i', $sqlSemEspacos)) {
                throw new InvalidArgumentException('SQL inválido.');
            }
        }

        if (!preg_match('/^[A-Za-z0-9_\s\.\,\(\)\*\'\"=<>!\+\-\/\%]+$/', $sqlSemEspacos)) {
            throw new InvalidArgumentException('SQL inválido.');
        }
    }

    private function validarCondicaoSql(string $condicao): void
    {
        $this->validarListaSql($condicao);

        if (preg_match('/(^|\s)(?:OR|AND)\s*(?:1|true|false|null)\s*(?:=|<>|!=|IS)\s*(?:1|true|false|null)\s*$/i', trim($condicao))) {
            throw new InvalidArgumentException('SQL inválido.');
        }

        if (preg_match('/^\s*(?:[0-9]+|true|false|null)\s*(?:=|<>|!=|IS)\s*(?:[0-9]+|true|false|null)\s*$/i', trim($condicao))) {
            throw new InvalidArgumentException('SQL inválido.');
        }

        if (!preg_match('/[A-Za-z_][A-Za-z0-9_.]*/', $condicao)) {
            throw new InvalidArgumentException('SQL inválido.');
        }
    }
}
