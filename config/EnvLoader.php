<?php
/**
 * EnvLoader
 * A simple class to load environment variables from a .env file.
 */
class EnvLoader {
    public static function load($filePath) {
        if (!file_exists($filePath)) {
            throw new Exception("Environment file not found: $filePath");
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parse key-value pairs
            [$name, $value] = explode('=', $line, 2);

            // Remove surrounding quotes (if any) and trim spaces
            $name = trim($name);
            $value = trim($value, " \t\n\r\0\x0B\"'");

            // Set the environment variable
            $_ENV[$name] = $value;
            putenv("$name=$value");
        }
    }
}
?>
