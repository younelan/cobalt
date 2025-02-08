<?php
// Prevent direct access to this file
if (!defined('ABSPATH') && basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Direct access not permitted');
}

// Database configuration
$host = 'localhost';     // Database host
$user = 'root'; // Database username
$password = ''; // Database password

// Error reporting settings
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Custom error handler
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    error_log("Error [$errno]: $errstr in $errfile:$errline");
    return true;
}
set_error_handler("customErrorHandler");

// Set default timezone
date_default_timezone_set('UTC');

// Database connection charset
$charset = 'utf8mb4';

// PDO options
$pdoOptions = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

// Define base path
define('ABSPATH', dirname(__FILE__) . '/');
