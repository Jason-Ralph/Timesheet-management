<?php
// scripts/accrue-leave.php

require_once __DIR__ . '/../models/LeaveModel.php';
require_once __DIR__ . '/../libraries/Logger.php'; // Assuming you have a Logger class

$leaveModel = new LeaveModel();
$logger = Logger::getInstance();

// Define the leave type to accrue
$accrue_type = 'annual';
$accrue_days = 1.25;

try {
    // Accrue leave for all users
    $leaveModel->accrueLeave($accrue_type, $accrue_days);

    // Log the accrual
    $logger->log(
        'System',                     // Username (system)
        'System',                     // Title
        'System',                     // Department
        'accrue-leave.php',           // Page or Action
        'accrue_leave',
        "Accrued $accrue_days days of $accrue_type leave for all users.",
        'INFO'
    );

    echo "Leave accrual completed successfully.\n";
} catch (Exception $e) {
    $logger->log(
        'System',
        'System',
        'System',
        'accrue-leave.php',
        'accrue_leave_error',
        "Failed to accrue leave: " . $e->getMessage(),
        'ERROR'
    );
    echo "Leave accrual failed: " . $e->getMessage() . "\n";
}
?>
