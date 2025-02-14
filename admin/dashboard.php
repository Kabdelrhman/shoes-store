<?php
require_once __DIR__ . '/includes/header.php';

// Get total number of products
$sql = "SELECT COUNT(*) as total FROM products";
$result = $conn->query($sql);
$total_products = $result->fetch_assoc()['total'];

// Get total number of orders
$sql = "SELECT COUNT(*) as total FROM orders";
$result = $conn->query($sql);
$total_orders = $result->fetch_assoc()['total'];

// Get total number of users
$sql = "SELECT COUNT(*) as total FROM users WHERE is_admin = 0";
$result = $conn->query($sql);
$total_users = $result->fetch_assoc()['total'];

// Get recent orders
$sql = "SELECT o.*, u.username 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        ORDER BY o.order_date DESC 
        LIMIT 5";
$recent_orders = $conn->query($sql);
?>

<div class="dashboard-stats">
    <div class="stat-card">
        <h3>Total Products</h3>
        <p><?php echo $total_products; ?></p>
    </div>
    <div class="stat-card">
        <h3>Total Orders</h3>
        <p><?php echo $total_orders; ?></p>
    </div>
    <div class="stat-card">
        <h3>Total Users</h3>
        <p><?php echo $total_users; ?></p>
    </div>
</div>

<div class="recent-orders">
    <h2>Recent Orders</h2>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($order = $recent_orders->fetch_assoc()): ?>
            <tr>
                <td>#<?php echo $order['id']; ?></td>
                <td><?php echo htmlspecialchars($order['username']); ?></td>
                <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                <td>
                    <a href="view_order.php?id=<?php echo $order['id']; ?>" class="btn">View Details</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<style>
.dashboard-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    text-align: center;
}

.stat-card h3 {
    margin: 0;
    color: #666;
    font-size: 16px;
}

.stat-card p {
    margin: 10px 0 0;
    font-size: 24px;
    font-weight: bold;
    color: #2c3e50;
}

.recent-orders {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.recent-orders h2 {
    margin-top: 0;
    color: #2c3e50;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

th, td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

th {
    background-color: #f8f9fa;
    font-weight: 600;
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
