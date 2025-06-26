<?php

require_once 'pessoa.php';

class Medico extends Pessoa {
    private $crm, $especialidade;
    private $tableMedico = 'medico';

    public function __construct($db) {
        parent::__construct($db); // chama construtor da Pessoa
    }

    // GETTERS
    public function getCrm() {
        return $this->crm;
    }

    public function getEspecialidade() {
        return $this->especialidade;
    }

    // SETTERS

    public function setCrm($crm) {
        if (!is_numeric($crm) || strlen((string)$crm) != 6) {
            throw new Exception("CRM inválido");
        }
        $this->crm = $crm;
    }

    public function setEspecialidade($especialidade) {
        $this->especialidade = $especialidade; // pode ser vazio
    }

    public function inserir() {
    try {
        $this->conn->beginTransaction(); //USADO COMO COMIT PARA AS DUAS TABELAS.

        // Verifica se já existe pessoa com esse CPF
        $pessoaExistente = $this->pessoaExiste($this->getCpf());

        if ($pessoaExistente) {
            $idPessoa = $pessoaExistente['id_pessoa'];

//"Se a variável $pessoaExistente existe, no caso achou alguem com aquele CPF
//"Então, atribua à variável $idPessoa o valor do campo 'id_pessoa' que veio no resultado da consulta."

        } else {
            $sqlPessoa = "INSERT INTO pessoa (nome, cpf, data_nascimento, sexo, telefone, email) 
                          VALUES (:nome, :cpf, :data_nascimento, :sexo, :telefone, :email)";
            $comandoPessoa = $this->conn->prepare($sqlPessoa);
            $comandoPessoa->bindParam(':nome', $this->getNome());
            $comandoPessoa->bindParam(':cpf', $this->getCpf());
            $comandoPessoa->bindParam(':data_nascimento', $this->getDataNascimento());
            $comandoPessoa->bindParam(':sexo', $this->getSexo());
            $comandoPessoa->bindParam(':telefone', $this->getTelefone());
            $comandoPessoa->bindParam(':email', $this->getEmail());
            $comandoPessoa->execute();

            $idPessoa = $this->conn->lastInsertId(); //usa lastInsertId pois essa função nativa pega o ultimo id criado pelo banco
            //e apenas assim consegue associar medico e pessoa.
        }

        // Insere na tabela medico com idPessoa
        $sqlMedico = "INSERT INTO medico (id_medico, crm, especialidade) VALUES (:id, :crm, :especialidade)";
        $comandoMedico = $this->conn->prepare($sqlMedico);
        $comandoMedico->bindParam(':id', $idPessoa);
        $comandoMedico->bindParam(':crm', $this->crm);
        $comandoMedico->bindParam(':especialidade', $this->especialidade);
        $comandoMedico->execute();

        $this->conn->commit();
        return;

    } 
        catch (PDOException $e) {
        $this->conn->rollBack();
        throw new Exception("Erro ao inserir médico: " . $e->getMessage());
    }
}


    // Atualizar médico: deve atualizar pessoa e medico

    public function atualizar($id) {
        try {
            $this->conn->beginTransaction();

            // Atualiza pessoa
            $sqlPessoa = "UPDATE pessoa SET nome = ?, cpf = ?, data_nascimento = ?, sexo = ?, telefone = ?, email = ? WHERE id_pessoa = ?";
            $comandoPessoa = $this->conn->prepare($sqlPessoa);
            $comandoPessoa->execute([
                $this->getNome(),
                $this->getCpf(),
                $this->getDataNascimento(),
                $this->getSexo(),
                $this->getTelefone(),
                $this->getEmail(),
                $id
            ]);

            // Atualiza medico
            $sqlMedico = "UPDATE medico SET crm = ?, especialidade = ? WHERE id_medico = ?";
            $stmtMedico = $this->conn->prepare($sqlMedico);
            $stmtMedico->execute([
                $this->crm,
                $this->especialidade,
                $id
            ]);

            $this->conn->commit();
            return;

        } catch (PDOException $e) {
            $this->conn->rollBack();
            throw new Exception("Erro ao atualizar médico: " . $e->getMessage());
        }
    }

    // Listar médicos com dados de pessoa (join)

    public function listar() {
        $sql = "SELECT p.id_pessoa, p.nome, p.cpf, p.data_nascimento, p.sexo, p.telefone, p.email, m.crm, m.especialidade 
                FROM pessoa p 
                INNER JOIN medico m ON p.id_pessoa = m.id_medico";

        $comando = $this->conn->prepare($sql);
        $comando->execute();

        return $comando->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar médico pelo ID com dados completos

    public function buscarPorId($id) {
        $sql = "SELECT p.id_pessoa, p.nome, p.cpf, p.data_nascimento, p.sexo, p.telefone, p.email, m.crm, m.especialidade 
                FROM pessoa p 
                INNER JOIN medico m ON p.id_pessoa = m.id_medico
                WHERE p.id_pessoa = ?";

        $comando = $this->conn->prepare($sql);
        $comando->execute([$id]);

        return $comando->fetch(PDO::FETCH_ASSOC);
    }

    // Excluir médico (apaga de medico e pessoa)

    public function excluir($id) {
        try {
            $this->conn->beginTransaction();

            $sqlMedico = "DELETE FROM medico WHERE id_medico = ?";
            $comandoMedico = $this->conn->prepare($sqlMedico);
            $comandoMedico->execute([$id]);

            $sqlPessoa = "DELETE FROM pessoa WHERE id_pessoa = ?";
            $comandoPessoa = $this->conn->prepare($sqlPessoa);
            $comandoPessoa->execute([$id]);

            $this->conn->commit();
            return true;

        } catch (PDOException $e) {
            $this->conn->rollBack();
            throw new Exception("Erro ao excluir médico: " . $e->getMessage());
        }
    }
}

?>
