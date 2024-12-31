<?php
// TaskController.php
require_once __DIR__ . '/../models/TaskModel.php';
require_once __DIR__ . '/../models/TaskDescriptionModel.php';
require_once __DIR__ . '/../models/ClientModel.php';
require_once __DIR__ . '/../models/ReportTypeModel.php';
require_once __DIR__ . '/../libraries/SimpleXLSXGen.php';
require_once __DIR__ . '/../libraries/Logger.php'; // Include the Logger class

use Shuchkin\SimpleXLSXGen;

class TaskController {
    private $taskModel;
    private $taskDescriptionModel;
    private $clientModel;
    private $reportTypeModel;
    private $logger; // Add Logger as a property

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->taskModel = new TaskModel();
        $this->taskDescriptionModel = new TaskDescriptionModel();
        $this->clientModel = new ClientModel();
        $this->reportTypeModel = new ReportTypeModel();
        $this->logger = Logger::getInstance(); // Initialize the Logger
    }

    public function hasUnfinishedTasks($tasks) {
        foreach ($tasks as $task) {
            if (empty($task['taskEndTime']) || empty($task['taskTotalTime'])) {
                return true;
            }
        }
        return false;
    }

    //---------------------------------------------------------------------------GET TASKS WITH USERS USERNAME-------------------------------------------------------------------------------
    public function listTasks() {
        $name = $_SESSION['user']['name'];
        $tasks = $this->taskModel->getTasksByUserName($name);
        $taskDescriptions = $this->taskDescriptionModel->getAllTaskDescriptions();
        $clients = $this->clientModel->getAllClients();
        $reports = $this->reportTypeModel->getAllReportTypes();

        // Determine if the user has any unfinished tasks
        $hasUnfinishedTasks = $this->hasUnfinishedTasks($tasks);

        // Log the view all tasks action
        $this->logger->log(
            $_SESSION['user']['name'],
            $_SESSION['user']['userTitle'],
            $_SESSION['user']['department'],
            'TaskController::listTasks',
            'view_all_tasks',
            "Viewed all tasks for user: $name",
            'INFO'
        ); 

        // Pass the information to the view
        include __DIR__ . '/../views/tasks.php';
    }

    //---------------------------------------------------------------------------FETCH TASK FROM RECYCLE BIN-------------------------------------------------------------------------------

    public function listDeletedTasks() {
        $tasks = $this->taskModel->getDeletedTasks();

        // Log the view deleted tasks action
        $this->logger->log(
            $_SESSION['user']['name'],
            $_SESSION['user']['userTitle'],
            $_SESSION['user']['department'],
            'TaskController::listDeletedTasks',
            'view_deleted_tasks',
            "Viewed deleted tasks",
            'INFO'
        ); 

        include __DIR__ . '/../views/task-recycle-bin.php';
    }

    //---------------------------------------------------------------------------FETCH TASK FROM RECYCLE BIN-------------------------------------------------------------------------------

    public function adminTimesheet() {
        $tasks = $this->taskModel->getAllTasks();

        // Log the view admin timesheet action
        $this->logger->log(
            $_SESSION['user']['name'],
            $_SESSION['user']['userTitle'],
            $_SESSION['user']['department'],
            'TaskController::adminTimesheet',
            'view_admin_timesheet',
            "Viewed all tasks in admin timesheet",
            'INFO'
        ); 

        include __DIR__ . '/../views/adminTasks.php';
    }

    //---------------------------------------------------------------------------VIEW TASK PROFILE-------------------------------------------------------------------------------

    public function viewAdminTask($id) {
        // Fetch the task details
        $task = $this->taskModel->getTaskById($id);
        if (!$task) {
            $_SESSION['error_message'] = "Task not found!";
            header("Location: " . BASE_URL . "/admin-tasks");
            exit;
        }

        // Fetch the task history
        $taskHistory = $this->taskModel->getTaskHistoryByTaskId($id);

        // Fetch any additional data if needed (e.g., task descriptions, clients, reports)
        $taskDescriptions = $this->taskDescriptionModel->getAllTaskDescriptions();
        $clientLogo = $this->clientModel->getClientLogoByName($task['client']);
        $task['clientLogo'] = $clientLogo ? $clientLogo : 'assets/images/Placeholder.jpg';
        $reports = $this->reportTypeModel->getAllReportTypes();

        // Record history
        $userId = $_SESSION['user']['id'] ?? null;
        $action = 'viewed';
        $changes = null; // Typically, viewing doesn't change data

        $this->taskModel->recordHistory($id, $action, $userId, $changes);

        // Log the view action
        $this->logger->log(
            $_SESSION['user']['name'],
            $_SESSION['user']['userTitle'],
            $_SESSION['user']['department'],
            'TaskController::viewAdminTask',
            'view_admin_task',
            "Viewed Admin Task ID: $id",
            'INFO'
        );

        // Pass data to the admin task profile view
        include __DIR__ . '/../views/admin-task-profile.php';
    }

    public function exportTaskHistoryExcel($id) {
        // Validate Task ID
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if (!$id) {
            $_SESSION['error_message'] = "Invalid Task ID!";
            header("Location: " . BASE_URL . "/admin-tasks-profile");
            exit;
        }

        // Fetch task and history
        $task = $this->taskModel->getTaskById($id);
        if (!$task) {
            $_SESSION['error_message'] = "Task not found!";
            header("Location: " . BASE_URL . "/admin-tasks-profile");
            exit;
        }

        $taskHistory = $this->taskModel->getTaskHistoryByTaskId($id);

        // Prepare data for Excel
        $data = [];
        // Define headers
        $data[] = ['Action', 'User', 'Date & Time', 'Changes'];

        foreach ($taskHistory as $history) {
            // Determine Changes based on Action
            if (!empty($history['action']) && strtolower($history['action']) === 'added') {
                $changes = 'N/A';
            } elseif (!empty($history['changes'])) {
                $changesArray = json_decode($history['changes'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($changesArray)) {
                    $changes = '';
                    foreach ($changesArray as $field => $change) {
                        $old = isset($change['old']) ? $change['old'] : 'N/A';
                        $new = isset($change['new']) ? $change['new'] : 'N/A';
                        $changes .= $field . ": " . $old . " → " . $new . "\n";
                    }
                    // Remove the trailing newline character
                    $changes = rtrim($changes, "\n");
                } else {
                    $changes = 'Invalid changes data.';
                }
            } else {
                $changes = 'No additional details.';
            }

            $data[] = [
                ucfirst($history['action']),
                htmlspecialchars($history['userName'] ?? 'System'),
                isset($history['dateTime']) ? (new DateTime($history['dateTime']))->format('Y-m-d H:i:s') : 'N/A',
                $changes
            ];
        }

        // Generate Excel File
        $xlsx = SimpleXLSXGen::create('Admin_Tasks_Report')
            ->addSheet($data, 'Task Histories');

        // Log the export action
        $this->logger->log(
            $_SESSION['user']['name'],
            $_SESSION['user']['userTitle'],
            $_SESSION['user']['department'],
            'TaskController::exportTaskHistoryExcel',
            'export_task_history',
            "Exported Task History Excel for Task ID: $id",
            'INFO'
        );

        // Download the Excel file
        $xlsx->downloadAs('task_history_' . $id . '.xlsx');
        exit;
    }

    public function exportTaskExcel() {
        // Fetch all tasks
        $tasks = $this->taskModel->getAllTasks(); // Ensure this method retrieves all tasks

        // Fetch all task histories
        $taskHistories = $this->taskModel->getAllTaskHistories(); // Ensure this method retrieves all histories

        // Prepare data for Sheet 1: All Tasks
        $sheet1Data = [];
        // Define headers for Sheet 1
        $sheet1Data[] = [
            'ID', 
            'User Name', 
            'Task Name', 
            'Client', 
            'Report Type', 
            'Start Time', 
            'End Time', 
            'Time So Far', 
            'Total Time', 
            'Quoted Cost', 
            'Actual Cost', 
            'Late Work', 
            'Overtime', 
            'Comments'
        ];

        foreach ($tasks as $task) {
            $sheet1Data[] = [
                $task['id'],
                htmlspecialchars($task['userName']),
                htmlspecialchars($task['taskType']),
                htmlspecialchars($task['client']),
                htmlspecialchars($task['reportType']),
                htmlspecialchars($task['taskStartTime']),
                htmlspecialchars($task['taskEndTime'] ?? 'TBD'),
                htmlspecialchars($task['timeSoFar'] ?? '00:00:00'),
                htmlspecialchars($task['taskTotalTime'] ?? 'TBD'),
                htmlspecialchars('R' . $task['taskQuotedCost'] ?? 'TBD'),
                htmlspecialchars('R' . $task['taskActualCost'] ?? 'TBD'),
                $task['taskLateWork'] ? '✔️' : '❌',
                $task['taskOvertime'] ? '✔️' : '❌',
                htmlspecialchars($task['comments'] ?? '')
            ];
        }

        // Prepare data for Sheet 2: Task Histories
        $sheet2Data = [];
        // Define headers for Sheet 2
        $sheet2Data[] = ['Task ID', 'Action', 'User', 'Date & Time', 'Changes'];

        foreach ($taskHistories as $history) {
            // Initialize Changes
            if (isset($history['action']) && strtolower($history['action']) === 'added') {
                $changes = 'N/A';
            } elseif (isset($history['changes']) && !empty($history['changes'])) {
                // Decode the 'changes' JSON field
                $changesArray = json_decode($history['changes'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($changesArray)) {
                    $changes = '';
                    foreach ($changesArray as $field => $change) {
                        $old = isset($change['old']) ? $change['old'] : 'N/A';
                        $new = isset($change['new']) ? $change['new'] : 'N/A';
                        $changes .= $field . ": " . $old . " → " . $new . "\n";
                    }
                    // Remove the trailing newline character
                    $changes = rtrim($changes, "\n");
                } else {
                    $changes = 'Invalid changes data.';
                }
            } else {
                $changes = 'No additional details.';
            }

            $sheet2Data[] = [
                $history['taskId'], // Corrected Task ID reference
                ucfirst($history['action']),
                htmlspecialchars($history['userName'] ?? 'System'),
                isset($history['dateTime']) ? (new DateTime($history['dateTime']))->format('Y-m-d H:i:s') : 'N/A',
                $changes
            ];
        }

        // Create Excel file with two sheets
        $xlsx = SimpleXLSXGen::create('Admin_Tasks_Report')
            ->addSheet($sheet1Data, 'All Tasks')
            ->addSheet($sheet2Data, 'Task Histories');

        // Log the export action
        $this->logger->log(
            $_SESSION['user']['name'],
            $_SESSION['user']['userTitle'],
            $_SESSION['user']['department'],
            'TaskController::exportTaskExcel',
            'export_all_tasks',
            "Exported all tasks and histories",
            'INFO'
        ); 

        // Download the Excel file
        $xlsx->downloadAs('admin_tasks_report_' . date('Y-m-d_H-i-s') . '.xlsx');
        exit;
    }

    //---------------------------------------------------------------------------ADD NEW TASK-------------------------------------------------------------------------------
    public function addTask() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF Token Validation
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $_SESSION['error_message'] = 'Invalid CSRF token';
                header("Location: " . BASE_URL . "/tasks");
                exit;
            }

            // Prepare data for insertion
            $data = [
                'client' => filter_var($_POST['client_select'], FILTER_SANITIZE_SPECIAL_CHARS),
                'reportType' => filter_var($_POST['report_select'], FILTER_SANITIZE_SPECIAL_CHARS),
                'taskType' => filter_var($_POST['taskDesc'], FILTER_SANITIZE_SPECIAL_CHARS),
                'taskStartTime' => $_POST['taskStartTime'],
                'taskEndTime' => null,
                'taskTotalTime' => null,
                'taskQuotedCost' => 0,
                'taskActualCost' => 0,
                'taskLateWork' => 0,
                'taskOvertime' => 0,
                'isDeleted' => 0,
                'userName' => $_SESSION['user']['name'],
                'userRole' => $_SESSION['user']['role_id'],
                'timeCreated' => date('Y-m-d H:i:s'),
                'timeUpdated' => date('Y-m-d H:i:s'),
                'comments' => filter_var($_POST['comments'], FILTER_SANITIZE_SPECIAL_CHARS)
            ];

            try {
                $newTaskId = $this->taskModel->addTask($data); // Receive the new task ID
                $_SESSION['success_message'] = "Task added successfully!";

                // Record history
                $userId = $_SESSION['user']['id'] ?? null;
                $action = 'added';
                $changes = json_encode($data); // Optionally, store initial data

                $this->taskModel->recordHistory($newTaskId, $action, $userId, $changes);

                // Log the add action
                $this->logger->log(
                    $_SESSION['user']['name'],
                    $_SESSION['user']['userTitle'],
                    $_SESSION['user']['department'],
                    'TaskController::addTask',
                    'add_task',
                    "Added Task ID: $newTaskId",
                    'INFO'
                );

                header("Location: " . BASE_URL . "/tasks");
                exit;
            } catch (Exception $e) {
                $_SESSION['error_message'] = "Failed to add task: " . $e->getMessage();
                header("Location: " . BASE_URL . "/tasks");
                exit;
            }
        }
    }

    //---------------------------------------------------------------------------EDIT AN EXISTING TASK-------------------------------------------------------------------------------

    public function editTask() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF Token Validation
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $_SESSION['error_message'] = 'Invalid CSRF token';
                header("Location: " . BASE_URL . "/tasks");
                exit;
            }

            $id = $_POST['id'];
            $existingTask = $this->taskModel->getTaskById($id);
            $data = [];
            $modifiedFields = []; // Initialize the modifiedFields array

            if (!$existingTask) {
                $_SESSION['error_message'] = "Task not found.";
                header("Location: " . BASE_URL . "/tasks");
                exit;
            }

            // Only allow editing 'client', 'taskType', and 'reportType'
            
            // Compare 'client'
            $newClient = filter_var($_POST['client_select'], FILTER_SANITIZE_SPECIAL_CHARS);
            if ($newClient != $existingTask['client']) {
                $modifiedFields['client'] = [
                    'old' => $existingTask['client'],
                    'new' => $newClient
                ];
                $data['client'] = $newClient;
            }

            // Compare 'reportType'
            $newReportType = filter_var($_POST['report_select'], FILTER_SANITIZE_SPECIAL_CHARS);
            if ($newReportType != $existingTask['reportType']) {
                $modifiedFields['reportType'] = [
                    'old' => $existingTask['reportType'],
                    'new' => $newReportType
                ];
                $data['reportType'] = $newReportType;
            }

            // Compare 'taskType'
            $newTaskType = filter_var($_POST['taskDesc'], FILTER_SANITIZE_SPECIAL_CHARS);
            if ($newTaskType != $existingTask['taskType']) {
                $modifiedFields['taskType'] = [
                    'old' => $existingTask['taskType'],
                    'new' => $newTaskType
                ];
                $data['taskType'] = $newTaskType;
            }

            // If no changes, do nothing
            if (empty($data)) {
                $_SESSION['info_message'] = "No changes made to the task.";
                header("Location: " . BASE_URL . "/tasks");
                exit;
            }

            // Always update 'timeUpdated'
            $data['timeUpdated'] = date('Y-m-d H:i:s');

            try {
                $this->taskModel->updateTask($id, $data);
                $_SESSION['success_message'] = "Task updated successfully!";

                // Record history if any modifications were made
                if (!empty($modifiedFields)) {
                    $userId = $_SESSION['user']['id'] ?? null;
                    $action = 'edited';
                    $changes = json_encode($modifiedFields);

                    $this->taskModel->recordHistory($id, $action, $userId, $changes);
                }

                // Log the edit action
                $this->logger->log(
                    $_SESSION['user']['name'],
                    $_SESSION['user']['userTitle'],
                    $_SESSION['user']['department'],
                    'TaskController::editTask',
                    'edit_task',
                    "Edited Task ID: $id",
                    'INFO'
                );

            } catch (Exception $e) {
                $_SESSION['error_message'] = "Failed to update task: " . $e->getMessage();
                header("Location: " . BASE_URL . "/tasks");
                exit;
            }

            header("Location: " . BASE_URL . "/tasks");
            exit;
        }
    }


    //---------------------------------------------------------------------------SENT TASK TO RECYCLE BIN-------------------------------------------------------------------------------

    public function deleteTask() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF Token Validation
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $_SESSION['error_message'] = 'Invalid CSRF token';
                header("Location: " . BASE_URL . "/tasks");
                exit;
            }

            $id = $_POST['id'];

            try {
                $this->taskModel->softDeleteTask($id);
                $_SESSION['success_message'] = "Task moved to recycle bin successfully!";

                // Record history
                $userId = $_SESSION['user']['id'] ?? null;
                $action = 'deleted';
                $changes = null; // Optionally, include reason or other data

                $this->taskModel->recordHistory($id, $action, $userId, $changes);

                // Log the delete action
                $this->logger->log(
                    $_SESSION['user']['name'],
                    $_SESSION['user']['userTitle'],
                    $_SESSION['user']['department'],
                    'TaskController::deleteTask',
                    'delete_task',
                    "Soft Deleted Task ID: $id",
                    'INFO'
                );

            } catch (Exception $e) {
                $_SESSION['error_message'] = "Failed to move task to recycle bin: " . $e->getMessage();
                header("Location: " . BASE_URL . "/tasks");
                exit;
            }

            header("Location: " . BASE_URL . "/tasks");
            exit;
        }
    }

    //---------------------------------------------------------------------------RESTORE TASK FROM RECYCLE BIN-------------------------------------------------------------------------------

    public function restoreTask() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF Token Validation
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $_SESSION['error_message'] = 'Invalid CSRF token';
                header("Location: " . BASE_URL . "/task-recycle-bin");
                exit;
            }

            $id = $_POST['id'];

            try {
                $this->taskModel->restoreTask($id);
                $_SESSION['success_message'] = "Task restored successfully!";

                // Record history
                $userId = $_SESSION['user']['id'] ?? null;
                $action = 'restored';
                $changes = null; // Optionally, include data if needed

                $this->taskModel->recordHistory($id, $action, $userId, $changes);

                // Log the restore action
                $this->logger->log(
                    $_SESSION['user']['name'],
                    $_SESSION['user']['userTitle'],
                    $_SESSION['user']['department'],
                    'TaskController::restoreTask',
                    'restore_task',
                    "Restored Task ID: $id",
                    'INFO'
                );

            } catch (Exception $e) {
                $_SESSION['error_message'] = "Failed to restore task: " . $e->getMessage();
                header("Location: " . BASE_URL . "/task-recycle-bin");
                exit;
            }

            header("Location: " . BASE_URL . "/tasks");
            exit;
        }
    }

    //---------------------------------------------------------------------------PERMANENTLY DELETE TASK-------------------------------------------------------------------------------

    public function permanentDeleteTask() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF Token Validation
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $_SESSION['error_message'] = 'Invalid CSRF token';
                header("Location: " . BASE_URL . "/task-recycle-bin");
                exit;
            }

            $id = $_POST['id'];

            try {
                $this->taskModel->permanentDeleteTask($id);
                $_SESSION['success_message'] = "Task deleted permanently!";

                // Record history
                $userId = $_SESSION['user']['id'] ?? null;
                $action = 'permanently_deleted';
                $changes = null; // Optionally, include data if needed

                $this->taskModel->recordHistory($id, $action, $userId, $changes);

                // Log the permanent delete action
                $this->logger->log(
                    $_SESSION['user']['name'],
                    $_SESSION['user']['userTitle'],
                    $_SESSION['user']['department'],
                    'TaskController::permanentDeleteTask',
                    'permanent_delete_task',
                    "Permanently Deleted Task ID: $id",
                    'WARNING' // Use a higher severity level
                );

            } catch (Exception $e) {
                $_SESSION['error_message'] = "Failed to delete task permanently: " . $e->getMessage();
                header("Location: " . BASE_URL . "/task-recycle-bin");
                exit;
            }

            header("Location: " . BASE_URL . "/task-recycle-bin");
            exit;
        }
    }

    //---------------------------------------------------------------------------VIEW TASK PROFILE-------------------------------------------------------------------------------

    public function viewTask($id) {
        try {
            $task = $this->taskModel->getTaskById($id);
            if (!$task) {
                $_SESSION['error_message'] = "Task not found.";
                header("Location: " . BASE_URL . "/tasks");
                exit;
            }

            // Fetch additional data if needed
            $taskDescriptions = $this->taskDescriptionModel->getAllTaskDescriptions();
            $clients = $this->clientModel->getAllClients();
            $reports = $this->reportTypeModel->getAllReportTypes();
            $clientLogo = $this->clientModel->getClientLogoByName($task['client']);
            $task['clientLogo'] = $clientLogo ? $clientLogo : 'assets/images/Placeholder.jpg';

            // Record history
            $userId = $_SESSION['user']['id'] ?? null;
            $action = 'viewed';
            $changes = null; // Typically, viewing doesn't change data

            $this->taskModel->recordHistory($id, $action, $userId, $changes);

            // Log the view action
            $this->logger->log(
                $_SESSION['user']['name'],
                $_SESSION['user']['userTitle'],
                $_SESSION['user']['department'],
                'TaskController::viewTask',
                'view_task',
                "Viewed Task ID: $id",
                'INFO'
            );

            include __DIR__ . '/../views/task-profile.php';
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Failed to retrieve task information: " . $e->getMessage();
            header("Location: " . BASE_URL . "/tasks-profile");
            exit;
        }
    }

    //---------------------------------------------------------------------------UPDATE TASK VALUE-------------------------------------------------------------------------------

    public function updateTaskValue() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);

            $id = $input['id'] ?? null;
            $column = $input['column'] ?? null;
            $value = $input['value'] ?? null;

            // Validate input
            $allowedColumns = ['taskQuotedCost', 'taskOvertime'];
            if (!$id || !$column || $value === null || !in_array($column, $allowedColumns)) {
                echo json_encode(['success' => false, 'message' => 'Invalid input']);
                exit;
            }

            try {
                // Fetch existing value
                $existingTask = $this->taskModel->getTaskById($id);
                if (!$existingTask) {
                    echo json_encode(['success' => false, 'message' => 'Task not found']);
                    exit;
                }

                $oldValue = $existingTask[$column];

                // Update the value
                $this->taskModel->updateTaskValue($id, $column, $value);

                // Check if the value has changed
                if ($oldValue != $value) {
                    $modifiedFields = [
                        $column => [
                            'old' => $oldValue,
                            'new' => $value
                        ]
                    ];

                    // Record history
                    $userId = $_SESSION['user']['id'] ?? null;
                    $action = 'edited';
                    $changes = json_encode($modifiedFields);

                    $this->taskModel->recordHistory($id, $action, $userId, $changes);

                    // Log the update action
                    $this->logger->log(
                        $_SESSION['user']['name'],
                        $_SESSION['user']['userTitle'],
                        $_SESSION['user']['department'],
                        'TaskController::updateTaskValue',
                        'update_task_value',
                        "Updated $column for Task ID: $id",
                        'INFO'
                    );
                }

                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        }
    }

    public function finishTask() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF Token Validation
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
                exit;
            }

            $id = $_POST['id'] ?? null;
            $column = $_POST['column'] ?? null;
            $value = $_POST['value'] ?? null;

            // Validate input
            $allowedColumns = ['taskEndTime'];
            if (!$id || !$column || $value === null || !in_array($column, $allowedColumns)) {
                echo json_encode(['success' => false, 'message' => 'Invalid input']);
                exit;
            }

            try {
                // Fetch existing task
                $existingTask = $this->taskModel->getTaskById($id);
                if (!$existingTask) {
                    echo json_encode(['success' => false, 'message' => 'Task not found']);
                    exit;
                }

                $oldValue = $existingTask[$column];

                // Prepare data for update
                $data = [$column => $value];

                // Update the task
                $this->taskModel->finishTask($id, $data);

                // Calculate 'taskTotalTime'
                $startTime = strtotime($existingTask['taskStartTime']);
                $endTime = strtotime($value);
                $totalSeconds = $endTime - $startTime;

                if ($totalSeconds > 0) {
                    $hours = floor($totalSeconds / 3600);
                    $minutes = floor(($totalSeconds % 3600) / 60);
                    $totalTime = sprintf('%02dH:%02dM', $hours, $minutes);
                } else {
                    $totalTime = '00H:00M';
                }

                // Calculate 'taskActualCost'
                $taskCost = $this->taskModel->getTaskCostByType($existingTask['taskType']);
                if ($taskCost) {
                    $totalHours = $totalSeconds / 3600; // Convert seconds to hours
                    $actualCost = round($taskCost * $totalHours, 2);
                } else {
                    $actualCost = 0;
                }

                // Determine 'taskLateWork'
                $isLate = $this->isLateWork($startTime) || $this->isLateWork($endTime);

                // Prepare data for updating other fields
                $additionalData = [
                    'taskTotalTime' => $totalTime,
                    'taskActualCost' => $actualCost,
                    'taskLateWork' => $isLate ? 1 : 0
                ];

                // Update additional fields
                $this->taskModel->updateTask($id, $additionalData);

                // Prepare modified fields for history
                $modifiedFields = [];

                // Compare and record 'taskEndTime'
                if ($oldValue != $value) {
                    $modifiedFields['taskEndTime'] = [
                        'old' => $oldValue ? date('Y-m-d H:i:s', strtotime($oldValue)) : 'TBD',
                        'new' => $value
                    ];
                }

                // Compare and record 'taskTotalTime'
                if ($existingTask['taskTotalTime'] != $totalTime) {
                    $modifiedFields['taskTotalTime'] = [
                        'old' => $existingTask['taskTotalTime'] ?? 'TBD',
                        'new' => $totalTime
                    ];
                }

                // Compare and record 'taskActualCost'
                if ($existingTask['taskActualCost'] != $actualCost) {
                    $modifiedFields['taskActualCost'] = [
                        'old' => $existingTask['taskActualCost'] ?? 0,
                        'new' => $actualCost
                    ];
                }

                // Compare and record 'taskLateWork'
                if ($existingTask['taskLateWork'] != ($isLate ? 1 : 0)) {
                    $modifiedFields['taskLateWork'] = [
                        'old' => $existingTask['taskLateWork'],
                        'new' => $isLate ? 1 : 0
                    ];
                }

                // Record history if any modifications were made
                if (!empty($modifiedFields)) {
                    $userId = $_SESSION['user']['id'] ?? null;
                    $action = 'finished';
                    $changes = json_encode($modifiedFields);

                    $this->taskModel->recordHistory($id, $action, $userId, $changes);
                }

                // Log the finish action
                $this->logger->log(
                    $_SESSION['user']['name'],
                    $_SESSION['user']['userTitle'],
                    $_SESSION['user']['department'],
                    'TaskController::finishTask',
                    'finish_task',
                    "Finished Task ID: $id",
                    'INFO'
                );

                echo json_encode(['success' => true, 'message' => 'Task finished successfully.']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        }
    }

    // Helper function to determine if a time is considered "late"
    private function isLateWork($timestamp) {
        if ($timestamp === null) return false;
        $hour = (int)date('H', $timestamp);
        return ($hour >= 19 || $hour < 5); // Between 7 PM and 5 AM
    }
}
?>
