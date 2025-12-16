<?php
require_once '../config/database.php';

if (!isset($_SESSION['order_number'])) {
    header("Location: index.php");
    exit;
}

$order_number = $_SESSION['order_number'];

// Get order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE order_number = ?");
$stmt->bind_param("s", $order_number);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

// Get order items
$stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->bind_param("i", $order['id']);
$stmt->execute();
$order_items = $stmt->get_result();

// Clear session order number
unset($_SESSION['order_number']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Placed - Knits & Prints</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="customer-page">
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="cart-container" style="text-align: center; max-width: 700px; margin: 50px auto;">
            <div style="font-size: 80px; margin-bottom: 20px;">🎉</div>
            <h1 style="color: #ec407a; font-size: 36px; margin-bottom: 15px;">Order Placed Successfully!</h1>
            <p style="font-size: 18px; color: #666; margin-bottom: 30px;">
                Thank you for your order! We'll process your payment and contact you soon.
            </p>
            
            <div style="background: #fce4ec; border-radius: 20px; padding: 30px; margin-bottom: 30px; text-align: left;">
                <h2 style="color: #ec407a; margin-bottom: 20px;">Order Details</h2>
                
                <div style="margin-bottom: 15px;">
                    <strong>Order Number:</strong> #<?php echo $order['order_number']; ?>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <strong>Customer Name:</strong> <?php echo $order['customer_name']; ?>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <strong>Phone:</strong> <?php echo $order['customer_phone']; ?>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <strong>Delivery Address:</strong><br>
                    <?php echo nl2br($order['customer_address']); ?>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <strong>Payment Method:</strong> <?php echo ucfirst($order['payment_method']); ?>
                </div>
                
                <hr style="border: none; border-top: 2px solid #f8bbd0; margin: 20px 0;">
                
                <h3 style="color: #ec407a; margin-bottom: 15px;">Items Ordered</h3>
                
                <?php while ($item = $order_items->fetch_assoc()): ?>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #f8bbd0;">
                        <span><?php echo $item['product_name']; ?> (×<?php echo $item['quantity']; ?>)</span>
                        <span style="font-weight: bold;">₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                    </div>
                <?php endwhile; ?>
                
                <div style="display: flex; justify-content: space-between; font-size: 24px; font-weight: bold; color: #ec407a; margin-top: 20px;">
                    <span>Total Amount:</span>
                    <span>₱<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
            </div>
            
            <div style="background: #fff9c4; border-radius: 15px; padding: 20px; margin-bottom: 30px;">
                <p style="color: #f57f17; font-weight: bold; margin-bottom: 10px;">⏳ Payment Status: Pending Verification</p>
                <p style="color: #666;">We'll verify your payment and update you within 24 hours. Please keep your phone available for our call or message.</p>
            </div>
            
            <a href="index.php" class="btn btn-primary" style="font-size: 18px;">Continue Shopping 🛍️</a>
        </div>
    </div>
</body>
</html>