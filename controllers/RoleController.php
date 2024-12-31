<?php
// controllers/RoleController.php

// Include necessary models and libraries
require_once __DIR__ . '/../models/RoleModel.php';
require_once __DIR__ . '/../libraries/Logger.php'; // Include the Logger class

/**
 * RoleController
 * Handles operations for creating and managing roles.
 */
class RoleController {
    private $roleModel;
    private $logger;

    public function __construct() {
        // Ensure the session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->roleModel = new RoleModel();
        $this->logger = Logger::getInstance(); // Initialize the Logger
    }

    /**
     * List all roles.
     *
     * @return array
     */
    public function listRoles() {
        try {
            $roles = $this->roleModel->getAllRoles();

            // Log the action of listing roles
            if (isset($_SESSION['user'])) {
                $this->logger->log(
                    $_SESSION['user']['name'],                  // Username
                    $_SESSION['user']['userTitle'],             // Title
                    $_SESSION['user']['department'],            // Department
                    'RoleController::listRoles',                // Page or Action
                    'view_roles',                                // Action
                    "Listed all roles.",                         // Additional Info
                    'INFO'                                       // Log Level
                );
            }

            return $roles;
        } catch (Exception $e) {
            // Log the error
            if (isset($_SESSION['user'])) {
                $this->logger->log(
                    $_SESSION['user']['name'],                  // Username
                    $_SESSION['user']['userTitle'],             // Title
                    $_SESSION['user']['department'],            // Department
                    'RoleController::listRoles',                // Page or Action
                    'error',                                     // Action
                    "Failed to list roles: " . $e->getMessage(), // Additional Info
                    'ERROR'                                      // Log Level
                );
            }

            // Optionally, rethrow the exception or handle it as needed
            throw $e;
        }
    }

    /**
     * Add a new role.
     */
    public function addRole() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF Token Validation
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $_SESSION['error_message'] = 'Invalid CSRF token';
                header("Location: " . BASE_URL . "/roles");
                exit;
            }

            // Prepare data for insertion
            $data = [
                'name' => filter_var($_POST['name'], FILTER_SANITIZE_SPECIAL_CHARS),
                'description' => filter_var($_POST['description'], FILTER_SANITIZE_SPECIAL_CHARS)
            ];

            try {
                $newRoleId = $this->roleModel->addRole($data);
                $_SESSION['success_message'] = "Role added successfully!";

                // Log the addition of a new role
                if (isset($_SESSION['user'])) {
                    $this->logger->log(
                        $_SESSION['user']['name'],                      // Username
                        $_SESSION['user']['userTitle'],                 // Title
                        $_SESSION['user']['department'],                // Department
                        'RoleController::addRole',                      // Page or Action
                        'add_role',                                      // Action
                        "Added Role ID: $newRoleId with Name: " . $data['name'], // Additional Info
                        'INFO'                                           // Log Level
                    );
                }

            } catch (Exception $e) {
                $_SESSION['error_message'] = "Failed to add role: " . $e->getMessage();

                // Log the failure to add a role
                if (isset($_SESSION['user'])) {
                    $this->logger->log(
                        $_SESSION['user']['name'],                      // Username
                        $_SESSION['user']['userTitle'],                 // Title
                        $_SESSION['user']['department'],                // Department
                        'RoleController::addRole',                      // Page or Action
                        'error',                                         // Action
                        "Failed to add Role with Name: " . $data['name'] . ". Error: " . $e->getMessage(), // Additional Info
                        'ERROR'                                          // Log Level
                    );
                }
            }

            header("Location: " . BASE_URL . "/roles");
            exit;
        }
    }

    /**
     * Delete a role.
     */
    public function deleteRole() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF Token Validation
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $_SESSION['error_message'] = 'Invalid CSRF token';
                header("Location: " . BASE_URL . "/roles");
                exit;
            }

            $id = intval($_POST['id']);

            try {
                // Fetch role details before deletion for logging
                $role = $this->roleModel->getRoleById($id);
                if (!$role) {
                    throw new Exception("Role not found.");
                }

                $this->roleModel->deleteRole($id);
                $_SESSION['success_message'] = "Role deleted successfully!";

                // Log the deletion of a role
                if (isset($_SESSION['user'])) {
                    $this->logger->log(
                        $_SESSION['user']['name'],                      // Username
                        $_SESSION['user']['userTitle'],                 // Title
                        $_SESSION['user']['department'],                // Department
                        'RoleController::deleteRole',                   // Page or Action
                        'delete_role',                                   // Action
                        "Deleted Role ID: $id with Name: " . $role['name'], // Additional Info
                        'INFO'                                           // Log Level
                    );
                }

            } catch (Exception $e) {
                $_SESSION['error_message'] = "Failed to delete role: " . $e->getMessage();

                // Log the failure to delete a role
                if (isset($_SESSION['user'])) {
                    $this->logger->log(
                        $_SESSION['user']['name'],                      // Username
                        $_SESSION['user']['userTitle'],                 // Title
                        $_SESSION['user']['department'],                // Department
                        'RoleController::deleteRole',                   // Page or Action
                        'error',                                         // Action
                        "Failed to delete Role ID: $id. Error: " . $e->getMessage(), // Additional Info
                        'ERROR'                                          // Log Level
                    );
                }
            }

            header("Location: " . BASE_URL . "/roles");
            exit;
        }
    }
}
?>
