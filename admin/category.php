<?php
// This is the logic for AJAX requests. It runs first and exits.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    // We MUST include the config here for the AJAX call to have DB access and functions.
    require_once __DIR__ . '/../common/config.php';

    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => 'An unknown error occurred.'];
    $action = $_POST['action'] ?? '';

    // --- ADD CATEGORY ---
    if ($action === 'add') {
        $name = trim($_POST['name']);
        $image_name = '';

        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = __DIR__ . "/../uploads/";
            $image_name = time() . '_' . basename($_FILES["image"]["name"]);
            if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $image_name)) {
                $response['message'] = 'Failed to upload image.';
                echo json_encode($response);
                exit();
            }
        }

        $stmt = $conn->prepare("INSERT INTO categories (name, image) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $image_name);
        if ($stmt->execute()) {
            $response = [
                'success' => true,
                'message' => 'Category added successfully!',
                'category' => [
                    'id' => $stmt->insert_id,
                    'name' => htmlspecialchars($name),
                    'image' => $image_name
                ]
            ];
        } else {
            $response['message'] = 'Database error: Could not add category.';
        }
        $stmt->close();
    }

    // --- FETCH CATEGORY FOR EDIT ---
    elseif ($action === 'fetch' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("SELECT id, name, image FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($category = $result->fetch_assoc()) {
            $response = ['success' => true, 'category' => $category];
        } else {
            $response['message'] = 'Category not found.';
        }
    }

    // --- EDIT CATEGORY ---
    elseif ($action === 'edit') {
        $id = (int)$_POST['id'];
        $name = trim($_POST['name']);
        $current_image = $_POST['current_image'];
        $image_name = $current_image;

        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = __DIR__ . "/../uploads/";
            $image_name = time() . '_' . basename($_FILES["image"]["name"]);
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $image_name)) {
                if (!empty($current_image) && file_exists($target_dir . $current_image)) {
                    unlink($target_dir . $current_image);
                }
            } else {
                 $response['message'] = 'Failed to upload new image.';
                 echo json_encode($response);
                 exit();
            }
        }

        $stmt = $conn->prepare("UPDATE categories SET name = ?, image = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $image_name, $id);
        if ($stmt->execute()) {
            $response = [
                'success' => true,
                'message' => 'Category updated successfully!',
                'category' => ['id' => $id, 'name' => htmlspecialchars($name), 'image' => $image_name]
            ];
        } else {
            $response['message'] = 'Database error: Could not update category.';
        }
    }

    // --- DELETE CATEGORY ---
    elseif ($action === 'delete' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $stmt_img = $conn->prepare("SELECT image FROM categories WHERE id = ?");
        $stmt_img->bind_param("i", $id);
        $stmt_img->execute();
        if ($category = $stmt_img->get_result()->fetch_assoc()) {
            $stmt_del = $conn->prepare("DELETE FROM categories WHERE id = ?");
            $stmt_del->bind_param("i", $id);
            if ($stmt_del->execute()) {
                if (!empty($category['image']) && file_exists(__DIR__ . "/../uploads/" . $category['image'])) {
                    unlink(__DIR__ . "/../uploads/" . $category['image']);
                }
                $response = ['success' => true, 'message' => 'Category deleted successfully!'];
            } else {
                $response['message'] = 'Database error: Could not delete category.';
            }
        } else {
            $response['message'] = 'Category not found.';
        }
    }

    echo json_encode($response);
    exit();
}

// --- This part is for NORMAL PAGE LOAD ---
// The header file will load config and check the session.
require_once 'common/header.php';
require_once 'common/sidebar.php';
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Manage Categories</h1>
    <button onclick="openCategoryModal('add')" class="bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-indigo-700 transition">
        <i class="fas fa-plus mr-2"></i>Add Category
    </button>
</div>

<!-- Categories List -->
<div class="bg-white p-4 rounded-lg shadow-md">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3">Image</th>
                    <th scope="col" class="px-6 py-3">Name</th>
                    <th scope="col" class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="category-list">
                <?php
                // This will now display correctly
                $result = $conn->query("SELECT * FROM categories ORDER BY name ASC");
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $image_url = BASE_URL . 'uploads/' . (!empty($row['image']) ? $row['image'] : 'placeholder.png');
                        echo "<tr class='bg-white border-b' id='cat-row-{$row['id']}'>
                                <td class='px-6 py-4'><img src='{$image_url}' class='h-10 w-10 object-cover rounded-md'></td>
                                <td class='px-6 py-4 font-medium text-gray-900'>".htmlspecialchars($row['name'])."</td>
                                <td class='px-6 py-4 text-right'>
                                    <button onclick='openCategoryModal(\"edit\", {$row['id']})' class='font-medium text-blue-600 hover:underline mr-3'><i class='fas fa-edit'></i></button>
                                    <button onclick='confirmDelete({$row['id']})' class='font-medium text-red-600 hover:underline'><i class='fas fa-trash'></i></button>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='3' class='text-center py-4'>No categories found. Click 'Add Category' to start.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Category Modal -->
<div id="category-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-40 items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
        <div class="flex justify-between items-center border-b pb-3 mb-4">
            <h3 class="text-xl font-semibold" id="modal-title">Add Category</h3>
            <button onclick="closeCategoryModal()" class="text-gray-500 hover:text-gray-800 text-2xl">&times;</button>
        </div>
        <form id="category-form" onsubmit="handleFormSubmit(event)">
            <input type="hidden" id="category-id" name="id">
            <input type="hidden" id="form-action" name="action">
            <input type="hidden" id="current-image" name="current_image">
            <div class="mb-4">
                <label for="category-name" class="block text-sm font-medium text-gray-700">Category Name</label>
                <input type="text" id="category-name" name="name" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" required>
            </div>
            <div class="mb-4">
                <label for="category-image" class="block text-sm font-medium text-gray-700">Category Image</label>
                <input type="file" id="category-image" name="image" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" accept="image/*">
                <img id="image-preview" src="" class="hidden h-20 w-20 object-cover rounded-md mt-2">
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeCategoryModal()" class="bg-gray-200 text-gray-800 font-bold py-2 px-4 rounded-lg">Cancel</button>
                <button type="submit" class="bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-sm p-6 text-center">
        <h3 class="text-lg font-semibold mb-4">Are you sure?</h3>
        <p class="text-gray-600 mb-6">Do you really want to delete this category? This process cannot be undone.</p>
        <div class="flex justify-center space-x-4">
            <button onclick="closeDeleteModal()" class="bg-gray-200 text-gray-800 font-bold py-2 px-6 rounded-lg">Cancel</button>
            <button id="confirm-delete-btn" class="bg-red-600 text-white font-bold py-2 px-6 rounded-lg">Delete</button>
        </div>
    </div>
</div>

<script>
    const modal = document.getElementById('category-modal');
    const deleteModal = document.getElementById('delete-modal');
    const form = document.getElementById('category-form');
    const modalTitle = document.getElementById('modal-title');
    const categoryIdInput = document.getElementById('category-id');
    const formActionInput = document.getElementById('form-action');
    const categoryNameInput = document.getElementById('category-name');
    const currentImageInput = document.getElementById('current-image');
    const imagePreview = document.getElementById('image-preview');

    function openCategoryModal(mode, id = null) {
        form.reset();
        imagePreview.classList.add('hidden');
        imagePreview.src = '';
        if (mode === 'add') {
            modalTitle.innerText = 'Add New Category';
            formActionInput.value = 'add';
            categoryIdInput.value = '';
            currentImageInput.value = '';
            toggleModal('category-modal', true);
        } else if (mode === 'edit') {
            modalTitle.innerText = 'Edit Category';
            formActionInput.value = 'edit';
            categoryIdInput.value = id;
            const formData = new FormData();
            formData.append('action', 'fetch');
            formData.append('id', id);
            sendRequest('category.php', { method: 'POST', body: formData })
                .then(data => {
                    if (data.success) {
                        categoryNameInput.value = data.category.name;
                        currentImageInput.value = data.category.image;
                        if(data.category.image) {
                            imagePreview.src = '<?= BASE_URL ?>uploads/' + data.category.image;
                            imagePreview.classList.remove('hidden');
                        }
                        toggleModal('category-modal', true);
                    } else {
                        showAlert('error', data.message);
                    }
                });
        }
    }

    function closeCategoryModal() {
        toggleModal('category-modal', false);
    }

    function confirmDelete(id) {
        const confirmBtn = document.getElementById('confirm-delete-btn');
        confirmBtn.onclick = () => deleteCategory(id);
        toggleModal('delete-modal', true);
    }

    function closeDeleteModal() {
        toggleModal('delete-modal', false);
    }

    async function handleFormSubmit(event) {
        event.preventDefault();
        const formData = new FormData(form);
        const data = await sendRequest('category.php', {
            method: 'POST',
            body: formData
        });

        if (data.success) {
            showAlert('success', data.message);
            updateCategoryList(data.category, formActionInput.value);
            closeCategoryModal();
        } else {
            showAlert('error', data.message);
        }
    }

    async function deleteCategory(id) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);
        const data = await sendRequest('category.php', {
            method: 'POST',
            body: formData
        });
        if (data.success) {
            showAlert('success', data.message);
            document.getElementById(`cat-row-${id}`).remove();
        } else {
            showAlert('error', data.message);
        }
        closeDeleteModal();
    }

    function updateCategoryList(category, mode) {
        const imageUrl = '<?= BASE_URL ?>uploads/' + (category.image ? category.image : 'placeholder.png');
        const newRowHtml = `
            <td class='px-6 py-4'><img src='${imageUrl}' class='h-10 w-10 object-cover rounded-md'></td>
            <td class='px-6 py-4 font-medium text-gray-900'>${category.name}</td>
            <td class='px-6 py-4 text-right'>
                <button onclick='openCategoryModal("edit", ${category.id})' class='font-medium text-blue-600 hover:underline mr-3'><i class='fas fa-edit'></i></button>
                <button onclick='confirmDelete(${category.id})' class='font-medium text-red-600 hover:underline'><i class='fas fa-trash'></i></button>
            </td>
        `;
        if (mode === 'add') {
            const list = document.getElementById('category-list');
            const newRow = list.insertRow(0);
            newRow.id = `cat-row-${category.id}`;
            newRow.className = 'bg-white border-b';
            newRow.innerHTML = newRowHtml;
        } else {
            document.getElementById(`cat-row-${category.id}`).innerHTML = newRowHtml;
        }
    }
</script>

<?php
// This will now be included correctly, loading main.js and enabling all buttons.
require_once 'common/bottom.php';
?>