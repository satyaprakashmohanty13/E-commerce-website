<?php
require_once 'common/header.php';
require_once 'common/sidebar.php';

// Fetch stats
$total_users = $conn->query("SELECT COUNT(id) as count FROM users")->fetch_assoc()['count'];
$total_orders = $conn->query("SELECT COUNT(id) as count FROM orders")->fetch_assoc()['count'];
$total_products = $conn->query("SELECT COUNT(id) as count FROM products")->fetch_assoc()['count'];
$total_revenue = $conn->query("SELECT SUM(total_amount) as sum FROM orders WHERE status = 'Delivered'")->fetch_assoc()['sum'];
$pending_orders = $conn->query("SELECT COUNT(id) as count FROM orders WHERE status = 'Placed'")->fetch_assoc()['count'];
$shipped_orders = $conn->query("SELECT COUNT(id) as count FROM orders WHERE status = 'Dispatched'")->fetch_assoc()['count'];
?>

<h1 class="text-2xl font-bold text-gray-800 mb-6">Dashboard</h1>

<!-- Stats Grid -->
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
    <!-- Total Revenue -->
    <div class="bg-green-500 text-white p-4 rounded-lg shadow-lg">
        <div class="flex items-center">
            <i class="fas fa-rupee-sign text-3xl"></i>
            <div class="ml-4">
                <p class="font-semibold">Total Revenue</p>
                <p class="text-2xl font-bold">₹<?= number_format($total_revenue ?? 0) ?></p>
            </div>
        </div>
    </div>
    <!-- Total Orders -->
    <div class="bg-blue-500 text-white p-4 rounded-lg shadow-lg">
        <div class="flex items-center">
            <i class="fas fa-receipt text-3xl"></i>
            <div class="ml-4">
                <p class="font-semibold">Total Orders</p>
                <p class="text-2xl font-bold"><?= $total_orders ?></p>
            </div>
        </div>
    </div>
    <!-- Total Users -->
    <div class="bg-indigo-500 text-white p-4 rounded-lg shadow-lg">
        <div class="flex items-center">
            <i class="fas fa-users text-3xl"></i>
            <div class="ml-4">
                <p class="font-semibold">Total Users</p>
                <p class="text-2xl font-bold"><?= $total_users ?></p>
            </div>
        </div>
    </div>
    <!-- Total Products -->
    <div class="bg-purple-500 text-white p-4 rounded-lg shadow-lg">
        <div class="flex items-center">
            <i class="fas fa-box text-3xl"></i>
            <div class="ml-4">
                <p class="font-semibold">Active Products</p>
                <p class="text-2xl font-bold"><?= $total_products ?></p>
            </div>
        </div>
    </div>
     <!-- Pending Orders -->
    <div class="bg-yellow-500 text-white p-4 rounded-lg shadow-lg col-span-2 md:col-span-1">
        <div class="flex items-center">
            <i class="fas fa-clock text-3xl"></i>
            <div class="ml-4">
                <p class="font-semibold">Pending Orders</p>
                <p class="text-2xl font-bold"><?= $pending_orders ?></p>
            </div>
        </div>
    </div>
     <!-- Shipped Orders -->
    <div class="bg-cyan-500 text-white p-4 rounded-lg shadow-lg col-span-2 md:col-span-1">
        <div class="flex items-center">
            <i class="fas fa-truck text-3xl"></i>
            <div class="ml-4">
                <p class="font-semibold">Shipments</p>
                <p class="text-2xl font-bold"><?= $shipped_orders ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="mt-8">
    <h2 class="text-xl font-bold text-gray-800 mb-4">Quick Actions</h2>
    <div class="flex flex-wrap gap-4">
        <a href="product.php" class="bg-white text-gray-800 font-semibold py-3 px-5 rounded-lg shadow hover:bg-gray-50 flex items-center">
            <i class="fas fa-plus-circle text-green-500 mr-2"></i> Add New Product
        </a>
        <a href="order.php" class="bg-white text-gray-800 font-semibold py-3 px-5 rounded-lg shadow hover:bg-gray-50 flex items-center">
            <i class="fas fa-list-alt text-blue-500 mr-2"></i> Manage Orders
        </a>
        <a href="user.php" class="bg-white text-gray-800 font-semibold py-3 px-5 rounded-lg shadow hover:bg-gray-50 flex items-center">
             <i class="fas fa-user-edit text-indigo-500 mr-2"></i> Manage Users
        </a>
    </div>
</div>

<?php require_once 'common/bottom.php'; ?>