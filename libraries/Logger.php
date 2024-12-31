<?php
// libraries/Logger.php
require_once __DIR__ . '/../config/Database.php';

class Logger {
    private static $instance = null;
    private $db;

    public function __construct() {
        // Initialize the database connection
        $this->db = (new Database())->connect();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Logger();
        }
        return self::$instance;
    }

    public function log($username, $title, $department, $page, $action, $additionalInfo = null, $level = 'INFO') {
        $stmt = $this->db->prepare("INSERT INTO logs (username, title, department, page, action, level, ip_address, user_agent, additional_info)
            VALUES (:username, :title, :department, :page, :action, :level, :ip_address, :user_agent, :additional_info)");

        // Get IP address
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

        // Get User Agent
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':department', $department, PDO::PARAM_STR);
        $stmt->bindParam(':page', $page, PDO::PARAM_STR);
        $stmt->bindParam(':action', $action, PDO::PARAM_STR);
        $stmt->bindParam(':level', $level, PDO::PARAM_STR);
        $stmt->bindParam(':ip_address', $ipAddress, PDO::PARAM_STR);
        $stmt->bindParam(':user_agent', $userAgent, PDO::PARAM_STR);
        $stmt->bindParam(':additional_info', $additionalInfo, PDO::PARAM_STR);

        try {
            $stmt->execute();
        } catch (PDOException $e) {
            // Handle logging errors (optional)
            error_log("Failed to log action: " . $e->getMessage());
        }
    }

    public function getLogsForUser($username, $startDate = null, $endDate = null) {
        $query = "SELECT * FROM logs WHERE username = :username";
        if ($startDate && $endDate) {
            $query .= " AND DATE(timestamp) BETWEEN :startDate AND :endDate";
        }
        $query .= " ORDER BY date_time DESC";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        if ($startDate && $endDate) {
            $stmt->bindParam(':startDate', $startDate, PDO::PARAM_STR);
            $stmt->bindParam(':endDate', $endDate, PDO::PARAM_STR);
        }

        try {
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Failed to fetch logs for user $username: " . $e->getMessage());
            return [];
        }
    }
}
?>
