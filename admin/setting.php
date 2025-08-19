<?php
require_once 'common/header.php';

// --- AJAX UPDATE HANDLER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Content-Type: application/json');
    $response = ['success' => false];
    $action = $_POST['action'] ?? '';
    $admin_id = $_SESSION['admin_id'];

    if ($action === 'update_username') {
        $username = trim($_POST['username']);
        $stmt = $conn->prepare("UPDATE admin SET username = ? WHERE id = ?");
        $stmt->bind_param("si", $username, $admin_id);
        if ($stmt->execute()) {
            $_SESSION['admin_username'] = $username;
            $response = ['success' => true, 'message' => 'Username updated successfully!'];
        } else {
            $response['message'] = 'Username might be taken or a database error occurred.';
        }
    } elseif ($action === 'change_password') {
        $old_pass = $_POST['old_password'];
        $new_pass = $_POST['new_password'];

        $stmt = $conn->prepare("SELECT password FROM admin WHERE id = ?");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();

        if ($admin && password_verify($old_pass, $admin['password'])) {
            $hashed_new_pass = password_hash($new_pass, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE admin SET password = ? WHERE id = ?");
            $update_stmt->bind_param("si", $hashed_new_pass, $admin_id);
            if ($update_stmt->execute()) {
                $response = ['success' => true, 'message' => 'Password changed successfully!'];
            } else {
                $response['message'] = 'Failed to update password.';
            }
        } else {
            $response['message'] = 'Incorrect old password.';
        }
    }
    echo json_encode($response);
    exit();
}

require_once 'common/sidebar.php';
?>

<h1 class="text-2xl font-bold text-gray-800 mb-6">Admin Settings</h1>
<div class="max-w-md">
    <!-- Update Username -->
    <div class="bg-white p-4 rounded-lg shadow-md mb-6">
        <h2 class="text-lg font-semibold mb-3 border-b pb-2">Update Username</h2>
        <form id="username-form" onsubmit="handleFormSubmit(event, 'update_username')">
            <input type="hidden" name="action" value="update_username">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Username</label>
                <input type="text" name="username" class="mt-1 block w-full input-style" value="<?= htmlspecialchars($_SESSION['admin_username']) ?>" required>
            </div>
            <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-2 rounded-lg">Save Username</button>
        </form>
    </div>

    <!-- Change Password -->
    <div class="bg-white p-4 rounded-lg shadow-md">
        <h2 class="text-lg font-semibold mb-3 border-b pb-2">Change Password</h2>
        <form id="password-form" onsubmit="handleFormSubmit(event, 'change_password')">
            <input type="hidden" name="action" value="change_password">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Old Password</label>
                <input type="password" name="old_password" class="mt-1 block w-full input-style" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">New Password</label>
                <input type="password" name="new_password" class="mt-1 block w-full input-style" required>
            </div>
            <button type="submit" class="w-full bg-gray-700 text-white font-bold py-2 rounded-lg">Update Password</button>
        </form>
    </div>
</div>
<style>.input-style { width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; }</style>

<script>
    async function handleFormSubmit(event, formType) {
        event.preventDefault();
        const formId = formType === 'update_username' ? 'username-form' : 'password-form';
        const form = document.getElementById(formId);
        const data = await sendRequest('setting.php', {
            method: 'POST',
            body: new FormData(form)
        });

        if (data.success) {
            showAlert('success', data.message);
            if (formType === 'change_password') form.reset();
        } else {
            showAlert('error', data.message);
        }
    }
</script>

<?php require_once 'common/bottom.php'; ?>