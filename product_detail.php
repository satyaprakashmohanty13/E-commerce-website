<?php
// ================= FIX #1: AJAX HANDLER MOVED TO THE TOP =================
// This block must be BEFORE any HTML output (like require_once 'common/header.php')
require_once 'common/config.php'; // Config is safe as it doesn't output HTML

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Please login to add items to cart.', 'redirect' => BASE_URL . 'login.php']);
        exit();
    }

    $pid = $_POST['product_id'];
    $qty = $_POST['quantity'];

    if (isset($_SESSION['cart'][$pid])) {
        $_SESSION['cart'][$pid] += $qty;
    } else {
        $_SESSION['cart'][$pid] = $qty;
    }

    echo json_encode(['success' => true, 'message' => 'Product added to cart!', 'cart_count' => count($_SESSION['cart'])]);
    exit(); // Crucially, we exit right after sending the JSON response.
}
// ================= END OF AJAX HANDLER =================

// Now we can proceed with rendering the HTML page
require_once 'common/header.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($product_id === 0) {
    echo "Product not found.";
    exit();
}

$stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.cat_id = c.id WHERE p.id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    echo "Product not found.";
    exit();
}
?>

<!-- Main content now has standard bottom padding (pb-24) -->
<main class="pt-16 pb-24 bg-white">
    <!-- Header -->
    <header class="fixed top-0 left-0 right-0 bg-white shadow-sm z-10 flex items-center p-4">
        <a href="javascript:history.back()" class="text-gray-800 text-xl"><i class="fas fa-arrow-left"></i></a>
        <h1 class="text-xl font-bold text-gray-800 mx-auto">Product Details</h1>
    </header>

    <!-- Product Image Slider -->
    <section class="h-64 bg-gray-200 flex items-center justify-center">
         <img src="<?= BASE_URL . 'uploads/' . ($product['image'] ?? 'placeholder.png') ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="h-full w-full object-contain">
    </section>

    <!-- Product Info -->
    <section class="p-4">
        <span class="text-xs font-semibold text-indigo-600 bg-indigo-100 py-1 px-2 rounded-full"><?= htmlspecialchars($product['category_name']) ?></span>
        <h2 class="text-2xl font-bold text-gray-800 mt-2"><?= htmlspecialchars($product['name']) ?></h2>

        <div class="flex justify-between items-center mt-3">
            <p class="text-3xl font-extrabold text-gray-900">₹<?= number_format($product['price']) ?></p>
            <span class="text-sm font-medium <?= $product['stock'] > 0 ? 'text-green-600' : 'text-red-600' ?>">
                <?= $product['stock'] > 0 ? 'In Stock' : 'Out of Stock' ?>
            </span>
        </div>

        <div class="my-6">
            <h3 class="text-md font-semibold text-gray-800 mb-2">Description</h3>
            <p class="text-gray-600 text-sm leading-relaxed"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
        </div>

        <!-- ================= FIX #2: ADD TO CART SECTION MOVED HERE ================= -->
        <div class="mt-6 p-4 border-t">
            <div class="flex items-center justify-between mb-4">
                <span class="font-semibold text-gray-700">Quantity:</span>
                <div class="flex items-center space-x-3">
                    <button onclick="updateQty(-1)" class="w-8 h-8 rounded-full border border-gray-300 text-lg font-bold flex items-center justify-center">-</button>
                    <input id="quantity-input" type="text" value="1" readonly class="w-12 text-center font-semibold text-lg border-none bg-transparent">
                    <button onclick="updateQty(1)" class="w-8 h-8 rounded-full border border-gray-300 text-lg font-bold flex items-center justify-center">+</button>
                </div>
            </div>

            <button
                onclick="addToCart(<?= $product['id'] ?>)"
                class="w-full font-bold py-3 px-6 rounded-lg transition duration-300 flex items-center justify-center
                       <?= $product['stock'] > 0
                            ? 'bg-indigo-600 text-white hover:bg-indigo-700'
                            : 'bg-gray-300 text-gray-500 cursor-not-allowed' ?>"
                <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>

                <i class="fas fa-shopping-cart mr-2"></i>
                <span><?= $product['stock'] > 0 ? 'Add to Cart' : 'Out of Stock' ?></span>
            </button>
        </div>
        <!-- ================= END OF MOVED SECTION ================= -->

    </section>

    <!-- Related Products -->
    <section class="p-4 bg-gray-50 mt-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-3">Related Products</h3>
        <div class="grid grid-cols-2 gap-4">
             <?php
            $related_stmt = $conn->prepare("SELECT * FROM products WHERE cat_id = ? AND id != ? LIMIT 4");
            $related_stmt->bind_param("ii", $product['cat_id'], $product_id);
            $related_stmt->execute();
            $related_result = $related_stmt->get_result();
            while ($related_product = $related_result->fetch_assoc()) {
            ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <a href="product_detail.php?id=<?= $related_product['id'] ?>">
                    <img src="<?= BASE_URL . 'uploads/' . ($related_product['image'] ?? 'placeholder.png') ?>" alt="<?= htmlspecialchars($related_product['name']) ?>" class="h-24 w-full object-cover">
                </a>
                <div class="p-2">
                    <h4 class="text-xs font-semibold text-gray-800 truncate"><?= htmlspecialchars($related_product['name']) ?></h4>
                    <p class="text-sm font-bold text-indigo-600">₹<?= number_format($related_product['price']) ?></p>
                </div>
            </div>
            <?php } ?>
        </div>
    </section>
</main>

<script>
    const qtyInput = document.getElementById('quantity-input');
    const maxStock = <?= (int)$product['stock'] ?>;

    function updateQty(change) {
        let currentQty = parseInt(qtyInput.value);
        let newQty = currentQty + change;
        if (newQty >= 1 && (maxStock === 0 || newQty <= maxStock)) {
            qtyInput.value = newQty;
        }
    }

    async function addToCart(productId) {
        const quantity = parseInt(qtyInput.value);
        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('quantity', quantity);

        const data = await window.sendRequest('product_detail.php?id=' + productId, {
            method: 'POST',
            body: formData
        });

        if (data.success) {
            showAlert('success', data.message);
            // Reload to update the cart count in the bottom nav
            setTimeout(() => location.reload(), 1000);
        } else {
            if(data.redirect){
                 showAlert('error', data.message);
                 setTimeout(() => window.location.href = data.redirect, 1500);
            } else {
                 showAlert('error', data.message || 'Could not add to cart.');
            }
        }
    }
</script>

<?php require_once 'common/bottom.php'; ?>