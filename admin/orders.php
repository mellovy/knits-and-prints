<?php
require_once '../config/database.php';
requireAdmin();

// Get filter
$status_filter = isset($_GET['status']) ? clean($_GET['status']) : 'all';

// Get orders
if ($status_filter !== 'all') {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE status = ? ORDER BY created_at DESC");
    $stmt->bind_param("s", $status_filter);
    $stmt->execute();
    $orders = $stmt->get_result();
} else {
    $orders = $conn->query("SELECT * FROM orders ORDER BY created_at DESC");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Knits & Prints</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="admin-page">
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h1 class="page-title">📋 Manage Orders</h1>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="success-message"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        
        <!-- Filter -->
        <div class="upload-form" style="margin-bottom: 30px;">
            <form method="GET" action="">
                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <select name="status" onchange="this.form.submit()">
                            <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Orders</option>
                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="paid" <?php echo $status_filter === 'paid' ? 'selected' : ''; ?>>Paid</option>
                            <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Orders List -->
        <?php if ($orders->num_rows === 0): ?>
            <div class="orders-list">
                <p style="text-align: center; color: #666; padding: 40px;">No orders found.</p>
            </div>
        <?php else: ?>
            <div style="display: grid; gap: 20px;">
                <?php while ($order = $orders->fetch_assoc()): ?>
                    <div style="background: white; border-radius: 20px; padding: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px;">
                            <div>
                                <h2 style="color: #ec407a; margin-bottom: 10px;">Order #<?php echo $order['order_number']; ?></h2>
                                <p style="color: #666; margin-bottom: 5px;">
                                    <strong>Date:</strong> <?php echo date('M d, Y g:i A', strtotime($order['created_at'])); ?>
                                </p>
                                <p style="color: #666;">
                                    <strong>Status:</strong> 
                                    <span class="order-status status-<?php echo $order['status']; ?>" style="display: inline-block; margin-left: 10px;">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </p>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 28px; font-weight: bold; color: #ec407a;">
                                    ₱<?php echo number_format($order['total_amount'], 2); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div style="background: #fce4ec; border-radius: 15px; padding: 20px; margin-bottom: 20px;">
                            <h3 style="color: #333; margin-bottom: 15px;">Customer Information</h3>
                            <p style="margin-bottom: 8px;"><strong>Name:</strong> <?php echo $order['customer_name']; ?></p>
                            <p style="margin-bottom: 8px;"><strong>Phone:</strong> <?php echo $order['customer_phone']; ?></p>
                            <p style="margin-bottom: 8px;"><strong>Address:</strong> <?php echo nl2br($order['customer_address']); ?></p>
                            <p style="margin-bottom: 8px;"><strong>Payment Method:</strong> <?php echo ucfirst($order['payment_method']); ?></p>
                        </div>
                        
                        <?php
                        // Get order items
                        $stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
                        $stmt->bind_param("i", $order['id']);
                        $stmt->execute();
                        $items = $stmt->get_result();
                        ?>
                        
                        <div style="margin-bottom: 20px;">
                            <h3 style="color: #333; margin-bottom: 15px;">Order Items</h3>
                            <div style="display: grid; gap: 10px;">
                                <?php while ($item = $items->fetch_assoc()): ?>
                                    <div style="display: flex; gap: 15px; align-items: center; padding: 10px; background: #f8f8f8; border-radius: 10px;">
                                        <img src="../uploads/products/<?php echo $item['product_image']; ?>" alt="" style="width: 60px; height: 60px; object-fit: cover; border-radius: 10px;">
                                        <div style="flex: 1;">
                                            <strong><?php echo $item['product_name']; ?></strong>
                                            <p style="color: #666; font-size: 14px;">Qty: <?php echo $item['quantity']; ?> × ₱<?php echo number_format($item['price'], 2); ?></p>
                                        </div>
                                        <div style="font-weight: bold; color: #ec407a;">
                                            ₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                        
                        <?php if ($order['payment_proof']): ?>
                            <div style="margin-bottom: 20px;">
                                <h3 style="color: #333; margin-bottom: 15px;">Payment Proof</h3>
                                <img src="../uploads/payments/<?php echo $order['payment_proof']; ?>" alt="Payment Proof" style="max-width: 300px; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); cursor: pointer;" onclick="window.open(this.src, '_blank')">
                            </div>
                        <?php endif; ?>
                        
                        <div style="display: flex; gap: 10px;">
                            <?php if ($order['status'] === 'pending'): ?>
                                <button onclick="updateOrderStatus(<?php echo $order['id']; ?>, 'paid')" class="btn btn-primary">Mark as Paid</button>
                                <button onclick="updateOrderStatus(<?php echo $order['id']; ?>, 'cancelled')" class="btn" style="background: #f44336; color: white;">Cancel Order</button>
                            <?php elseif ($order['status'] === 'paid'): ?>
                                <button onclick="updateOrderStatus(<?php echo $order['id']; ?>, 'completed')" class="btn btn-primary">Mark as Completed</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        function updateOrderStatus(orderId, status) {
            if (!confirm('Update order status to ' + status + '?')) return;
            
            fetch('process/update-order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'order_id=' + orderId + '&status=' + status
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to update order');
                }
            });
        }
    </script>
</body>
</html>