<?php
// controllers/UserController.php

// Include necessary models
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/TaskModel.php';
require_once __DIR__ . '/../models/RoleModel.php';
require_once __DIR__ . '/../models/PermissionHelperModel.php';
require_once __DIR__ . '/../libraries/Logger.php'; // Include the Logger class
require_once __DIR__ . '/../libraries/discord.php'; // Include the DiscordNotifier


class UserController {
    private $userModel;
    private $taskModel;
    private $roleModel;
    private $logger;
    private $discordWebhookUrl = "https://discord.com/api/webhooks/1323345170795597845/957OjTA-wu6yTDRLkDnhF_Fvrih-W26UV_5DhpfAQgmlooF5YqcPfvApyZnBNuQx6Mqj"; // Replace with your webhook URL


    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->userModel = new UserModel();
        $this->taskModel = new TaskModel();
        $this->roleModel = new RoleModel();
        $this->logger = Logger::getInstance(); // Initialize the Logger
    }

    // Fetch all users
    public function listUsers() {
        $users = $this->userModel->getAllUsersWithRoles();
        $roles = $this->roleModel->getAllRoles(); // Fetch roles

        // Log the view all users action
        $this->logger->log(
            $_SESSION['user']['name'],          // Username
            $_SESSION['user']['userTitle'],         // Title
            $_SESSION['user']['department'],    // Department
            'UserController::listUsers',        // Page or Action
            'view_all_users',                    // Action (e.g., add, edit)
            "Viewed all users for user: " . $_SESSION['user']['name'], // Any extra info (optional)
            'INFO'                               // Log level
        );

        include __DIR__ . '/../views/users.php';
    }

    // Fetch deleted users
    public function listDeletedUsers() { 
        $users = $this->userModel->getDeletedUsers();

        // Log the view deleted users action
        $this->logger->log(
            $_SESSION['user']['name'],          // Username
            $_SESSION['user']['userTitle'],         // Title
            $_SESSION['user']['department'],    // Department
            'UserController::listDeletedUsers', // Page or Action
            'view_deleted_users',                // Action
            "Viewed deleted users for user: " . $_SESSION['user']['name'], // Additional info
            'INFO'                               // Log level
        );

        include __DIR__ . '/../views/user-recycle-bin.php';
    }

    // Add a new user
    public function addUser() { 
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF Token Validation
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $_SESSION['error_message'] = 'Invalid CSRF token';
                header("Location: " . BASE_URL . "/users");
                exit;
            }

            // Prepare data for insertion
            $data = [
                'name' => filter_var($_POST['name'], FILTER_SANITIZE_SPECIAL_CHARS),
                'email' => filter_var($_POST['email'], FILTER_VALIDATE_EMAIL),
                'password' => password_hash($_POST['password'], PASSWORD_BCRYPT),
                'phone' => filter_var($_POST['phone'], FILTER_SANITIZE_SPECIAL_CHARS),
                'userTitle' => filter_var($_POST['userTitle'], FILTER_SANITIZE_SPECIAL_CHARS),
                'department' => filter_var($_POST['department'], FILTER_SANITIZE_SPECIAL_CHARS),
                'manager' => filter_var($_POST['manager'], FILTER_SANITIZE_SPECIAL_CHARS),
                'joinDate' => $_POST['joinDate'],
                'birthday' => $_POST['birthday'],
                'experience' => filter_var($_POST['experience'], FILTER_SANITIZE_SPECIAL_CHARS),
                'address' => filter_var($_POST['address'], FILTER_SANITIZE_SPECIAL_CHARS),
                'userImg' => filter_var($_POST['userImg'], FILTER_SANITIZE_SPECIAL_CHARS),
                'linkedin' => filter_var($_POST['linkedin'], FILTER_SANITIZE_SPECIAL_CHARS),
                'facebook' => filter_var($_POST['facebook'], FILTER_SANITIZE_SPECIAL_CHARS),
                'languages' => filter_var($_POST['languages'], FILTER_SANITIZE_SPECIAL_CHARS),
                'role_id' => intval($_POST['role_id'])
            ];

            try {
                $newUserId = $this->userModel->addUser($data);
                $_SESSION['success_message'] = "User added successfully!";

                // Record history
                $userId = $_SESSION['user']['id'] ?? null;
                $action = 'added';
                $changes = json_encode($data); // Optionally, store initial data

              

                // Log the add action
                $this->logger->log(
                    $_SESSION['user']['name'],          // Username
                    $_SESSION['user']['userTitle'],         // Title
                    $_SESSION['user']['department'],    // Department
                    'UserController::addUser',          // Page or Action
                    'add_user',                         // Action
                    "Added User ID: $newUserId",        // Additional info
                    'INFO'                              // Log level
                );  

                header("Location: " . BASE_URL . "/users");  
                exit;
            } catch (Exception $e) {
                $_SESSION['error_message'] = "Failed to add user: " . $e->getMessage();
                header("Location: " . BASE_URL . "/users");  
                exit;
            }
        }
    }

    // Edit a user
    public function editUser() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // CSRF Token Validation
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error_message'] = 'Invalid CSRF token';
            header("Location: " . BASE_URL . "/users");
            exit;
        }

        $id = $_POST['id'];
        $existingUser = $this->userModel->getUserById($id);
        $data = [];
        $modifiedFields = []; // Initialize the modifiedFields array

        if (!$existingUser) {
            $_SESSION['error_message'] = "User not found.";
            header("Location: " . BASE_URL . "/users");
            exit;
        }

        // Define fields that can be edited
        $editableFields = [
            'name',
            'email',
            'phone',
            'userTitle',
            'department',
            'manager',
            'joinDate',
            'birthday',
            'experience',
            'address',
            'userImg',
            'linkedin',
            'facebook',
            'languages',
            'role_id'
        ];

        foreach ($editableFields as $field) {
            if (isset($_POST[$field])) {
                $newValue = filter_var($_POST[$field], FILTER_SANITIZE_SPECIAL_CHARS);
                if ($field === 'email') {
                    $newValue = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
                    if (!$newValue) {
                        $_SESSION['error_message'] = "Invalid email format.";
                        header("Location: " . BASE_URL . "/users");
                        exit;
                    }
                } elseif ($field === 'role_id') {
                    $newValue = intval($_POST['role_id']);
                }

                if ($newValue != $existingUser[$field]) {
                    $modifiedFields[$field] = [
                        'old' => $existingUser[$field],
                        'new' => $newValue
                    ];
                    $data[$field] = $newValue;
                }
            }
        }

        // If no changes, do nothing
        if (empty($data)) {
            $_SESSION['info_message'] = "No changes made to the user.";
            header("Location: " . BASE_URL . "/users");
            exit;
        }

        try {
            $this->userModel->updateUser($id, $data);
            $_SESSION['success_message'] = "User updated successfully!";

            // Record history if any modifications were made
            if (!empty($modifiedFields)) {
                $userId = $_SESSION['user']['id'] ?? null;
                $action = 'edited';
                $changes = json_encode($modifiedFields);
                // Implement history logging as needed
            }

            // Log the edit action
            $this->logger->log(
                $_SESSION['user']['name'],          // Username
                $_SESSION['user']['userTitle'],     // Title
                $_SESSION['user']['department'],    // Department
                'UserController::editUser',         // Page or Action
                'edit_user',                        // Action
                "Edited User ID: $id",               // Additional info
                'INFO'                              // Log level
            );  

            header("Location: " . BASE_URL . "/users");
            exit;
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Failed to update user: " . $e->getMessage();
            header("Location: " . BASE_URL . "/users");
            exit;
        }
    }
}


    // Soft delete user (move to recycle bin)
    public function deleteUser() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF Token Validation
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $_SESSION['error_message'] = 'Invalid CSRF token';
                header("Location: " . BASE_URL . "/users");
                exit;
            }

            $id = $_POST['id'];

            try {
                $this->userModel->softDeleteUser($id);
                $_SESSION['success_message'] = "User moved to recycle bin successfully!";

                // Record history
                $userId = $_SESSION['user']['id'] ?? null;
                $action = 'deleted';
                $changes = null; // Optionally, include reason or other data

               

                // Log the delete action
                $this->logger->log(
                    $_SESSION['user']['name'],          // Username
                    $_SESSION['user']['userTitle'],         // Title
                    $_SESSION['user']['department'],    // Department
                    'UserController::deleteUser',       // Page or Action
                    'delete_user',                       // Action
                    "Soft Deleted User ID: $id",         // Additional info
                    'INFO'                               // Log level
                );  

            } catch (Exception $e) {
                $_SESSION['error_message'] = "Failed to move user to recycle bin: " . $e->getMessage();
            }

            header("Location: " . BASE_URL . "/users");
            exit;
        }
    }

    // Restore user from the recycle bin
    public function restoreUser() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF Token Validation
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $_SESSION['error_message'] = 'Invalid CSRF token';
                header("Location: " . BASE_URL . "/user-recycle-bin");
                exit;
            }

            $id = $_POST['id'];

            try {
                $this->userModel->restoreUser($id);
                $_SESSION['success_message'] = "User restored successfully!";

                // Record history
                $userId = $_SESSION['user']['id'] ?? null;
                $action = 'restored';
                $changes = null; // Optionally, include data if needed

                

                // Log the restore action
                $this->logger->log(
                    $_SESSION['user']['name'],          // Username
                    $_SESSION['user']['userTitle'],         // Title
                    $_SESSION['user']['department'],    // Department
                    'UserController::restoreUser',      // Page or Action
                    'restore_user',                      // Action
                    "Restored User ID: $id",             // Additional info
                    'INFO'                               // Log level
                );  

            } catch (Exception $e) {
                $_SESSION['error_message'] = "Failed to restore user: " . $e->getMessage();
            }

            header("Location: " . BASE_URL . "/users");
            exit;
        }
    }

    // Permanently delete user
    public function permanentDeleteUser() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF Token Validation
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $_SESSION['error_message'] = 'Invalid CSRF token';
                header("Location: " . BASE_URL . "/user-recycle-bin");
                exit;
            }

            $id = $_POST['id'];

            try {
                $this->userModel->permanentDeleteUser($id);
                $_SESSION['success_message'] = "User deleted permanently!";

                // Record history
                $userId = $_SESSION['user']['id'] ?? null;
                $action = 'permanently_deleted';
                $changes = null; // Optionally, include data if needed

                

                // Log the permanent delete action
                $this->logger->log(
                    $_SESSION['user']['name'],          // Username
                    $_SESSION['user']['userTitle'],         // Title
                    $_SESSION['user']['department'],    // Department
                    'UserController::permanentDeleteUser', // Page or Action
                    'permanent_delete_user',             // Action
                    "Permanently Deleted User ID: $id",  // Additional info
                    'WARNING'                            // Log level (use higher severity for deletions)
                );  

            } catch (Exception $e) {
                $_SESSION['error_message'] = "Failed to delete user permanently: " . $e->getMessage();
            }

            header("Location: " . BASE_URL . "/user-recycle-bin");
            exit;
        }
    }

    // View user details (like a profile)
   public function viewUser($id) {
    try {
        // 1. Fetch the user by ID
        $user = $this->userModel->getUserById($id);
        if (!$user) {
            $_SESSION['error_message'] = "User not found.";
            header("Location: " . BASE_URL . "/users");
            exit;
        }

        // 2. Fetch additional data if needed
        $roles = $this->roleModel->getAllRoles();

        // 3. Get the name of the user being viewed
        $viewedUsername = $user['name'] ?? null;

        if (!$viewedUsername) {
            $_SESSION['error_message'] = "User does not have a valid name.";
            header("Location: " . BASE_URL . "/users");
            exit;
        }

        // 4. Fetch logs for the viewed user
        $logs = $this->logger->getLogsForUser($viewedUsername);

        // 5. Aggregate log actions
        $logActionsCount = [];
        foreach ($logs as $logEntry) {
            $action = $logEntry['action'] ?? 'undefined_action';
            $action = trim($action);
            if ($action === '') {
                $action = 'undefined_action';
            }
            if (!isset($logActionsCount[$action])) {
                $logActionsCount[$action] = 0;
            }
            $logActionsCount[$action]++;
        }

        // 6. Fetch tasks for the viewed user
        $tasks = $this->taskModel->getTasksByUserName($viewedUsername);

        // 7. Aggregate task data
        $data = $this->aggregateUserTaskData($tasks, $viewedUsername);

        // 8. Log the view action (current user viewing another user)
        $currentUserName = $_SESSION['user']['name'] ?? 'Unknown';
        $currentUserTitle = $_SESSION['user']['userTitle'] ?? 'Unknown';
        $currentUserDepartment = $_SESSION['user']['department'] ?? 'Unknown';

        $this->logger->log(
            $currentUserName,              // Username of the current user
            $currentUserTitle,             // Title of the current user
            $currentUserDepartment,        // Department of the current user
            'UserController::viewUser',    // Page or Action
            'view_user',                    // Action
            "Viewed User ID: $id",           // Additional info
            'INFO'                          // Log level
        );  

        // 9. Include the view with the aggregated data
        include __DIR__ . '/../views/user-profile.php';
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Failed to retrieve user information: " . $e->getMessage();
        header("Location: " . BASE_URL . "/users");
        exit;
    }
}


	
	
	
	
	
	
	
	
	private function aggregateUserTaskData($tasks, $viewedUsername) {
    // Initialize counters and arrays
    $countUser = [];
    $timeUser = [];
    $actualUser = [];
    $quotedUser = [];
    $overtimeUser = [];
    $lateUser = [];
    $doneUser = [];
    $activeUser = [];
    $avgUser = [];
    $ratioOvertimeU = [];
    $ratioLateU = [];

    // Global counters
    $totalCompleted = 0;
    $totalActive = 0;

    // Additional stats
    $tasks_by_month = [];
    $start_hour_dist = array_fill(0, 24, 0);

    // Extra
    $day_of_week_count = [0,0,0,0,0,0,0];
    $costVarianceUser = [];
    $durationBuckets = ['0-2'=>0, '2-4'=>0, '4-8'=>0, '8+'=>0];

    // Loop through each task
    foreach ($tasks as $task) {
        $userName = $task['userName'] ?? 'Unknown';
        $quoted = (float)($task['taskQuotedCost'] ?? 0);
        $actual = (float)($task['taskActualCost'] ?? 0);
        $isOvertime = !empty($task['taskOvertime']) && $task['taskOvertime'] != '0';
        $isLate = !empty($task['taskLateWork']) && $task['taskLateWork'] != '0';
        $isDone = !empty($task['taskEndTime']);

        if ($isDone) $totalCompleted++; else $totalActive++;

        // timeSpent
        $startTime = strtotime($task['taskStartTime'] ?? '');
        $endTime = $isDone ? strtotime($task['taskEndTime']) : time();
        $timeSpent = 0;
        if ($startTime && $endTime && $endTime > $startTime) {
            $timeSpent = $endTime - $startTime;
        }

        // tasks by month
        if ($startTime) {
            $monthKey = date('Y-m', $startTime);
            if (!isset($tasks_by_month[$monthKey])) {
                $tasks_by_month[$monthKey] = 0;
            }
            $tasks_by_month[$monthKey]++;
        }

        // distribution of start hour
        if ($startTime) {
            $hr = (int)date('G', $startTime);
            $start_hour_dist[$hr]++;
        }

        // day_of_week_count
        if ($startTime) {
            $dw = (int)date('w', $startTime); // 0=Sunday..6=Saturday
            $day_of_week_count[$dw]++;
        }

        // cost variance
        $costDiff = $actual - $quoted;
        if (!isset($costVarianceUser[$userName])) $costVarianceUser[$userName] = 0;
        $costVarianceUser[$userName] += $costDiff;

        // Duration Buckets
        $hoursSpent = $timeSpent / 3600;
        if ($hoursSpent <= 2)      $durationBuckets['0-2']++;
        else if ($hoursSpent <= 4) $durationBuckets['2-4']++;
        else if ($hoursSpent <= 8) $durationBuckets['4-8']++;
        else                       $durationBuckets['8+']++;

        // Stats by user (since it's a single user, simplify)
        $countUser[$userName] = ($countUser[$userName] ?? 0) + 1;
        $timeUser[$userName] = ($timeUser[$userName] ?? 0) + $timeSpent;
        $actualUser[$userName] = ($actualUser[$userName] ?? 0) + $actual;
        $quotedUser[$userName] = ($quotedUser[$userName] ?? 0) + $quoted;
        if ($isOvertime) $overtimeUser[$userName] = ($overtimeUser[$userName] ?? 0) + 1;
        if ($isLate)     $lateUser[$userName] = ($lateUser[$userName] ?? 0) + 1;
        if ($isDone)     $doneUser[$userName] = ($doneUser[$userName] ?? 0) + 1;
        else            $activeUser[$userName] = ($activeUser[$userName] ?? 0) + 1;
    }

    // Compute average and ratio stats for user
    foreach ($countUser as $u => $cnt) {
        $avgUser[$u] = $cnt ? ($timeUser[$u]/$cnt) : 0; // in seconds
        $ratioOvertimeU[$u] = $cnt ? ($overtimeUser[$u]/$cnt) : 0;
        $ratioLateU[$u]     = $cnt ? ($lateUser[$u]/$cnt)     : 0;
    }

    // Return the aggregated data
    return [
        // *** User stats ***
        'count_user'     => $countUser,
        'time_user'      => $timeUser,
        'actual_user'    => $actualUser,
        'quoted_user'    => $quotedUser,
        'overtime_user'  => $overtimeUser,
        'late_user'      => $lateUser,
        'completed_user' => $doneUser,
        'active_user'    => $activeUser,
        'avg_user'            => $avgUser,
        'ratio_overtime_user' => $ratioOvertimeU,
        'ratio_late_user'     => $ratioLateU,

        // *** Global stats ***
        'totalCompleted' => $totalCompleted,
        'totalActive'    => $totalActive,
        'tasks_by_month' => $tasks_by_month,
        'start_hour_dist'=> $start_hour_dist,

        // *** Additional ***
        'day_of_week_count'    => $day_of_week_count,
        'cost_variance_user'   => $costVarianceUser,
        'duration_buckets'     => $durationBuckets
    ];
}
	
	
	
	
	
}
?>
