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

// Get the current route
$request = str_replace( '/TOTG-admin-center/public', '', $_SERVER[ 'REQUEST_URI' ] );
$request = strtok( $request, '?' ); // Remove query string for clarity
$permissionHelperController = new PermissionsHelperController();
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

    // Pass user data to the dashboard
    $user = $_SESSION[ 'user' ];

    // Load the dashboard view
    include __DIR__ . '/../views/dashboard.php';
    break;

		
  //---------------------------------------------------------------------------CLIENTS.PHP-------------------------------------------------------------------------------
  case '/clients':
    ensureSessionStarted();

    // Check if the user has permission to access this page
    $allowed_roles = [ 'Admin', 'Account executive', 'Production assistant', 'IT' ];
    if ( !in_array( $_SESSION[ 'user' ][ 'role_name' ], $allowed_roles ) ) {
      // Optionally, set a session message to inform the user
        $_SESSION['error_message'] = "You do not have permission to access that page.";
        header("Location: " . BASE_URL . "/dashboard");
      exit;
    }

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

    // Check if the user has permission to access this page
    $allowed_roles = [ 'Admin', 'Account executive', 'Production assistant', 'IT' ];
    if ( !in_array( $_SESSION[ 'user' ][ 'role_name' ], $allowed_roles ) ) {
      // Optionally, set a session message to inform the user
        $_SESSION['error_message'] = "You do not have permission to access that page.";
        header("Location: " . BASE_URL . "/dashboard");
      exit;
    }

    // Fetch deleted clients
    $controller = new ClientController();
    $clients = $controller->listDeletedClients();

    // Include the recycle bin view
    include __DIR__ . '/../views/client-recycle-bin.php';
    break;



		
  //---------------------------------------------------------------------------CLIENT RESTORE FROM RECYCLE BIN-------------------------------------------------------------------------------		
  case '/restore-client':
    ensureSessionStarted();

    // Check if the user has permission to access this page
    $allowed_roles = [ 'Admin', 'Account executive', 'Production assistant', 'IT' ];
    if ( !in_array( $_SESSION[ 'user' ][ 'role_name' ], $allowed_roles ) ) {
      // Optionally, set a session message to inform the user
        $_SESSION['error_message'] = "You do not have permission to access that page.";
        header("Location: " . BASE_URL . "/dashboard");
      exit;
    }

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
    $allowed_roles = [ 'Admin', 'Account executive', 'Production assistant', 'IT' ];
    if ( !in_array( $_SESSION[ 'user' ][ 'role_name' ], $allowed_roles ) ) {
      // Optionally, set a session message to inform the user
        $_SESSION['error_message'] = "You do not have permission to access that page.";
        header("Location: " . BASE_URL . "/dashboard");
      exit;
    }

    // Permanently delete the client
    $controller = new ClientController();
    $controller->permanentDeleteClient();
    break;


		
 //---------------------------------------------------------------------------CLIENTS VIEW ALL-------------------------------------------------------------------------------
  case '/view-client':
    ensureSessionStarted();

    // Check if the user has permission to access this action
    $allowed_roles = [ 'Admin', 'Account executive', 'IT' ];
    if ( !in_array( $_SESSION[ 'user' ][ 'role_name' ], $allowed_roles ) ) {
      // Optionally, set a session message to inform the user
        $_SESSION['error_message'] = "You do not have permission to access that page.";
        header("Location: " . BASE_URL . "/dashboard");
      exit;
    }

    if ( isset( $_GET[ 'id' ] ) && !empty( $_GET[ 'id' ] ) ) {
      $controller = new ClientController();
      $controller->viewClient( $_GET[ 'id' ] );
    } else {
      // Optionally, set a session message to inform the user
        $_SESSION['error_message'] = "Client not found.";
        header("Location: " . BASE_URL . "/clients");
    }
    break;

		
		
		
  //---------------------------------------------------------------------------USERS.PHP-------------------------------------------------------------------------------
case '/users':
        ensureSessionStarted();
        $permissionHelperController->ensurePermission('Access users page');

        $controller = new UserController();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action']) && $_POST['action'] === 'delete') {
                $controller->deleteUser();
            } elseif (isset($_POST['id']) && !empty($_POST['id'])) {
                $controller->editUser();
            } else {
                $controller->addUser();
            }
        } else {
            $controller->listUsers();
        }
        break;

    


		
		
  //---------------------------------------------------------------------------USERS RECYCLE BIN-------------------------------------------------------------------------------		
  case '/user-recycle-bin':
    ensureSessionStarted();

    // Check if the user has permission to access this page
    $allowed_roles = [ 'Admin', 'Account executive', 'IT' ];
    if ( !in_array( $_SESSION[ 'user' ][ 'role_name' ], $allowed_roles ) ) {
      // Optionally, set a session message to inform the user
        $_SESSION['error_message'] = "You do not have permission to access that page.";
        header("Location: " . BASE_URL . "/dashboard");
      exit;
    }

    // Fetch deleted users
    $controller = new UserController();
    $users = $controller->listDeletedUsers();
    break;

		
		
  //---------------------------------------------------------------------------USERS RESTORE FROM RECYCLE BIN-------------------------------------------------------------------------------		
  case '/restore-user':
    ensureSessionStarted();

    // Check if the user has permission to access this page
    $allowed_roles = [ 'Admin', 'Account executive', 'IT' ];
    if ( !in_array( $_SESSION[ 'user' ][ 'role_name' ], $allowed_roles ) ) {
      // Optionally, set a session message to inform the user
        $_SESSION['error_message'] = "You do not have permission to access that page.";
        header("Location: " . BASE_URL . "/dashboard");
      exit;
    }

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
    $allowed_roles = [ 'Admin', 'Account executive', 'IT' ];
    if ( !in_array( $_SESSION[ 'user' ][ 'role_name' ], $allowed_roles ) ) {
      // Optionally, set a session message to inform the user
        $_SESSION['error_message'] = "You do not have permission to access that page.";
        header("Location: " . BASE_URL . "/dashboard");
      exit;
    }

    // Permanently delete the user
    $controller = new UserController();
    $controller->permanentDeleteUser();
    break;

		
		
		
		
  //---------------------------------------------------------------------------USERS VIEW PROFILE-------------------------------------------------------------------------------		
  case '/user-profile':
    ensureSessionStarted();

    // Check if the user has permission to access this action
    $allowed_roles = [ 'Admin', 'Account executive', 'IT' ];
    if ( !in_array( $_SESSION[ 'user' ][ 'role_name' ], $allowed_roles ) ) {
      // Optionally, set a session message to inform the user
        $_SESSION['error_message'] = "You do not have permission to access that page.";
        header("Location: " . BASE_URL . "/dashboard");
      exit;
    }

    if ( isset( $_GET[ 'id' ] ) && !empty( $_GET[ 'id' ] ) ) {
      $controller = new UserController();
      $controller->viewUser( $_GET[ 'id' ] );
    } else {
      // Optionally, set a session message to inform the user
        $_SESSION['error_message'] = "User not found!";
        header("Location: " . BASE_URL . "/users");
    }
    break;

		
		
		
		
  //---------------------------------------------------------------------------TASKS.PHP-------------------------------------------------------------------------------
case '/tasks':
    ensureSessionStarted();

    // Check if the user has permission to access this page
    $allowed_roles = [ 'Admin', 'Account executive', 'Production assistant', 'Director', 'Developer', 'Typesetter', 'IT', 'Designer', 'Executive and client Manager', 'Reader' ];
    if ( !in_array( $_SESSION[ 'user' ][ 'role_name' ], $allowed_roles ) ) {
        // Optionally, set a session message to inform the user
        $_SESSION['error_message'] = "You do not have permission to access that page.";
        header("Location: " . BASE_URL . "/dashboard");
        exit;
    }

    $controller = new TaskController();

    if ( $_SERVER[ 'REQUEST_METHOD' ] === 'POST' ) {
        // Handle add, update, and delete operations based on the "action" field
        if ( isset( $_POST[ 'action' ] ) && $_POST[ 'action' ] === 'delete' ) {
            // Delete user
            $controller->deleteTask();
        } else if ( isset( $_POST[ 'id' ] ) && !empty( $_POST[ 'id' ] ) ) {
            // Edit user
            $controller->editTask();
        } else {
            // Add user
            $controller->addTask();
        }
    } else {
        // Fetch and display users
        $controller->listTasks();
    }
    break;


		
		
  //---------------------------------------------------------------------------TASKS RECYCLE BIN-------------------------------------------------------------------------------		
  case '/task-recycle-bin':
    ensureSessionStarted();

    // Check if the user has permission to access this page
    $allowed_roles = [ 'Admin', 'Director', 'IT' ];
    if ( !in_array( $_SESSION[ 'user' ][ 'role_name' ], $allowed_roles ) ) {
      // Optionally, set a session message to inform the user
        $_SESSION['error_message'] = "You do not have permission to access that page.";
        header("Location: " . BASE_URL . "/dashboard");
      exit;
    }

    // Fetch deleted tasks
    $controller = new TaskController();
    $deletedTasks = $controller->listDeletedTasks();
    break;

		
		
		
 //---------------------------------------------------------------------------TASK RESTORE FROM RECYCLE BIN-------------------------------------------------------------------------------	
  case '/restore-task':
    ensureSessionStarted();

    // Check if the user has permission to access this page
    $allowed_roles = [ 'Admin', 'Director', 'IT' ];
    if ( !in_array( $_SESSION[ 'user' ][ 'role_name' ], $allowed_roles ) ) {
      // Optionally, set a session message to inform the user
        $_SESSION['error_message'] = "You do not have permission to access that page.";
        header("Location: " . BASE_URL . "/dashboard");
      exit;
    }

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
    $allowed_roles = [ 'Admin', 'Director', 'IT' ];
    if ( !in_array( $_SESSION[ 'user' ][ 'role_name' ], $allowed_roles ) ) {
      // Optionally, set a session message to inform the user
        $_SESSION['error_message'] = "You do not have permission to access that page.";
        header("Location: " . BASE_URL . "/dashboard");
      exit;
    }

    // Permanently delete the user
    $controller = new TaskController();
    $controller->permanentDeleteTask();
    break;

		
		
		
		
		
  //---------------------------------------------------------------------------TASK VIEW TASK PROFILE-------------------------------------------------------------------------------		
  case '/task-profile':
    ensureSessionStarted();

    // Check if the user has permission to access this action
    $allowed_roles = [ 'Admin', 'Account executive', 'Production assistant', 'Director', 'Developer', 'Typesetter', 'IT', 'Designer', 'Executive and client Manager', 'Reader' ];
    if ( !in_array( $_SESSION[ 'user' ][ 'role_name' ], $allowed_roles ) ) {
      // Optionally, set a session message to inform the user
        $_SESSION['error_message'] = "You do not have permission to access that page.";
        header("Location: " . BASE_URL . "/dashboard");
      exit;
    }

    if ( isset( $_GET[ 'id' ] ) && !empty( $_GET[ 'id' ] ) ) {
      $controller = new TaskController();
      $controller->viewTask( $_GET[ 'id' ] );
    } else {
      // Optionally, set a session message to inform the user
        $_SESSION['error_message'] = "Task not found!";
        header("Location: " . BASE_URL . "/tasks");
    }
    break;		
		
	
//---------------------------------------------------------------------------ADMIN TIMESHEET-------------------------------------------------------------------------------
case '/admin-tasks':
    ensureSessionStarted();

    // Ensure only admin users can access
    $allowed_roles = [ 'Admin', 'Director', 'IT' ];
    if (!in_array($_SESSION['user']['role_name'], $allowed_roles)) {
        $_SESSION['error_message'] = "You do not have permission to access this page.";
        header("Location: " . BASE_URL . "/dashboard");
        exit;
    }

    $controller = new TaskController();
    $controller->adminTimesheet();
    break;
			
	
//---------------------------------------------------------------------------ADMIN UPDATE QUOTED COST-------------------------------------------------------------------------------
case '/update-task':
    ensureSessionStarted();

    // Ensure only authorized roles can update tasks
    $allowed_roles = ['Admin', 'Director', 'IT'];
    if (!in_array($_SESSION['user']['role_name'], $allowed_roles)) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        exit;
    }

    // Delegate to the TaskController's update method
    $controller = new TaskController();
    $controller->updateTaskValue();
    break;
	
				
	
//---------------------------------------------------------------------------ADMIN UPDATE QUOTED COST-------------------------------------------------------------------------------
case '/reports':
    ensureSessionStarted();

    // Check role-based permission if needed
    $allowed_roles = ['Admin', 'Director', 'IT'];
    if (!in_array($_SESSION['user']['role_name'], $allowed_roles)) {
        $_SESSION['error_message'] = "You do not have permission to access that page.";
        header("Location: " . BASE_URL . "/dashboard");
        exit;
    }

    // Call the report controller
    $controller = new ReportsController();
    $controller->index();
    break;
		
		
		
		
		
		
		
	//--------------------------------------------------------------------------REPORT TYPES ADMIN----------------------------------------------
case '/report-types':
    ensureSessionStarted();

    // Role check if needed (e.g., only 'Admin' can edit report types):
    $allowed_roles = [ 'Admin', 'Account executive', 'Production assistant', 'IT' ];
    if (!in_array($_SESSION['user']['role_name'], $allowed_roles)) {
        $_SESSION['error_message'] = "You do not have permission to access that page.";
        header("Location: " . BASE_URL . "/dashboard");
        exit;
    }

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
    $allowed_roles = [ 'Admin', 'Account executive', 'Production assistant', 'IT' ];
    if (!in_array($_SESSION['user']['role_name'], $allowed_roles)) {
        $_SESSION['error_message'] = "You do not have permission.";
        header("Location: " . BASE_URL . "/dashboard");
        exit;
    }

    $rtController = new ReportTypeController();
    $rtController->listDeletedReportTypes();
    break;

case '/restore-report-type':
    ensureSessionStarted();
    $allowed_roles = [ 'Admin', 'Account executive', 'Production assistant', 'IT' ];
    if (!in_array($_SESSION['user']['role_name'], $allowed_roles)) {
        $_SESSION['error_message'] = "You do not have permission.";
        header("Location: " . BASE_URL . "/dashboard");
        exit;
    }
    $rtController = new ReportTypeController();
    $rtController->restoreReportType();
    break;

case '/permanent-delete-report-type':
    ensureSessionStarted();
    $allowed_roles = [ 'Admin', 'Account executive', 'Production assistant', 'IT' ];
    if (!in_array($_SESSION['user']['role_name'], $allowed_roles)) {
        $_SESSION['error_message'] = "No permission.";
        header("Location: " . BASE_URL . "/dashboard");
        exit;
    }
    $rtController = new ReportTypeController();
    $rtController->permanentDeleteReportType();
    break;


//--------------------------------------------------------------------------TASK DESCRIPTIONS ADMIN------------------------------------------
case '/task-descriptions':
    ensureSessionStarted();
    $allowed_roles = [ 'Admin', 'Account executive', 'Production assistant', 'IT' ];
    if (!in_array($_SESSION['user']['role_name'], $allowed_roles)) {
        $_SESSION['error_message'] = "No permission.";
        header("Location: " . BASE_URL . "/dashboard");
        exit;
    }

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
    $allowed_roles = [ 'Admin', 'Account executive', 'Production assistant', 'IT' ];
    if (!in_array($_SESSION['user']['role_name'], $allowed_roles)) {
        $_SESSION['error_message'] = "No permission.";
        header("Location: " . BASE_URL . "/dashboard");
        exit;
    }
    $tdController = new TaskDescriptionController();
    $tdController->listDeletedTaskDescriptions();
    break;

case '/restore-task-description':
    ensureSessionStarted();
    $allowed_roles = [ 'Admin', 'Account executive', 'Production assistant', 'IT' ];
    if (!in_array($_SESSION['user']['role_name'], $allowed_roles)) {
        $_SESSION['error_message'] = "No permission.";
        header("Location: " . BASE_URL . "/dashboard");
        exit;
    }
    $tdController = new TaskDescriptionController();
    $tdController->restoreTaskDescription();
    break;

case '/permanent-delete-task-description':
    ensureSessionStarted();
    $allowed_roles = [ 'Admin', 'Account executive', 'Production assistant', 'IT' ];
    if (!in_array($_SESSION['user']['role_name'], $allowed_roles)) {
        $_SESSION['error_message'] = "No permission.";
        header("Location: " . BASE_URL . "/dashboard");
        exit;
    }
    $tdController = new TaskDescriptionController();
    $tdController->permanentDeleteTaskDescription();
    break;	
		
		
		
		
		
	case '/roles-permissions':
    ensureSessionStarted();
    $controller = new RolesPermissionsController();
    $controller->listRolesPermissions();
    break;

case '/update-roles-permissions':
    ensureSessionStarted();
	$allowed_roles = [ 'Admin', 'Account executive', 'Production assistant', 'IT' ];
    if (!in_array($_SESSION['user']['role_name'], $allowed_roles)) {
        $_SESSION['error_message'] = "No permission.";
        header("Location: " . BASE_URL . "/dashboard");
        exit;
    }
    $controller = new RolesPermissionsController();
    $controller->updateRolesPermissions();
    break;	
		
	case '/add-permission':
    ensureSessionStarted();
	$allowed_roles = [ 'Admin', 'Account executive', 'Production assistant', 'IT' ];
    if (!in_array($_SESSION['user']['role_name'], $allowed_roles)) {
        $_SESSION['error_message'] = "No permission.";
        header("Location: " . BASE_URL . "/dashboard");
        exit;
    }
    $controller = new RolesPermissionsController();
    $controller->addPermission();
    break;

case '/edit-permission':
    ensureSessionStarted();
	$allowed_roles = [ 'Admin', 'Account executive', 'Production assistant', 'IT' ];
    if (!in_array($_SESSION['user']['role_name'], $allowed_roles)) {
        $_SESSION['error_message'] = "No permission.";
        header("Location: " . BASE_URL . "/dashboard");
        exit;
    }
    $controller = new RolesPermissionsController();
    $controller->editPermission();
    break;

case '/delete-permission':
    ensureSessionStarted();
	$allowed_roles = [ 'Admin', 'Account executive', 'Production assistant', 'IT' ];
    if (!in_array($_SESSION['user']['role_name'], $allowed_roles)) {
        $_SESSION['error_message'] = "No permission.";
        header("Location: " . BASE_URL . "/dashboard");
        exit;
    }
    $controller = new RolesPermissionsController();
    $controller->deletePermission();
    break;


		
//--------------------------------------------------------------------IF PAGE REQUESTED IS NOT ONE OF THESE LIST ABOVE-----------------------------------------------------
  default:
    http_response_code( 404 );
    // Optionally, set a session message to inform the user
        $_SESSION['error_message'] = "The page you are looking for has not been found";
        header("Location: " . BASE_URL . "/dashboard");
    break;
}