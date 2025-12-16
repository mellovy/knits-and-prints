<?php
require_once '../config/database.php';

$session_id = session_id();

// Get cart items
$query = "SELECT c.*, p.name, p.image, p.price, p.stock 
          FROM cart c 
          JOIN products p ON c.product_id = p.id 
          WHERE c.session_id = ? AND p.is_active = 1 AND p.is_sold = 0";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $session_id);
$stmt->execute();
$cart_items = $stmt->get_result();

// Calculate total
$total = 0;
$items_array = [];
while ($item = $cart_items->fetch_assoc()) {
    $total += $item['price'] * $item['quantity'];
    $items_array[] = $item;
}

// Redirect if cart is empty
if (empty($items_array)) {
    header("Location: cart.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Knits & Prints</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="customer-page">
    <?php include 'includes/header.php'; ?>
    
    <div class="checkout-container">
        <h1 class="page-title">💳 Checkout</h1>
        
        <div class="checkout-form">
            <form method="POST" action="process/place-order.php" enctype="multipart/form-data">
                <h2 style="color: #ec407a; margin-bottom: 20px;">Customer Information</h2>
                
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="customer_name" required placeholder="Juan Dela Cruz">
                </div>
                
                <div class="form-group">
                    <label>Phone Number *</label>
                    <input type="tel" name="customer_phone" required placeholder="09XX XXX XXXX">
                </div>
                
                <div class="form-group">
                    <label>Delivery Address *</label>
                    <textarea name="customer_address" rows="4" required placeholder="Complete address including street, barangay, city, and postal code"></textarea>
                </div>
                
                <h2 style="color: #ec407a; margin: 30px 0 20px;">Payment Method</h2>
                
                <div class="payment-methods">
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="gcash" required onchange="selectPaymentMethod('gcash')">
                        <div>
                            <strong style="font-size: 18px;">💸 GCash</strong>
                            <p style="color: #666; margin-top: 5px;">Pay via GCash transfer</p>
                        </div>
                    </label>
                    
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="bank" required onchange="selectPaymentMethod('bank')">
                        <div>
                            <strong style="font-size: 18px;">🏦 Bank Transfer</strong>
                            <p style="color: #666; margin-top: 5px;">Pay via bank deposit or online transfer</p>
                        </div>
                    </label>
                </div>
                
                <!-- GCash Payment Details -->
                <div id="gcash-details" class="payment-details" style="display: none;">
                    <h4>GCash Payment Details</h4>
                    <div class="payment-info">
                        <p><strong>Account Name:</strong> Knits & Prints Shop</p>
                        <p><strong>GCash Number:</strong> 0917 123 4567</p>
                        <p><strong>Amount to Pay:</strong> ₱<?php echo number_format($total, 2); ?></p>
                    </div>
                    <div class="qr-code">
                        <p style="margin-bottom: 10px;"><strong>Scan QR Code:</strong></p>
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200'%3E%3Crect width='200' height='200' fill='%23f8bbd0'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-size='16' fill='%23ec407a'%3EGCash QR%3C/text%3E%3C/svg%3E" alt="GCash QR Code">
                        <p style="margin-top: 10px; color: #666; font-size: 14px;">Upload your actual GCash QR code image</p>
                    </div>
                </div>
                
                <!-- Bank Transfer Payment Details -->
                <div id="bank-details" class="payment-details" style="display: none;">
                    <h4>Bank Transfer Details</h4>
                    <div class="payment-info">
                        <p><strong>Bank:</strong> BDO (Banco de Oro)</p>
                        <p><strong>Account Name:</strong> Knits & Prints Shop</p>
                        <p><strong>Account Number:</strong> 1234-5678-9012</p>
                        <p><strong>Amount to Pay:</strong> ₱<?php echo number_format($total, 2); ?></p>
                    </div>
                    <div class="payment-info" style="margin-top: 10px;">
                        <p><strong>Alternative Bank:</strong> BPI</p>
                        <p><strong>Account Number:</strong> 9876-5432-1098</p>
                    </div>
                </div>
                
                <div class="form-group" style="margin-top: 25px;">
                    <label>Upload Proof of Payment (Screenshot) *</label>
                    <input type="file" name="payment_proof" accept="image/*" required>
                    <small>Upload a clear screenshot or photo of your payment receipt</small>
                </div>
                
                <h2 style="color: #ec407a; margin: 30px 0 20px;">Order Summary</h2>
                
                <div style="background: #fce4ec; border-radius: 15px; padding: 20px; margin-bottom: 20px;">
                    <?php foreach ($items_array as $item): ?>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #f8bbd0;">
                            <span><?php echo $item['name']; ?> (×<?php echo $item['quantity']; ?>)</span>
                            <span style="font-weight: bold;">₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                    <div style="display: flex; justify-content: space-between; font-size: 20px; font-weight: bold; color: #ec407a; margin-top: 15px;">
                        <span>Total Amount:</span>
                        <span>₱<?php echo number_format($total, 2); ?></span>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 18px; padding: 15px;">
                    Place Order 🎀
                </button>
            </form>
        </div>
    </div>
    
    <script src="../js/main.js"></script>
</body>
</html>