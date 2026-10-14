<?php
// Copy this file to config.php and fill in your own values.
// config.php is listed in .gitignore on purpose: real passwords must never be committed.

$host     = 'localhost';          // 'localhost' on XAMPP and on cPanel
$user     = 'DATABASE_USER';      // on cPanel this is prefixed with your account name
$password = 'DATABASE_PASSWORD';
$database = 'DATABASE_NAME';      // on cPanel this is prefixed too
$port     = 3306;

$conn = mysqli_connect($host, $user, $password, $database, $port);

if (!$conn) {
    // Log the real error server-side; never echo mysqli_connect_error() to the browser.
    error_log('DB connection failed: ' . mysqli_connect_error());
    http_response_code(500);
    die('Something went wrong. Please try again shortly.');
}

mysqli_set_charset($conn, 'utf8mb4');
