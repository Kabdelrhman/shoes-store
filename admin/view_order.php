<?php
require_once __DIR__ . '/includes/header.php';

if (!isset($_GET['id'])) {
    header("Location: view_orders.php");
    exit;
}

$order_id = intval($_GET['id']);

// Get order details with customer information
$sql = "SELECT o.*, u.username, u.email 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE o.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    $_SESSION['error_message'] = "Order not found";
    header("Location: view_orders.php");
    exit;
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

<div class="order-details">
    <div class="order-header">
        <h2>Order #<?php echo $order_id; ?> Details</h2>
        <a href="view_orders.php" class="btn back-btn">← Back to Orders</a>
    </div>

    <div class="customer-info">
        <h3>Customer Information</h3>
        <table class="info-table">
            <tr>
                <th>Customer Name:</th>
                <td><?php echo htmlspecialchars($order['username']); ?></td>
            </tr>
            <tr>
                <th>Email:</th>
                <td><?php echo htmlspecialchars($order['email']); ?></td>
            </tr>
            <tr>
                <th>Order Date:</th>
                <td><?php echo date('F j, Y g:i A', strtotime($order['order_date'])); ?></td>
            </tr>
            <tr>
                <th>Total Amount:</th>
                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
            </tr>
        </table>
    </div>

    <div class="order-items">
        <h3>Order Items</h3>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Image</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total = 0;
                while ($item = $items->fetch_assoc()): 
                    $subtotal = $item['price'] * $item['quantity'];
                    $total += $subtotal;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td>
                        <?php if ($item['image']): ?>
                            <img src="../img/<?php echo htmlspecialchars($item['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                 class="product-thumbnail">
                        <?php else: ?>
                            No Image
                        <?php endif; ?>
                    </td>
                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>$<?php echo number_format($subtotal, 2); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-right">Total:</th>
                    <td>$<?php echo number_format($total, 2); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<style>
.order-details {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.back-btn {
    text-decoration: none;
    color: #fff;
    background: #6c757d;
    padding: 8px 16px;
    border-radius: 4px;
}

.back-btn:hover {
    background: #5a6268;
}

.customer-info {
    margin-bottom: 30px;
}

.info-table {
    width: 100%;
    max-width: 600px;
    margin-top: 15px;
}

.info-table th {
    width: 150px;
    text-align: left;
    padding: 8px;
    background-color: #f8f9fa;
}

.info-table td {
    padding: 8px;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

.items-table th,
.items-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #dee2e6;
}

.items-table th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.items-table tfoot {
    font-weight: bold;
}

.items-table tfoot td {
    border-top: 2px solid #dee2e6;
}

.product-thumbnail {
    max-width: 50px;
    max-height: 50px;
    object-fit: cover;
}

.text-right {
    text-align: right;
}

h3 {
    color: #2c3e50;
    margin-top: 0;
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
