<?php
require_once '../config/database.php';

$album_id = (int)$_GET['id'];

// Get album details
$album_query = $conn->prepare("SELECT * FROM albums WHERE id = ?");
$album_query->bind_param("i", $album_id);
$album_query->execute();
$album = $album_query->get_result()->fetch_assoc();

if (!$album) {
    header("Location: index.php");
    exit;
}

// Get products in album
$products_query = $conn->prepare("SELECT * FROM products WHERE album_id = ? AND is_active = 1 AND is_sold = 0 ORDER BY created_at DESC");
$products_query->bind_param("i", $album_id);
$products_query->execute();
$products = $products_query->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $album['name']; ?> - Knits & Prints</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="customer-page">
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <a href="index.php" class="back-link">← Back to Albums</a>
        
        <h1 class="page-title"><?php echo $album['name']; ?></h1>
        
        <div class="products-grid">
            <?php while ($product = $products->fetch_assoc()): ?>
                <div class="product-card" onclick="openLightbox(<?php echo $product['id']; ?>)">
                    <div class="product-image">
                        <img src="../uploads/products/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    
    <!-- Lightbox Modal -->
    <div id="lightbox" class="lightbox">
        <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
        <button class="lightbox-prev" onclick="changeLightbox(-1)">&#10094;</button>
        <button class="lightbox-next" onclick="changeLightbox(1)">&#10095;</button>
        
        <div class="lightbox-content">
            <img id="lightbox-image" src="" alt="">
            <div class="lightbox-info">
                <h3 id="lightbox-name"></h3>
                <div class="lightbox-details">
                    <div>
                        <p class="lightbox-price" id="lightbox-price"></p>
                        <p class="lightbox-stock" id="lightbox-stock"></p>
                    </div>
                    <button class="btn btn-primary" onclick="addToCartFromLightbox()">
                        🛒 Add to Cart
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../js/main.js"></script>
    <script>
        // Store products data
        const products = <?php 
            $products->data_seek(0);
            $products_array = [];
            while ($p = $products->fetch_assoc()) {
                $products_array[] = $p;
            }
            echo json_encode($products_array);
        ?>;
    </script>
</body>
</html>