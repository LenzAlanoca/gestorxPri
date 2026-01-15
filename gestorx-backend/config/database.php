<?php
// Include guard para evitar múltiples requires
if (defined('GESTORX_DATABASE_LOADED')) {
    return;
}
define('GESTORX_DATABASE_LOADED', true);

require_once __DIR__ . '/Initializer.php';

class Database
{
    private $host = "localhost";
    private $db_name = "gestorxbd";
    private $username = "root";
    private $password = "";
    private $conn;

    public function getConnection()
    {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");

            // Inicializar la base de datos automáticamente
            // Si las tablas no existen, las crea
            \GestorX\Database\initializeDatabase($this->conn);
        } catch (PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
