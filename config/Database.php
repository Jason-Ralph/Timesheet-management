<?php
require_once __DIR__ . '/EnvLoader.php';

class Database {
    private $host;
    private $dbname;
    private $username;
    private $password;
    private $charset = "utf8mb4";
    public $conn;

    public function __construct() {
        // Load environment variables
        EnvLoader::load(__DIR__ . '/../.env');

        // Assign environment variables to properties
        $this->host = $_ENV['DB_HOST'];
        $this->dbname = $_ENV['DB_NAME'];
        $this->username = $_ENV['DB_USER'];
        $this->password = $_ENV['DB_PASS'];
    }

    /**
     * Connect to the database and return the connection.
     *
     * @return PDO
     */
    public function connect() {
        $this->conn = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->dbname . ";charset=" . $this->charset;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Throw exceptions on errors
        } catch (PDOException $e) {
            // Log the error to a file and display a generic message
            error_log("Database connection failed: " . $e->getMessage());
            die("A database error occurred. Please try again later.");
        }

        return $this->conn;
    }
}
?>