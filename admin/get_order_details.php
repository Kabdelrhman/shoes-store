<?php
session_start();
require_once '../config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    die("Unauthorized access");
}

if (!isset($_GET['id'])) {
    die("Order ID not provided");
}

$order_id = intval($_GET['id']);

// Get order details
$sql = "SELECT o.*, u.username, u.email 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE o.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("Order not found");
}

// Get order items
$sql = "SELECT oi.*, p.name, p.image 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result();
?>

<h2>Order #<?php echo $order_id; ?> Details</h2>

<div class="order-info">
    <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['username']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
    <p><strong>Order Date:</strong> <?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?></p>
    <p><strong>Total Amount:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
</div>

<h3>Order Items</h3>
<table class="items-table">
    <thead>
        <tr>
            <th>Product</th>
            <th>Image</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Subtotal</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($item = $items->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($item['name']); ?></td>
            <td>
                <?php if ($item['image']): ?>
                    <img src="../img/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="product-thumbnail">
                <?php else: ?>
                    No Image
                <?php endif; ?>
            </td>
            <td><?php echo $item['quantity']; ?></td>
            <td>$<?php echo number_format($item['price'], 2); ?></td>
            <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<style>
.order-info {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 20px;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

.items-table th, .items-table td {
    padding: 8px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.product-thumbnail {
    max-width: 50px;
    max-height: 50px;
    object-fit: cover;
}
</style>
