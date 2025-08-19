<?php
// THIS IS THE FINAL, CORRECTED VERSION FOR THIS FILE
// This line is ESSENTIAL. It ensures the config is loaded for every admin page.
require_once __DIR__ . '/../../common/config.php';

// Security Check: Now this will work correctly because BASE_URL is defined.
if (!isset($_SESSION['admin_id']) && basename($_SERVER['PHP_SELF']) !== 'login.php') {
    header('Location: ' . BASE_URL . 'admin/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-g">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Quick Kart - Admin Panel</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div id="app-container" class="relative min-h-screen lg:flex">
        <!-- Overlay for sidebar on mobile -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden" onclick="toggleSidebar()"></div>

        <!-- Alert & Loader Containers -->
        <div id="alert-container"></div>
        <div id="loader-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 items-center justify-center">
            <div class="loader-dots flex space-x-2">
                <div class="w-4 h-4 bg-white rounded-full"></div>
                <div class="w-4 h-4 bg-white rounded-full"></div>
                <div class="w-4 h-4 bg-white rounded-full"></div>
            </div>
        </div>