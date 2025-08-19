<?php
require_once 'common/header.php';
require_once 'common/sidebar.php';

$status_filter = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';
$where_clause = '';
if (!empty($status_filter)) {
    $where_clause = "WHERE o.status = '$status_filter'";
}
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Manage Orders</h1>
    <div class="relative">
        <select onchange="window.location.href=this.value" class="appearance-none bg-white border border-gray-300 rounded-md py-2 pl-3 pr-10 text-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            <option value="order.php" <?= empty($status_filter) ? 'selected' : '' ?>>All Orders</option>
            <option value="order.php?status=Placed" <?= $status_filter == 'Placed' ? 'selected' : '' ?>>Placed</option>
            <option value="order.php?status=Dispatched" <?= $status_filter == 'Dispatched' ? 'selected' : '' ?>>Dispatched</option>
            <option value="order.php?status=Delivered" <?= $status_filter == 'Delivered' ? 'selected' : '' ?>>Delivered</option>
            <option value="order.php?status=Cancelled" <?= $status_filter == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
        </select>
        <i class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 pointer-events-none"></i>
    </div>
</div>

<!-- Orders List -->
<div class="bg-white p-4 rounded-lg shadow-md">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th class="px-6 py-3">Order ID</th>
                    <th class="px-6 py-3">User</th>
                    <th class="px-6 py-3">Amount</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Date</th>
                    <th class="px-6 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT o.id, o.total_amount, o.status, o.created_at, u.name as user_name
                        FROM orders o
                        JOIN users u ON o.user_id = u.id
                        $where_clause
                        ORDER BY o.created_at DESC";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $status_color = '';
                        switch ($row['status']) {
                            case 'Placed': $status_color = 'bg-yellow-100 text-yellow-800'; break;
                            case 'Dispatched': $status_color = 'bg-blue-100 text-blue-800'; break;
                            case 'Delivered': $status_color = 'bg-green-100 text-green-800'; break;
                            case 'Cancelled': $status_color = 'bg-red-100 text-red-800'; break;
                        }
                        echo "<tr class='bg-white border-b'>
                                <td class='px-6 py-4 font-medium text-gray-900'>#{$row['id']}</td>
                                <td class='px-6 py-4'>".htmlspecialchars($row['user_name'])."</td>
                                <td class='px-6 py-4'>₹".number_format($row['total_amount'])."</td>
                                <td class='px-6 py-4'><span class='px-2 py-1 text-xs font-semibold rounded-full {$status_color}'>{$row['status']}</span></td>
                                <td class='px-6 py-4'>".date('d M, Y', strtotime($row['created_at']))."</td>
                                <td class='px-6 py-4 text-right'>
                                    <a href='order_detail.php?id={$row['id']}' class='font-medium text-indigo-600 hover:underline'>View Details</a>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center py-4'>No orders found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'common/bottom.php'; ?>