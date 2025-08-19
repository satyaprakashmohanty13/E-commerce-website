<?php
require_once 'common/header.php';

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($order_id === 0) {
    echo "<p>Invalid Order ID.</p>";
    exit();
}

// --- AJAX STATUS UPDATE HANDLER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Content-Type: application/json');
    $new_status = $_POST['status'];
    $oid = (int)$_POST['order_id'];

    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $oid);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Order status updated successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update status.']);
    }
    exit();
}

// Fetch order details
$sql = "SELECT o.*, u.email as user_email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

// Fetch order items
$items_sql = "SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?";
$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$order_items = $items_stmt->get_result();

require_once 'common/sidebar.php';
?>

<a href="order.php" class="text-indigo-600 hover:underline mb-4 inline-block"><i class="fas fa-arrow-left mr-2"></i>Back to Orders</a>
<h1 class="text-2xl font-bold text-gray-800 mb-6">Order Details #<?= $order_id ?></h1>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column -->
    <div class="lg:col-span-2">
        <!-- Order Items -->
        <div class="bg-white p-4 rounded-lg shadow-md">
            <h2 class="text-lg font-semibold border-b pb-2 mb-3">Order Items (<?= $order_items->num_rows ?>)</h2>
            <div class="space-y-3">
            <?php while ($item = $order_items->fetch_assoc()): ?>
                <div class="flex items-center">
                    <img src="<?= BASE_URL . 'uploads/' . ($item['image'] ?? 'placeholder.png') ?>" class="w-16 h-16 object-cover rounded-md">
                    <div class="flex-grow ml-4">
                        <p class="font-semibold text-gray-800"><?= htmlspecialchars($item['name']) ?></p>
                        <p class="text-sm text-gray-500">Qty: <?= $item['quantity'] ?> &times; ₹<?= number_format($item['price']) ?></p>
                    </div>
                    <p class="font-bold text-gray-800">₹<?= number_format($item['quantity'] * $item['price']) ?></p>
                </div>
            <?php endwhile; ?>
            </div>
            <div class="text-right border-t pt-3 mt-4">
                <span class="font-semibold text-lg">Total: </span>
                <span class="font-bold text-xl text-indigo-600">₹<?= number_format($order['total_amount']) ?></span>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div>
        <!-- Status Update -->
        <div class="bg-white p-4 rounded-lg shadow-md mb-6">
            <h2 class="text-lg font-semibold mb-3">Update Status</h2>
            <div class="flex items-center space-x-3">
                <select id="status-select" class="flex-grow bg-white border border-gray-300 rounded-md py-2 px-3 text-sm">
                    <option value="Placed" <?= $order['status'] == 'Placed' ? 'selected' : '' ?>>Placed</option>
                    <option value="Dispatched" <?= $order['status'] == 'Dispatched' ? 'selected' : '' ?>>Dispatched</option>
                    <option value="Delivered" <?= $order['status'] == 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                    <option value="Cancelled" <?= $order['status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
                <button onclick="updateStatus()" class="bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg">Update</button>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="bg-white p-4 rounded-lg shadow-md">
            <h2 class="text-lg font-semibold border-b pb-2 mb-3">Customer & Shipping Info</h2>
            <p><strong>Name:</strong> <?= htmlspecialchars($order['name']) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($order['phone']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($order['user_email']) ?></p>
            <p class="mt-2"><strong>Address:</strong><br><?= nl2br(htmlspecialchars($order['address'])) ?></p>
        </div>
    </div>
</div>

<script>
    async function updateStatus() {
        const newStatus = document.getElementById('status-select').value;
        const formData = new FormData();
        formData.append('status', newStatus);
        formData.append('order_id', <?= $order_id ?>);

        const data = await sendRequest('order_detail.php?id=<?= $order_id ?>', {
            method: 'POST',
            body: formData
        });

        if (data.success) {
            showAlert('success', data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert('error', data.message);
        }
    }
</script>

<?php require_once 'common/bottom.php'; ?>