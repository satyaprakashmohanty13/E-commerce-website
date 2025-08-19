<?php
// --- CONFIGURATION ---
$db_host = '127.0.0.1';
$db_user = 'root';
$db_pass = 'root';
$db_name = 'quick_kart_db';
$admin_user = 'admin';
$admin_pass = 'admin123'; // Plain text, will be hashed
// --- END CONFIGURATION ---

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Connect to MySQL Server
    $conn = new mysqli($db_host, $db_user, $db_pass);
    if ($conn->connect_error) {
        $error = "Connection failed: " . $conn->connect_error;
    } else {
        // 2. Create Database
        $sql_db = "CREATE DATABASE IF NOT EXISTS `$db_name`";
        if ($conn->query($sql_db) === TRUE) {
            $success .= "Database '$db_name' created or already exists.<br>";
            $conn->select_db($db_name);

            // 3. Create Tables
            $sql_tables = "
            CREATE TABLE IF NOT EXISTS `users` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(255) NOT NULL,
              `phone` varchar(20) NOT NULL,
              `email` varchar(255) NOT NULL,
              `password` varchar(255) NOT NULL,
              `address` TEXT NULL,
              `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
              PRIMARY KEY (`id`),
              UNIQUE KEY `email` (`email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS `admin` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `username` varchar(255) NOT NULL,
              `password` varchar(255) NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `username` (`username`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS `categories` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(255) NOT NULL,
              `image` varchar(255) DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS `products` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `cat_id` int(11) NOT NULL,
              `name` varchar(255) NOT NULL,
              `description` text NOT NULL,
              `price` decimal(10,2) NOT NULL,
              `stock` int(11) NOT NULL,
              `image` varchar(255) NOT NULL,
              `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
              PRIMARY KEY (`id`),
              KEY `cat_id` (`cat_id`),
              CONSTRAINT `products_ibfk_1` FOREIGN KEY (`cat_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS `orders` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `user_id` int(11) NOT NULL,
              `name` varchar(255) NOT NULL,
              `address` text NOT NULL,
              `phone` varchar(20) NOT NULL,
              `total_amount` decimal(10,2) NOT NULL,
              `status` varchar(50) NOT NULL DEFAULT 'Placed',
              `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
              PRIMARY KEY (`id`),
              KEY `user_id` (`user_id`),
              CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS `order_items` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `order_id` int(11) NOT NULL,
              `product_id` int(11) NOT NULL,
              `quantity` int(11) NOT NULL,
              `price` decimal(10,2) NOT NULL,
              PRIMARY KEY (`id`),
              KEY `order_id` (`order_id`),
              KEY `product_id` (`product_id`),
              CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
              CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ";

            if ($conn->multi_query($sql_tables)) {
                do {
                    if ($result = $conn->store_result()) {
                        $result->free();
                    }
                } while ($conn->next_result());
                $success .= "All tables created successfully.<br>";

                // 4. Insert Default Admin User
                $hashed_pass = password_hash($admin_pass, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO `admin` (username, password) VALUES (?, ?) ON DUPLICATE KEY UPDATE password = ?");
                $stmt->bind_param("sss", $admin_user, $hashed_pass, $hashed_pass);
                if ($stmt->execute()) {
                    $success .= "Admin user created/updated successfully.<br>";
                    $success .= "Username: <strong>$admin_user</strong><br>";
                    $success .= "Password: <strong>$admin_pass</strong><br>";
                } else {
                    $error .= "Error creating admin user: " . $stmt->error . "<br>";
                }
                $stmt->close();

            } else {
                $error .= "Error creating tables: " . $conn->error . "<br>";
            }

            // 5. Create Uploads Directory
            if (!is_dir('uploads')) {
                if (mkdir('uploads', 0755, true)) {
                    $success .= "'uploads' directory created successfully.<br>";
                } else {
                    $error .= "Failed to create 'uploads' directory.<br>";
                }
            } else {
                 $success .= "'uploads' directory already exists.<br>";
            }

            if(empty($error)){
                // Redirect to login page after 5 seconds
                header("refresh:5;url=index.php");
            }

        } else {
            $error = "Error creating database: " . $conn->error;
        }
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick Kart - Installation</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="w-full max-w-md bg-white rounded-lg shadow-md p-8">
        <h1 class="text-2xl font-bold text-center text-gray-800 mb-4">Quick Kart Installer</h1>
        <?php if ($_SERVER['REQUEST_METHOD'] !== 'POST'): ?>
        <p class="text-center text-gray-600 mb-6">Click the button below to set up your database and admin account.</p>
        <form method="POST" action="install.php">
            <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50 transition duration-300">
                Install Now
            </button>
        </form>
        <?php else: ?>
            <?php if (!empty($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline"><?php echo $error; ?></span>
                </div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Success!</strong>
                    <span class="block sm:inline"><?php echo $success; ?></span>
                    <p class="mt-4">Installation complete. You will be redirected to the homepage shortly. Please delete this `install.php` file for security reasons.</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>