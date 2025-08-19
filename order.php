<?php
require_once 'common/header.php';
require_once 'common/sidebar.php';

if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Fetch orders
$active_orders = [];
$past_orders = [];

$sql = "
    SELECT
        o.id, o.total_amount, o.status, o.created_at,
        oi.product_id, oi.quantity,
        p.name as product_name, p.image as product_image
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC, o.id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$orders_grouped = [];
while ($row = $result->fetch_assoc()) {
    $orders_grouped[$row['id']]['details'] = [
        'total_amount' => $row['total_amount'],
        'status' => $row['status'],
        'created_at' => $row['created_at']
    ];
    $orders_grouped[$row['id']]['items'][] = [
        'product_id' => $row['product_id'],
        'quantity' => $row['quantity'],
        'product_name' => $row['product_name'],
        'product_image' => $row['product_image']
    ];
}

foreach ($orders_grouped as $order_id => $data) {
    if (in_array($data['details']['status'], ['Placed', 'Dispatched'])) {
        $active_orders[$order_id] = $data;
    } else {
        $past_orders[$order_id] = $data;
    }
}
?>

<style>
    .tab-active { color: #4f46e5; border-bottom-color: #4f46e5; }
    .progress-line { content: ''; display: block; position: absolute; top: 50%; transform: translateY(-50%); width: 100%; height: 2px; background-color: #e5e7eb; z-index: -1; }
    .progress-line.active { background-color: #4f46e5; }
</style>

<!-- Main Content -->
<main class="pt-16 pb-24">
    <!-- Header -->
    <header class="fixed top-0 left-0 right-0 bg-white shadow-sm z-10 flex items-center p-4">
        <a href="profile.php" class="text-gray-800 text-xl"><i class="fas fa-arrow-left"></i></a>
        <h1 class="text-xl font-bold text-gray-800 mx-auto">My Orders</h1>
        <div class="w-6"></div>
    </header>

    <!-- Tabs -->
    <div class="sticky top-16 bg-white z-10 flex border-b">
        <button id="active-tab-btn" onclick="switchTab('active')" class="flex-1 py-3 text-center font-semibold border-b-2 tab-active">Active Orders</button>
        <button id="history-tab-btn" onclick="switchTab('history')" class="flex-1 py-3 text-center font-semibold text-gray-500 border-b-2">Order History</button>
    </div>

    <div class="p-4">
        <?php if (isset($_GET['success'])): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
          <p class="font-bold">Order Placed Successfully!</p>
          <p>Thank you for your purchase. You can track your order status here.</p>
        </div>
        <?php endif; ?>

        <!-- Active Orders Content -->
        <div id="active-orders-content">
            <?php if (empty($active_orders)): ?>
            <p class="text-center text-gray-500 mt-8">No active orders found.</p>
            <?php else: foreach ($active_orders as $order_id => $data): ?>
            <div class="bg-white rounded-lg shadow-sm mb-4 overflow-hidden">
                <div class="p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-bold text-gray-800">Order #<?= $order_id ?></h3>
                            <p class="text-xs text-gray-500">Placed on: <?= date('d M Y, h:i A', strtotime($data['details']['created_at'])) ?></p>
                        </div>
                        <span class="text-sm font-semibold text-blue-600"><?= $data['details']['status'] ?></span>
                    </div>
                    <div class="flex items-center my-4">
                        <img src="<?= BASE_URL . 'uploads/' . ($data['items'][0]['product_image'] ?? 'placeholder.png') ?>" class="w-16 h-16 object-cover rounded-md">
                        <div class="ml-3">
                            <p class="font-semibold text-sm"><?= htmlspecialchars($data['items'][0]['product_name']) ?></p>
                            <?php if (count($data['items']) > 1): ?>
                            <p class="text-xs text-gray-500">+ <?= count($data['items']) - 1 ?> other item(s)</p>
                            <?php endif; ?>
                        </div>
                    </div>
                     <!-- Progress Tracker -->
                    <?php
                        $status = $data['details']['status'];
                        $placed_active = in_array($status, ['Placed', 'Dispatched', 'Delivered']);
                        $dispatched_active = in_array($status, ['Dispatched', 'Delivered']);
                        $delivered_active = in_array($status, ['Delivered']);
                    ?>
                    <div class="relative flex justify-between items-center text-center text-xs font-medium text-gray-500 mt-6">
                        <div class="progress-line <?= $dispatched_active ? 'active' : '' ?>"></div>
                        <div class="flex-1 relative">
                            <div class="mx-auto w-8 h-8 rounded-full flex items-center justify-center <?= $placed_active ? 'bg-indigo-600 text-white' : 'bg-gray-200' ?>">
                                <i class="fas fa-box-open"></i>
                            </div>
                            <p class="mt-1">Placed</p>
                        </div>
                        <div class="flex-1 relative">
                            <div class="mx-auto w-8 h-8 rounded-full flex items-center justify-center <?= $dispatched_active ? 'bg-indigo-600 text-white' : 'bg-gray-200' ?>">
                                <i class="fas fa-truck"></i>
                            </div>
                            <p class="mt-1">Dispatched</p>
                        </div>
                        <div class="flex-1 relative">
                             <div class="mx-auto w-8 h-8 rounded-full flex items-center justify-center <?= $delivered_active ? 'bg-indigo-600 text-white' : 'bg-gray-200' ?>">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <p class="mt-1">Delivered</p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-2 text-center text-sm">
                    <p class="font-bold text-gray-800">Total: ₹<?= number_format($data['details']['total_amount']) ?></p>
                </div>
            </div>
            <?php endforeach; endif; ?>
        </div>

        <!-- Order History Content -->
        <div id="history-orders-content" class="hidden">
             <?php if (empty($past_orders)): ?>
            <p class="text-center text-gray-500 mt-8">No past orders found.</p>
            <?php else: foreach ($past_orders as $order_id => $data): ?>
             <div class="bg-white rounded-lg shadow-sm mb-4 overflow-hidden">
                <div class="p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-bold text-gray-800">Order #<?= $order_id ?></h3>
                            <p class="text-xs text-gray-500">Completed on: <?= date('d M Y', strtotime($data['details']['created_at'])) ?></p>
                        </div>
                        <span class="text-sm font-semibold <?= $data['details']['status'] === 'Delivered' ? 'text-green-600' : 'text-red-600' ?>">
                            <?= $data['details']['status'] ?>
                        </span>
                    </div>
                    <div class="flex items-center my-4 border-t border-b py-3">
                        <img src="<?= BASE_URL . 'uploads/' . ($data['items'][0]['product_image'] ?? 'placeholder.png') ?>" class="w-12 h-12 object-cover rounded-md">
                        <div class="ml-3 flex-grow">
                            <p class="font-semibold text-sm"><?= htmlspecialchars($data['items'][0]['product_name']) ?></p>
                            <?php if (count($data['items']) > 1): ?>
                            <p class="text-xs text-gray-500">+ <?= count($data['items']) - 1 ?> other item(s)</p>
                            <?php endif; ?>
                        </div>
                    </div>
                     <div class="bg-gray-50 px-4 py-2 text-center text-sm rounded-md">
                        <p class="font-bold text-gray-800">Total Paid: ₹<?= number_format($data['details']['total_amount']) ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</main>

<script>
    function switchTab(tab) {
        const activeBtn = document.getElementById('active-tab-btn');
        const historyBtn = document.getElementById('history-tab-btn');
        const activeContent = document.getElementById('active-orders-content');
        const historyContent = document.getElementById('history-orders-content');

        if (tab === 'active') {
            activeBtn.classList.add('tab-active');
            historyBtn.classList.remove('tab-active');
            activeContent.classList.remove('hidden');
            historyContent.classList.add('hidden');
        } else {
            historyBtn.classList.add('tab-active');
            activeBtn.classList.remove('tab-active');
            historyContent.classList.remove('hidden');
            activeContent.classList.add('hidden');
        }
    }
</script>

<?php require_once 'common/bottom.php'; ?>