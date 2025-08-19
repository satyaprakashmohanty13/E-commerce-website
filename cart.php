<?php
require_once 'common/config.php';

// --- AJAX HANDLER ---
// This part handles asynchronous updates (delete, quantity change)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Content-Type: application/json');
    $response = ['success' => false];
    $action = $_POST['action'] ?? '';
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

    if ($action === 'update' && $product_id > 0) {
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        if ($quantity > 0) {
            $_SESSION['cart'][$product_id] = $quantity;
            $response['success'] = true;
        }
    } elseif ($action === 'delete' && $product_id > 0) {
        unset($_SESSION['cart'][$product_id]);
        $response['success'] = true;
    }

    // Recalculate total for the AJAX response
    $total = 0;
    if (!empty($_SESSION['cart'])) {
        $product_ids_str = implode(',', array_map('intval', array_keys($_SESSION['cart'])));
        $sql = "SELECT id, price FROM products WHERE id IN ($product_ids_str)";
        $result = $conn->query($sql);
        $products_data = [];
        while($row = $result->fetch_assoc()) {
            $products_data[$row['id']] = $row;
        }
        foreach ($_SESSION['cart'] as $pid => $qty) {
            if (isset($products_data[$pid])) {
                $total += $products_data[$pid]['price'] * $qty;
            }
        }
    }
    $response['total'] = '₹' . number_format($total);
    $response['cart_count'] = count($_SESSION['cart'] ?? []);

    echo json_encode($response);
    exit();
}
// --- END AJAX HANDLER ---

// --- NORMAL PAGE LOAD LOGIC ---
// This part runs when you navigate to the cart page directly

// First, include the header which starts the HTML document
require_once 'common/header.php';

// Now, check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
    exit(); // Always exit after a redirect
}

// Now include the visual sidebar
require_once 'common/sidebar.php';

// Fetch all cart items from the database for display
$cart_items = [];
$total_amount = 0;
if (!empty($_SESSION['cart'])) {
    $product_ids_str = implode(',', array_map('intval', array_keys($_SESSION['cart'])));
    $sql = "SELECT id, name, price, image, stock FROM products WHERE id IN ($product_ids_str)";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $pid = $row['id'];
            $row['quantity'] = $_SESSION['cart'][$pid];
            $cart_items[] = $row;
            $total_amount += $row['price'] * $row['quantity'];
        }
    }
}
?>

<!-- Main Content -->
<main class="pt-16 pb-24">
    <!-- Header -->
    <header class="fixed top-0 left-0 right-0 bg-white shadow-sm z-10 flex items-center p-4">
        <a href="index.php" class="text-gray-800 text-xl"><i class="fas fa-arrow-left"></i></a>
        <h1 class="text-xl font-bold text-gray-800 mx-auto">My Cart</h1>
        <div class="w-6"></div> <!-- Placeholder for alignment -->
    </header>

    <div class="p-4" id="cart-container">
        <?php if (empty($cart_items)): ?>
            <div class="text-center py-20">
                <i class="fas fa-shopping-cart text-5xl text-gray-300 mb-4"></i>
                <h2 class="text-xl font-semibold text-gray-700">Your Cart is Empty</h2>
                <p class="text-gray-500 mt-2">Looks like you haven't added anything yet.</p>
                <a href="index.php" class="mt-6 inline-block bg-indigo-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-indigo-700">Shop Now</a>
            </div>
        <?php else: ?>
            <div id="cart-items-list">
                <?php foreach ($cart_items as $item): ?>
                <div class="flex items-center bg-white p-3 rounded-lg shadow-sm mb-3" id="item-<?= $item['id'] ?>">
                    <img src="<?= BASE_URL . 'uploads/' . ($item['image'] ?? 'placeholder.png') ?>" class="w-20 h-20 object-cover rounded-md">
                    <div class="flex-grow ml-3">
                        <h3 class="text-sm font-semibold text-gray-800"><?= htmlspecialchars($item['name']) ?></h3>
                        <p class="text-lg font-bold text-indigo-600">₹<?= number_format($item['price']) ?></p>
                        <div class="flex items-center mt-2">
                            <button onclick="updateCart(<?= $item['id'] ?>, -1)" class="w-6 h-6 rounded-full border">-</button>
                            <input type="text" id="qty-<?= $item['id'] ?>" value="<?= $item['quantity'] ?>" readonly class="w-10 text-center border-none">
                            <button onclick="updateCart(<?= $item['id'] ?>, 1)" class="w-6 h-6 rounded-full border">+</button>
                        </div>
                    </div>
                    <button onclick="deleteFromCart(<?= $item['id'] ?>)" class="text-red-500 hover:text-red-700 ml-2">
                        <i class="fas fa-trash-alt text-lg"></i>
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Fixed Checkout Footer -->
<?php if (!empty($cart_items)): ?>
<footer id="cart-footer" class="fixed bottom-[56px] left-0 right-0 bg-white p-4 border-t" style="box-shadow: 0 -2px 10px rgba(0,0,0,0.1);">
    <div class="flex justify-between items-center mb-3">
        <span class="text-gray-600 font-medium">Total Price:</span>
        <span class="text-2xl font-bold text-gray-900" id="total-price">₹<?= number_format($total_amount) ?></span>
    </div>
    <a href="checkout.php" class="block w-full text-center bg-indigo-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-indigo-700 transition">
        Proceed to Checkout
    </a>
</footer>
<?php endif; ?>

<script>
    // The JavaScript for this page remains the same and will now work correctly.
    async function handleCartAction(action, productId, quantity = null) { /* ... */ }
    function updateCart(productId, change) { /* ... */ }
    function deleteFromCart(productId) { /* ... */ }
</script>

<?php require_once 'common/bottom.php'; ?>