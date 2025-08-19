<?php
require_once 'common/config.php';

if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Handle AJAX form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => 'An error occurred.'];
    $action = $_POST['action'] ?? '';

    // Update Profile
    if ($action === 'update_profile') {
        $name = trim($_POST['name']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);

        $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $phone, $address, $user_id);
        if ($stmt->execute()) {
            $_SESSION['user_name'] = $name;
            $response = ['success' => true, 'message' => 'Profile updated successfully!'];
        } else {
            $response['message'] = 'Failed to update profile.';
        }
        $stmt->close();
    }
    // Change Password
    elseif ($action === 'change_password') {
        $old_pass = $_POST['old_password'];
        $new_pass = $_POST['new_password'];

        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($old_pass, $user['password'])) {
            $hashed_new_pass = password_hash($new_pass, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update_stmt->bind_param("si", $hashed_new_pass, $user_id);
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

// Fetch user data for display
$stmt = $conn->prepare("SELECT name, email, phone, address FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

require_once 'common/header.php';
require_once 'common/sidebar.php';
?>

<!-- Main Content -->
<main class="pt-16 pb-24">
    <!-- Header -->
    <header class="fixed top-0 left-0 right-0 bg-white shadow-sm z-10 flex items-center p-4">
        <a href="index.php" class="text-gray-800 text-xl"><i class="fas fa-arrow-left"></i></a>
        <h1 class="text-xl font-bold text-gray-800 mx-auto">My Profile</h1>
        <div class="w-6"></div>
    </header>

    <div class="p-4">
        <!-- Edit Profile Form -->
        <div class="bg-white p-4 rounded-lg shadow-sm mb-4">
            <h2 class="text-lg font-semibold mb-4 border-b pb-2">Edit Profile</h2>
            <form id="profile-form" onsubmit="handleFormSubmit(event, 'update_profile')">
                <input type="hidden" name="action" value="update_profile">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Full Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" class="mt-1 block w-full input-style" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" value="<?= htmlspecialchars($user['email']) ?>" class="mt-1 block w-full input-style bg-gray-100" readonly>
                </div>
                 <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Phone</label>
                    <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" class="mt-1 block w-full input-style" required>
                </div>
                 <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Address</label>
                    <textarea name="address" rows="3" class="mt-1 block w-full input-style"><?= htmlspecialchars($user['address']) ?></textarea>
                </div>
                <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-2 rounded-lg">Save Changes</button>
            </form>
        </div>

        <!-- Change Password Form -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
             <h2 class="text-lg font-semibold mb-4 border-b pb-2">Change Password</h2>
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

        <!-- Logout Button -->
        <div class="mt-6">
            <a href="logout.php" class="block text-center w-full bg-red-500 text-white font-bold py-2 rounded-lg hover:bg-red-600">Logout</a>
        </div>
    </div>
</main>

<style>.input-style { px: 3; py: 2; border: 1px solid #d1d5db; border-radius: 0.375rem; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); outline: none; transition: border-color 0.2s; } .input-style:focus { border-color: #4f46e5; }</style>

<script>
    async function handleFormSubmit(event, formType) {
        event.preventDefault();
        const formId = formType === 'update_profile' ? 'profile-form' : 'password-form';
        const form = document.getElementById(formId);
        const formData = new FormData(form);

        const data = await window.sendRequest('profile.php', {
            method: 'POST',
            body: formData
        });

        if (data.success) {
            showAlert('success', data.message);
            if (formType === 'change_password') {
                form.reset();
            }
        } else {
            showAlert('error', data.message);
        }
    }
</script>

<?php require_once 'common/bottom.php'; ?>