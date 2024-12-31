<?php
// utils/EmailNotifier.php

class EmailNotifier {
    private $from_email;
    private $from_name;

    public function __construct($from_email = 'no-reply@theoaktreegroup.co.za', $from_name = 'Leave Management System') {
        $this->from_email = $from_email;
        $this->from_name = $from_name;
    }

    /**
     * Sends an email.
     *
     * @param string $to        Recipient email address.
     * @param string $subject   Email subject.
     * @param string $body      Email body (HTML allowed).
     * @param string $altBody   Alternative plain text body.
     *
     * @return bool Returns true on success, false on failure.
     */
    public function sendEmail($to, $subject, $body, $altBody = '') {
        // Set content-type headers for HTML email
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

        // Additional headers
        $headers .= "From: " . $this->from_name . " <" . $this->from_email . ">" . "\r\n";
        $headers .= "Reply-To: " . $this->from_email . "\r\n";

        // Attempt to send the email
        if (mail($to, $subject, $body, $headers)) {
            return true;
        } else {
            error_log("Failed to send email to $to with subject '$subject'.");
            return false;
        }
    }
}
?>
