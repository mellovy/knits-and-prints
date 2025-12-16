<?php
require_once '../config/database.php';
requireAdmin();

// Get filter
$album_filter = isset($_GET['album']) ? (int)$_GET['album'] : 0;

// Get all albums for filter
$albums = $conn->query("SELECT * FROM albums ORDER BY name ASC");

// Get products
if ($album_filter > 0) {
    $stmt = $conn->prepare("SELECT p.*, a.name as album_name FROM products p JOIN albums a ON p.album_id = a.id WHERE p.album_id = ? ORDER BY p.created_at DESC");
    $stmt->bind_param("i", $album_filter);
    $stmt->execute();
    $products = $stmt->get_result();
} else {
    $products = $conn->query("SELECT p.*, a.name as album_name FROM products p JOIN albums a ON p.album_id = a.id ORDER BY p.created_at DESC");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Knits & Prints</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="admin-page">
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <h1 class="page-title">🖼️ Manage Products</h1>
        
        <!-- Filter -->
<div class="filter-dropdown-container">
    <form method="GET" action="" style="width: 100%; display: flex; justify-content: center;">
        <div class="form-group" style="max-width: 400px; width: 100%; margin-bottom: 0;">
            <select name="album" onchange="this.form.submit()">
                <option value="0">📁 All Albums</option>
                <?php 
                $albums->data_seek(0);
                while ($album = $albums->fetch_assoc()): 
                ?>
                    <option value="<?php echo $album['id']; ?>" <?php echo $album_filter == $album['id'] ? 'selected' : ''; ?>>
                        <?php echo $album['name']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
    </form>
</div>

<!-- Products Grid -->
<?php if ($products->num_rows === 0): ?>
    <div class="empty-state-box">
        <div class="empty-state-icon">🖼️</div>
        <p>No products found. Upload some products!</p>
    </div>
<?php else: ?>
            <div class="products-grid">
                <?php while ($product = $products->fetch_assoc()): ?>
                    <div class="product-card" style="cursor: default;">
                        <div class="product-image">
                            <img src="../uploads/products/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                            <?php if ($product['is_sold']): ?>
                                <div style="position: absolute; top: 10px; right: 10px; background: #f44336; color: white; padding: 5px 10px; border-radius: 10px; font-weight: bold; font-size: 12px;">
                                    SOLD
                                </div>
                            <?php elseif (!$product['is_active']): ?>
                                <div style="position: absolute; top: 10px; right: 10px; background: #666; color: white; padding: 5px 10px; border-radius: 10px; font-weight: bold; font-size: 12px;">
                                    HIDDEN
                                </div>
                            <?php endif; ?>
                        </div>
                        <div style="padding: 15px;">
                            <h3 style="font-size: 14px; margin-bottom: 5px; color: #333;"><?php echo $product['name']; ?></h3>
                            <p style="color: #ec407a; font-weight: bold; margin-bottom: 5px;">₱<?php echo number_format($product['price'], 2); ?></p>
                            <p style="color: #666; font-size: 12px; margin-bottom: 10px;">Stock: <?php echo $product['stock']; ?></p>
                            <p style="color: #999; font-size: 11px; margin-bottom: 10px;"><?php echo $product['album_name']; ?></p>
                            <div style="display: flex; gap: 5px;">
                                <button onclick="editProduct(<?php echo $product['id']; ?>)" class="btn btn-secondary" style="flex: 1; padding: 8px; font-size: 12px;">Edit</button>
                                <button onclick="deleteProduct(<?php echo $product['id']; ?>)" class="btn" style="background: #f44336; color: white; padding: 8px 12px; font-size: 12px;">🗑️</button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Edit Product Modal -->
    <div id="edit-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: white; border-radius: 25px; padding: 40px; max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto;">
            <h2 style="color: #ec407a; margin-bottom: 20px;">Edit Product</h2>
            <form id="edit-form" method="POST" action="process/update-product.php">
                <input type="hidden" name="product_id" id="edit-product-id">
                
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" name="name" id="edit-name" required>
                </div>
                
                <div class="form-group">
                    <label>Price (₱)</label>
                    <input type="number" name="price" id="edit-price" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label>Stock</label>
                    <input type="number" name="stock" id="edit-stock" required>
                </div>
                
                <div class="form-group">
                    <label>Album</label>
                    <select name="album_id" id="edit-album">
                        <?php 
                        $albums->data_seek(0);
                        while ($album = $albums->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $album['id']; ?>"><?php echo $album['name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="checkbox" name="is_active" id="edit-active" value="1" style="width: auto;">
                        <span>Product is active (visible to customers)</span>
                    </label>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Save Changes</button>
                    <button type="button" onclick="closeEditModal()" class="btn" style="background: #666; color: white;">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        const productsData = <?php 
            $products->data_seek(0);
            $products_array = [];
            while ($p = $products->fetch_assoc()) {
                $products_array[] = $p;
            }
            echo json_encode($products_array);
        ?>;
        
        function editProduct(id) {
            const product = productsData.find(p => p.id == id);
            if (!product) return;
            
            document.getElementById('edit-product-id').value = product.id;
            document.getElementById('edit-name').value = product.name;
            document.getElementById('edit-price').value = product.price;
            document.getElementById('edit-stock').value = product.stock;
            document.getElementById('edit-album').value = product.album_id;
            document.getElementById('edit-active').checked = product.is_active == 1;
            
            document.getElementById('edit-modal').style.display = 'flex';
        }
        
        function closeEditModal() {
            document.getElementById('edit-modal').style.display = 'none';
        }
        
        function deleteProduct(id) {
            if (!confirm('Delete this product permanently?')) return;
            
            window.location.href = 'process/delete-product.php?id=' + id;
        }
    </script>
</body>
</html>