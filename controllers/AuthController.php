<?php
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../libraries/Logger.php'; // Include the Logger class

class AuthController {
    private $userModel;
    private $logger;

	
    public function __construct() {
        $this->userModel = new UserModel();
        $this->logger = Logger::getInstance(); // Initialize the Logger
    }

    //---------------------------------------------------------------------------LOGIN-------------------------------------------------------------------------------

    public function login($errorMessage = null) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'];

            try {
                // Check user credentials
                $user = $this->userModel->getUserByEmail($email);

                if ($user && password_verify($password, $user['password'])) {
                    // Start session and store user data
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }

                    // Store user details in session
                    $_SESSION['user'] = [
                        'id' => $user['id'],
                        'name' => $user['name'],
                        'email' => $user['email'],
                        'role_id' => $user['role_id'],
                        'role_name' => $user['role_name'],                
                        'userTitle' => isset($user['userTitle']) && $user['userTitle'] ? $user['userTitle'] : 'N/A',
                        'department' => isset($user['department']) && $user['department'] ? $user['department'] : 'N/A',
                        'manager' => isset($user['manager']) && $user['manager'] ? $user['manager'] : 'N/A',
                        'languages' => isset($user['languages']) && $user['languages'] ? $user['languages'] : 'N/A',
                        'joinDate' => isset($user['joinDate']) && $user['joinDate'] ? $user['joinDate'] : 'N/A',
                        'birthday' => isset($user['birthday']) && $user['birthday'] ? $user['birthday'] : 'N/A',
                        'experience' => isset($user['experience']) && $user['experience'] ? $user['experience'] : 'N/A',
                        'address' => isset($user['address']) && $user['address'] ? $user['address'] : 'N/A',
                        'phone' => isset($user['phone']) && $user['phone'] ? $user['phone'] : 'N/A',
                        'userImg' => isset($user['userImg']) && $user['userImg'] ? $user['userImg'] : 'Placeholder.jpg',
                        'linkedin' => isset($user['linkedin']) && $user['linkedin'] ? $user['linkedin'] : 'N/A',
                        'facebook' => isset($user['facebook']) && $user['facebook'] ? $user['facebook'] : 'N/A',
                    ];

					
					
					
					
                    // Log the successful login
                    $this->logger->log(
                        $_SESSION['user']['name'],
                        $_SESSION['user']['userTitle'],
                        $_SESSION['user']['department'],
                        'AuthController::login',
                        'login_success',
                        'User logged in successfully.',
                        'INFO'
                    );

                    // Redirect to dashboard
                    header("Location: " . BASE_URL . "/dashboard");
                    exit;
                } else {
                    // Log the failed login attempt
                    $this->logger->log(
                        $email,
                        'N/A',
                        'N/A',
                        'AuthController::login',
                        'login_failed',
                        'Invalid email or password.',
                        'WARNING'
                    );

                    // Redirect to login page with an error message
                    header("Location: " . BASE_URL . "/login?error=invalid");
                    exit;
                }
            } catch (Exception $e) {
                // Log the exception during login
                $this->logger->log(
                    $email,
                    'N/A',
                    'N/A',
                    'AuthController::login',
                    'login_error',
                    'Exception during login: ' . $e->getMessage(),
                    'ERROR'
                );

                // Redirect to login page with an error message
                header("Location: " . BASE_URL . "/login?error=exception");
                exit;
            }
        }

        // Load the login form with an optional error message
        include __DIR__ . '/../views/login.php';
    }

    //---------------------------------------------------------------------------LOGOUT-------------------------------------------------------------------------------

    public function logout() {
        if (isset($_SESSION['user'])) {
            try {
                // Log the logout action
                $this->logger->log(
                    $_SESSION['user']['name'],
                    $_SESSION['user']['userTitle'],
                    $_SESSION['user']['department'],
                    'AuthController::logout',
                    'logout',
                    'User logged out successfully.',
                    'INFO'
                );

                // Clear session data
                session_unset();
                session_destroy();

                // Start a new session to set the success message
                session_start();
               
            } catch (Exception $e) {
                // Log any exception during logout
                $this->logger->log(
                    $_SESSION['user']['name'] ?? 'Unknown',
                    $_SESSION['user']['userTitle'] ?? 'N/A',
                    $_SESSION['user']['department'] ?? 'N/A',
                    'AuthController::logout',
                    'logout_error',
                    'Exception during logout: ' . $e->getMessage(),
                    'ERROR'
                );

                // Redirect to login with an error message
                header("Location: " . BASE_URL . "/login?error=logout_exception");
                exit;
            }
        }

        // Redirect to login page
        header("Location: " . BASE_URL . "/login");
        exit;
    }
}
