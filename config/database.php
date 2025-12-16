<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'knits_prints');

// Create connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper function to escape strings
function clean($str) {
    global $conn;
    return $conn->real_escape_string(trim($str));
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['admin_id']);
}

// Redirect if not admin
function requireAdmin() {
    if (!isAdmin()) {
        header("Location: admin.php");
        exit;
    }
}
?>