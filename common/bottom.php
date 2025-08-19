        <!-- Bottom Navigation -->
        <footer class="fixed bottom-0 left-0 right-0 bg-white shadow-lg" style="box-shadow: 0 -2px 10px rgba(0,0,0,0.1);">
            <nav class="flex justify-around py-2">
                <a href="<?= BASE_URL ?>index.php" class="flex flex-col items-center text-gray-600 hover:text-indigo-600 w-1/4">
                    <i class="fas fa-home text-xl"></i>
                    <span class="text-xs">Home</span>
                </a>
                <a href="<?= BASE_URL ?>cart.php" class="flex flex-col items-center text-gray-600 hover:text-indigo-600 w-1/4 relative">
                    <i class="fas fa-shopping-cart text-xl"></i>
                    <span class="text-xs">Cart</span>
                    <?php
                        $cart_count = 0;
                        if(isset($_SESSION['cart'])){
                            $cart_count = count($_SESSION['cart']);
                        }
                    ?>
                    <?php if($cart_count > 0): ?>
                    <span class="absolute -top-1 right-5 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"><?= $cart_count ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?= BASE_URL ?>profile.php" class="flex flex-col items-center text-gray-600 hover:text-indigo-600 w-1/4">
                    <i class="fas fa-user text-xl"></i>
                    <span class="text-xs">Profile</span>
                </a>
            </nav>
        </footer>
    </div> <!-- End #app-container -->

    <!-- Custom JS -->
    <script src="<?= BASE_URL ?>assets/js/main.js"></script>
</body>
</html>