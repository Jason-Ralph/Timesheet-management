<?php
// controllers/TaskDescriptionController.php

// Include necessary models and libraries
require_once __DIR__ . '/../models/TaskDescriptionModel.php';
require_once __DIR__ . '/../libraries/Logger.php'; // Include the Logger class

/**
 * TaskDescriptionController
 * Handles the management of task descriptions.
 */
class TaskDescriptionController {
    private $tdModel;
    private $logger;

    public function __construct() {
        // Ensure the session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Initialize models
        $this->tdModel = new TaskDescriptionModel();

        // Initialize the Logger
        $this->logger = Logger::getInstance();
    }

    /**
     * List all active task descriptions.
     */
    public function listTaskDescriptions() {
        try {
            $taskDescriptions = $this->tdModel->getAllTaskDescriptions();

            // Log the action of listing task descriptions
            if (isset($_SESSION['user'])) {
                $this->logger->log(
                    $_SESSION['user']['name'],                      // Username
                    $_SESSION['user']['userTitle'],                 // Title
                    $_SESSION['user']['department'],                // Department
                    'TaskDescriptionController::listTaskDescriptions', // Page or Action
                    'view_task_descriptions',                        // Action
                    "Listed all active task descriptions.",           // Additional Info
                    'INFO'                                           // Log Level
                );
            }

            include __DIR__ . '/../views/taskDescriptions.php';
        } catch (Exception $e) {
            // Log the error
            if (isset($_SESSION['user'])) {
                $this->logger->log(
                    $_SESSION['user']['name'],                      // Username
                    $_SESSION['user']['userTitle'],                 // Title
                    $_SESSION['user']['department'],                // Department
                    'TaskDescriptionController::listTaskDescriptions', // Page or Action
                    'error',                                         // Action
                    "Failed to list task descriptions: " . $e->getMessage(), // Additional Info
                    'ERROR'                                          // Log Level
                );
            }

            // Set error message and redirect to dashboard or an error page
            $_SESSION['error_message'] = "Failed to list task descriptions: " . $e->getMessage();
            header("Location: " . BASE_URL . "/dashboard");
            exit;
        }
    }

    /**
     * List all deleted (soft-deleted) task descriptions.
     */
    public function listDeletedTaskDescriptions() {
        try {
            $taskDescriptions = $this->tdModel->getDeletedTaskDescriptions();

            // Log the action of listing deleted task descriptions
            if (isset($_SESSION['user'])) {
                $this->logger->log(
                    $_SESSION['user']['name'],                      // Username
                    $_SESSION['user']['userTitle'],                 // Title
                    $_SESSION['user']['department'],                // Department
                    'TaskDescriptionController::listDeletedTaskDescriptions', // Page or Action
                    'view_deleted_task_descriptions',                  // Action
                    "Listed all deleted task descriptions.",              // Additional Info
                    'INFO'                                           // Log Level
                );
            }

            include __DIR__ . '/../views/taskDescription-recycle-bin.php';
        } catch (Exception $e) {
            // Log the error
            if (isset($_SESSION['user'])) {
                $this->logger->log(
                    $_SESSION['user']['name'],                      // Username
                    $_SESSION['user']['userTitle'],                 // Title
                    $_SESSION['user']['department'],                // Department
                    'TaskDescriptionController::listDeletedTaskDescriptions', // Page or Action
                    'error',                                         // Action
                    "Failed to list deleted task descriptions: " . $e->getMessage(), // Additional Info
                    'ERROR'                                          // Log Level
                );
            }

            // Set error message and redirect to dashboard or an error page
            $_SESSION['error_message'] = "Failed to list deleted task descriptions: " . $e->getMessage();
            header("Location: " . BASE_URL . "/dashboard");
            exit;
        }
    }

    /**
     * Add a new task description.
     */
    public function addTaskDescription() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF Token Validation (Assuming you have implemented CSRF tokens)
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $_SESSION['error_message'] = 'Invalid CSRF token.';
                header("Location: " . BASE_URL . "/task-descriptions");
                exit;
            }

            // Prepare data for insertion
            $data = [
                ':taskName'        => filter_var($_POST['taskName'], FILTER_SANITIZE_SPECIAL_CHARS),
                ':taskDescription' => filter_var($_POST['taskDescription'], FILTER_SANITIZE_SPECIAL_CHARS),
                ':taskRoles'       => filter_var($_POST['taskRoles'], FILTER_SANITIZE_SPECIAL_CHARS),
                ':taskCost'        => (float)$_POST['taskCost'],
            ];

            try {
                $this->tdModel->addTaskDescription($data);
                $_SESSION['success_message'] = "Task Description added!";

                // Log the addition of a new task description
                if (isset($_SESSION['user'])) {
                    $this->logger->log(
                        $_SESSION['user']['name'],                      // Username
                        $_SESSION['user']['userTitle'],                 // Title
                        $_SESSION['user']['department'],                // Department
                        'TaskDescriptionController::addTaskDescription', // Page or Action
                        'add_task_description',                           // Action
                        "Added Task Description: " . $data[':taskName'],   // Additional Info
                        'INFO'                                           // Log Level
                    );
                }
            } catch (Exception $e) {
                $_SESSION['error_message'] = "Failed to add Task Description: " . $e->getMessage();

                // Log the failure to add a task description
                if (isset($_SESSION['user'])) {
                    $this->logger->log(
                        $_SESSION['user']['name'],                      // Username
                        $_SESSION['user']['userTitle'],                 // Title
                        $_SESSION['user']['department'],                // Department
                        'TaskDescriptionController::addTaskDescription', // Page or Action
                        'error',                                         // Action
                        "Failed to add Task Description: " . $e->getMessage(), // Additional Info
                        'ERROR'                                          // Log Level
                    );
                }
            }

            header("Location: " . BASE_URL . "/task-descriptions");
            exit;
        }
    }

    /**
     * Edit an existing task description.
     */
    public function editTaskDescription() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF Token Validation (Assuming you have implemented CSRF tokens)
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $_SESSION['error_message'] = 'Invalid CSRF token.';
                header("Location: " . BASE_URL . "/task-descriptions");
                exit;
            }

            $id = intval($_POST['id']);
            $data = [
                'taskName'        => filter_var($_POST['taskName'], FILTER_SANITIZE_SPECIAL_CHARS),
                'taskDescription' => filter_var($_POST['taskDescription'], FILTER_SANITIZE_SPECIAL_CHARS),
                'taskRoles'       => filter_var($_POST['taskRoles'], FILTER_SANITIZE_SPECIAL_CHARS),
                'taskCost'        => (float)$_POST['taskCost'],
            ];

            try {
                $this->tdModel->updateTaskDescription($id, $data);
                $_SESSION['success_message'] = "Task Description updated!";

                // Log the update of a task description
                if (isset($_SESSION['user'])) {
                    $this->logger->log(
                        $_SESSION['user']['name'],                      // Username
                        $_SESSION['user']['userTitle'],                 // Title
                        $_SESSION['user']['department'],                // Department
                        'TaskDescriptionController::editTaskDescription', // Page or Action
                        'edit_task_description',                           // Action
                        "Edited Task Description ID: $id to " . $data['taskName'], // Additional Info
                        'INFO'                                           // Log Level
                    );
                }
            } catch (Exception $e) {
                $_SESSION['error_message'] = "Failed to update Task Description: " . $e->getMessage();

                // Log the failure to update a task description
                if (isset($_SESSION['user'])) {
                    $this->logger->log(
                        $_SESSION['user']['name'],                      // Username
                        $_SESSION['user']['userTitle'],                 // Title
                        $_SESSION['user']['department'],                // Department
                        'TaskDescriptionController::editTaskDescription', // Page or Action
                        'error',                                         // Action
                        "Failed to update Task Description ID: $id. Error: " . $e->getMessage(), // Additional Info
                        'ERROR'                                          // Log Level
                    );
                }
            }

            header("Location: " . BASE_URL . "/task-descriptions");
            exit;
        }
    }

    /**
     * Soft delete a task description (move to recycle bin).
     */
    public function deleteTaskDescription() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF Token Validation (Assuming you have implemented CSRF tokens)
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $_SESSION['error_message'] = 'Invalid CSRF token.';
                header("Location: " . BASE_URL . "/task-descriptions");
                exit;
            }

            $id = intval($_POST['id'] ?? 0);

            try {
                // Fetch task description details before deletion for logging
                $taskDescription = $this->tdModel->getTaskDescriptionById($id);
                if (!$taskDescription) {
                    throw new Exception("Task Description not found.");
                }

                $this->tdModel->softDeleteTaskDescription($id);
                $_SESSION['success_message'] = "Task Description moved to recycle bin!";

                // Log the soft deletion of a task description
                if (isset($_SESSION['user'])) {
                    $this->logger->log(
                        $_SESSION['user']['name'],                          // Username
                        $_SESSION['user']['userTitle'],                     // Title
                        $_SESSION['user']['department'],                    // Department
                        'TaskDescriptionController::deleteTaskDescription',  // Page or Action
                        'soft_delete_task_description',                      // Action
                        "Soft Deleted Task Description ID: $id with Name: " . $taskDescription['taskName'], // Additional Info
                        'INFO'                                               // Log Level
                    );
                }
            } catch (Exception $e) {
                $_SESSION['error_message'] = "Failed to move Task Description: " . $e->getMessage();

                // Log the failure to soft delete a task description
                if (isset($_SESSION['user'])) {
                    $this->logger->log(
                        $_SESSION['user']['name'],                          // Username
                        $_SESSION['user']['userTitle'],                     // Title
                        $_SESSION['user']['department'],                    // Department
                        'TaskDescriptionController::deleteTaskDescription',  // Page or Action
                        'error',                                             // Action
                        "Failed to soft delete Task Description ID: $id. Error: " . $e->getMessage(), // Additional Info
                        'ERROR'                                              // Log Level
                    );
                }
            }

            header("Location: " . BASE_URL . "/task-descriptions");
            exit;
        }
    }

    /**
     * Restore a task description from the recycle bin.
     */
    public function restoreTaskDescription() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF Token Validation (Assuming you have implemented CSRF tokens)
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $_SESSION['error_message'] = 'Invalid CSRF token.';
                header("Location: " . BASE_URL . "/task-descriptions");
                exit;
            }

            $id = intval($_POST['id'] ?? 0);

            try {
                // Fetch task description details before restoration for logging
                $taskDescription = $this->tdModel->getTaskDescriptionById($id);
                if (!$taskDescription) {
                    throw new Exception("Task Description not found.");
                }

                $this->tdModel->restoreTaskDescription($id);
                $_SESSION['success_message'] = "Task Description restored!";

                // Log the restoration of a task description
                if (isset($_SESSION['user'])) {
                    $this->logger->log(
                        $_SESSION['user']['name'],                          // Username
                        $_SESSION['user']['userTitle'],                     // Title
                        $_SESSION['user']['department'],                    // Department
                        'TaskDescriptionController::restoreTaskDescription', // Page or Action
                        'restore_task_description',                           // Action
                        "Restored Task Description ID: $id with Name: " . $taskDescription['taskName'], // Additional Info
                        'INFO'                                               // Log Level
                    );
                }
            } catch (Exception $e) {
                $_SESSION['error_message'] = "Failed to restore Task Description: " . $e->getMessage();

                // Log the failure to restore a task description
                if (isset($_SESSION['user'])) {
                    $this->logger->log(
                        $_SESSION['user']['name'],                          // Username
                        $_SESSION['user']['userTitle'],                     // Title
                        $_SESSION['user']['department'],                    // Department
                        'TaskDescriptionController::restoreTaskDescription', // Page or Action
                        'error',                                             // Action
                        "Failed to restore Task Description ID: $id. Error: " . $e->getMessage(), // Additional Info
                        'ERROR'                                              // Log Level
                    );
                }
            }

            header("Location: " . BASE_URL . "/task-descriptions");
            exit;
        }
    }

    /**
     * Permanently delete a task description from the recycle bin.
     */
    public function permanentDeleteTaskDescription() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF Token Validation (Assuming you have implemented CSRF tokens)
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $_SESSION['error_message'] = 'Invalid CSRF token.';
                header("Location: " . BASE_URL . "/task-description-recycle-bin");
                exit;
            }

            $id = intval($_POST['id'] ?? 0);

            try {
                // Fetch task description details before permanent deletion for logging
                $taskDescription = $this->tdModel->getTaskDescriptionById($id);
                if (!$taskDescription) {
                    throw new Exception("Task Description not found.");
                }

                $this->tdModel->permanentDeleteTaskDescription($id);
                $_SESSION['success_message'] = "Task Description permanently deleted!";

                // Log the permanent deletion of a task description
                if (isset($_SESSION['user'])) {
                    $this->logger->log(
                        $_SESSION['user']['name'],                          // Username
                        $_SESSION['user']['userTitle'],                     // Title
                        $_SESSION['user']['department'],                    // Department
                        'TaskDescriptionController::permanentDeleteTaskDescription', // Page or Action
                        'permanent_delete_task_description',                  // Action
                        "Permanently Deleted Task Description ID: $id with Name: " . $taskDescription['taskName'], // Additional Info
                        'WARNING'                                            // Log Level (Higher severity for permanent deletions)
                    );
                }
            } catch (Exception $e) {
                $_SESSION['error_message'] = "Failed to permanently delete Task Description: " . $e->getMessage();

                // Log the failure to permanently delete a task description
                if (isset($_SESSION['user'])) {
                    $this->logger->log(
                        $_SESSION['user']['name'],                          // Username
                        $_SESSION['user']['userTitle'],                     // Title
                        $_SESSION['user']['department'],                    // Department
                        'TaskDescriptionController::permanentDeleteTaskDescription', // Page or Action
                        'error',                                             // Action
                        "Failed to permanently delete Task Description ID: $id. Error: " . $e->getMessage(), // Additional Info
                        'ERROR'                                              // Log Level
                    );
                }
            }

            header("Location: " . BASE_URL . "/task-description-recycle-bin");
            exit;
        }
    }
    }
