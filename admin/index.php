<?php
require_once '../config/database.php';
requireAdmin();

// Get statistics
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$total_sales = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status IN ('paid', 'completed')")->fetch_assoc()['total'] ?? 0;
$total_products = $conn->query("SELECT COUNT(*) as count FROM products WHERE is_active = 1 AND is_sold = 0")->fetch_assoc()['count'];

// Get recent orders
$recent_orders = $conn->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Knits & Prints</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="admin-page">
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h1 class="page-title">📊 Admin Dashboard</h1>
        
        <div class="stats-grid">
            <div class="stat-card pink">
                <div class="stat-number"><?php echo $total_orders; ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
            <div class="stat-card rose">
                <div class="stat-number">₱<?php echo number_format($total_sales, 2); ?></div>
                <div class="stat-label">Total Sales</div>
            </div>
            <div class="stat-card purple">
                <div class="stat-number"><?php echo $total_products; ?></div>
                <div class="stat-label">Active Products</div>
            </div>
        </div>
        
        <div class="action-buttons">
            <a href="bulk-upload.php" class="btn btn-primary">📤 Bulk Upload Products</a>
            <a href="albums.php" class="btn btn-secondary">📁 Manage Albums</a>
            <a href="products.php" class="btn btn-tertiary">🖼️ Manage Products</a>
            <a href="orders.php" class="btn btn-quaternary">📋 View Orders</a>
        </div>
        
        <div class="recent-orders">
            <h2>Recent Orders</h2>
            <div class="orders-list">
                <?php while ($order = $recent_orders->fetch_assoc()): ?>
                    <div class="order-card">
                        <div class="order-info">
                            <strong>#<?php echo $order['order_number']; ?></strong>
                            <span><?php echo $order['customer_name']; ?></span>
                            <span>₱<?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                        <div class="order-status status-<?php echo $order['status']; ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</body>
</html>