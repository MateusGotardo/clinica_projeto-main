<?php

class Pessoa {
    protected $id_pessoa, $nome, $cpf, $data_nascimento, $sexo, $telefone, $email;
    protected $table = 'pessoa';
    protected $conn;

    public function __construct($db) {      //Isso permite que a classe execute comandos no banco.
        $this->conn = $db;
    }

    // GETTERS
    public function getIdPessoa() {
        return $this->id_pessoa;
    }

    public function getNome() {
        return $this->nome;
    }

    public function getCpf() {
        return $this->cpf;
    }

    public function getDataNascimento() {
        return $this->data_nascimento;
    }

    public function getSexo() {
        return $this->sexo;
    }

    public function getTelefone() {
        return $this->telefone;
    }

    public function getEmail() {
        return $this->email;
    }

    // SETTERS

    public function setNome($nome) {
        if (strlen($nome) < 3) {
            throw new Exception("Nome deve conter ao menos 3 caracteres");
        } elseif (preg_match('/[0-9]/', $nome) || preg_match('/[^a-zA-ZÀ-ÿ\s]/u', $nome)) {
            throw new Exception("Nome não pode conter números ou caracteres especiais");
        } else {
            $this->nome = $nome;
        }
    }

    public function setCpf($cpf) {
        $cpfLimpo = preg_replace('/[^0-9]/', '', $cpf);
        if (strlen($cpfLimpo) != 11) {
            throw new Exception("CPF inválido");
        }
        $this->cpf = $cpfLimpo;
    }

    public function setDataNascimento($data) {
        if (!preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $data)) {
            throw new Exception("Data de nascimento inválida. Use o formato DD/MM/AAAA");
        }
        $this->data_nascimento = $data;
    }

    public function setSexo($sexo) {
        // $sexo = strtoupper($sexo);            está aqui para caso um humano burro alterar os valores F e M para minusculo
        $opcoes = ['M', 'F', 'OUTRO'];
        if (!in_array($sexo, $opcoes)) {
            throw new Exception("Sexo inválido. Use 'Maculino', 'Feminino' ou 'Outro'");
        }
        $this->sexo = $sexo;
    }

    public function setTelefone($telefone) {
        // Aceita números, espaços, parênteses e traços
        if (!preg_match('/^[0-9\s\-\(\)]+$/', $telefone)) {
            throw new Exception("Telefone inválido");
        }
        $this->telefone = $telefone;
    }

    public function setEmail($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email inválido");
        }
        $this->email = $email;
    }

    // FUNÇÕES CRUD    INSERIR DADOS NO BANCO

    public function inserir() {
        $sql = "INSERT INTO {$this->table} (nome, cpf, data_nascimento, sexo, telefone, email) 
                VALUES (:nome, :cpf, :data_nascimento, :sexo, :telefone, :email)";

        $comando = $this->conn->prepare($sql);            //PREPARE EVITA SQL INJECTION

//:nome e :email são placeholders (espaços reservados na SQL).
//Quando você chama execute(), o PDO substitui os marcadores pelos valores e envia a consulta para o banco.

        $comando->bindParam(':nome', $this->nome);  
        $comando->bindParam(':cpf', $this->cpf);
        $comando->bindParam(':data_nascimento', $this->data_nascimento);
        $comando->bindParam(':sexo', $this->sexo);
        $comando->bindParam(':telefone', $this->telefone);
        $comando->bindParam(':email', $this->email);

        return $comando->execute();
    }

    public function atualizar($id) {
        $sql = "UPDATE {$this->table} SET nome = ?, cpf = ?, data_nascimento = ?, sexo = ?, telefone = ?, email = ? WHERE id_pessoa = ?";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            $this->nome,
            $this->cpf,
            $this->data_nascimento,
            $this->sexo,
            $this->telefone,
            $this->email,
            $id
        ]);
    }

    public function listar() {
        $sql = "SELECT * FROM {$this->table}";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id_pessoa = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function excluir($id) {
        $sql = "DELETE FROM {$this->table} WHERE id_pessoa = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }
}

?>
