<?php
// scripts/upcoming-leaves.php

require_once __DIR__ . '/../models/LeaveModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../libraries/Logger.php'; // Assuming you have a Logger class
require_once __DIR__ . '/../utils/DiscordNotifier.php'; // For Discord notifications
require_once __DIR__ . '/../utils/EmailNotifier.php';    // For email notifications

$leaveModel = new LeaveModel();
$userModel = new UserModel();
$logger = Logger::getInstance();
$discordWebhookUrl = "YOUR_DISCORD_WEBHOOK_URL_HERE"; // Replace with your webhook URL
$emailNotifier = new EmailNotifier();
$director_email = "director@example.com"; // Replace with actual director email

$today = new DateTime();
$notify_days = 3; // Number of days ahead to notify

$sql = "SELECT * FROM leaves 
        WHERE status = 'approved' 
          AND start_date BETWEEN :today AND :notify_until";
$stmt = $leaveModel->db->prepare($sql);
$notify_until = (clone $today)->modify("+$notify_days days")->format('Y-m-d');
$stmt->execute([
    'today'       => $today->format('Y-m-d'),
    'notify_until'=> $notify_until
]);

$upcoming_leaves = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($upcoming_leaves as $leave) {
    $message = "**Upcoming Leave:**\n**User:** " . $leave['username'] . "\n**Type:** " . ucfirst($leave['leave_type']) . "\n**From:** " . $leave['start_date'] . "\n**To:** " . $leave['end_date'];

    // Send Discord Notification
    DiscordNotifier::sendMessage($discordWebhookUrl, $message);

    // Send Email Notification to Director
    $subject = "Upcoming Leave Notification for " . $leave['username'];
    $body = "<p>Dear Director,</p>
             <p><strong>" . $leave['username'] . "</strong> has an upcoming <strong>" . ucfirst($leave['leave_type']) . "</strong> leave.</p>
             <p><strong>Duration:</strong> " . $leave['total_days'] . " days<br>
                <strong>From:</strong> " . $leave['start_date'] . "<br>
                <strong>To:</strong> " . $leave['end_date'] . "</p>
             <p>Please make necessary arrangements.</p>
             <p>Best Regards,<br>Leave Management System</p>";
    $altBody = "Dear Director,\n\n" . $leave['username'] . " has an upcoming " . ucfirst($leave['leave_type']) . " leave.\n\nDuration: " . $leave['total_days'] . " days\nFrom: " . $leave['start_date'] . "\nTo: " . $leave['end_date'] . "\n\nPlease make necessary arrangements.\n\nBest Regards,\nLeave Management System";

    $emailNotifier->sendEmail($director_email, $subject, $body, $altBody);

    // Log the notification
    $logger->log(
        'System',
        'System',
        'System',
        'upcoming-leaves.php',
        'notify_upcoming_leave',
        "Notified about upcoming leave for user: " . $leave['username'],
        'INFO'
    );
}

echo "Upcoming leave notifications sent successfully.\n";
?>
