<?php
// ADD THESE LINES FOR DEBUGGING
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Start session
session_start();

// Database credentials
define('DB_HOST', 'sql204.infinityfree.com');
define('DB_USER', 'if0_39737490');
define('DB_PASS', '4KWRdanAK7DCZz');
define('DB_NAME', 'if0_39737490_satya');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Base URL for the application
define('BASE_URL', 'http://localhost:8080/');

// Set timezone
date_default_timezone_set('Asia/Kolkata');

// Function to safely redirect
function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit();
}
?>