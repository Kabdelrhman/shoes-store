<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = intval($_POST['add_to_cart']);
    
    // Get product details
    $sql = "SELECT * FROM products WHERE id = ? AND stock > 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();
    
    if ($product) {
        $current_cart_quantity = isset($_SESSION['cart'][$product_id]) ? $_SESSION['cart'][$product_id]['quantity'] : 0;
        
        // Check if adding one more would exceed stock
        if ($current_cart_quantity + 1 <= $product['stock']) {
            // Add to cart or increment quantity
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id]['quantity']++;
            } else {
                $_SESSION['cart'][$product_id] = array(
                    'name' => $product['name'],
                    'price' => floatval($product['price']),
                    'quantity' => 1,
                    'stock' => $product['stock']
                );
            }
            $_SESSION['success_message'] = "Product added to cart successfully!";
        } else {
            $_SESSION['error_message'] = "Sorry, not enough stock available!";
        }
    }
}

// Handle remove from cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_from_cart'])) {
    $product_id = intval($_POST['remove_from_cart']);
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
        $_SESSION['success_message'] = "Product removed from cart!";
    }
}

// Calculate cart total
$cart_total = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_total += floatval($item['price']) * intval($item['quantity']);
}

// Check if user is admin
$sql = "SELECT is_admin FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Shoes Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="header">
        <div class="header-top">
            <div class="user-info">
                Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> 
                | <a href="edit_profile.php">Edit Profile</a>
                <?php if ($user['is_admin']): ?>
                    | <a href="admin/manage_products.php">Manage Products</a>
                <?php endif; ?>
                | <a href="logout.php">Logout</a>
            </div>
        </div>
        <h1>Shoes Store</h1>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="message success">
            <?php 
                echo htmlspecialchars($_SESSION['success_message']); 
                unset($_SESSION['success_message']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="message error">
            <?php 
                echo htmlspecialchars($_SESSION['error_message']); 
                unset($_SESSION['error_message']);
            ?>
        </div>
    <?php endif; ?>

    <div class="cart-section">
        <h2>Shopping Cart</h2>
        <?php if (empty($_SESSION['cart'])): ?>
            <p>Your cart is empty</p>
        <?php else: ?>
            <div class="cart-items">
                <?php foreach ($_SESSION['cart'] as $product_id => $item): ?>
                    <div class="cart-item">
                        <span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                        <span class="item-price">$<?php echo number_format($item['price'], 2); ?></span>
                        <span class="item-quantity">x<?php echo $item['quantity']; ?></span>
                        <span class="item-total">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                        <form method="post" action="" style="display: inline;">
                            <input type="hidden" name="remove_from_cart" value="<?php echo $product_id; ?>">
                            <button type="submit" class="remove-btn">Remove</button>
                        </form>
                    </div>
                <?php endforeach; ?>
                <div class="cart-total">
                    Total: $<?php echo number_format($cart_total, 2); ?>
                </div>
                <?php if ($cart_total > 0): ?>
                    <form method="get" action="checkout.php">
                        <button type="submit" class="checkout-btn">Checkout</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="products-grid">
        <?php
        // Fetch products from database
        $sql = "SELECT * FROM products WHERE stock > 0";
        $result = $conn->query($sql);

        while ($product = $result->fetch_assoc()) {
            echo '<div class="product-card">';
            if (isset($product['image']) && $product['image']) {
                echo '<img src="img/' . htmlspecialchars($product['image']) . '" alt="' . htmlspecialchars($product['name']) . '" class="product-image">';
            } else {
                echo '<div class="no-image">No Image Available</div>';
            }
            echo '<h3>' . htmlspecialchars($product['name']) . '</h3>';
            echo '<p class="price">$' . number_format($product['price'], 2) . '</p>';
            echo '<p class="description">' . htmlspecialchars($product['description']) . '</p>';
            echo '<p class="stock">Stock: ' . $product['stock'] . '</p>';
            
            // Only show add to cart button if product is in stock
            if ($product['stock'] > 0) {
                $current_cart_quantity = isset($_SESSION['cart'][$product['id']]) ? $_SESSION['cart'][$product['id']]['quantity'] : 0;
                if ($current_cart_quantity < $product['stock']) {
                    echo '<form method="post" action="">';
                    echo '<input type="hidden" name="add_to_cart" value="' . $product['id'] . '">';
                    echo '<button type="submit" class="add-to-cart-btn">Add to Cart</button>';
                    echo '</form>';
                } else {
                    echo '<p class="out-of-stock">Maximum stock reached in cart</p>';
                }
            } else {
                echo '<p class="out-of-stock">Out of Stock</p>';
            }
            
            echo '</div>';
        }
        ?>
    </div>
    <?php $conn->close(); ?>
</body>
</html>
