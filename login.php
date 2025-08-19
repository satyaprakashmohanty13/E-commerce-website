<?php
require_once 'common/config.php';

// If user is already logged in, redirect to homepage
if (isset($_SESSION['user_id'])) {
    redirect('index.php');
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => 'An error occurred.'];

    $action = $_POST['action'] ?? '';

    // --- SIGN UP LOGIC ---
    if ($action === 'signup') {
        $name = trim($_POST['name']);
        $phone = trim($_POST['phone']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        if (empty($name) || empty($phone) || empty($email) || empty($password)) {
            $response['message'] = 'Please fill in all fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['message'] = 'Invalid email format.';
        } else {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $response['message'] = 'Email already registered.';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt_insert = $conn->prepare("INSERT INTO users (name, phone, email, password) VALUES (?, ?, ?, ?)");
                $stmt_insert->bind_param("ssss", $name, $phone, $email, $hashed_password);
                if ($stmt_insert->execute()) {
                    $_SESSION['user_id'] = $stmt_insert->insert_id;
                    $_SESSION['user_name'] = $name;
                    $response = ['success' => true, 'message' => 'Registration successful!', 'redirect' => BASE_URL . 'index.php'];
                } else {
                    $response['message'] = 'Registration failed. Please try again.';
                }
                $stmt_insert->close();
            }
            $stmt->close();
        }
    }

    // --- LOGIN LOGIC ---
    elseif ($action === 'login') {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        if (empty($email) || empty($password)) {
            $response['message'] = 'Please enter email and password.';
        } else {
            $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($user = $result->fetch_assoc()) {
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $response = ['success' => true, 'message' => 'Login successful!', 'redirect' => BASE_URL . 'index.php'];
                } else {
                    $response['message'] = 'Invalid email or password.';
                }
            } else {
                $response['message'] = 'Invalid email or password.';
            }
            $stmt->close();
        }
    }

    echo json_encode($response);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login / Sign Up - Quick Kart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <style>
        .tab-active { border-color: #4f46e5; color: #4f46e5; }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div id="alert-container"></div>
    <div id="loader-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 items-center justify-center">
        <div class="loader-dots flex space-x-2">
            <div class="w-4 h-4 bg-white rounded-full"></div>
            <div class="w-4 h-4 bg-white rounded-full"></div>
            <div class="w-4 h-4 bg-white rounded-full"></div>
        </div>
    </div>

    <main class="w-full max-w-sm p-4">
        <div class="text-center mb-6">
             <h1 class="text-3xl font-bold text-indigo-600">Quick Kart</h1>
             <p class="text-gray-500">Your shopping journey starts here</p>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <!-- Tabs -->
            <div class="flex">
                <button id="login-tab-btn" class="w-1/2 p-4 text-center font-semibold border-b-2 tab-active" onclick="switchTab('login')">Login</button>
                <button id="signup-tab-btn" class="w-1/2 p-4 text-center font-semibold text-gray-500 border-b-2" onclick="switchTab('signup')">Sign Up</button>
            </div>

            <!-- Login Form -->
            <div id="login-form-container" class="p-6">
                <form id="login-form" onsubmit="handleFormSubmit(event, 'login')">
                    <input type="hidden" name="action" value="login">
                    <div class="mb-4">
                        <label for="login-email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" id="login-email" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required>
                    </div>
                    <div class="mb-6">
                        <label for="login-password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" name="password" id="login-password" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required>
                    </div>
                    <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-indigo-700 transition duration-300">
                        Login
                    </button>
                </form>
            </div>

            <!-- Sign Up Form -->
            <div id="signup-form-container" class="p-6 hidden">
                <form id="signup-form" onsubmit="handleFormSubmit(event, 'signup')">
                    <input type="hidden" name="action" value="signup">
                    <div class="mb-4">
                        <label for="signup-name" class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" name="name" id="signup-name" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required>
                    </div>
                    <div class="mb-4">
                        <label for="signup-phone" class="block text-sm font-medium text-gray-700">Phone</label>
                        <input type="tel" name="phone" id="signup-phone" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required>
                    </div>
                    <div class="mb-4">
                        <label for="signup-email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" id="signup-email" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required>
                    </div>
                    <div class="mb-6">
                        <label for="signup-password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" name="password" id="signup-password" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required>
                    </div>
                    <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-indigo-700 transition duration-300">
                        Create Account
                    </button>
                </form>
            </div>
        </div>
    </main>

    <script src="<?= BASE_URL ?>assets/js/main.js"></script>
    <script>
        function switchTab(tab) {
            const loginBtn = document.getElementById('login-tab-btn');
            const signupBtn = document.getElementById('signup-tab-btn');
            const loginForm = document.getElementById('login-form-container');
            const signupForm = document.getElementById('signup-form-container');

            if (tab === 'login') {
                loginBtn.classList.add('tab-active');
                signupBtn.classList.remove('tab-active');
                loginForm.classList.remove('hidden');
                signupForm.classList.add('hidden');
            } else {
                signupBtn.classList.add('tab-active');
                loginBtn.classList.remove('tab-active');
                signupForm.classList.remove('hidden');
                loginForm.classList.add('hidden');
            }
        }

        async function handleFormSubmit(event, action) {
            event.preventDefault();
            const form = document.getElementById(`${action}-form`);
            const formData = new FormData(form);

            const data = await window.sendRequest('login.php', {
                method: 'POST',
                body: formData
            });

            if (data.success) {
                showAlert('success', data.message);
                if (data.redirect) {
                    setTimeout(() => window.location.href = data.redirect, 1500);
                }
            } else {
                showAlert('error', data.message);
            }
        }
    </script>
</body>
</html>