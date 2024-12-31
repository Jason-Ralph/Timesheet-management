<?php
// controllers/ReportTypeController.php

// Include necessary models and libraries
require_once __DIR__ . '/../models/ReportTypeModel.php';
require_once __DIR__ . '/../libraries/Logger.php'; // Include the Logger class

/**
 * ReportTypeController
 * Handles the management of report types.
 */
class ReportTypeController {
    private $reportTypeModel;
    private $logger;

    public function __construct() {
        // Ensure the session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Initialize models
        $this->reportTypeModel = new ReportTypeModel();

        // Initialize the Logger
        $this->logger = Logger::getInstance();
    }

    /**
     * List all active report types.
     */
    public function listReportTypes() {
        try {
            $reportTypes = $this->reportTypeModel->getAllReportTypes();

            // Log the action of listing report types
            if (isset($_SESSION['user'])) {
                $this->logger->log(
                    $_SESSION['user']['name'],                      // Username
                    $_SESSION['user']['userTitle'],                 // Title
                    $_SESSION['user']['department'],                // Department
                    'ReportTypeController::listReportTypes',        // Page or Action
                    'view_report_types',                            // Action
                    "Listed all active report types.",               // Additional Info
                    'INFO'                                           // Log Level
                );
            }

            include __DIR__ . '/../views/reportTypes.php';
        } catch (Exception $e) {
            // Log the error
            if (isset($_SESSION['user'])) {
                $this->logger->log(
                    $_SESSION['user']['name'],                      // Username
                    $_SESSION['user']['userTitle'],                 // Title
                    $_SESSION['user']['department'],                // Department
                    'ReportTypeController::listReportTypes',        // Page or Action
                    'error',                                         // Action
                    "Failed to list report types: " . $e->getMessage(), // Additional Info
                    'ERROR'                                          // Log Level
                );
            }

            // Set error message and redirect to dashboard or an error page
            $_SESSION['error_message'] = "Failed to list report types: " . $e->getMessage();
            header("Location: " . BASE_URL . "/dashboard");
            exit;
        }
    }

    /**
     * List all deleted (soft-deleted) report types.
     */
    public function listDeletedReportTypes() {
        try {
            $reportTypes = $this->reportTypeModel->getDeletedReportTypes();

            // Log the action of listing deleted report types
            if (isset($_SESSION['user'])) {
                $this->logger->log(
                    $_SESSION['user']['name'],                      // Username
                    $_SESSION['user']['userTitle'],                 // Title
                    $_SESSION['user']['department'],                // Department
                    'ReportTypeController::listDeletedReportTypes', // Page or Action
                    'view_deleted_report_types',                    // Action
                    "Listed all deleted report types.",              // Additional Info
                    'INFO'                                           // Log Level
                );
            }

            include __DIR__ . '/../views/reportType-recycle-bin.php';
        } catch (Exception $e) {
            // Log the error
            if (isset($_SESSION['user'])) {
                $this->logger->log(
                    $_SESSION['user']['name'],                      // Username
                    $_SESSION['user']['userTitle'],                 // Title
                    $_SESSION['user']['department'],                // Department
                    'ReportTypeController::listDeletedReportTypes', // Page or Action
                    'error',                                         // Action
                    "Failed to list deleted report types: " . $e->getMessage(), // Additional Info
                    'ERROR'                                          // Log Level
                );
            }

            // Set error message and redirect to dashboard or an error page
            $_SESSION['error_message'] = "Failed to list deleted report types: " . $e->getMessage();
            header("Location: " . BASE_URL . "/dashboard");
            exit;
        }
    }

    /**
     * Add a new report type.
     */
    public function addReportType() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF Token Validation (Assuming you have implemented CSRF tokens)
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $_SESSION['error_message'] = 'Invalid CSRF token.';
                header("Location: " . BASE_URL . "/report-types");
                exit;
            }

            // Prepare data for insertion
            $data = [
                ':reportType' => filter_var($_POST['reportType'], FILTER_SANITIZE_SPECIAL_CHARS),
                ':reportDescription' => filter_var($_POST['reportDescription'], FILTER_SANITIZE_SPECIAL_CHARS),
            ];

            try {
                $this->reportTypeModel->addReportType($data);
                $_SESSION['success_message'] = "Report Type added successfully!";

                // Log the addition of a new report type
                if (isset($_SESSION['user'])) {
                    $this->logger->log(
                        $_SESSION['user']['name'],                      // Username
                        $_SESSION['user']['userTitle'],                 // Title
                        $_SESSION['user']['department'],                // Department
                        'ReportTypeController::addReportType',          // Page or Action
                        'add_report_type',                              // Action
                        "Added Report Type: " . $data[':reportType'],    // Additional Info
                        'INFO'                                           // Log Level
                    );
                }
            } catch (Exception $e) {
                $_SESSION['error_message'] = "Failed to add Report Type: " . $e->getMessage();

                // Log the failure to add a report type
                if (isset($_SESSION['user'])) {
                    $this->logger->log(
                        $_SESSION['user']['name'],                      // Username
                        $_SESSION['user']['userTitle'],                 // Title
                        $_SESSION['user']['department'],                // Department
                        'ReportTypeController::addReportType',          // Page or Action
                        'error',                                         // Action
                        "Failed to add Report Type: " . $e->getMessage(), // Additional Info
                        'ERROR'                                          // Log Level
                    );
                }
            }

            header("Location: " . BASE_URL . "/report-types");
            exit;
        }
    }

    /**
     * Edit an existing report type.
     */
    public function editReportType() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF Token Validation (Assuming you have implemented CSRF tokens)
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $_SESSION['error_message'] = 'Invalid CSRF token.';
                header("Location: " . BASE_URL . "/report-types");
                exit;
            }

            $id = intval($_POST['id']);
            $data = [
                'reportType' => filter_var($_POST['reportType'], FILTER_SANITIZE_SPECIAL_CHARS),
                'reportDescription' => filter_var($_POST['reportDescription'], FILTER_SANITIZE_SPECIAL_CHARS),
            ];

            try {
                $this->reportTypeModel->updateReportType($id, $data);
                $_SESSION['success_message'] = "Report Type updated successfully!";

                // Log the update of a report type
                if (isset($_SESSION['user'])) {
                    $this->logger->log(
                        $_SESSION['user']['name'],                      // Username
                        $_SESSION['user']['userTitle'],                 // Title
                        $_SESSION['user']['department'],                // Department
                        'ReportTypeController::editReportType',         // Page or Action
                        'edit_report_type',                             // Action
                        "Edited Report Type ID: $id to " . $data['reportType'], // Additional Info
                        'INFO'                                           // Log Level
                    );
                }
            } catch (Exception $e) {
                $_SESSION['error_message'] = "Failed to update Report Type: " . $e->getMessage();

                // Log the failure to update a report type
                if (isset($_SESSION['user'])) {
                    $this->logger->log(
                        $_SESSION['user']['name'],                      // Username
                        $_SESSION['user']['userTitle'],                 // Title
                        $_SESSION['user']['department'],                // Department
                        'ReportTypeController::editReportType',         // Page or Action
                        'error',                                         // Action
                        "Failed to update Report Type ID: $id. Error: " . $e->getMessage(), // Additional Info
                        'ERROR'                                          // Log Level
                    );
                }
            }

            header("Location: " . BASE_URL . "/report-types");
            exit;
        }
    }

    /**
     * Soft delete a report type (move to recycle bin).
     */
    public function deleteReportType() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF Token Validation (Assuming you have implemented CSRF tokens)
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $_SESSION['error_message'] = 'Invalid CSRF token.';
                header("Location: " . BASE_URL . "/report-types");
                exit;
            }

            $id = intval($_POST['id'] ?? 0);

            try {
                // Fetch report type details before deletion for logging
                $reportType = $this->reportTypeModel->getReportTypeById($id);
                if (!$reportType) {
                    throw new Exception("Report Type not found.");
                }

                $this->reportTypeModel->softDeleteReportType($id);
                $_SESSION['success_message'] = "Report Type moved to recycle bin!";

                // Log the soft deletion of a report type
                if (isset($_SESSION['user'])) {
                    $this->logger->log(
                        $_SESSION['user']['name'],                          // Username
                        $_SESSION['user']['userTitle'],                     // Title
                        $_SESSION['user']['department'],                    // Department
                        'ReportTypeController::deleteReportType',           // Page or Action
                        'soft_delete_report_type',                           // Action
                        "Soft Deleted Report Type ID: $id with Name: " . $reportType['reportType'], // Additional Info
                        'INFO'                                               // Log Level
                    );
                }
            } catch (Exception $e) {
                $_SESSION['error_message'] = "Failed to move Report Type: " . $e->getMessage();

                // Log the failure to soft delete a report type
                if (isset($_SESSION['user'])) {
                    $this->logger->log(
                        $_SESSION['user']['name'],                          // Username
                        $_SESSION['user']['userTitle'],                     // Title
                        $_SESSION['user']['department'],                    // Department
                        'ReportTypeController::deleteReportType',           // Page or Action
                        'error',                                             // Action
                        "Failed to soft delete Report Type ID: $id. Error: " . $e->getMessage(), // Additional Info
                        'ERROR'                                              // Log Level
                    );
                }
            }

            header("Location: " . BASE_URL . "/report-types");
            exit;
        }
    }

    /**
     * Restore a report type from the recycle bin.
     */
    public function restoreReportType() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF Token Validation (Assuming you have implemented CSRF tokens)
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $_SESSION['error_message'] = 'Invalid CSRF token.';
                header("Location: " . BASE_URL . "/report-type-recycle-bin");
                exit;
            }

            $id = intval($_POST['id'] ?? 0);

            try {
                // Fetch report type details before restoration for logging
                $reportType = $this->reportTypeModel->getReportTypeById($id);
                if (!$reportType) {
                    throw new Exception("Report Type not found.");
                }

                $this->reportTypeModel->restoreReportType($id);
                $_SESSION['success_message'] = "Report Type restored!";

                // Log the restoration of a report type
                if (isset($_SESSION['user'])) {
                    $this->logger->log(
                        $_SESSION['user']['name'],                          // Username
                        $_SESSION['user']['userTitle'],                     // Title
                        $_SESSION['user']['department'],                    // Department
                        'ReportTypeController::restoreReportType',          // Page or Action
                        'restore_report_type',                               // Action
                        "Restored Report Type ID: $id with Name: " . $reportType['reportType'], // Additional Info
                        'INFO'                                               // Log Level
                    );
                }
            } catch (Exception $e) {
                $_SESSION['error_message'] = "Failed to restore Report Type: " . $e->getMessage();

                // Log the failure to restore a report type
                if (isset($_SESSION['user'])) {
                    $this->logger->log(
                        $_SESSION['user']['name'],                          // Username
                        $_SESSION['user']['userTitle'],                     // Title
                        $_SESSION['user']['department'],                    // Department
                        'ReportTypeController::restoreReportType',          // Page or Action
                        'error',                                             // Action
                        "Failed to restore Report Type ID: $id. Error: " . $e->getMessage(), // Additional Info
                        'ERROR'                                              // Log Level

                    );
                }
            }

            header("Location: " . BASE_URL . "/report-type-recycle-bin");
            exit;
        }
    }

    /**
     * Permanently delete a report type from the recycle bin.
     */
    public function permanentDeleteReportType() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF Token Validation (Assuming you have implemented CSRF tokens)
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $_SESSION['error_message'] = 'Invalid CSRF token.';
                header("Location: " . BASE_URL . "/report-type-recycle-bin");
                exit;
            }

            $id = intval($_POST['id'] ?? 0);

            try {
                // Fetch report type details before permanent deletion for logging
                $reportType = $this->reportTypeModel->getReportTypeById($id);
                if (!$reportType) {
                    throw new Exception("Report Type not found.");
                }

                $this->reportTypeModel->permanentDeleteReportType($id);
                $_SESSION['success_message'] = "Report Type permanently deleted!";

                // Log the permanent deletion of a report type
                if (isset($_SESSION['user'])) {
                    $this->logger->log(
                        $_SESSION['user']['name'],                          // Username
                        $_SESSION['user']['userTitle'],                     // Title
                        $_SESSION['user']['department'],                    // Department
                        'ReportTypeController::permanentDeleteReportType',  // Page or Action
                        'permanent_delete_report_type',                      // Action
                        "Permanently Deleted Report Type ID: $id with Name: " . $reportType['reportType'], // Additional Info
                        'WARNING'                                            // Log Level (Higher severity for permanent deletions)
                    );
                }
            } catch (Exception $e) {
                $_SESSION['error_message'] = "Failed to permanently delete Report Type: " . $e->getMessage();

                // Log the failure to permanently delete a report type
                if (isset($_SESSION['user'])) {
                    $this->logger->log(
                        $_SESSION['user']['name'],                          // Username
                        $_SESSION['user']['userTitle'],                     // Title
                        $_SESSION['user']['department'],                    // Department
                        'ReportTypeController::permanentDeleteReportType',  // Page or Action
                        'error',                                             // Action
                        "Failed to permanently delete Report Type ID: $id. Error: " . $e->getMessage(), // Additional Info
                        'ERROR'                                              // Log Level
                    );
                }
            }

            header("Location: " . BASE_URL . "/report-type-recycle-bin");
            exit;
        }
    }
}



