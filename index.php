<?php
require_once 'common/config.php'; // Start session + DB connection + redirect() helper

// --- SECURITY CHECK ---
// If the user is not logged in, send them to login.php
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
    exit();
}
// --- END SECURITY CHECK ---

require_once 'common/header.php';
require_once 'common/sidebar.php';
?>

<!-- Main Content -->
<main class="pt-16 pb-24">
    <div class="p-4">
        <!-- Header -->
        <header class="flex justify-between items-center mb-4">
            <button onclick="toggleSidebar()" class="text-gray-800 text-2xl">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="text-2xl font-bold text-indigo-600">Quick Kart</h1>
            <a href="#" class="text-gray-800 text-2xl">
                <i class="fas fa-search"></i>
            </a>
        </header>

        <!-- Categories Section -->
        <section class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Categories</h2>
            <div class="flex space-x-4 overflow-x-auto pb-2 -mx-4 px-4">
                <?php
                $cat_result = $conn->query("SELECT * FROM categories ORDER BY name ASC LIMIT 10");
                if ($cat_result && $cat_result->num_rows > 0) {
                    while ($category = $cat_result->fetch_assoc()) {
                ?>
                <a href="product.php?cat_id=<?= $category['id'] ?>" class="flex-shrink-0 text-center">
                    <div class="w-16 h-16 bg-white rounded-full shadow flex items-center justify-center">
                        <img src="<?= BASE_URL . 'uploads/' . ($category['image'] ?? 'placeholder.png') ?>"
                             alt="<?= htmlspecialchars($category['name']) ?>"
                             class="h-10 w-10 object-contain">
                    </div>
                    <p class="mt-2 text-xs font-medium text-gray-700">
                        <?= htmlspecialchars($category['name']) ?>
                    </p>
                </a>
                <?php
                    }
                } else {
                    echo "<p class='text-gray-500'>No categories found.</p>";
                }
                ?>
            </div>
        </section>

        <!-- Featured Products Section -->
        <section>
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Featured Products</h2>
            <div class="grid grid-cols-2 gap-4">
                <?php
                $prod_result = $conn->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 8");
                if ($prod_result && $prod_result->num_rows > 0) {
                    while ($product = $prod_result->fetch_assoc()) {
                ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <a href="product_detail.php?id=<?= $product['id'] ?>">
                        <img src="<?= BASE_URL . 'uploads/' . ($product['image'] ?? 'placeholder.png') ?>"
                             alt="<?= htmlspecialchars($product['name']) ?>"
                             class="h-32 w-full object-cover">
                    </a>
                    <div class="p-3">
                        <h3 class="text-sm font-semibold text-gray-800 truncate">
                            <?= htmlspecialchars($product['name']) ?>
                        </h3>
                        <p class="text-lg font-bold text-indigo-600 mt-1">
                            ₹<?= number_format($product['price']) ?>
                        </p>
                        <a href="product_detail.php?id=<?= $product['id'] ?>"
                           class="mt-2 w-full text-center bg-indigo-50 text-indigo-600 text-xs font-bold py-2 px-3 rounded-lg hover:bg-indigo-100 transition duration-300 block">
                            View Details
                        </a>
                    </div>
                </div>
                <?php
                    }
                } else {
                     echo "<p class='text-gray-500 col-span-2'>No products found.</p>";
                }
                ?>
            </div>
        </section>
    </div>
</main>

<?php require_once 'common/bottom.php'; ?>