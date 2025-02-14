<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SESSION['cart'])) {
    try {
        $conn->begin_transaction();

        // Calculate total amount
        $total_amount = 0;
        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            $sql = "SELECT price, stock FROM products WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($product = $result->fetch_assoc()) {
                // Check stock
                if ($product['stock'] < $quantity) {
                    throw new Exception("Not enough stock for product ID: " . $product_id);
                }
                $total_amount += $product['price'] * $quantity;
            }
            $stmt->close();
        }

        // Create order with user_id
        $sql = "INSERT INTO orders (user_id, total_amount) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("id", $_SESSION['user_id'], $total_amount);
        $stmt->execute();
        $order_id = $conn->insert_id;
        $stmt->close();

        // Create order items and update stock
        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            // Get product price
            $sql = "SELECT price FROM products WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();
            $stmt->close();

            // Insert order item
            $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiid", $order_id, $product_id, $quantity, $product['price']);
            $stmt->execute();
            $stmt->close();

            // Update stock
            $sql = "UPDATE products SET stock = stock - ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $quantity, $product_id);
            $stmt->execute();
            $stmt->close();
        }

        $conn->commit();
        // Clear the cart
        $_SESSION['cart'] = array();
        echo "Order placed successfully!";
        header("Refresh: 2; URL=index.php");

    } catch (Exception $e) {
        $conn->rollback();
        echo "Error placing order: " . $e->getMessage();
        header("Refresh: 2; URL=index.php");
    }
} else {
    header("Location: index.php");
}

$conn->close();
