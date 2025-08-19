<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Quick Kart</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body class="bg-gray-50 font-sans antialiased overflow-x-hidden">
    <!-- Main container with relative positioning for sidebar -->
    <div id="app-container" class="relative min-h-screen">
        <!-- Overlay for sidebar -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden" onclick="toggleSidebar()"></div>

        <!-- Alert & Loader Containers -->
        <div id="alert-container"></div>
        <div id="loader-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 items-center justify-center">
            <div class="loader-dots flex space-x-2">
                <div class="w-4 h-4 bg-white rounded-full"></div>
                <div class="w-4 h-4 bg-white rounded-full"></div>
                <div class="w-4 h-4 bg-white rounded-full"></div>
            </div>
        </div>