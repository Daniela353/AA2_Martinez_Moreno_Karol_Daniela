<?php
// Conexión a la base de datos usando PDO
class Database {
    private $host = '127.0.0.1';
    private $db_name = 'aa2_martinez_moreno_karol_daniela';
    private $username = 'root';
    private $password = '';
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'DB connection error: '.$e->getMessage()]);
            exit;
        }
        return $this->conn;
    }
}
?>
