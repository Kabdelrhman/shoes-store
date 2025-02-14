<?php
require_once __DIR__ . '/includes/header.php';

// Get all orders with user information
$sql = "SELECT o.*, u.username 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        ORDER BY o.order_date DESC";
$result = $conn->query($sql);
?>

<h2>All Orders</h2>

<table class="orders-table">
    <thead>
        <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Date</th>
            <th>Total Amount</th>
            <th>Items</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($order = $result->fetch_assoc()): 
            // Get order items
            $sql = "SELECT oi.*, p.name 
                    FROM order_items oi 
                    JOIN products p ON oi.product_id = p.id 
                    WHERE oi.order_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $order['id']);
            $stmt->execute();
            $items = $stmt->get_result();
            $item_count = $items->num_rows;
        ?>
        <tr>
            <td>#<?php echo $order['id']; ?></td>
            <td><?php echo htmlspecialchars($order['username']); ?></td>
            <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
            <td><?php echo $item_count; ?> items</td>
            <td>
                <button onclick="viewOrderDetails(<?php echo $order['id']; ?>)" class="btn">View Details</button>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<!-- Order Details Modal -->
<div id="orderModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div id="orderDetails"></div>
    </div>
</div>

<style>
.orders-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.orders-table th, .orders-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.orders-table th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.4);
}

.modal-content {
    background-color: #fefefe;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 600px;
    border-radius: 8px;
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover {
    color: black;
}
</style>

<script>
function viewOrderDetails(orderId) {
    const modal = document.getElementById('orderModal');
    const orderDetails = document.getElementById('orderDetails');
    
    // Fetch order details using AJAX
    fetch(`get_order_details.php?id=${orderId}`)
        .then(response => response.text())
        .then(data => {
            orderDetails.innerHTML = data;
            modal.style.display = "block";
        });
}

// Close modal when clicking the X
document.querySelector('.close').onclick = function() {
    document.getElementById('orderModal').style.display = "none";
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('orderModal');
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
