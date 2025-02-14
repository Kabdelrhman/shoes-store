<?php
require_once __DIR__ . '/includes/header.php';

$success_message = '';
$error_message = '';

// Handle product operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = trim($_POST['name']);
                $price = floatval($_POST['price']);
                $description = trim($_POST['description']);
                $stock = intval($_POST['stock']);

                // Handle image upload
                $image = null;
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                    $filename = $_FILES['image']['name'];
                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    
                    if (in_array($ext, $allowed)) {
                        $image = uniqid() . '.' . $ext;
                        $upload_path = '../img/' . $image;
                        
                        if (!file_exists('../img')) {
                            mkdir('../img', 0777, true);
                        }
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                            // Image uploaded successfully
                        } else {
                            $error_message = "Error uploading image";
                            break;
                        }
                    }
                }

                if (!empty($name) && $price > 0) {
                    $sql = "INSERT INTO products (name, price, description, stock, image) VALUES (?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sdsss", $name, $price, $description, $stock, $image);
                    if ($stmt->execute()) {
                        $success_message = "Product added successfully";
                    } else {
                        $error_message = "Error adding product";
                    }
                    $stmt->close();
                }
                break;

            case 'edit':
                if (!isset($_POST['id']) || empty($_POST['id'])) {
                    $error_message = "Product ID is missing";
                    break;
                }
                
                $id = intval($_POST['id']);
                $name = trim($_POST['name']);
                $price = floatval($_POST['price']);
                $description = trim($_POST['description']);
                $stock = intval($_POST['stock']);

                // Handle image upload for edit
                $image = $_POST['current_image'];
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                    $filename = $_FILES['image']['name'];
                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    
                    if (in_array($ext, $allowed)) {
                        // Delete old image if exists
                        if (!empty($image) && file_exists('../img/' . $image)) {
                            unlink('../img/' . $image);
                        }
                        
                        $image = uniqid() . '.' . $ext;
                        $upload_path = '../img/' . $image;
                        
                        if (!file_exists('../img')) {
                            mkdir('../img', 0777, true);
                        }
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                            // Image uploaded successfully
                        } else {
                            $error_message = "Error uploading image";
                            break;
                        }
                    }
                }

                if (!empty($name) && $price > 0) {
                    $sql = "UPDATE products SET name = ?, price = ?, description = ?, stock = ?, image = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sdsssi", $name, $price, $description, $stock, $image, $id);
                    if ($stmt->execute()) {
                        $success_message = "Product updated successfully";
                    } else {
                        $error_message = "Error updating product";
                    }
                    $stmt->close();
                }
                break;

            case 'delete':
                $id = intval($_POST['id']);
                
                // Get current image
                $sql = "SELECT image FROM products WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $product = $result->fetch_assoc();
                $stmt->close();
                
                // Delete the product
                $sql = "DELETE FROM products WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    // Delete image file if exists
                    if (!empty($product['image']) && file_exists('../img/' . $product['image'])) {
                        unlink('../img/' . $product['image']);
                    }
                    $success_message = "Product deleted successfully";
                } else {
                    $error_message = "Error deleting product";
                }
                $stmt->close();
                break;
        }
    }
}

// Fetch all products
$sql = "SELECT * FROM products ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Manage Products</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 3px;
        }
        .success {
            background-color: #dff0d8;
            color: #3c763d;
        }
        .error {
            background-color: #f2dede;
            color: #a94442;
        }
        .product-form {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .products-table th, .products-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .products-table th {
            background-color: #f5f5f5;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        .edit-btn, .delete-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .edit-btn {
            background-color: #337ab7;
            color: white;
        }
        .delete-btn {
            background-color: #d9534f;
            color: white;
        }
        .product-image {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
        }
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1>Manage Products</h1>
        <div>
            <a href="../index.php" class="btn">Back to Store</a>
        </div>
    </div>

    <?php if ($success_message): ?>
        <div class="message success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="message error"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <div class="product-form">
        <h2>Add New Product</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="id" value="">
            <input type="hidden" name="current_image" value="">
            <div class="form-group">
                <label>Name:</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Price:</label>
                <input type="number" name="price" step="0.01" min="0" required>
            </div>
            <div class="form-group">
                <label>Description:</label>
                <textarea name="description" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label>Stock:</label>
                <input type="number" name="stock" min="0" required>
            </div>
            <div class="form-group">
                <label>Image:</label>
                <input type="file" name="image" accept="image/*" onchange="previewImage(this)">
                <img id="preview" class="preview-image">
            </div>
            <input type="submit" value="Add Product">
        </form>
    </div>

    <div class="products-list">
        <h2>Current Products</h2>
        <table class="products-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Description</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($product = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $product['id']; ?></td>
                        <td>
                            <?php if ($product['image']): ?>
                                <img src="../img/<?php echo htmlspecialchars($product['image']); ?>" class="product-image">
                            <?php else: ?>
                                No image
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td>$<?php echo number_format($product['price'], 2); ?></td>
                        <td><?php echo htmlspecialchars($product['description']); ?></td>
                        <td><?php echo $product['stock']; ?></td>
                        <td class="action-buttons">
                            <form method="post" action="" style="display: inline;">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                <button type="button" class="edit-btn" onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)">Edit</button>
                            </form>
                            <form method="post" action="" style="display: inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete this product?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script>
    function previewImage(input) {
        var preview = document.getElementById('preview');
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function editProduct(product) {
        document.querySelector('input[name="action"]').value = 'edit';
        document.querySelector('input[name="name"]').value = product.name;
        document.querySelector('input[name="price"]').value = product.price;
        document.querySelector('textarea[name="description"]').value = product.description;
        document.querySelector('input[name="stock"]').value = product.stock;
        
        // Add hidden input for current image
        let currentImageInput = document.querySelector('input[name="current_image"]');
        if (!currentImageInput) {
            currentImageInput = document.createElement('input');
            currentImageInput.type = 'hidden';
            currentImageInput.name = 'current_image';
            document.querySelector('form').appendChild(currentImageInput);
        }
        currentImageInput.value = product.image;
        
        // Show current image in preview
        let preview = document.getElementById('preview');
        if (product.image) {
            preview.src = '../img/' + product.image;
            preview.style.display = 'block';
        } else {
            preview.style.display = 'none';
        }
        
        // Add hidden input for product ID
        let idInput = document.querySelector('input[name="id"]');
        if (!idInput) {
            idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            document.querySelector('form').appendChild(idInput);
        }
        idInput.value = product.id;
        
        // Change submit button text
        document.querySelector('input[type="submit"]').value = 'Update Product';
        
        // Scroll to form
        document.querySelector('.product-form').scrollIntoView({ behavior: 'smooth' });
    }
    </script>
</body>
</html>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
