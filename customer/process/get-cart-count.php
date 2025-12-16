<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

$session_id = session_id();

$stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE session_id = ?");
$stmt->bind_param("s", $session_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

$count = $result['total'] ?? 0;

echo json_encode(['count' => (int)$count]);
?>