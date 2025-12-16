<?php
require_once '../../config/database.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../products.php");
    exit;
}

$product_id = (int)$_POST['product_id'];
$name = clean($_POST['name']);
$price = (float)$_POST['price'];
$stock = (int)$_POST['stock'];
$album_id = (int)$_POST['album_id'];
$is_active = isset($_POST['is_active']) ? 1 : 0;

$stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, stock = ?, album_id = ?, is_active = ? WHERE id = ?");
$stmt->bind_param("sdiii", $name, $price, $stock, $album_id, $is_active, $product_id);

if ($stmt->execute()) {
    $_SESSION['message'] = 'Product updated successfully!';
} else {
    $_SESSION['error'] = 'Failed to update product';
}

header("Location: ../products.php");
exit;
?>