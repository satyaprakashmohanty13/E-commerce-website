<?php
require_once 'common/config.php';

// Unset all of the session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect to login page
redirect('login.php');
?>