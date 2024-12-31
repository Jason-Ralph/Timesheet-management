<?php
require_once __DIR__ . '/../libraries/Logger.php';
require_once __DIR__ . '/../config/Database.php';

class LogsController {
    private $db;
    private $logger;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->logger = new Logger();

        // Initialize database connection
        $this->db = (new Database())->connect();
    }

    /**
     * Display all logs.
     */
    public function viewLogs() {
               // Fetch logs from the database
        $stmt = $this->db->prepare("SELECT * FROM logs ORDER BY date_time DESC");
        $stmt->execute();
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Pass logs to the view
        include __DIR__ . '/../views/view-logs.php';
    }
}
?>
