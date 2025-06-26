<?php

require_once 'pessoa.php';

class Paciente extends Pessoa {
    protected $convenio, $numero_carteira, $validade_carteira;
    private $tablePaciente = 'paciente';

    public function __construct($db) {
        parent::__construct($db);
    }

    // GETTERS
    public function getConvenio() {
        return $this->convenio;
    }

    public function getNumeroCarteira() {
        return $this->numero_carteira;
    }

    public function getValidadeCarteira() {
        return $this->validade_carteira;
    }

    // SETTERS com validações simples

    public function setConvenio($convenio) {
        // Pode ser vazio ou string
        $this->convenio = trim($convenio);
    }

    public function setNumeroCarteira($numero) {
        // Pode ser vazio ou string
        $this->numero_carteira = trim($numero);
    }

    public function setValidadeCarteira($data) {
        if (!empty($data)) {
            // Espera formato YYYY-MM-DD
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
                throw new Exception("Data de validade da carteira inválida. Use formato AAAA-MM-DD");
            }
            $this->validade_carteira = $data;
        } else {
            $this->validade_carteira = null;
        }
    }

    // FUNÇÕES CRUD

    public function inserir() {
        try {
            $this->conn->beginTransaction();

            // 1. Inserir na tabela pessoa
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

            // 2. Inserir na tabela paciente
            $sqlPaciente = "INSERT INTO paciente (id_paciente, convenio, numero_carteira, validade_carteira) 
                            VALUES (:id, :convenio, :numero_carteira, :validade_carteira)";
            $stmtPaciente = $this->conn->prepare($sqlPaciente);
            $stmtPaciente->bindParam(':id', $idPessoa);
            $stmtPaciente->bindParam(':convenio', $this->convenio);
            $stmtPaciente->bindParam(':numero_carteira', $this->numero_carteira);
            $stmtPaciente->bindParam(':validade_carteira', $this->validade_carteira);
            $stmtPaciente->execute();

            $this->conn->commit();
            return true;

        } catch (PDOException $e) {
            $this->conn->rollBack();
            throw new Exception("Erro ao inserir paciente: " . $e->getMessage());
        }
    }

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

            // Atualiza paciente
            $sqlPaciente = "UPDATE paciente SET convenio = ?, numero_carteira = ?, validade_carteira = ? WHERE id_paciente = ?";
            $stmtPaciente = $this->conn->prepare($sqlPaciente);
            $stmtPaciente->execute([
                $this->convenio,
                $this->numero_carteira,
                $this->validade_carteira,
                $id
            ]);

            $this->conn->commit();
            return true;

        } catch (PDOException $e) {
            $this->conn->rollBack();
            throw new Exception("Erro ao atualizar paciente: " . $e->getMessage());
        }
    }

    public function listar() {
        $sql = "SELECT p.id_pessoa, p.nome, p.cpf, p.data_nascimento, p.sexo, p.telefone, p.email, 
                       pa.convenio, pa.numero_carteira, pa.validade_carteira
                FROM pessoa p 
                INNER JOIN paciente pa ON p.id_pessoa = pa.id_paciente";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($id) {
        $sql = "SELECT p.id_pessoa, p.nome, p.cpf, p.data_nascimento, p.sexo, p.telefone, p.email, 
                       pa.convenio, pa.numero_carteira, pa.validade_carteira
                FROM pessoa p 
                INNER JOIN paciente pa ON p.id_pessoa = pa.id_paciente
                WHERE p.id_pessoa = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function excluir($id) {
        try {
            $this->conn->beginTransaction();

            $sqlPaciente = "DELETE FROM paciente WHERE id_paciente = ?";
            $stmtPaciente = $this->conn->prepare($sqlPaciente);
            $stmtPaciente->execute([$id]);

            $sqlPessoa = "DELETE FROM pessoa WHERE id_pessoa = ?";
            $stmtPessoa = $this->conn->prepare($sqlPessoa);
            $stmtPessoa->execute([$id]);

            $this->conn->commit();
            return true;

        } catch (PDOException $e) {
            $this->conn->rollBack();
            throw new Exception("Erro ao excluir paciente: " . $e->getMessage());
        }
    }
}

?>
