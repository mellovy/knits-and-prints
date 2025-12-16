<?php
require_once '../config/database.php';

// Get all albums with product count
$query = "SELECT a.*, COUNT(p.id) as item_count 
          FROM albums a 
          LEFT JOIN products p ON a.id = p.album_id AND p.is_active = 1 AND p.is_sold = 0
          GROUP BY a.id 
          ORDER BY a.created_at DESC";
$albums = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Knits & Prints - Shop</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="customer-page">
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h1 class="page-title">✨ Browse Our Collections</h1>
        
        <div class="albums-grid">
            <?php while ($album = $albums->fetch_assoc()): ?>
                <a href="album.php?id=<?php echo $album['id']; ?>" class="album-card">
                    <div class="album-cover">
                        <?php if ($album['cover_image']): ?>
                            <img src="../uploads/products/<?php echo $album['cover_image']; ?>" alt="<?php echo $album['name']; ?>">
                        <?php else: ?>
                            <div class="album-placeholder">📷</div>
                        <?php endif; ?>
                    </div>
                    <div class="album-info">
                        <h3><?php echo $album['name']; ?></h3>
                        <p><?php echo $album['item_count']; ?> items</p>
                    </div>
                </a>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>