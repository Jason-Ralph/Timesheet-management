<?php
// controllers/ClientController.php

// Include necessary models and libraries
require_once __DIR__ . '/../models/ClientModel.php';
require_once __DIR__ . '/../libraries/Logger.php'; // Include the Logger class

/**
 * ClientController
 * Handles operations for creating, managing, and viewing clients.
 */
class ClientController {
    private $clientModel;
    private $logger;

    public function __construct() {
        // Ensure the session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->clientModel = new ClientModel();
        $this->logger = Logger::getInstance(); // Initialize the Logger
    }

    //---------------------------------------------------------------------------GET ALL CLIENTS-------------------------------------------------------------------------------
    /**
     * List all clients.
     *
     * @return array
     */
    public function listClients() {
        try {
            $clients = $this->clientModel->getClients();

            // Log the action of listing clients
            if (isset($_SESSION['user'])) {
                $this->logger->log(
                    $_SESSION['user']['name'],                  // Username
                    $_SESSION['user']['userTitle'],             // Title
                    $_SESSION['user']['department'],            // Department
                    'ClientController::listClients',            // Page or Action
                    'view_clients',                              // Action
                    "Listed all clients.",                       // Additional Info
                    'INFO'                                       // Log Level
                );
            }

            return $clients;
        } catch (Exception $e) {
            // Log the error
            if (isset($_SESSION['user'])) {
                $this->logger->log(
                    $_SESSION['user']['name'],                  // Username
                    $_SESSION['user']['userTitle'],             // Title
                    $_SESSION['user']['department'],            // Department
                    'ClientController::listClients',            // Page or Action
                    'error',                                     // Action
                    "Failed to list clients: " . $e->getMessage(), // Additional Info
                    'ERROR'                                      // Log Level
                );
            }

            // Optionally, rethrow the exception or handle it as needed
            throw $e;
        }
    }

    //---------------------------------------------------------------------------LIST DELETED CLIENTS-------------------------------------------------------------------------------
    /**
     * List all deleted (soft-deleted) clients.
     *
     * @return array
     */
    public function listDeletedClients() {
        try {
            $deletedClients = $this->clientModel->getDeletedClients();

            // Log the action of listing deleted clients
            if (isset($_SESSION['user'])) {
                $this->logger->log(
                    $_SESSION['user']['name'],                  // Username
                    $_SESSION['user']['userTitle'],             // Title
                    $_SESSION['user']['department'],            // Department
                    'ClientController::listDeletedClients',     // Page or Action
                    'view_deleted_clients',                      // Action
                    "Listed all deleted clients.",               // Additional Info
                    'INFO'                                       // Log Level
                );
            }

            return $deletedClients;
        } catch (Exception $e) {
            // Log the error
            if (isset($_SESSION['user'])) {
                $this->logger->log(
                    $_SESSION['user']['name'],                  // Username
                    $_SESSION['user']['userTitle'],             // Title
                    $_SESSION['user']['department'],            // Department
                    'ClientController::listDeletedClients',     // Page or Action
                    'error',                                     // Action
                    "Failed to list deleted clients: " . $e->getMessage(), // Additional Info
                    'ERROR'                                      // Log Level
                );
            }

            // Optionally, rethrow the exception or handle it as needed
            throw $e;
        }
    }

    //---------------------------------------------------------------------------ADD NEW CLIENT-------------------------------------------------------------------------------
    /**
     * Add a new client.
     */
    public function addClient() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF Token Validation
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $_SESSION['error_message'] = 'Invalid CSRF token';
                header("Location: " . BASE_URL . "/clients");
                exit;
            }

            // Prepare data for insertion
            $data = [
                'client_name' => filter_var($_POST['client_name'], FILTER_SANITIZE_SPECIAL_CHARS),
                'accountExecutive' => filter_var($_POST['accountExecutive'], FILTER_SANITIZE_SPECIAL_CHARS),
                'contactEmail' => filter_var($_POST['contactEmail'], FILTER_VALIDATE_EMAIL),
                'contactPhone' => filter_var($_POST['contactPhone'], FILTER_SANITIZE_SPECIAL_CHARS),
                'contactName' => filter_var($_POST['contactName'], FILTER_SANITIZE_SPECIAL_CHARS),
                'contactTitle' => filter_var($_POST['contactTitle'], FILTER_SANITIZE_SPECIAL_CHARS),
                'secondaryContactEmail' => filter_var($_POST['secondaryContactEmail'], FILTER_SANITIZE_EMAIL),
                'secondaryContactName' => filter_var($_POST['secondaryContactName'], FILTER_SANITIZE_SPECIAL_CHARS),
                'secondaryContactPhone' => filter_var($_POST['secondaryContactPhone'], FILTER_SANITIZE_SPECIAL_CHARS),
                'secondaryContactTitle' => filter_var($_POST['secondaryContactTitle'], FILTER_SANITIZE_SPECIAL_CHARS),
                'clientLogo' => filter_var($_POST['clientLogo'], FILTER_SANITIZE_SPECIAL_CHARS),
                'clientAddress' => filter_var($_POST['clientAddress'], FILTER_SANITIZE_SPECIAL_CHARS),
                'clientJoinDate' => $_POST['clientJoinDate'],
                'ClientYearEnd' => $_POST['ClientYearEnd'],
            ];

            try {
                $newClientId = $this->clientModel->addClient($data);
                $_SESSION['success_message'] = "Client added successfully!";

                // Log the addition of a new client
                if (isset($_SESSION['user'])) {
                    $this->logger->log(
                        $_SESSION['user']['name'],                      // Username
                        $_SESSION['user']['userTitle'],                 // Title
                        $_SESSION['user']['department'],                // Department
                        'ClientController::addClient',                  // Page or Action
                        'add_client',                                    // Action
                        "Added Client ID: $newClientId with Name: " . $data['client_name'], // Additional Info
                        'INFO'                                           // Log Level
                    );
                }

            } catch (Exception $e) {
                $_SESSION['error_message'] = "Failed to add client: " . $e->getMessage();

                // Log the failure to add a client
                if (isset($_SESSION['user'])) {
                    $this->logger->log(
                        $_SESSION['user']['name'],                      // Username
                        $_SESSION['user']['userTitle'],                 // Title
                        $_SESSION['user']['department'],                // Department
                        'ClientController::addClient',                  // Page or Action
                        'error',                                         // Action
                        "Failed to add Client with Name: " . $data['client_name'] . ". Error: " . $e->getMessage(), // Additional Info
                        'ERROR'                                          // Log Level
                    );
                }
            }

            header("Location: " . BASE_URL . "/clients");
            exit;
        }
    }

    //---------------------------------------------------------------------------EDIT CLIENT-------------------------------------------------------------------------------
    /**
     * Edit an existing client.
     */
    public function editClient() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF Token Validation
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $_SESSION['error_message'] = 'Invalid CSRF token';
                header("Location: " . BASE_URL . "/clients");
                exit;
            }

            $id = intval($_POST['id']);
            $data = [
                'client_name' => filter_var($_POST['client_name'], FILTER_SANITIZE_SPECIAL_CHARS),
                'accountExecutive' => filter_var($_POST['accountExecutive'], FILTER_SANITIZE_SPECIAL_CHARS),
                'contactEmail' => filter_var($_POST['contactEmail'], FILTER_VALIDATE_EMAIL),
                'contactPhone' => filter_var($_POST['contactPhone'], FILTER_SANITIZE_SPECIAL_CHARS),
                'contactName' => filter_var($_POST['contactName'], FILTER_SANITIZE_SPECIAL_CHARS),
                'contactTitle' => filter_var($_POST['contactTitle'], FILTER_SANITIZE_SPECIAL_CHARS),
                'secondaryContactEmail' => filter_var($_POST['secondaryContactEmail'], FILTER_SANITIZE_EMAIL),
                'secondaryContactName' => filter_var($_POST['secondaryContactName'], FILTER_SANITIZE_SPECIAL_CHARS),
                'secondaryContactPhone' => filter_var($_POST['secondaryContactPhone'], FILTER_SANITIZE_SPECIAL_CHARS),
                'secondaryContactTitle' => filter_var($_POST['secondaryContactTitle'], FILTER_SANITIZE_SPECIAL_CHARS),
                'clientLogo' => filter_var($_POST['clientLogo'], FILTER_SANITIZE_SPECIAL_CHARS),
                'clientAddress' => filter_var($_POST['clientAddress'], FILTER_SANITIZE_SPECIAL_CHARS),
                'clientJoinDate' => $_POST['clientJoinDate'],
                'ClientYearEnd' => $_POST['ClientYearEnd'],
            ];

            try {
                $this->clientModel->updateClient($id, $data);
                $_SESSION['success_message'] = "Client updated successfully!";

                // Log the update of a client
                if (isset($_SESSION['user'])) {
                    $this->logger->log(
                        $_SESSION['user']['name'],                      // Username
                        $_SESSION['user']['userTitle'],                 // Title
                        $_SESSION['user']['department'],                // Department
                        'ClientController::editClient',                 // Page or Action
                        'edit_client',                                   // Action
                        "Edited Client ID: $id with Name: " . $data['client_name'], // Additional Info
                        'INFO'                                           // Log Level
                    );
                }

            } catch (Exception $e) {
                $_SESSION['error_message'] = "Failed to update client: " . $e->getMessage();

                // Log the failure to update a client
                if (isset($_SESSION['user'])) {
                    $this->logger->log(
                        $_SESSION['user']['name'],                      // Username
                        $_SESSION['user']['userTitle'],                 // Title
                        $_SESSION['user']['department'],                // Department
                        'ClientController::editClient',                 // Page or Action
                        'error',                                         // Action
                        "Failed to edit Client ID: $id. Error: " . $e->getMessage(), // Additional Info
                        'ERROR'                                          // Log Level
                    );
                }
            }

            header("Location: " . BASE_URL . "/clients");
            exit;
        }
    }

    //---------------------------------------------------------------------------DELETE CLIENT (SOFT DELETE)-------------------------------------------------------------------------------
    /**
     * Soft delete a client (move to recycle bin).
     */
    public function deleteClient() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF Token Validation
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $_SESSION['error_message'] = 'Invalid CSRF token.';
                header("Location: " . BASE_URL . "/clients");
                exit;
            }

            // Get the ID of the client to "delete"
            if (isset($_POST['id']) && !empty($_POST['id'])) {
                $id = intval($_POST['id']);

                // Try marking the client as deleted
                try {
                    // Fetch client details before deletion for logging
                    $client = $this->clientModel->getClientById($id);
                    if (!$client) {
                        throw new Exception("Client not found.");
                    }

                    $this->clientModel->softDeleteClient($id); // Soft delete
                    $_SESSION['success_message'] = "Client moved to recycle bin successfully!";

                    // Log the soft deletion of a client
                    if (isset($_SESSION['user'])) {
                        $this->logger->log(
                            $_SESSION['user']['name'],                      // Username
                            $_SESSION['user']['userTitle'],                 // Title
                            $_SESSION['user']['department'],                // Department
                            'ClientController::deleteClient',               // Page or Action
                            'soft_delete_client',                            // Action
                            "Soft Deleted Client ID: $id with Name: " . $client['client_name'], // Additional Info
                            'INFO'                                           // Log Level
                        );
                    }

                } catch (Exception $e) {
                    $_SESSION['error_message'] = "Failed to move client to recycle bin: " . $e->getMessage();

                    // Log the failure to soft delete a client
                    if (isset($_SESSION['user'])) {
                        $this->logger->log(
                            $_SESSION['user']['name'],                      // Username
                            $_SESSION['user']['userTitle'],                 // Title
                            $_SESSION['user']['department'],                // Department
                            'ClientController::deleteClient',               // Page or Action
                            'error',                                         // Action
                            "Failed to soft delete Client ID: $id. Error: " . $e->getMessage(), // Additional Info
                            'ERROR'                                          // Log Level
                        );
                    }
                }
            } else {
                $_SESSION['error_message'] = "Client ID is missing for deletion.";

                // Log the attempt to delete without ID
                if (isset($_SESSION['user'])) {
                    $this->logger->log(
                        $_SESSION['user']['name'],                      // Username
                        $_SESSION['user']['userTitle'],                 // Title
                        $_SESSION['user']['department'],                // Department
                        'ClientController::deleteClient',               // Page or Action
                        'error',                                         // Action
                        "Attempted to soft delete without Client ID.",    // Additional Info
                        'WARNING'                                        // Log Level
                    );
                }
            }

            // Redirect back to the client list (this will reload the page)
            header("Location: " . BASE_URL . "/clients");
            exit;
        }
    }

    //---------------------------------------------------------------------------RESTORE CLIENT FROM RECYCLE BIN-------------------------------------------------------------------------------
    /**
     * Restore a client from the recycle bin.
     */
    public function restoreClient() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF Token Validation
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $_SESSION['error_message'] = 'Invalid CSRF token.';
                header("Location: " . BASE_URL . "/client-recycle-bin");
                exit;
            }

            // Get client ID to restore
            if (isset($_POST['id']) && !empty($_POST['id'])) {
                $id = intval($_POST['id']);

                // Try restoring the client
                try {
                    // Fetch client details before restoration for logging
                    $client = $this->clientModel->getClientById($id);
                    if (!$client) {
                        throw new Exception("Client not found.");
                    }

                    $this->clientModel->restoreClient($id);
                    $_SESSION['success_message'] = "Client restored successfully!";

                    // Log the restoration of a client
                    if (isset($_SESSION['user'])) {
                        $this->logger->log(
                            $_SESSION['user']['name'],                      // Username
                            $_SESSION['user']['userTitle'],                 // Title
                            $_SESSION['user']['department'],                // Department
                            'ClientController::restoreClient',              // Page or Action
                            'restore_client',                                // Action
                            "Restored Client ID: $id with Name: " . $client['client_name'], // Additional Info
                            'INFO'                                           // Log Level
                        );
                    }

                } catch (Exception $e) {
                    $_SESSION['error_message'] = "Failed to restore client: " . $e->getMessage();

                    // Log the failure to restore a client
                    if (isset($_SESSION['user'])) {
                        $this->logger->log(
                            $_SESSION['user']['name'],                      // Username
                            $_SESSION['user']['userTitle'],                 // Title
                            $_SESSION['user']['department'],                // Department
                            'ClientController::restoreClient',              // Page or Action
                            'error',                                         // Action
                            "Failed to restore Client ID: $id. Error: " . $e->getMessage(), // Additional Info
                            'ERROR'                                          // Log Level
                        );
                    }
                }
            } else {
                $_SESSION['error_message'] = "Client ID is missing for restoration.";

                // Log the attempt to restore without ID
                if (isset($_SESSION['user'])) {
                    $this->logger->log(
                        $_SESSION['user']['name'],                      // Username
                        $_SESSION['user']['userTitle'],                 // Title
                        $_SESSION['user']['department'],                // Department
                        'ClientController::restoreClient',              // Page or Action
                        'error',                                         // Action
                        "Attempted to restore without Client ID.",        // Additional Info
                        'WARNING'                                        // Log Level
                    );
                }
            }

            // Redirect back to the clients view to refresh the page
            header("Location: " . BASE_URL . "/clients");
            exit;
        }
    }

    //---------------------------------------------------------------------------PERMANENT DELETE CLIENT-------------------------------------------------------------------------------
    /**
     * Permanently delete a client.
     */
    public function permanentDeleteClient() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF Token Validation
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $_SESSION['error_message'] = 'Invalid CSRF token.';
                header("Location: " . BASE_URL . "/client-recycle-bin");
                exit;
            }

            // Get the ID of the client to delete permanently
            if (isset($_POST['id']) && !empty($_POST['id'])) {
                $id = intval($_POST['id']);

                // Try permanently deleting the client
                try {
                    // Fetch client details before permanent deletion for logging
                    $client = $this->clientModel->getClientById($id);
                    if (!$client) {
                        throw new Exception("Client not found.");
                    }

                    $this->clientModel->permanentDeleteClient($id);
                    $_SESSION['success_message'] = "Client deleted permanently!";

                    // Log the permanent deletion of a client
                    if (isset($_SESSION['user'])) {
                        $this->logger->log(
                            $_SESSION['user']['name'],                      // Username
                            $_SESSION['user']['userTitle'],                 // Title
                            $_SESSION['user']['department'],                // Department
                            'ClientController::permanentDeleteClient',      // Page or Action
                            'permanent_delete_client',                       // Action
                            "Permanently Deleted Client ID: $id with Name: " . $client['client_name'], // Additional Info
                            'WARNING'                                        // Log Level (Higher severity for permanent deletions)
                        );
                    }

                } catch (Exception $e) {
                    $_SESSION['error_message'] = "Failed to delete client permanently: " . $e->getMessage();

                    // Log the failure to permanently delete a client
                    if (isset($_SESSION['user'])) {
                        $this->logger->log(
                            $_SESSION['user']['name'],                      // Username
                            $_SESSION['user']['userTitle'],                 // Title
                            $_SESSION['user']['department'],                // Department
                            'ClientController::permanentDeleteClient',      // Page or Action
                            'error',                                         // Action
                            "Failed to permanently delete Client ID: $id. Error: " . $e->getMessage(), // Additional Info
                            'ERROR'                                          // Log Level
                        );
                    }
                }
            } else {
                $_SESSION['error_message'] = "Client ID is missing for deletion.";

                // Log the attempt to permanently delete without ID
                if (isset($_SESSION['user'])) {
                    $this->logger->log(
                        $_SESSION['user']['name'],                      // Username
                        $_SESSION['user']['userTitle'],                 // Title
                        $_SESSION['user']['department'],                // Department
                        'ClientController::permanentDeleteClient',      // Page or Action
                        'error',                                         // Action
                        "Attempted to permanently delete without Client ID.", // Additional Info
                        'WARNING'                                        // Log Level
                    );
                }
            }

            // Redirect back to the recycle bin to refresh the page
            header("Location: " . BASE_URL . "/client-recycle-bin");
            exit;
        }
    }

    //---------------------------------------------------------------------------VIEW CLIENT BY ID-------------------------------------------------------------------------------
    /**
     * View a specific client's profile.
     *
     * @param int $id
     */
    public function viewClient($id) {
        try {
            $id = intval($id);
            $client = $this->clientModel->getClientById($id);
            if (!$client) {
                throw new Exception("Client not found.");
            }

            // Log the viewing of a client's profile
            if (isset($_SESSION['user'])) {
                $this->logger->log(
                    $_SESSION['user']['name'],                  // Username
                    $_SESSION['user']['userTitle'],             // Title
                    $_SESSION['user']['department'],            // Department
                    'ClientController::viewClient',             // Page or Action
                    'view_client',                              // Action
                    "Viewed Client ID: $id with Name: " . $client['client_name'], // Additional Info
                    'INFO'                                       // Log Level
                );
            }

            require __DIR__ . '/../views/client-profile.php';
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Failed to retrieve client information: " . $e->getMessage();

            // Log the failure to view a client's profile
            if (isset($_SESSION['user'])) {
                $this->logger->log(
                    $_SESSION['user']['name'],                  // Username
                    $_SESSION['user']['userTitle'],             // Title
                    $_SESSION['user']['department'],            // Department
                    'ClientController::viewClient',             // Page or Action
                    'error',                                     // Action
                    "Failed to view Client ID: $id. Error: " . $e->getMessage(), // Additional Info
                    'ERROR'                                      // Log Level
                );
            }

            header("Location: " . BASE_URL . "/clients");
            exit;
        }
    }
}
?>
