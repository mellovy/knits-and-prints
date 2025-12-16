<?php
require_once '../config/database.php';
requireAdmin();

// Get all albums
$albums = $conn->query("SELECT * FROM albums ORDER BY name ASC");

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $album_id = (int)$_POST['album_id'];
    $default_price = (float)$_POST['default_price'];
    $default_stock = (int)$_POST['default_stock'];
    
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        $upload_dir = '../uploads/products/';
        
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $uploaded_count = 0;
        $files = $_FILES['images'];
        
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $tmp_name = $files['tmp_name'][$i];
                $original_name = $files['name'][$i];
                $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
                
                // Validate image
                if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $new_name = uniqid() . '.' . $extension;
                    $destination = $upload_dir . $new_name;
                    
                    // Compress and save image
                    if (compressImage($tmp_name, $destination, 80)) {
                        // Extract product name from filename
                        $product_name = pathinfo($original_name, PATHINFO_FILENAME);
                        $product_name = ucwords(str_replace(['_', '-'], ' ', $product_name));
                        
                        // Insert product
                        $stmt = $conn->prepare("INSERT INTO products (album_id, name, image, price, stock) VALUES (?, ?, ?, ?, ?)");
                        $stmt->bind_param("issdi", $album_id, $product_name, $new_name, $default_price, $default_stock);
                        
                        if ($stmt->execute()) {
                            $uploaded_count++;
                        }
                    }
                }
            }
        }
        
        $message = "Successfully uploaded $uploaded_count products!";
    } else {
        $error = "Please select at least one image.";
    }
}

// Image compression function
function compressImage($source, $destination, $quality) {
    $info = getimagesize($source);
    
    if ($info['mime'] == 'image/jpeg') {
        $image = imagecreatefromjpeg($source);
    } elseif ($info['mime'] == 'image/png') {
        $image = imagecreatefrompng($source);
    } elseif ($info['mime'] == 'image/webp') {
        $image = imagecreatefromwebp($source);
    } else {
        return false;
    }
    
    // Resize if too large
    $width = imagesx($image);
    $height = imagesy($image);
    $max_size = 1200;
    
    if ($width > $max_size || $height > $max_size) {
        $ratio = $width / $height;
        
        if ($width > $height) {
            $new_width = $max_size;
            $new_height = $max_size / $ratio;
        } else {
            $new_height = $max_size;
            $new_width = $max_size * $ratio;
        }
        
        $new_image = imagecreatetruecolor($new_width, $new_height);
        imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        $image = $new_image;
    }
    
    return imagejpeg($image, $destination, $quality);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Upload - Knits & Prints</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="admin-page">
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h1 class="page-title">📤 Bulk Upload Products</h1>
        
        <?php if ($message): ?>
            <div class="success-message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="upload-form">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Select Album</label>
                    <select name="album_id" required>
                        <option value="">-- Choose Album --</option>
                        <?php while ($album = $albums->fetch_assoc()): ?>
                            <option value="<?php echo $album['id']; ?>">
                                <?php echo $album['name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Default Price (₱)</label>
                        <input type="number" name="default_price" step="0.01" value="500" required>
                    </div>
                    <div class="form-group">
                        <label>Default Stock</label>
                        <input type="number" name="default_stock" value="1" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Upload Images (Multiple)</label>
                    <input type="file" name="images[]" multiple accept="image/*" required>
                    <small>Hold Ctrl/Cmd to select multiple images. Supported: JPG, PNG, WEBP</small>
                </div>
                
                <button type="submit" class="btn btn-primary">Upload Products</button>
            </form>
        </div>
    </div>
</body>
</html>