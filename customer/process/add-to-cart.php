<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$product_id = (int)$_POST['product_id'];

// Check if product exists and is available
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1 AND is_sold = 0");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not available']);
    exit;
}

// Check if product has stock
if ($product['stock'] < 1) {
    echo json_encode(['success' => false, 'message' => 'Product out of stock']);
    exit;
}

// Get or create session ID
$session_id = session_id();

// Check if product already in cart
$stmt = $conn->prepare("SELECT * FROM cart WHERE session_id = ? AND product_id = ?");
$stmt->bind_param("si", $session_id, $product_id);
$stmt->execute();
$cart_item = $stmt->get_result()->fetch_assoc();

if ($cart_item) {
    // Update quantity
    $new_quantity = $cart_item['quantity'] + 1;
    
    if ($new_quantity > $product['stock']) {
        echo json_encode(['success' => false, 'message' => 'Not enough stock']);
        exit;
    }
    
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_quantity, $cart_item['id']);
    $stmt->execute();
} else {
    // Add new item to cart
    $stmt = $conn->prepare("INSERT INTO cart (session_id, product_id, quantity) VALUES (?, ?, 1)");
    $stmt->bind_param("si", $session_id, $product_id);
    $stmt->execute();
}

echo json_encode(['success' => true, 'message' => 'Product added to cart']);
?>