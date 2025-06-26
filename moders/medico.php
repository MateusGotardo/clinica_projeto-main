<?php

require_once 'pessoa.php'; // incluir a superclasse Pessoa

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

    // SETTERS com validações

    public function setCrm($crm) {
        if (!is_numeric($crm) || strlen((string)$crm) != 6) {
            throw new Exception("CRM inválido");
        }
        $this->crm = $crm;
    }

    public function setEspecialidade($especialidade) {
        $this->especialidade = $especialidade; // pode ser vazio
    }

    // Sobrescrever o método inserir para inserir em pessoa e medico
    public function inserir() {
        try {
            $this->conn->beginTransaction();

            // 1. Inserir na tabela pessoa (usa método da superclasse)
            $sqlPessoa = "INSERT INTO pessoa (nome, cpf, data_nascimento, sexo, telefone, email) 
                          VALUES (:nome, :cpf, :data_nascimento, :sexo, :telefone, :email)";
            $stmtPessoa = $this->conn->prepare($sqlPessoa);
            $stmtPessoa->bindParam(':nome', $this->getNome());
            $stmtPessoa->bindParam(':cpf', $this->getCpf());
            $stmtPessoa->bindParam(':data_nascimento', $this->getDataNascimento());
            $stmtPessoa->bindParam(':sexo', $this->getSexo());
            $stmtPessoa->bindParam(':telefone', $this->getTelefone());
            $stmtPessoa->bindParam(':email', $this->getEmail());
            $stmtPessoa->execute();

            $idPessoa = $this->conn->lastInsertId();

            // 2. Inserir na tabela medico
            $sqlMedico = "INSERT INTO medico (id_medico, crm, especialidade) VALUES (:id, :crm, :especialidade)";
            $stmtMedico = $this->conn->prepare($sqlMedico);
            $stmtMedico->bindParam(':id', $idPessoa);
            $stmtMedico->bindParam(':crm', $this->crm);
            $stmtMedico->bindParam(':especialidade', $this->especialidade);
            $stmtMedico->execute();

            $this->conn->commit();
            return true;

        } catch (PDOException $e) {
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
            $stmtPessoa = $this->conn->prepare($sqlPessoa);
            $stmtPessoa->execute([
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
            return true;

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

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar médico pelo ID com dados completos

    public function buscarPorId($id) {
        $sql = "SELECT p.id_pessoa, p.nome, p.cpf, p.data_nascimento, p.sexo, p.telefone, p.email, m.crm, m.especialidade 
                FROM pessoa p 
                INNER JOIN medico m ON p.id_pessoa = m.id_medico
                WHERE p.id_pessoa = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Excluir médico (apaga de medico e pessoa)

    public function excluir($id) {
        try {
            $this->conn->beginTransaction();

            $sqlMedico = "DELETE FROM medico WHERE id_medico = ?";
            $stmtMedico = $this->conn->prepare($sqlMedico);
            $stmtMedico->execute([$id]);

            $sqlPessoa = "DELETE FROM pessoa WHERE id_pessoa = ?";
            $stmtPessoa = $this->conn->prepare($sqlPessoa);
            $stmtPessoa->execute([$id]);

            $this->conn->commit();
            return true;

        } catch (PDOException $e) {
            $this->conn->rollBack();
            throw new Exception("Erro ao excluir médico: " . $e->getMessage());
        }
    }
}

?>
