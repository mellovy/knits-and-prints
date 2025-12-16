<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$product_id = (int)$_POST['product_id'];
$action = $_POST['action'];
$session_id = session_id();

if ($action === 'remove') {
    // Remove item from cart
    $stmt = $conn->prepare("DELETE FROM cart WHERE session_id = ? AND product_id = ?");
    $stmt->bind_param("si", $session_id, $product_id);
    $stmt->execute();
    
    echo json_encode(['success' => true, 'message' => 'Item removed']);
    exit;
}

if ($action === 'update') {
    $change = (int)$_POST['change'];
    
    // Get current cart item
    $stmt = $conn->prepare("SELECT c.*, p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.session_id = ? AND c.product_id = ?");
    $stmt->bind_param("si", $session_id, $product_id);
    $stmt->execute();
    $cart_item = $stmt->get_result()->fetch_assoc();
    
    if (!$cart_item) {
        echo json_encode(['success' => false, 'message' => 'Item not in cart']);
        exit;
    }
    
    $new_quantity = $cart_item['quantity'] + $change;
    
    if ($new_quantity < 1) {
        echo json_encode(['success' => false, 'message' => 'Quantity must be at least 1']);
        exit;
    }
    
    if ($new_quantity > $cart_item['stock']) {
        echo json_encode(['success' => false, 'message' => 'Not enough stock']);
        exit;
    }
    
    // Update quantity
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE session_id = ? AND product_id = ?");
    $stmt->bind_param("isi", $new_quantity, $session_id, $product_id);
    $stmt->execute();
    
    echo json_encode(['success' => true, 'message' => 'Quantity updated']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>