<?php
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../cart.php");
    exit;
}

$session_id = session_id();

// Get form data
$customer_name = clean($_POST['customer_name']);
$customer_phone = clean($_POST['customer_phone']);
$customer_address = clean($_POST['customer_address']);
$payment_method = clean($_POST['payment_method']);

// Validate inputs
if (empty($customer_name) || empty($customer_phone) || empty($customer_address) || empty($payment_method)) {
    $_SESSION['error'] = 'Please fill in all required fields';
    header("Location: ../checkout.php");
    exit;
}

// Handle payment proof upload
$payment_proof = '';
if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../../uploads/payments/';
    
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $extension = strtolower(pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION));
    
    if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
        $payment_proof = uniqid() . '.' . $extension;
        move_uploaded_file($_FILES['payment_proof']['tmp_name'], $upload_dir . $payment_proof);
    }
}

// Get cart items
$query = "SELECT c.*, p.name, p.image, p.price, p.stock 
          FROM cart c 
          JOIN products p ON c.product_id = p.id 
          WHERE c.session_id = ? AND p.is_active = 1 AND p.is_sold = 0";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $session_id);
$stmt->execute();
$cart_items = $stmt->get_result();

// Check if cart is empty
if ($cart_items->num_rows === 0) {
    $_SESSION['error'] = 'Your cart is empty';
    header("Location: ../cart.php");
    exit;
}

// Calculate total and validate stock
$total = 0;
$items_array = [];
while ($item = $cart_items->fetch_assoc()) {
    if ($item['quantity'] > $item['stock']) {
        $_SESSION['error'] = "Not enough stock for {$item['name']}";
        header("Location: ../cart.php");
        exit;
    }
    
    $total += $item['price'] * $item['quantity'];
    $items_array[] = $item;
}

// Generate order number
$order_number = 'KP' . date('Ymd') . rand(1000, 9999);

// Start transaction
$conn->begin_transaction();

try {
    // Create order
    $stmt = $conn->prepare("INSERT INTO orders (order_number, customer_name, customer_phone, customer_address, payment_method, payment_proof, total_amount, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("ssssssd", $order_number, $customer_name, $customer_phone, $customer_address, $payment_method, $payment_proof, $total);
    $stmt->execute();
    $order_id = $conn->insert_id;
    
    // Create order items and update product stock
    foreach ($items_array as $item) {
        // Insert order item
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, product_image, price, quantity) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissdi", $order_id, $item['product_id'], $item['name'], $item['image'], $item['price'], $item['quantity']);
        $stmt->execute();
        
        // Update product stock
        $new_stock = $item['stock'] - $item['quantity'];
        $stmt = $conn->prepare("UPDATE products SET stock = ?, is_sold = IF(? = 0, 1, 0) WHERE id = ?");
        $stmt->bind_param("iii", $new_stock, $new_stock, $item['product_id']);
        $stmt->execute();
    }
    
    // Clear cart
    $stmt = $conn->prepare("DELETE FROM cart WHERE session_id = ?");
    $stmt->bind_param("s", $session_id);
    $stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    // Redirect to success page
    $_SESSION['order_number'] = $order_number;
    header("Location: ../order-success.php");
    exit;
    
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = 'Failed to place order. Please try again.';
    header("Location: ../checkout.php");
    exit;
}
?>