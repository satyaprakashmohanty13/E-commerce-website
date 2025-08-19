<?php
require_once __DIR__ . '/../common/config.php';

// Unset all of the session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect to login page
header('Location: ' . BASE_URL . 'admin/login.php');
exit();
?>