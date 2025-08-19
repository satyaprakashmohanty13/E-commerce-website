<!-- Sidebar -->
<aside id="sidebar" class="fixed top-0 left-0 h-full w-64 bg-gray-800 text-white shadow-lg transform -translate-x-full transition-transform duration-300 ease-in-out z-40 lg:translate-x-0 lg:relative lg:flex-shrink-0">
    <div class="p-4 border-b border-gray-700">
        <h2 class="text-2xl font-bold text-center">Quick Kart Admin</h2>
    </div>
    <nav class="mt-4">
        <a href="<?= BASE_URL ?>admin/index.php" class="flex items-center px-4 py-3 hover:bg-gray-700">
            <i class="fas fa-tachometer-alt w-6 text-center"></i><span class="ml-3">Dashboard</span>
        </a>
        <a href="<?= BASE_URL ?>admin/category.php" class="flex items-center px-4 py-3 hover:bg-gray-700">
            <i class="fas fa-tags w-6 text-center"></i><span class="ml-3">Categories</span>
        </a>
        <a href="<?= BASE_URL ?>admin/product.php" class="flex items-center px-4 py-3 hover:bg-gray-700">
            <i class="fas fa-box w-6 text-center"></i><span class="ml-3">Products</span>
        </a>
        <a href="<?= BASE_URL ?>admin/order.php" class="flex items-center px-4 py-3 hover:bg-gray-700">
            <i class="fas fa-receipt w-6 text-center"></i><span class="ml-3">Orders</span>
        </a>
        <a href="<?= BASE_URL ?>admin/user.php" class="flex items-center px-4 py-3 hover:bg-gray-700">
            <i class="fas fa-users w-6 text-center"></i><span class="ml-3">Users</span>
        </a>
        <a href="<?= BASE_URL ?>admin/setting.php" class="flex items-center px-4 py-3 hover:bg-gray-700">
            <i class="fas fa-cog w-6 text-center"></i><span class="ml-3">Settings</span>
        </a>
        <a href="<?= BASE_URL ?>admin/logout.php" class="flex items-center px-4 py-3 text-red-400 hover:bg-gray-700">
            <i class="fas fa-sign-out-alt w-6 text-center"></i><span class="ml-3">Logout</span>
        </a>
    </nav>
</aside>

<div class="flex-1 flex flex-col">
    <!-- Top Header for Mobile -->
    <header class="bg-white shadow-md p-4 flex justify-between items-center lg:hidden">
        <button onclick="toggleSidebar()" class="text-gray-800 text-2xl">
            <i class="fas fa-bars"></i>
        </button>
        <h1 class="text-xl font-bold text-indigo-600">Admin Panel</h1>
        <div></div>
    </header>

    <!-- Main content area -->
    <main class="flex-1 p-4 lg:p-6">

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    }
</script>