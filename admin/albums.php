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

// Handle album cover upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_cover') {
    $album_id = (int)$_POST['album_id'];
    
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/products/';
        
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $extension = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
        
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
            $new_name = 'cover_' . uniqid() . '.' . $extension;
            $destination = $upload_dir . $new_name;
            
            if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $destination)) {
                // Get old cover image
                $stmt = $conn->prepare("SELECT cover_image FROM albums WHERE id = ?");
                $stmt->bind_param("i", $album_id);
                $stmt->execute();
                $result = $stmt->get_result()->fetch_assoc();
                
                // Delete old cover if exists
                if ($result && $result['cover_image']) {
                    $old_file = $upload_dir . $result['cover_image'];
                    if (file_exists($old_file)) {
                        unlink($old_file);
                    }
                }
                
                // Update album with new cover
                $stmt = $conn->prepare("UPDATE albums SET cover_image = ? WHERE id = ?");
                $stmt->bind_param("si", $new_name, $album_id);
                
                if ($stmt->execute()) {
                    $message = 'Album cover updated successfully!';
                } else {
                    $error = 'Failed to update album cover';
                }
            } else {
                $error = 'Failed to upload image';
            }
        } else {
            $error = 'Invalid image format. Use JPG, PNG, WEBP, or GIF';
        }
    } else {
        $error = 'Please select an image';
    }
}

// Handle album deletion
if (isset($_GET['delete'])) {
    $album_id = (int)$_GET['delete'];
    
    // Get album cover to delete
    $stmt = $conn->prepare("SELECT cover_image FROM albums WHERE id = ?");
    $stmt->bind_param("i", $album_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    // Delete cover image if exists
    if ($result && $result['cover_image']) {
        $cover_file = '../uploads/products/' . $result['cover_image'];
        if (file_exists($cover_file)) {
            unlink($cover_file);
        }
    }
    
    // Delete album
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
            <h2 style="color: #2d2d2d; margin-bottom: 20px;">Create New Album</h2>
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
            <h2 style="color: #2d2d2d;">All Albums</h2>
            
            <?php if ($albums->num_rows === 0): ?>
                <p style="text-align: center; color: #666; padding: 40px;">No albums yet. Create your first album!</p>
            <?php else: ?>
                <div style="display: grid; gap: 15px;">
                    <?php 
                    $albums->data_seek(0);
                    while ($album = $albums->fetch_assoc()): 
                    ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 20px; background: white; border: 1px solid #FFE5EC; border-radius: 8px;">
                            <div style="display: flex; gap: 20px; align-items: center; flex: 1;">
                                <!-- Album Cover Preview -->
                                <div style="width: 80px; height: 80px; border-radius: 6px; overflow: hidden; background: #FFE5EC; border: 1px solid #FFE5EC;">
                                    <?php if ($album['cover_image']): ?>
                                        <img src="../uploads/products/<?php echo $album['cover_image']; ?>" style="width: 100%; height: 100%; object-fit: cover;" alt="Cover">
                                    <?php else: ?>
                                        <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 32px; opacity: 0.5;">📷</div>
                                    <?php endif; ?>
                                </div>
                                
                                <div>
                                    <h3 style="color: #2d2d2d; margin-bottom: 5px; font-weight: 500;"><?php echo $album['name']; ?></h3>
                                    <p style="color: #666; font-size: 14px;"><?php echo $album['product_count']; ?> products</p>
                                </div>
                            </div>
                            
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <!-- Upload Cover Button -->
                                <button onclick="openCoverUpload(<?php echo $album['id']; ?>)" class="btn" style="background: #C9E4DE; color: #2d2d2d; font-size: 13px; padding: 8px 16px;">
                                    📸 <?php echo $album['cover_image'] ? 'Change' : 'Add'; ?> Cover
                                </button>
                                
                                <a href="products.php?album=<?php echo $album['id']; ?>" class="btn btn-secondary" style="font-size: 13px; padding: 8px 16px;">View Products</a>
                                
                                <a href="?delete=<?php echo $album['id']; ?>" class="btn" style="background: #FFE5EC; color: #d4779b; font-size: 13px; padding: 8px 16px;" onclick="return confirm('Delete this album and all its products?')">Delete</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Cover Upload Modal -->
    <div id="cover-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: white; border-radius: 12px; padding: 32px; max-width: 500px; width: 90%;">
            <h2 style="color: #2d2d2d; margin-bottom: 20px;">Upload Album Cover</h2>
            
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload_cover">
                <input type="hidden" name="album_id" id="cover-album-id">
                
                <div class="form-group">
                    <label>Select Cover Image</label>
                    <input type="file" name="cover_image" accept="image/*" required>
                    <small>Upload a cover image for this album (JPG, PNG, WEBP, GIF)</small>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 24px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Upload Cover</button>
                    <button type="button" onclick="closeCoverUpload()" class="btn" style="background: #f5f5f5; color: #666;">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function openCoverUpload(albumId) {
            document.getElementById('cover-album-id').value = albumId;
            document.getElementById('cover-modal').style.display = 'flex';
        }
        
        function closeCoverUpload() {
            document.getElementById('cover-modal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        document.getElementById('cover-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCoverUpload();
            }
        });
    </script>
</body>
</html>