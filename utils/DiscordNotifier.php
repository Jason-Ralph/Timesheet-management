<?php
// utils/DiscordNotifier.php

class DiscordNotifier {
    /**
     * Sends a message to a Discord channel via webhook.
     *
     * @param string $webhook_url The Discord webhook URL.
     * @param string $message The message content.
     * @param string $username (Optional) The username to display.
     * @param string $avatar_url (Optional) The avatar image URL.
     *
     * @return bool Returns true on success, false on failure.
     */
    public static function sendMessage($webhook_url, $message, $username = "Leave Bot", $avatar_url = "") {
        $json_data = json_encode([
            "content"    => $message,
            "username"   => $username,
            "avatar_url" => $avatar_url
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

        $ch = curl_init($webhook_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Set timeout to prevent hanging

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            error_log("Discord Notifier cURL Error: $error");
            return false;
        }

        if ($http_code >= 200 && $http_code < 300) {
            return true;
        } else {
            error_log("Discord Notifier HTTP Error: $http_code Response: $response");
            return false;
        }
    }
}
?>
