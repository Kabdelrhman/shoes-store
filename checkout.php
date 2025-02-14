<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['cart'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn->begin_transaction();
    try {
        // First verify stock for all items
        foreach ($_SESSION['cart'] as $product_id => $item) {
            $sql = "SELECT stock FROM products WHERE id = ? FOR UPDATE";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();
            
            if (!$product || $product['stock'] < $item['quantity']) {
                throw new Exception("Not enough stock for product ID: " . $product_id);
            }
        }

        // Create order
        $total_amount = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total_amount += floatval($item['price']) * intval($item['quantity']);
        }

        $sql = "INSERT INTO orders (user_id, order_date, total_amount) VALUES (?, NOW(), ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("id", $_SESSION['user_id'], $total_amount);
        $stmt->execute();
        $order_id = $conn->insert_id;

        // Add order items and update stock
        foreach ($_SESSION['cart'] as $product_id => $item) {
            // Add order item
            $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiid", $order_id, $product_id, $item['quantity'], $item['price']);
            $stmt->execute();

            // Update stock
            $sql = "UPDATE products SET stock = stock - ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $item['quantity'], $product_id);
            $stmt->execute();
        }

        // Commit transaction
        $conn->commit();
        
        // Clear cart after successful order
        $_SESSION['cart'] = array();
        $_SESSION['success_message'] = "Order placed successfully!";
        header("Location: index.php");
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = "Error placing order: " . $e->getMessage();
        header("Location: index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="header">
        <h1>Checkout</h1>
        <a href="index.php" class="btn">Back to Store</a>
    </div>

    <div class="checkout-container">
        <h2>Order Summary</h2>
        <div class="order-items">
            <?php foreach ($_SESSION['cart'] as $product_id => $item): ?>
                <div class="order-item">
                    <span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                    <span class="item-quantity">x<?php echo $item['quantity']; ?></span>
                    <span class="item-price">$<?php echo number_format($item['price'], 2); ?></span>
                    <span class="item-total">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="order-total">
            <?php
            $total = 0;
            foreach ($_SESSION['cart'] as $item) {
                $total += floatval($item['price']) * intval($item['quantity']);
            }
            ?>
            <strong>Total: $<?php echo number_format($total, 2); ?></strong>
        </div>

        <form method="post" action="" class="checkout-form">
            <button type="submit" class="checkout-btn">Place Order</button>
        </form>
    </div>
</body>
</html>
