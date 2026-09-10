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
        return $this->conn;
    }
    public function login($email, $senha) {
        $sql = "SELECT admCodigo, admNome FROM adm WHERE admEmail = '$email' AND admSenha = '$senha'";
        $result = $this->conn->query($sql);
        return $result;
    }
    public function consulta($sql) {
        $result = $this->conn->query($sql);
        return $result;
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