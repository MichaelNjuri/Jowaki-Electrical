<?php
// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'jowaki_db';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

// Fetch all orders
$sql = "SELECT id, customer_info, cart, subtotal, tax, delivery_fee, total, delivery_method, delivery_address, payment_method, order_date, status 
        FROM orders 
        ORDER BY order_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Orders | Jowaki Store</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 2rem; background: #f5f5f5; }
        h1 { text-align: center; color: #2c3e50; }
        .orders-table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .orders-table th, .orders-table td { padding: 1rem; border: 1px solid #ecf0f1; text-align: left; }
        .orders-table th { background: #3498db; color: white; }
        .orders-table tr:nth-child(even) { background: #f9f9f9; }
        .order-details { margin-top: 1rem; padding: 1rem; background: #fff; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .btn { padding: 0.5rem 1rem; border: none; border-radius: 4px; cursor: pointer; }
        .btn-primary { background: #3498db; color: white; }
        .btn-secondary { background: #95a5a6; color: white; }
    </style>
</head>
<body>
    <h1>Admin - Customer Orders</h1>
    <table class="orders-table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Order Date</th>
                <th>Total (KSh)</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($order = $result->fetch_assoc()): ?>
                    <?php
                        $customer_info = json_decode($order['customer_info'], true);
                        $customer_name = $customer_info['firstName'] . ' ' . $customer_info['lastName'];
                        $order_date = date('Y-m-d H:i:s', strtotime($order['order_date']));
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['id']); ?></td>
                        <td><?php echo htmlspecialchars($customer_name); ?></td>
                        <td><?php echo htmlspecialchars($order_date); ?></td>
                        <td><?php echo number_format($order['total'], 0); ?></td>
                        <td><?php echo htmlspecialchars($order['status']); ?></td>
                        <td>
                            <button class="btn btn-primary" onclick="toggleDetails(<?php echo $order['id']; ?>)">View Details</button>
                        </td>
                    </tr>
                    <tr id="details-<?php echo $order['id']; ?>" style="display: none;">
                        <td colspan="6">
                            <div class="order-details">
                                <h3>Order #<?php echo htmlspecialchars($order['id']); ?> Details</h3>
                                <p><strong>Customer:</strong> <?php echo htmlspecialchars($customer_name); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($customer_info['email']); ?></p>
                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($customer_info['phone']); ?></p>
                                <p><strong>Address:</strong> <?php echo htmlspecialchars($customer_info['address'] . ', ' . $customer_info['city'] . ($customer_info['postalCode'] ? ', ' . $customer_info['postalCode'] : '')); ?></p>
                                <p><strong>Delivery Method:</strong> <?php echo htmlspecialchars(ucfirst($order['delivery_method'])); ?></p>
                                <p><strong>Delivery Address:</strong> <?php echo htmlspecialchars($order['delivery_address']); ?></p>
                                <p><strong>Payment Method:</strong> <?php echo htmlspecialchars(ucfirst($order['payment_method'])); ?></p>
                                <h4>Items:</h4>
                                <ul>
                                    <?php
                                        $cart = json_decode($order['cart'], true);
                                        foreach ($cart as $item):
                                    ?>
                                        <li><?php echo htmlspecialchars($item['name']); ?> x<?php echo $item['quantity']; ?> - KSh <?php echo number_format($item['price'] * $item['quantity'], 0); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <p><strong>Subtotal:</strong> KSh <?php echo number_format($order['subtotal'], 0); ?></p>
                                <p><strong>Tax (16%):</strong> KSh <?php echo number_format($order['tax'], 0); ?></p>
                                <p><strong>Delivery Fee:</strong> KSh <?php echo number_format($order['delivery_fee'], 0); ?></p>
                                <p><strong>Total:</strong> KSh <?php echo number_format($order['total'], 0); ?></p>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center;">No orders found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
        function toggleDetails(orderId) {
            const detailsRow = document.getElementById(`details-${orderId}`);
            detailsRow.style.display = detailsRow.style.display === 'none' ? 'table-row' : 'none';
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>