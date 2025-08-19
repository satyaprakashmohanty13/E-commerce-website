<?php
// STEP 1: Handle any incoming AJAX requests first, then exit.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    // We MUST include the config here for the AJAX call to have DB access.
    require_once __DIR__ . '/../common/config.php';

    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => 'An unknown error occurred.'];
    $action = $_POST['action'] ?? '';

    // ADD PRODUCT
    if ($action === 'add') {
        $cat_id = (int)$_POST['cat_id'];
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = (float)$_POST['price'];
        $stock = (int)$_POST['stock'];
        $image_name = '';

        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = __DIR__ . "/../uploads/";
            $image_name = time() . '_' . basename($_FILES["image"]["name"]);
            if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $image_name)) {
                 $response['message'] = 'Failed to upload image.'; echo json_encode($response); exit();
            }
        }
        $stmt = $conn->prepare("INSERT INTO products (cat_id, name, description, price, stock, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issdis", $cat_id, $name, $description, $price, $stock, $image_name);
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Product added successfully!'];
        } else {
            $response['message'] = 'Database error: ' . $stmt->error;
        }
        $stmt->close();
    }
    // FETCH PRODUCT
    elseif ($action === 'fetch' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        if ($product = $stmt->get_result()->fetch_assoc()) {
            $response = ['success' => true, 'product' => $product];
        } else {
            $response['message'] = 'Product not found.';
        }
    }
    // EDIT PRODUCT
    elseif ($action === 'edit') {
        $id = (int)$_POST['id'];
        $cat_id = (int)$_POST['cat_id'];
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = (float)$_POST['price'];
        $stock = (int)$_POST['stock'];
        $image_name = $_POST['current_image'];

        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = __DIR__ . "/../uploads/";
            $image_name = time() . '_' . basename($_FILES["image"]["name"]);
            move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $image_name);
        }
        $stmt = $conn->prepare("UPDATE products SET cat_id=?, name=?, description=?, price=?, stock=?, image=? WHERE id=?");
        $stmt->bind_param("issdisi", $cat_id, $name, $description, $price, $stock, $image_name, $id);
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Product updated successfully!'];
        } else {
            $response['message'] = 'Database error: ' . $stmt->error;
        }
    }
    // DELETE PRODUCT
    elseif ($action === 'delete' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $stmt_del = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt_del->bind_param("i", $id);
        if ($stmt_del->execute()) {
            $response = ['success' => true, 'message' => 'Product deleted successfully!'];
        } else {
            $response['message'] = 'Could not delete product.';
        }
    }

    echo json_encode($response);
    exit();
}

// STEP 2: If it's not AJAX, load the full page. The header will load the config.
require_once 'common/header.php';
require_once 'common/sidebar.php';
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Manage Products</h1>
    <button onclick="openProductModal('add')" class="bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-indigo-700 transition">
        <i class="fas fa-plus mr-2"></i>Add Product
    </button>
</div>

<!-- Products List -->
<div class="bg-white p-4 rounded-lg shadow-md">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th class="px-6 py-3">Product</th>
                    <th class="px-6 py-3">Category</th>
                    <th class="px-6 py-3">Price</th>
                    <th class="px-6 py-3">Stock</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="product-list">
                <?php
                $sql = "SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.cat_id = c.id ORDER BY p.created_at DESC";
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $image_url = BASE_URL . 'uploads/' . (!empty($row['image']) ? $row['image'] : 'placeholder.png');
                        echo "<tr class='bg-white border-b' id='prod-row-{$row['id']}'>
                                <td class='px-6 py-4 font-medium text-gray-900 flex items-center'>
                                    <img src='{$image_url}' class='h-10 w-10 object-cover rounded-md mr-3'>
                                    <span>".htmlspecialchars($row['name'])."</span>
                                </td>
                                <td class='px-6 py-4'>".htmlspecialchars($row['category_name'])."</td>
                                <td class='px-6 py-4'>₹".number_format($row['price'])."</td>
                                <td class='px-6 py-4'>{$row['stock']}</td>
                                <td class='px-6 py-4 text-right'>
                                    <button onclick='openProductModal(\"edit\", {$row['id']})' class='font-medium text-blue-600 hover:underline mr-3'><i class='fas fa-edit'></i></button>
                                    <button onclick='confirmDelete({$row['id']})' class='font-medium text-red-600 hover:underline'><i class='fas fa-trash'></i></button>
                                </td>
                              </tr>";
                    }
                } else {
                     echo "<tr><td colspan='5' class='text-center py-4'>No products found. Click 'Add Product' to start.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Product Modal -->
<div id="product-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-40 items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center border-b pb-3 mb-4">
            <h3 class="text-xl font-semibold" id="modal-title">Add Product</h3>
            <button onclick="closeProductModal()" class="text-gray-500 hover:text-gray-800 text-2xl">&times;</button>
        </div>
        <form id="product-form" onsubmit="handleFormSubmit(event)">
            <input type="hidden" id="product-id" name="id">
            <input type="hidden" id="form-action" name="action">
            <input type="hidden" id="current-image" name="current_image">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Product Name</label>
                    <input type="text" id="product-name" name="name" class="mt-1 block w-full input-style" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Category</label>
                    <select id="product-category" name="cat_id" class="mt-1 block w-full input-style" required>
                        <option value="">Select Category</option>
                        <?php
                        $cat_result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
                        while($cat = $cat_result->fetch_assoc()){
                            echo "<option value='{$cat['id']}'>".htmlspecialchars($cat['name'])."</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <textarea id="product-description" name="description" rows="4" class="mt-1 block w-full input-style" required></textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Price (₹)</label>
                    <input type="number" step="0.01" id="product-price" name="price" class="mt-1 block w-full input-style" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Stock</label>
                    <input type="number" id="product-stock" name="stock" class="mt-1 block w-full input-style" required>
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Product Image</label>
                <input type="file" id="product-image" name="image" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" accept="image/*">
                <img id="image-preview" src="" class="hidden h-20 w-20 object-cover rounded-md mt-2">
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeProductModal()" class="bg-gray-200 text-gray-800 font-bold py-2 px-4 rounded-lg">Cancel</button>
                <button type="submit" class="bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg">Save Product</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-sm p-6 text-center">
        <h3 class="text-lg font-semibold mb-4">Are you sure?</h3>
        <p class="text-gray-600 mb-6">Do you really want to delete this product?</p>
        <div class="flex justify-center space-x-4">
            <button onclick="closeDeleteModal()" class="bg-gray-200 text-gray-800 font-bold py-2 px-6 rounded-lg">Cancel</button>
            <button id="confirm-delete-btn" class="bg-red-600 text-white font-bold py-2 px-6 rounded-lg">Delete</button>
        </div>
    </div>
</div>

<style>.input-style { width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; transition: border-color 0.2s; } .input-style:focus { border-color: #4f46e5; outline: none; }</style>

<!-- =============================================================== -->
<!-- THIS SCRIPT BLOCK WAS MISSING AND IS NOW RESTORED               -->
<!-- =============================================================== -->
<script>
    const form = document.getElementById('product-form');

    function openProductModal(mode, id = null) {
        form.reset();
        document.getElementById('image-preview').classList.add('hidden');
        if (mode === 'add') {
            document.getElementById('modal-title').innerText = 'Add New Product';
            document.getElementById('form-action').value = 'add';
            document.getElementById('product-id').value = '';
            document.getElementById('current-image').value = '';
            toggleModal('product-modal', true);
        } else if (mode === 'edit') {
            document.getElementById('modal-title').innerText = 'Edit Product';
            document.getElementById('form-action').value = 'edit';
            document.getElementById('product-id').value = id;

            const formData = new FormData();
            formData.append('action', 'fetch');
            formData.append('id', id);
            sendRequest('product.php', { method: 'POST', body: formData })
                .then(data => {
                    if (data.success) {
                        const p = data.product;
                        document.getElementById('product-category').value = p.cat_id;
                        document.getElementById('product-name').value = p.name;
                        document.getElementById('product-description').value = p.description;
                        document.getElementById('product-price').value = p.price;
                        document.getElementById('product-stock').value = p.stock;
                        document.getElementById('current-image').value = p.image;
                        if(p.image) {
                            const preview = document.getElementById('image-preview');
                            preview.src = '<?= BASE_URL ?>uploads/' + p.image;
                            preview.classList.remove('hidden');
                        }
                        toggleModal('product-modal', true);
                    } else {
                        showAlert('error', data.message);
                    }
                });
        }
    }

    function closeProductModal() {
        toggleModal('product-modal', false);
    }

    function confirmDelete(id) {
        document.getElementById('confirm-delete-btn').onclick = () => deleteProduct(id);
        toggleModal('delete-modal', true);
    }

    function closeDeleteModal() {
        toggleModal('delete-modal', false);
    }

    async function handleFormSubmit(event) {
        event.preventDefault();
        const formData = new FormData(form);
        const data = await sendRequest('product.php', {
            method: 'POST',
            body: formData
        });

        if (data.success) {
            showAlert('success', data.message);
            // Reload the page to show changes. A more advanced app might update the table row.
            setTimeout(() => location.reload(), 1500);
            closeProductModal();
        } else {
            showAlert('error', data.message);
        }
    }

    async function deleteProduct(id) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);
        const data = await sendRequest('product.php', {
            method: 'POST',
            body: formData
        });
        if (data.success) {
            showAlert('success', data.message);
            document.getElementById(`prod-row-${id}`).remove();
        } else {
            showAlert('error', data.message);
        }
        closeDeleteModal();
    }
</script>

<?php
// This will now be included correctly, loading main.js
require_once 'common/bottom.php';
?>