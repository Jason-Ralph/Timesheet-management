<?php
error_reporting( E_ALL );
ini_set( 'display_errors', '1' );
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/RoleController.php';
require_once __DIR__ . '/../controllers/ClientController.php';
require_once __DIR__ . '/../controllers/TaskController.php';
require_once __DIR__ . '/../controllers/ReportsController.php';
require_once __DIR__ . '/../controllers/TaskDescriptionController.php';
require_once __DIR__ . '/../controllers/ReportTypeController.php';
require_once __DIR__ . '/../controllers/RolesPermissionsController.php';
require_once __DIR__ . '/../controllers/PermissionHelperController.php';
require_once __DIR__ . '/../controllers/LogsController.php'; // Include the Logger class
require_once __DIR__ . '/../controllers/LeaveController.php'; // Include the Leave controller
require_once __DIR__ . '/../libraries/Logger.php'; // Include the Logger class

// Get the current route
$request = str_replace( '/TOTG-admin-center/public', '', $_SERVER[ 'REQUEST_URI' ] );
$request = strtok( $request, '?' ); // Remove query string for clarity


function ensureSessionStarted() {
  if ( session_status() === PHP_SESSION_NONE ) {
    session_start();
  }
}

function ensurePermission($permissionName) {
    if (!hasPermission($_SESSION['user']['role_name'], $permissionName)) {
        $_SESSION['error_message'] = "You do not have access to this page.";
        header("Location: " . BASE_URL . "/dashboard");
        exit;
    }
}
$permissionHelperController = new PermissionsHelperController();

define( 'BASE_URL', '/TOTG-admin-center/public' );
define( 'ASSET_PATH', __DIR__ . '/../assets' );

// Routing logic
switch ( strtolower( $request ) ) {
    case '/': // Default route
        ensureSessionStarted();
        if ( !isset( $_SESSION[ 'user' ] ) || !isset( $_SESSION[ 'user' ][ 'id' ] ) ) {
            header( "Location: " . BASE_URL . "/login" );
            exit;
        }
        include __DIR__ . '/../views/dashboard.php'; // dashboard
        break;

    //---------------------------------------------------------------------------LOGIN-------------------------------------------------------------------------------		
    case '/login':
        ensureSessionStarted();
        if ( isset( $_SESSION[ 'user' ] ) ) {
            header( "Location: " . BASE_URL . "/dashboard" );
            exit;
        }

        // Capture error messages
        $errorMessage = null;
        if ( isset( $_GET[ 'error' ] ) ) {
            if ( $_GET[ 'error' ] === 'unauthorized' ) {
                $errorMessage = "You do not have permission to access that page.";
            } elseif ( $_GET[ 'error' ] === 'invalid' ) {
                $errorMessage = "Invalid email or password.";
            }
        }

        $controller = new AuthController();
        $controller->login( $errorMessage );
        break;

    //---------------------------------------------------------------------------LOGOUT-------------------------------------------------------------------------------		
    case '/logout':
        $controller = new AuthController();
        $controller->logout();
        break;

    //---------------------------------------------------------------------------LOAD DASHBOARD-------------------------------------------------------------------------------
    case '/dashboard':
        ensureSessionStarted();

        // Check if the user is logged in
        if ( !isset( $_SESSION[ 'user' ] ) ) {
            header( "Location: " . BASE_URL . "/login?error=unauthorized" );
            exit;
        }
        $permissionHelperController->ensurePermission('Access dashboard page');
        // Pass user data to the dashboard
        $user = $_SESSION[ 'user' ];

        // Load the dashboard view
        include __DIR__ . '/../views/dashboard.php';
        break;

    //---------------------------------------------------------------------------CLIENTS.PHP-------------------------------------------------------------------------------
    case '/clients':
        ensureSessionStarted();

        // Check if the user has permission to access this page
        $permissionHelperController->ensurePermission('Access clients page');

        if ( $_SERVER[ 'REQUEST_METHOD' ] === 'POST' ) {
            // Handle add, update, and delete operations based on the "action" field
            $controller = new ClientController();

            if ( isset( $_POST[ 'action' ] ) && $_POST[ 'action' ] === 'delete' ) {
                // Delete client
                $controller->deleteClient();
            } else if ( isset( $_POST[ 'id' ] ) && !empty( $_POST[ 'id' ] ) ) {
                // Edit client
                $controller->editClient();
            } else {
                // Add client
                $controller->addClient();
            }
        } else {
            // Fetch and display clients
            $controller = new ClientController();
            $clients = $controller->listClients();
            include __DIR__ . '/../views/clients.php';
        }
        break;

    //---------------------------------------------------------------------------CLIENT RECYCLE BIN-------------------------------------------------------------------------------		
    case '/client-recycle-bin':
        ensureSessionStarted();

        $permissionHelperController->ensurePermission('Access client recycle bin');

        // Fetch deleted clients
        $controller = new ClientController();
        $clients = $controller->listDeletedClients();

        // Include the recycle bin view
        include __DIR__ . '/../views/client-recycle-bin.php';
        break;

    //---------------------------------------------------------------------------CLIENT RESTORE FROM RECYCLE BIN-------------------------------------------------------------------------------		
    case '/restore-client':
        ensureSessionStarted();

        $permissionHelperController->ensurePermission('Restore client');

        // Call the restore client method
        $controller = new ClientController();
        $controller->restoreClient();

        // Redirect back to the recycle bin to refresh the page
        header( "Location: " . BASE_URL . "/client-recycle-bin" );
        exit;

    //---------------------------------------------------------------------------CLIENTS PERMANENT DELETE-------------------------------------------------------------------------------		
    case '/permanent-delete-client':
        ensureSessionStarted();

        // Check if the user has permission to access this action
        $permissionHelperController->ensurePermission('Permanent delete client');

        // Permanently delete the client
        $controller = new ClientController();
        $controller->permanentDeleteClient();
        break;

    //---------------------------------------------------------------------------CLIENTS VIEW ALL-------------------------------------------------------------------------------
    case '/view-client':
        ensureSessionStarted();

        // Check if the user has permission to access this action
        $permissionHelperController->ensurePermission('View client');

        if ( isset( $_GET[ 'id' ] ) && !empty( $_GET[ 'id' ] ) ) {
            $controller = new ClientController();
            $controller->viewClient( $_GET[ 'id' ] );
        } else {
            // Optionally, set a session message to inform the user
            $_SESSION['error_message'] = "Client not found.";
            header("Location: " . BASE_URL . "/clients");
            exit;
        }
        break;

    //---------------------------------------------------------------------------USERS.PHP-------------------------------------------------------------------------------
    case '/users':
        ensureSessionStarted();
        $permissionHelperController->ensurePermission('Access users page');
        $controller = new UserController();

        if ( $_SERVER[ 'REQUEST_METHOD' ] === 'POST' ) {
            // Handle add, update, and delete operations based on the "action" field
            if ( isset( $_POST[ 'action' ] ) && $_POST[ 'action' ] === 'delete' ) {
                // Delete user
                $controller->deleteUser();
            } else if ( isset( $_POST[ 'id' ] ) && !empty( $_POST[ 'id' ] ) ) {
                // Edit user
                $controller->editUser();
            } else {
                // Add user
                $controller->addUser();
            }
        } else {
            // Fetch and display users
            $controller->listUsers();
        }
        break;

    //---------------------------------------------------------------------------USERS RECYCLE BIN-------------------------------------------------------------------------------		
    case '/user-recycle-bin':
        ensureSessionStarted();

        // Check if the user has permission to access this page
        $permissionHelperController->ensurePermission('Access user recycle bin');

        // Fetch deleted users
        $controller = new UserController();
        $users = $controller->listDeletedUsers();
        break;

    //---------------------------------------------------------------------------USERS RESTORE FROM RECYCLE BIN-------------------------------------------------------------------------------		
    case '/restore-user':
        ensureSessionStarted();

        // Check if the user has permission to access this page
        $permissionHelperController->ensurePermission('Restore user');

        // Call the restore user method
        $controller = new UserController();
        $controller->restoreUser();

        // Redirect back to the recycle bin to refresh the page
        header( "Location: " . BASE_URL . "/users" );
        exit;

    //---------------------------------------------------------------------------USERS PERMANENT DELETE-------------------------------------------------------------------------------		
    case '/permanent-delete-user':
        ensureSessionStarted();

        // Check if the user has permission to access this action
        $permissionHelperController->ensurePermission('Permanent delete user');

        // Permanently delete the user
        $controller = new UserController();
        $controller->permanentDeleteUser();
        break;

    //---------------------------------------------------------------------------USERS VIEW PROFILE-------------------------------------------------------------------------------		
    case '/user-profile':
        ensureSessionStarted();

        // Check if the user has permission to access this action
        $permissionHelperController->ensurePermission('View user');

        if ( isset( $_GET[ 'id' ] ) && !empty( $_GET[ 'id' ] ) ) {
            $controller = new UserController();
            $controller->viewUser( $_GET[ 'id' ] );
			
        } else {
            // Optionally, set a session message to inform the user
            $_SESSION['error_message'] = "User not found!";
            header("Location: " . BASE_URL . "/users");
            exit;
        }
        break;

    //---------------------------------------------------------------------------TASKS.PHP-------------------------------------------------------------------------------
    case '/tasks':
        ensureSessionStarted();

        // Check if the user has permission to access this page
        $permissionHelperController->ensurePermission('Access tasks page');

        $controller = new TaskController();

        if ( $_SERVER[ 'REQUEST_METHOD' ] === 'POST' ) {
            // Handle add, update, and delete operations based on the "action" field
            if ( isset( $_POST[ 'action' ] ) && $_POST[ 'action' ] === 'delete' ) {
                // Delete task
                $controller->deleteTask();
            } else if ( isset( $_POST[ 'id' ] ) && !empty( $_POST[ 'id' ] ) ) {
                // Edit task
                $controller->editTask();
            } else {
                // Add task
                $controller->addTask();
            }
        } else {
            // Fetch and display tasks
            $controller->listTasks();
        }
        break;

    //---------------------------------------------------------------------------TASKS RECYCLE BIN-------------------------------------------------------------------------------		
    case '/task-recycle-bin':
        ensureSessionStarted();

        // Check if the user has permission to access this page
        $permissionHelperController->ensurePermission('Access tasks recycle bin');

        // Fetch deleted tasks
        $controller = new TaskController();
        $deletedTasks = $controller->listDeletedTasks();
        break;

    //---------------------------------------------------------------------------TASK RESTORE FROM RECYCLE BIN-------------------------------------------------------------------------------	
    case '/restore-task':
        ensureSessionStarted();

        // Check if the user has permission to access this page
        $permissionHelperController->ensurePermission('Restore tasks');

        // Call the restore task method
        $controller = new TaskController();
        $controller->restoreTask();

        // Redirect back to the recycle bin to refresh the page
        header( "Location: " . BASE_URL . "/tasks" );
        exit;

    //---------------------------------------------------------------------------TASK PERMANENT DELETE-------------------------------------------------------------------------------	
    case '/permanent-delete-task':
        ensureSessionStarted();

        // Check if the user has permission to access this action
        $permissionHelperController->ensurePermission('Permanent delete task');

        // Permanently delete the task
        $controller = new TaskController();
        $controller->permanentDeleteTask();
        break;

    //---------------------------------------------------------------------------TASK VIEW TASK PROFILE-------------------------------------------------------------------------------		
    case '/task-profile':
        ensureSessionStarted();

        // Check if the user has permission to access this action
        $permissionHelperController->ensurePermission('View task');

        if ( isset( $_GET[ 'id' ] ) && !empty( $_GET[ 'id' ] ) ) {
            $controller = new TaskController();
            $controller->viewTask( $_GET[ 'id' ] );
        } else {
            // Optionally, set a session message to inform the user
            $_SESSION['error_message'] = "Task not found!";
            header("Location: " . BASE_URL . "/tasks");
            exit;
        }
        break;	

    //---------------------------------------------------------------------------TASK VIEW TASK PROFILE-------------------------------------------------------------------------------		
     case '/admin-task-profile':
        ensureSessionStarted();

        // Check if the user has permission to access this action
        $permissionHelperController->ensurePermission('View admin task');

        if ( isset( $_GET[ 'id' ] ) && !empty( $_GET[ 'id' ] ) ) {
            $controller = new TaskController();
            $controller->viewAdminTask( $_GET[ 'id' ] );
        } else {
            // Optionally, set a session message to inform the user
            $_SESSION['error_message'] = "Task not found!";
            header("Location: " . BASE_URL . "/admin-tasks");
            exit;
        }
        break;		
    
    //---------------------------------------------------------------------------ADMIN TIMESHEET-------------------------------------------------------------------------------
    case '/admin-tasks':
        ensureSessionStarted();

        // Ensure only admin users can access
        $permissionHelperController->ensurePermission('Access admin tasks page');

        $controller = new TaskController();
        $controller->adminTimesheet();
        break;
    
		
	 //---------------------------------------------------------------------------EXPORT TASK HISTORY-------------------------------------------------------------------------------
    case '/export-task-history-excel':
    ensureSessionStarted();

    // Check if the user has permission to export task history
    $permissionHelperController->ensurePermission('Access admin tasks page');

    if ( isset( $_GET[ 'id' ] ) && !empty( $_GET[ 'id' ] ) ) {
        $controller = new TaskController();
        $controller->exportTaskHistoryExcel( $_GET[ 'id' ] );
    } else {
        // Optionally, set a session message to inform the user
        $_SESSION['error_message'] = "Task not found!";
        header("Location: " . BASE_URL . "/admin-tasks");
        exit;
    }
    break;	
		
		
	 case '/export-task-excel':
		
		// Check if the user has permission to export task history
    $permissionHelperController->ensurePermission('Access admin tasks page');
		
        $controller = new TaskController();
        $controller->exportTaskExcel();
        break;	
		
    //---------------------------------------------------------------------------ADMIN UPDATE QUOTED COST-------------------------------------------------------------------------------
    case '/update-task':
        ensureSessionStarted();

        // Ensure only authorized roles can update tasks
        $permissionHelperController->ensurePermission('Edit task');

        // Delegate to the TaskController's update method
        $controller = new TaskController();
        $controller->updateTaskValue();
        break;
    
    //---------------------------------------------------------------------------ADMIN FINISH TASK-------------------------------------------------------------------------------
    case '/finish-task':
        ensureSessionStarted();

        // Ensure only authorized roles can update tasks
        $permissionHelperController->ensurePermission('Edit task');

        // Delegate to the TaskController's finish method
        $controller = new TaskController();
        $controller->finishTask();
        break;
    
    //---------------------------------------------------------------------------REPORTS.PHP-------------------------------------------------------------------------------
    case '/reports':
        ensureSessionStarted();

        // Check role-based permission if needed
        $permissionHelperController->ensurePermission('Access reports page');

        // Call the report controller
        $controller = new ReportsController();
        $controller->index();
        break;

    //---------------------------------------------------------------------------REPORT TYPES ADMIN----------------------------------------------
    case '/report-types':
        ensureSessionStarted();

        // Role check if needed (e.g., only 'Admin' can edit report types):
        $permissionHelperController->ensurePermission('Access report types page');

        $rtController = new ReportTypeController();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action']) && $_POST['action'] === 'delete') {
                $rtController->deleteReportType();
            } else if (isset($_POST['id']) && !empty($_POST['id'])) {
                $rtController->editReportType();
            } else {
                $rtController->addReportType();
            }
        } else {
            // List
            $rtController->listReportTypes();
        }
        break;

    case '/report-type-recycle-bin':
        ensureSessionStarted();
        $permissionHelperController->ensurePermission('Access report types recycle bin');

        $rtController = new ReportTypeController();
        $rtController->listDeletedReportTypes();
        break;

    case '/restore-report-type':
        ensureSessionStarted();
        $permissionHelperController->ensurePermission('Restore report types');
        $rtController = new ReportTypeController();
        $rtController->restoreReportType();
        break;

    case '/permanent-delete-report-type':
        ensureSessionStarted();
        $permissionHelperController->ensurePermission('Permanent delete report types');
        $rtController = new ReportTypeController();
        $rtController->permanentDeleteReportType();
        break;

    //---------------------------------------------------------------------------TASK DESCRIPTIONS ADMIN------------------------------------------
    case '/task-descriptions':
        ensureSessionStarted();
        $permissionHelperController->ensurePermission('Access task description page');

        $tdController = new TaskDescriptionController();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action']) && $_POST['action'] === 'delete') {
                $tdController->deleteTaskDescription();
            } else if (isset($_POST['id']) && !empty($_POST['id'])) {
                $tdController->editTaskDescription();
            } else {
                $tdController->addTaskDescription();
            }
        } else {
            // Show list
            $tdController->listTaskDescriptions();
        }
        break;

    case '/task-description-recycle-bin':
        ensureSessionStarted();
        $permissionHelperController->ensurePermission('Access task description recycle bin');
        $tdController = new TaskDescriptionController();
        $tdController->listDeletedTaskDescriptions();
        break;

    case '/restore-task-description':
        ensureSessionStarted();
        $permissionHelperController->ensurePermission('Restore task description');
        $tdController = new TaskDescriptionController();
        $tdController->restoreTaskDescription();
        break;

    case '/permanent-delete-task-description':
        ensureSessionStarted();
        $permissionHelperController->ensurePermission('Permanent delete task description');
        $tdController = new TaskDescriptionController();
        $tdController->permanentDeleteTaskDescription();
        break;

    case '/delete-task-description':
        ensureSessionStarted();
        $permissionHelperController->ensurePermission('Permanent delete task description');
        $tdController = new TaskDescriptionController();
        $tdController->deleteTaskDescription();
        break;	
    		
    case '/roles-permissions':
        ensureSessionStarted();
        $permissionHelperController->ensurePermission('Access permissions page');	
        $controller = new RolesPermissionsController();
        $controller->listRolesPermissions();
        break;

    case '/update-role-permission':
        ensureSessionStarted();
        $permissionHelperController->ensurePermission('Edit permission');	
        $controller = new RolesPermissionsController();
        $controller->updateRolePermission();
        break;	
    		
    case '/add-permission':
        ensureSessionStarted();
        $permissionHelperController->ensurePermission('Add permission');
        $controller = new RolesPermissionsController();
        $controller->addPermission();
        break;

    case '/edit-permission':
        ensureSessionStarted();
        $permissionHelperController->ensurePermission('Edit permission');
        $controller = new RolesPermissionsController();
        $controller->editPermission();
        break;

    case '/delete-permission':
        ensureSessionStarted();
        $permissionHelperController->ensurePermission('Delete permission');
        $controller = new RolesPermissionsController();
        $controller->deletePermission();
        break;

		
		
	case '/view-logs':
		ensureSessionStarted();
        $permissionHelperController->ensurePermission('Access logs page');
        $controller = new LogsController();
        $controller->viewLogs();
        break;	
		
	
    case 'view-leaves':
		ensureSessionStarted();
		$permissionHelperController->ensurePermission('Access logs page');
        $leaveController = new LeaveController();
        $leaveController->viewLeaves();
        break;
	case 'apply-leave':
		ensureSessionStarted();
        $leaveController = new LeaveController();
        $leaveController->applyLeave();
        break;
    case 'manage-leaves':
		ensureSessionStarted();
        $leaveController = new LeaveController();
        $leaveController->manageLeaves();
        break;
    case 'approve-leave':
		ensureSessionStarted();
        $leaveController = new LeaveController();
        $leaveController->approveLeave();
        break;
    case 'reject-leave':
		ensureSessionStarted();
        $leaveController = new LeaveController();
        $leaveController->rejectLeave();
        break;
    case 'leave-report':
		ensureSessionStarted();
        $leaveController = new LeaveController();
        $leaveController->leaveReport();
        break;	
		
		
		
		
		
		
    //--------------------------------------------------------------------IF PAGE REQUESTED IS NOT ONE OF THESE LIST ABOVE-----------------------------------------------------
    default:
        http_response_code( 404 );
        // Optionally, set a session message to inform the user
        $_SESSION['error_message'] = "The page you are looking for has not been found.";
        header("Location: " . BASE_URL . "/dashboard");
        exit;
        break;
}
?>
