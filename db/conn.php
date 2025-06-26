<?php 

class Database {
    private $host = '127.0.0.2';
    private $db = 'clinica';
    private $user = 'root';
    private $pass = '';
    private $conn;

    public function conectar() {     //METODO CONECTAR 
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db};charset=utf8mb4",
                $this->user,
                $this->pass
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Erro na conexão: " . $e->getMessage();
            die();
        }

        return $this->conn;
    }
}

// Teste de conexão
// $teste = new Database();
// $teste->conectar();

?>
