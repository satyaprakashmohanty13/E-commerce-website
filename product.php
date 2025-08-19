<?php require_once 'common/header.php'; ?>
<?php require_once 'common/sidebar.php'; ?>

<?php
$cat_id = isset($_GET['cat_id']) ? (int)$_GET['cat_id'] : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'new';

$where_clause = "";
if ($cat_id > 0) {
    $where_clause = "WHERE cat_id = $cat_id";
    $stmt_cat = $conn->prepare("SELECT name FROM categories WHERE id = ?");
    $stmt_cat->bind_param("i", $cat_id);
    $stmt_cat->execute();
    $cat_name_result = $stmt_cat->get_result()->fetch_assoc();
    $page_title = $cat_name_result ? htmlspecialchars($cat_name_result['name']) : 'Products';
} else {
    $page_title = 'All Products';
}

$order_by_clause = "ORDER BY created_at DESC";
if ($sort === 'price_asc') {
    $order_by_clause = "ORDER BY price ASC";
} elseif ($sort === 'price_desc') {
    $order_by_clause = "ORDER BY price DESC";
}
?>

<!-- Main Content -->
<main class="pt-16 pb-24">
    <div class="p-4">
        <!-- Header -->
        <header class="flex items-center mb-4">
            <a href="index.php" class="text-gray-800 text-xl mr-4"><i class="fas fa-arrow-left"></i></a>
            <h1 class="text-xl font-bold text-gray-800"><?= $page_title ?></h1>
        </header>

        <!-- Filters -->
        <div class="flex justify-between items-center mb-4 bg-white p-2 rounded-lg shadow-sm">
            <span class="text-sm font-medium text-gray-600">Sort By:</span>
            <div>
                <select id="sort-filter" class="text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="new" <?= $sort === 'new' ? 'selected' : '' ?>>Newest</option>
                    <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                    <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                </select>
            </div>
        </div>

        <!-- Product Grid -->
        <div class="grid grid-cols-2 gap-4">
            <?php
            $sql = "SELECT * FROM products $where_clause $order_by_clause";
            $result = $conn->query($sql);
            if ($result && $result->num_rows > 0) {
                while ($product = $result->fetch_assoc()) {
            ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <a href="product_detail.php?id=<?= $product['id'] ?>">
                    <img src="<?= BASE_URL . 'uploads/' . ($product['image'] ?? 'placeholder.png') ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="h-32 w-full object-cover">
                </a>
                <div class="p-3">
                    <h3 class="text-sm font-semibold text-gray-800 truncate"><?= htmlspecialchars($product['name']) ?></h3>
                    <p class="text-lg font-bold text-indigo-600 mt-1">₹<?= number_format($product['price']) ?></p>
                    <a href="product_detail.php?id=<?= $product['id'] ?>" class="mt-2 w-full text-center bg-indigo-50 text-indigo-600 text-xs font-bold py-2 px-3 rounded-lg hover:bg-indigo-100 transition duration-300 block">
                        View Details
                    </a>
                </div>
            </div>
            <?php
                }
            } else {
                echo "<p class='text-gray-500 col-span-2 text-center mt-8'>No products found in this category.</p>";
            }
            ?>
        </div>
    </div>
</main>

<script>
document.getElementById('sort-filter').addEventListener('change', function() {
    const sortBy = this.value;
    const url = new URL(window.location.href);
    url.searchParams.set('sort', sortBy);
    window.location.href = url.toString();
});
</script>

<?php require_once 'common/bottom.php'; ?>