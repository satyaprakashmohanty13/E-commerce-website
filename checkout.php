<?php
require_once 'common/config.php';

// Must be logged in and have items in cart
if (!isset($_SESSION['user_id']) || empty($_SESSION['cart'])) {
    redirect('cart.php');
}

// Fetch user data for pre-filling the form
$user_id = $_SESSION['user_id'];
$user_stmt = $conn->prepare("SELECT name, phone, address FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

// Calculate total amount from cart
$total_amount = 0;
$product_ids = implode(',', array_keys($_SESSION['cart']));
$sql = "SELECT id, price, stock FROM products WHERE id IN ($product_ids)";
$result = $conn->query($sql);
$products_data = [];
while ($row = $result->fetch_assoc()) {
    $products_data[$row['id']] = $row;
}
foreach ($_SESSION['cart'] as $pid => $qty) {
    if (isset($products_data[$pid])) {
        $total_amount += $products_data[$pid]['price'] * $qty;
    }
}

// Handle Order Placement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);

    if (empty($name) || empty($address) || empty($phone)) {
        $error = "Please fill in all shipping details.";
    } else {
        // Start transaction
        $conn->begin_transaction();
        try {
            // 1. Insert into orders table
            $stmt_order = $conn->prepare("INSERT INTO orders (user_id, name, address, phone, total_amount, status) VALUES (?, ?, ?, ?, ?, 'Placed')");
            $stmt_order->bind_param("isssd", $user_id, $name, $address, $phone, $total_amount);
            $stmt_order->execute();
            $order_id = $stmt_order->insert_id;

            // 2. Insert into order_items and update stock
            $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt_stock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

            foreach ($_SESSION['cart'] as $pid => $qty) {
                $price = $products_data[$pid]['price'];
                $stmt_item->bind_param("iiid", $order_id, $pid, $qty, $price);
                $stmt_item->execute();

                $stmt_stock->bind_param("ii", $qty, $pid);
                $stmt_stock->execute();
            }

            // If all good, commit
            $conn->commit();

            // Clear the cart
            unset($_SESSION['cart']);

            // Redirect to order success page
            redirect('order.php?success=true');

        } catch (mysqli_sql_exception $exception) {
            $conn->rollback();
            $error = "Order placement failed. Please try again.";
        }
    }
}

require_once 'common/header.php';
?>

<!-- Main Content -->
<main class="pt-16 pb-24">
    <!-- Header -->
    <header class="fixed top-0 left-0 right-0 bg-white shadow-sm z-10 flex items-center p-4">
        <a href="cart.php" class="text-gray-800 text-xl"><i class="fas fa-arrow-left"></i></a>
        <h1 class="text-xl font-bold text-gray-800 mx-auto">Checkout</h1>
        <div class="w-6"></div> <!-- Placeholder for alignment -->
    </header>

    <div class="p-4">
        <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?= $error ?></span>
        </div>
        <?php endif; ?>

        <form method="POST" action="checkout.php">
            <!-- Shipping Details -->
            <div class="bg-white p-4 rounded-lg shadow-sm mb-4">
                <h2 class="text-lg font-semibold mb-3">Shipping Address</h2>
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                    <input type="text" name="name" id="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required>
                </div>
                <div class="mb-4">
                    <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                    <textarea name="address" id="address" rows="3" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                </div>
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                    <input type="tel" name="phone" id="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required>
                </div>
            </div>

            <!-- Payment Method -->
            <div class="bg-white p-4 rounded-lg shadow-sm">
                <h2 class="text-lg font-semibold mb-3">Payment Method</h2>
                <div class="flex items-center p-3 border border-indigo-500 rounded-lg bg-indigo-50">
                    <i class="fas fa-money-bill-wave text-indigo-600 text-xl"></i>
                    <span class="ml-3 font-semibold text-gray-800">Cash on Delivery (COD)</span>
                </div>
            </div>

            <!-- Fixed Place Order Footer -->
            <footer class="fixed bottom-0 left-0 right-0 bg-white p-4 border-t">
                <div class="flex justify-between items-center mb-3">
                    <span class="text-gray-600 font-medium">Total Payable:</span>
                    <span class="text-2xl font-bold text-gray-900">₹<?= number_format($total_amount) ?></span>
                </div>
                <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-3 rounded-lg hover:bg-indigo-700 transition">
                    Place Order
                </button>
            </footer>
        </form>
    </div>
</main>

<?php require_once 'common/bottom.php'; ?>