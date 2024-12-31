<?php

require_once __DIR__ . '/../models/PermissionHelperModel.php';
require_once __DIR__ . '/../libraries/Logger.php'; // Include the Logger class

class PermissionsHelperController {
    private $permissionHelperModel;
    private $logger;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->permissionHelperModel = new PermissionHelperModel();
        $this->logger = Logger::getInstance(); // Initialize the Logger
    }

   
    public function ensurePermission($requiredPermission) {
        if (!isset($_SESSION['user']['id'])) {
            $_SESSION['error_message'] = "You must be logged in to access this page.";
            header("Location: " . BASE_URL . "/login");
            exit;
        }

        $userId = $_SESSION['user']['id'];

        if (!$this->permissionHelperModel->checkUserPermission($userId, $requiredPermission)) {
            // Log the unauthorized access attempt
            $this->logger->log(
                $_SESSION['user']['name'],          // Username
                $_SESSION['user']['userTitle'],     // Title
                $_SESSION['user']['department'],    // Department
                'PermissionsHelperController::ensurePermission', // Page or Action
                'access_denied',                     // Action
                "User ID: $userId attempted to access: $requiredPermission", // Additional info
                'WARNING'                            // Log level
            );

            $_SESSION['error_message'] = "Access Denied: You do not have permission.";
            header("Location: " . BASE_URL . "/dashboard");
            exit;
        }

        // Optionally, log successful permission checks
        $this->logger->log(
            $_SESSION['user']['name'],          // Username
            $_SESSION['user']['userTitle'],     // Title
            $_SESSION['user']['department'],    // Department
            'PermissionsHelperController::ensurePermission', // Page or Action
            'permission_granted',                // Action
            "User ID: $userId accessed: $requiredPermission", // Additional info
            'INFO'                               // Log level
        );
    }

   
    public function hasPermission($requiredPermission) {
        if (!isset($_SESSION['user']['id'])) {
            return false;
        }
        $userId = $_SESSION['user']['id'];
        return $this->permissionHelperModel->checkUserPermission($userId, $requiredPermission);
    }
}
?>
