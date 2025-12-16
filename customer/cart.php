<?php
require_once '../config/database.php';

$session_id = session_id();

// Get cart items with product details
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Knits & Prints</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="customer-page">
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h1 class="page-title">🛒 Shopping Cart</h1>
        
        <?php if (empty($items_array)): ?>
            <div class="cart-container">
                <div class="cart-empty">
                    <div class="cart-empty-icon">🛍️</div>
                    <h2>Your cart is empty</h2>
                    <p>Start adding some cute items!</p>
                    <a href="index.php" class="btn btn-primary" style="margin-top: 20px;">Browse Albums</a>
                </div>
            </div>
        <?php else: ?>
            <div class="cart-container">
                <div class="cart-items">
                    <?php foreach ($items_array as $item): ?>
                        <div class="cart-item">
                            <div class="cart-item-image">
                                <img src="../uploads/products/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                            </div>
                            
                            <div class="cart-item-details">
                                <h3><?php echo $item['name']; ?></h3>
                                <p class="cart-item-price">₱<?php echo number_format($item['price'], 2); ?></p>
                                <p style="color: #666; font-size: 14px;">Stock available: <?php echo $item['stock']; ?></p>
                            </div>
                            
                            <div class="cart-item-actions">
                                <div class="quantity-control">
                                    <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['product_id']; ?>, -1)">−</button>
                                    <span class="quantity-display"><?php echo $item['quantity']; ?></span>
                                    <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['product_id']; ?>, 1)">+</button>
                                </div>
                                <button class="remove-btn" onclick="removeFromCart(<?php echo $item['product_id']; ?>)">🗑️ Remove</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="cart-summary">
                    <div class="cart-total">
                        <span>Total:</span>
                        <span class="cart-total-amount">₱<?php echo number_format($total, 2); ?></span>
                    </div>
                    <a href="checkout.php" class="btn btn-primary" style="width: 100%; text-align: center; font-size: 18px;">
                        Proceed to Checkout 💳
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="../js/main.js"></script>
</body>
</html>