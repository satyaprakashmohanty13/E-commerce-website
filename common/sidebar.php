<!-- Sidebar -->
<aside id="sidebar" class="fixed top-0 left-0 h-full w-64 bg-white shadow-lg transform -translate-x-full transition-transform duration-300 ease-in-out z-40">
    <div class="p-4 border-b">
        <h2 class="text-2xl font-bold text-indigo-600">Quick Kart</h2>
    </div>
    <nav class="mt-4">
        <a href="<?= BASE_URL ?>index.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100">
            <i class="fas fa-home w-6 text-center"></i>
            <span class="ml-3">Home</span>
        </a>
        <a href="<?= BASE_URL ?>profile.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100">
            <i class="fas fa-user w-6 text-center"></i>
            <span class="ml-3">My Profile</span>
        </a>
        <a href="<?= BASE_URL ?>order.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100">
            <i class="fas fa-box w-6 text-center"></i>
            <span class="ml-3">My Orders</span>
        </a>
        <a href="<?= BASE_URL ?>cart.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100">
            <i class="fas fa-shopping-cart w-6 text-center"></i>
            <span class="ml-3">My Cart</span>
        </a>
        <?php if (isset($_SESSION['user_id'])): ?>
        <a href="<?= BASE_URL ?>logout.php" class="flex items-center px-4 py-3 text-red-500 hover:bg-gray-100">
            <i class="fas fa-sign-out-alt w-6 text-center"></i>
            <span class="ml-3">Logout</span>
        </a>
        <?php else: ?>
        <a href="<?= BASE_URL ?>login.php" class="flex items-center px-4 py-3 text-green-500 hover:bg-gray-100">
            <i class="fas fa-sign-in-alt w-6 text-center"></i>
            <span class="ml-3">Login</span>
        </a>
        <?php endif; ?>
    </nav>
</aside>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    }
</script>