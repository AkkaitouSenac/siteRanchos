<?php

class Conexao {
    private $host = "localhost";
    private $usuario = "root";
    private $senha = "1234";
    private $banco = "ranchos";
    private $conn;

    public function __construct(){
        $this->conn = new mysqli($this->host, $this->usuario, $this->senha, $this->banco);
        if ($this->conn->connect_error) {
            die("Falha na conexão: " . $this->conn->connect_error);
        }
        $this->conn->set_charset('utf8mb4');
    }
    public function login($email, $senha) {
        $stmt = $this->conn->prepare('SELECT admCodigo, admNome, admSenha FROM adm WHERE admEmail = ?');
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $administrador = $resultado->fetch_assoc();
        if (!$administrador) {
            return false;
        }

        $senhaValida = password_verify($senha, $administrador['admSenha']);
        if (!$senhaValida && hash_equals($administrador['admSenha'], $senha)) {
            $novoHash = password_hash($senha, PASSWORD_DEFAULT);
            $atualizacao = $this->conn->prepare('UPDATE adm SET admSenha = ? WHERE admCodigo = ?');
            $atualizacao->bind_param('si', $novoHash, $administrador['admCodigo']);
            $atualizacao->execute();
            $senhaValida = true;
        }

        if (!$senhaValida) {
            return false;
        }

        unset($administrador['admSenha']);
        $resultado = $this->conn->query('SELECT admCodigo, admNome FROM adm WHERE admCodigo = ' . (int) $administrador['admCodigo']);
        return $resultado;
    }
    public function consulta($sql) {
        $result = $this->conn->query($sql);
        return $result;
    }
    public function escapar($valor) {
        return $this->conn->real_escape_string((string) $valor);
    }
    public function consultaAvancada(string $tabela, string $colunas, $condicao = null) {
        $sql = "SELECT $colunas FROM $tabela";
        if ($condicao !== null) {
            $sql .= " WHERE $condicao";
        }
        $result = $this->conn->query($sql);
        return $result;
    }
    public function salvar($tabela, $campos, $valores) {
        try {
            $this->conn->begin_transaction();
            $sql = "INSERT INTO $tabela ($campos) VALUES ($valores)";
            if ($this->conn->query($sql) === TRUE) {
                $this->conn->commit();
                return true;
            } 
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }
    public function atualizar($tabela, $camposValores, $condicao) {
        try {
            $this->conn->begin_transaction();
            $sql = "UPDATE $tabela SET $camposValores WHERE $condicao";
            if ($this->conn->query($sql) === TRUE) {
                $this->conn->commit();
                return true;
            } 
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }
    public function remover($tabela, $condicao) {
        try {
            $this->conn->begin_transaction();
            $sql = "DELETE FROM $tabela WHERE $condicao";
            if ($this->conn->query($sql) === TRUE) {
                $this->conn->commit();
                return true;
            } 
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    public function __destruct() {
        $this->conn->close();
    }
}