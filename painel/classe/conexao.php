<?php

class Conexao
{
    private string $host = 'localhost';
    private string $usuario = 'root';
    private string $senha = '1234';
    private string $banco = 'ranchos';
    private PDO $conn;

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
        $this->validarListaSql($condicao);

        return $this->executarTransacao(
            "UPDATE {$tabela} SET {$camposValores} WHERE {$condicao}"
        );
    }

    public function remover(string $tabela, string $condicao): bool
    {
        $this->validarTabela($tabela);
        $this->validarListaSql($condicao);

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
        if (preg_match('/(;|--|\/\*|\*\/|#)/', $sql)) {
            throw new InvalidArgumentException('SQL inválido.');
        }
    }
}
