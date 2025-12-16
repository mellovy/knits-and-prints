<?php
require_once '../config/database.php';
requireAdmin();

// Get all albums
$albums = $conn->query("SELECT a.*, COUNT(p.id) as product_count FROM albums a LEFT JOIN products p ON a.id = p.album_id GROUP BY a.id ORDER BY a.created_at DESC");

$message = '';
$error = '';

// Handle album creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $album_name = clean($_POST['album_name']);
    
    if (!empty($album_name)) {
        $stmt = $conn->prepare("INSERT INTO albums (name) VALUES (?)");
        $stmt->bind_param("s", $album_name);
        
        if ($stmt->execute()) {
            $message = 'Album created successfully!';
            header("Location: albums.php");
            exit;
        } else {
            $error = 'Failed to create album';
        }
    } else {
        $error = 'Album name is required';
    }
}

// Handle album deletion
if (isset($_GET['delete'])) {
    $album_id = (int)$_GET['delete'];
    
    $stmt = $conn->prepare("DELETE FROM albums WHERE id = ?");
    $stmt->bind_param("i", $album_id);
    
    if ($stmt->execute()) {
        $message = 'Album deleted successfully!';
        header("Location: albums.php");
        exit;
    } else {
        $error = 'Failed to delete album';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Albums - Knits & Prints</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="admin-page">
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h1 class="page-title">📁 Manage Albums</h1>
        
        <?php if ($message): ?>
            <div class="success-message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- Create Album Form -->
        <div class="upload-form" style="margin-bottom: 40px;">
            <h2 style="color: #ec407a; margin-bottom: 20px;">Create New Album</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="create">
                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <input type="text" name="album_name" placeholder="Album Name (e.g., Summer Collection 2024)" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Album</button>
                </div>
            </form>
        </div>
        
        <!-- Albums List -->
        <div class="orders-list">
            <h2>All Albums</h2>
            
            <?php if ($albums->num_rows === 0): ?>
                <p style="text-align: center; color: #666; padding: 40px;">No albums yet. Create your first album!</p>
            <?php else: ?>
                <div style="display: grid; gap: 15px;">
                    <?php while ($album = $albums->fetch_assoc()): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 20px; background: #fce4ec; border-radius: 15px;">
                            <div>
                                <h3 style="color: #333; margin-bottom: 5px;"><?php echo $album['name']; ?></h3>
                                <p style="color: #666;"><?php echo $album['product_count']; ?> products</p>
                            </div>
                            <div style="display: flex; gap: 10px;">
                                <a href="products.php?album=<?php echo $album['id']; ?>" class="btn btn-secondary">View Products</a>
                                <a href="?delete=<?php echo $album['id']; ?>" class="btn" style="background: #f44336; color: white;" onclick="return confirm('Delete this album and all its products?')">Delete</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>