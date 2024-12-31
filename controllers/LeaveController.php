<?php
// controllers/LeaveController.php

require_once __DIR__ . '/../models/LeaveModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../utils/DiscordNotifier.php'; // For Discord notifications
require_once __DIR__ . '/../utils/EmailNotifier.php';    // For email notifications
require_once __DIR__ . '/../libraries/Logger.php';      // Assuming you have a Logger class

class LeaveController {
    private $leaveModel;
    private $userModel;
    private $logger;
    private $discordWebhookUrl = "https://discord.com/api/webhooks/1323345170795597845/957OjTA-wu6yTDRLkDnhF_Fvrih-W26UV_5DhpfAQgmlooF5YqcPfvApyZnBNuQx6Mqj"; // Replace with your webhook URL

    private $emailNotifier;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->leaveModel = new LeaveModel();
        $this->userModel = new UserModel();
        $this->logger = Logger::getInstance(); // Initialize the Logger
        $this->emailNotifier = new EmailNotifier(); // Initialize EmailNotifier
    }

    /**
     * Displays the leave application form and handles submission.
     */
    public function applyLeave() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF Token Validation
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $_SESSION['error_message'] = 'Invalid CSRF token';
                header("Location: " . BASE_URL . "/apply-leave");
                exit;
            }

            // Retrieve and sanitize input
            $leave_type = $_POST['leave_type'] ?? '';
            $reason = $_POST['reason'] ?? '';
            $start_date = $_POST['start_date'] ?? '';
            $end_date = $_POST['end_date'] ?? '';

            // Basic Validation
            if (empty($leave_type) || empty($start_date) || empty($end_date)) {
                $_SESSION['error_message'] = 'Please fill in all required fields.';
                header("Location: " . BASE_URL . "/apply-leave");
                exit;
            }

            // If leave_type is 'other', reason must be provided
            if ($leave_type === 'other' && empty($reason)) {
                $_SESSION['error_message'] = 'Please specify a reason for other leave.';
                header("Location: " . BASE_URL . "/apply-leave");
                exit;
            }

            // Calculate total_days
            $start = new DateTime($start_date);
            $end = new DateTime($end_date);
            $interval = $start->diff($end);
            $total_days = $interval->days + 1; // Including the start day

            // Check if start_date is before end_date
            if ($start > $end) {
                $_SESSION['error_message'] = 'Start date must be before end date.';
                header("Location: " . BASE_URL . "/apply-leave");
                exit;
            }

            // Check if user has sufficient leave balance
            $balances = $this->leaveModel->getLeaveBalances($_SESSION['user']['name']);
            if (!$balances) {
                $_SESSION['error_message'] = 'Leave balances not found.';
                header("Location: " . BASE_URL . "/apply-leave");
                exit;
            }

            if ($balances[$leave_type] < $total_days) {
                $_SESSION['error_message'] = 'Insufficient leave balance for the selected leave type.';
                header("Location: " . BASE_URL . "/apply-leave");
                exit;
            }

            // Prepare data array
            $data = [
                'username'   => $_SESSION['user']['name'],
                'leave_type' => $leave_type,
                'reason'     => $leave_type === 'other' ? $reason : null,
                'start_date' => $start_date,
                'end_date'   => $end_date,
                'total_days' => $total_days
            ];

            try {
                $success = $this->leaveModel->applyLeave($data);

                if ($success) {
                    $_SESSION['success_message'] = "Leave application submitted successfully.";

                    // Log the action
                    $this->logger->log(
                        $_SESSION['user']['name'],
                        $_SESSION['user']['userTitle'],
                        $_SESSION['user']['department'],
                        'LeaveController::applyLeave',
                        'apply_leave',
                        "Applied for $leave_type leave from $start_date to $end_date",
                        'INFO'
                    );

                    // Send Discord Notification to Directors
                    $message = "**New Leave Application:**\n**User:** " . $_SESSION['user']['name'] . "\n**Type:** " . ucfirst($leave_type) . "\n**Duration:** $total_days days\n**From:** $start_date\n**To:** $end_date";
                    DiscordNotifier::sendMessage($this->discordWebhookUrl, $message);

                    // Send Email Notification to Directors
                    $director_email = "director@example.com"; // Replace with actual director email
                    $subject = "New Leave Application from " . $_SESSION['user']['name'];
                    $body = "<p>Dear Director,</p>
                             <p><strong>" . $_SESSION['user']['name'] . "</strong> has applied for <strong>" . ucfirst($leave_type) . "</strong> leave.</p>
                             <p><strong>Duration:</strong> $total_days days<br>
                                <strong>From:</strong> $start_date<br>
                                <strong>To:</strong> $end_date</p>
                             <p>Please review and approve or reject the application.</p>
                             <p>Best Regards,<br>Leave Management System</p>";
                    $altBody = "Dear Director,\n\n" . $_SESSION['user']['name'] . " has applied for " . ucfirst($leave_type) . " leave.\n\nDuration: $total_days days\nFrom: $start_date\nTo: $end_date\n\nPlease review and approve or reject the application.\n\nBest Regards,\nLeave Management System";

                    $emailSent = $this->emailNotifier->sendEmail($director_email, $subject, $body, $altBody);

                    if (!$emailSent) {
                        // Optionally, handle the failure to send email
                        $this->logger->log(
                            'System',
                            'System',
                            'System',
                            'LeaveController::applyLeave',
                            'email_failed',
                            "Failed to send email notification for leave application by " . $_SESSION['user']['name'],
                            'WARNING'
                        );
                    }

                    // Redirect to leaves page
                    header("Location: " . BASE_URL . "/leaves");
                    exit;
                } else {
                    throw new Exception("Failed to submit leave application.");
                }
            } catch (Exception $e) {
                $_SESSION['error_message'] = "Failed to apply for leave: " . $e->getMessage();
                header("Location: " . BASE_URL . "/apply-leave");
                exit;
            }
        }
}
        /**
         * Displays the user's leave history.
         */
        public function viewLeaves() {
            $leaves = $this->leaveModel->getLeavesByUsername($_SESSION['user']['name']);
			
			 // Log the view all users action
        $this->logger->log(
            $_SESSION['user']['name'],          // Username
            $_SESSION['user']['userTitle'],         // Title
            $_SESSION['user']['department'],    // Department
            'LeaveController::listLeaves',        // Page or Action
            'view_all_users',                    // Action (e.g., add, edit)
            "Viewed all users for user: " . $_SESSION['user']['name'], // Any extra info (optional)
            'INFO'                               // Log level
        );

			
			
            include __DIR__ . '/../views/view-leaves.php';
        }

        /**
         * Displays all pending leave applications to directors for approval.
         */
        public function manageLeaves() {
            $pending_leaves = $this->leaveModel->getPendingLeaves();
            include __DIR__ . '/../views/manage-leaves.php';
        }

        /**
         * Approves a leave application.
         */
        public function approveLeave() {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // CSRF Token Validation
                if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                    $_SESSION['error_message'] = 'Invalid CSRF token';
                    header("Location: " . BASE_URL . "/manage-leaves");
                    exit;
                }

                $leave_id = $_POST['leave_id'] ?? null;
                $comments = $_POST['comments'] ?? '';

                if (!$leave_id) {
                    $_SESSION['error_message'] = 'Invalid leave application.';
                    header("Location: " . BASE_URL . "/manage-leaves");
                    exit;
                }

                try {
                    $success = $this->leaveModel->approveLeave($leave_id, $_SESSION['user']['name'], $comments);

                    if ($success) {
                        $_SESSION['success_message'] = "Leave application approved.";

                        // Log the action
                        $this->logger->log(
                            $_SESSION['user']['name'],
                            $_SESSION['user']['userTitle'],
                            $_SESSION['user']['department'],
                            'LeaveController::approveLeave',
                            'approve_leave',
                            "Approved leave application ID: $leave_id",
                            'INFO'
                        );

                        // Fetch the leave details
                        $leave = $this->leaveModel->getLeaveById($leave_id);

                        // Fetch the user's email
                        $user = $this->userModel->getUserByUsername($leave['username']);
                        $user_email = $user['email']; // Adjust based on your users table structure

                        // Send Email Notification to the user
                        $subject = "Your Leave Application has been Approved";
                        $body = "<p>Dear " . htmlspecialchars($leave['username']) . ",</p>
                                 <p>Your leave application for <strong>" . ucfirst($leave['leave_type']) . "</strong> leave has been approved.</p>
                                 <p><strong>Duration:</strong> " . $leave['total_days'] . " days<br>
                                    <strong>From:</strong> " . $leave['start_date'] . "<br>
                                    <strong>To:</strong> " . $leave['end_date'] . "</p>
                                 <p><strong>Comments:</strong> " . htmlspecialchars($leave['comments'] ?? '-') . "</p>
                                 <p>Best Regards,<br>Leave Management System</p>";
                        $altBody = "Dear " . $leave['username'] . ",\n\nYour leave application for " . ucfirst($leave['leave_type']) . " leave has been approved.\n\nDuration: " . $leave['total_days'] . " days\nFrom: " . $leave['start_date'] . "\nTo: " . $leave['end_date'] . "\n\nComments: " . ($leave['comments'] ?? '-') . "\n\nBest Regards,\nLeave Management System";

                        $emailSent = $this->emailNotifier->sendEmail($user_email, $subject, $body, $altBody);

                        if (!$emailSent) {
                            // Optionally, handle the failure to send email
                            $this->logger->log(
                                'System',
                                'System',
                                'System',
                                'LeaveController::approveLeave',
                                'email_failed',
                                "Failed to send email notification for approved leave application ID: $leave_id",
                                'WARNING'
                            );
                        }

                        // Redirect back to manage leaves
                        header("Location: " . BASE_URL . "/manage-leaves");
                        exit;
                    } else {
                        throw new Exception("Failed to approve leave application.");
                    }
                } catch (Exception $e) {
                    $_SESSION['error_message'] = "Failed to approve leave: " . $e->getMessage();
                    header("Location: " . BASE_URL . "/manage-leaves");
                    exit;
                }
            }

            // If not POST, redirect to manage leaves
            header("Location: " . BASE_URL . "/manage-leaves");
            exit;
        }

        /**
         * Rejects a leave application.
         */
        public function rejectLeave() {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // CSRF Token Validation
                if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                    $_SESSION['error_message'] = 'Invalid CSRF token';
                    header("Location: " . BASE_URL . "/manage-leaves");
                    exit;
                }

                $leave_id = $_POST['leave_id'] ?? null;
                $comments = $_POST['comments'] ?? '';

                if (!$leave_id) {
                    $_SESSION['error_message'] = 'Invalid leave application.';
                    header("Location: " . BASE_URL . "/manage-leaves");
                    exit;
                }

                try {
                    $success = $this->leaveModel->rejectLeave($leave_id, $_SESSION['user']['name'], $comments);

                    if ($success) {
                        $_SESSION['success_message'] = "Leave application rejected.";

                        // Log the action
                        $this->logger->log(
                            $_SESSION['user']['name'],
                            $_SESSION['user']['userTitle'],
                            $_SESSION['user']['department'],
                            'LeaveController::rejectLeave',
                            'reject_leave',
                            "Rejected leave application ID: $leave_id",
                            'INFO'
                        );

                        // Fetch the leave details
                        $leave = $this->leaveModel->getLeaveById($leave_id);

                        // Fetch the user's email
                        $user = $this->userModel->getUserByUsername($leave['username']);
                        $user_email = $user['email']; // Adjust based on your users table structure

                        // Send Email Notification to the user
                        $subject = "Your Leave Application has been Rejected";
                        $body = "<p>Dear " . htmlspecialchars($leave['username']) . ",</p>
                                 <p>Your leave application for <strong>" . ucfirst($leave['leave_type']) . "</strong> leave has been rejected.</p>
                                 <p><strong>Duration:</strong> " . $leave['total_days'] . " days<br>
                                    <strong>From:</strong> " . $leave['start_date'] . "<br>
                                    <strong>To:</strong> " . $leave['end_date'] . "</p>
                                 <p><strong>Comments:</strong> " . htmlspecialchars($leave['comments'] ?? '-') . "</p>
                                 <p>If you believe this is a mistake, please contact your director.</p>
                                 <p>Best Regards,<br>Leave Management System</p>";
                        $altBody = "Dear " . $leave['username'] . ",\n\nYour leave application for " . ucfirst($leave['leave_type']) . " leave has been rejected.\n\nDuration: " . $leave['total_days'] . " days\nFrom: " . $leave['start_date'] . "\nTo: " . $leave['end_date'] . "\n\nComments: " . ($leave['comments'] ?? '-') . "\n\nIf you believe this is a mistake, please contact your director.\n\nBest Regards,\nLeave Management System";

                        $emailSent = $this->emailNotifier->sendEmail($user_email, $subject, $body, $altBody);

                        if (!$emailSent) {
                            // Optionally, handle the failure to send email
                            $this->logger->log(
                                'System',
                                'System',
                                'System',
                                'LeaveController::rejectLeave',
                                'email_failed',
                                "Failed to send email notification for rejected leave application ID: $leave_id",
                                'WARNING'
                            );
                        }

                        // Redirect back to manage leaves
                        header("Location: " . BASE_URL . "/manage-leaves");
                        exit;
                    } else {
                        throw new Exception("Failed to reject leave application.");
                    }
                } catch (Exception $e) {
                    $_SESSION['error_message'] = "Failed to reject leave: " . $e->getMessage();
                    header("Location: " . BASE_URL . "/manage-leaves");
                    exit;
                }
            }

            // If not POST, redirect to manage leaves
            header("Location: " . BASE_URL . "/manage-leaves");
            exit;
        }

        /**
         * Generates and displays leave reports.
         */
        public function leaveReport() {
            // Check if the user has director role
            if (!$this->hasDirectorRole()) {
                $_SESSION['error_message'] = 'Access denied.';
                header("Location: " . BASE_URL . "/dashboard");
                exit;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['start_date']) && isset($_GET['end_date'])) {
                $start_date = $_GET['start_date'];
                $end_date = $_GET['end_date'];

                // Validate dates
                if (!$start_date || !$end_date) {
                    $_SESSION['error_message'] = 'Please provide both start and end dates.';
                    header("Location: " . BASE_URL . "/leave-report");
                    exit;
                }

                // Ensure start_date is before end_date
                if (new DateTime($start_date) > new DateTime($end_date)) {
                    $_SESSION['error_message'] = 'Start date must be before end date.';
                    header("Location: " . BASE_URL . "/leave-report");
                    exit;
                }

                try {
                    $report_leaves = $this->leaveModel->generateLeaveReport($start_date, $end_date);
                    include __DIR__ . '/../views/leave-report.php';
                    exit;
                } catch (Exception $e) {
                    $_SESSION['error_message'] = "Failed to generate report: " . $e->getMessage();
                    header("Location: " . BASE_URL . "/leave-report");
                    exit;
                }
            }

            // If not submitting the form, display the report form
            include __DIR__ . '/../views/leave-report.php';
        }

        /**
         * Checks if the current user has a director role.
         *
         * @return bool Returns true if the user is a director.
         */
        private function hasDirectorRole() {
            // Assuming 'role_name' is a field in the 'users' table
            $user = $this->userModel->getUserById($_SESSION['user']['id']);
            return strtolower($user['role_name']) === 'director';
        }
    
}
?>
