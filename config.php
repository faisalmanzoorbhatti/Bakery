<?php
/**
 * Application Configuration
 * Online Bay Bakery
 */

// Simple .env parser to load environment variables without heavy dependencies
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (str_contains($line, '=')) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            // Remove wrapping quotes if present
            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv("$name=$value");
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

// Helper to get environment variable with fallback
if (!function_exists('env')) {
    function env($key, $default = null) {
        $val = getenv($key);
        if ($val === false) {
            $val = $_ENV[$key] ?? $_SERVER[$key] ?? $default;
        }
        return $val;
    }
}

// Database Credentials
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));
define('DB_NAME', env('DB_NAME', 'online_bay_bakery'));

// Base URL
define('APP_URL', env('APP_URL', 'http://localhost/online_bay_bakery'));

// SMTP Mail Settings (PHPMailer)
define('SMTP_HOST', env('SMTP_HOST', 'smtp.gmail.com'));
define('SMTP_PORT', (int)env('SMTP_PORT', 587));
define('SMTP_USER', env('SMTP_USER', ''));
define('SMTP_PASS', env('SMTP_PASS', ''));
define('SMTP_FROM_EMAIL', env('SMTP_FROM_EMAIL', 'no-reply@onlinebaybakery.com'));
define('SMTP_FROM_NAME', env('SMTP_FROM_NAME', 'Online Bay Bakery'));

// Twilio SMS Settings
define('TWILIO_ACCOUNT_SID', env('TWILIO_ACCOUNT_SID', ''));
define('TWILIO_AUTH_TOKEN', env('TWILIO_AUTH_TOKEN', ''));
define('TWILIO_PHONE_NUMBER', env('TWILIO_PHONE_NUMBER', ''));
define('TWILIO_ADMIN_PHONE', env('TWILIO_ADMIN_PHONE', ''));

/**
 * Helper to get a database connection
 * @return mysqli
 */
function getDbConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die("<div style='font-family:sans-serif;padding:20px;background:#ffebee;color:#c62828;border-radius:8px;max-width:600px;margin:40px auto;border:1px solid #ef9a9a;'><h3>Database Connection Error</h3><p>" . htmlspecialchars($conn->connect_error) . "</p><p><small>Tip: Ensure MySQL is running in your XAMPP Control Panel.</small></p></div>");
        }
        return $conn;
    } catch (mysqli_sql_exception $e) {
        die("<div style='font-family:sans-serif;padding:20px;background:#ffebee;color:#c62828;border-radius:8px;max-width:600px;margin:40px auto;border:1px solid #ef9a9a;'><h3>Database Connection Failed</h3><p>" . htmlspecialchars($e->getMessage()) . "</p><p><small>Tip: Start MySQL in XAMPP or verify database credentials in <code>.env</code>.</small></p></div>");
    }
}
