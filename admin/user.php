<?php
// STEP 1: Handle any incoming AJAX requests first, then exit.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    // We MUST include the config here for the AJAX call to have DB access.
    require_once __DIR__ . '/../common/config.php';

    header('Content-Type: application/json');
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'User deleted successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete user. Associated orders might exist.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID.']);
    }
    exit();
}

// STEP 2: If it's not AJAX, load the full page. The header will load the config.
require_once 'common/header.php';
require_once 'common/sidebar.php';
?>

<h1 class="text-2xl font-bold text-gray-800 mb-6">Manage Users</h1>

<!-- Users List -->
<div class="bg-white p-4 rounded-lg shadow-md">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th class="px-6 py-3">User ID</th>
                    <th class="px-6 py-3">Name</th>
                    <th class="px-6 py-3">Email</th>
                    <th class="px-6 py-3">Phone</th>
                    <th class="px-6 py-3">Registered On</th>
                    <th class="px-6 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // This will now execute correctly and display the users.
                $result = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()):
                ?>
                <tr class="bg-white border-b" id="user-row-<?= $row['id'] ?>">
                    <td class="px-6 py-4 font-medium">#<?= $row['id'] ?></td>
                    <td class="px-6 py-4 font-medium text-gray-900"><?= htmlspecialchars($row['name']) ?></td>
                    <td class="px-6 py-4"><?= htmlspecialchars($row['email']) ?></td>
                    <td class="px-6 py-4"><?= htmlspecialchars($row['phone']) ?></td>
                    <td class="px-6 py-4"><?= date('d M, Y', strtotime($row['created_at'])) ?></td>
                    <td class="px-6 py-4 text-right">
                        <button onclick="confirmDelete(<?= $row['id'] ?>)" class="font-medium text-red-600 hover:underline">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </td>
                </tr>
                <?php
                    endwhile;
                } else {
                    echo "<tr><td colspan='6' class='text-center py-4'>No registered users found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-sm p-6 text-center">
        <h3 class="text-lg font-semibold mb-4">Are you sure?</h3>
        <p class="text-gray-600 mb-6">Do you really want to delete this user? All their associated orders will also be deleted due to database constraints.</p>
        <div class="flex justify-center space-x-4">
            <button onclick="toggleModal('delete-modal', false)" class="bg-gray-200 text-gray-800 font-bold py-2 px-6 rounded-lg">Cancel</button>
            <button id="confirm-delete-btn" class="bg-red-600 text-white font-bold py-2 px-6 rounded-lg">Delete</button>
        </div>
    </div>
</div>

<!-- =============================================================== -->
<!-- THIS SCRIPT BLOCK IS NOW RESTORED TO MAKE BUTTONS WORK          -->
<!-- =============================================================== -->
<script>
    function confirmDelete(id) {
        document.getElementById('confirm-delete-btn').onclick = () => deleteUser(id);
        toggleModal('delete-modal', true);
    }

    async function deleteUser(id) {
        const formData = new FormData();
        formData.append('id', id);

        const data = await sendRequest('user.php', { method: 'POST', body: formData });

        if (data.success) {
            showAlert('success', data.message);
            const row = document.getElementById(`user-row-${id}`);
            row.style.transition = 'opacity 0.5s';
            row.style.opacity = '0';
            setTimeout(() => row.remove(), 500);
        } else {
            showAlert('error', data.message);
        }
        toggleModal('delete-modal', false);
    }
</script>

<?php
// This will now be included correctly, loading main.js
require_once 'common/bottom.php';
?>