<?php
session_start();
include('db_connection.php');

// Check if the post ID is set
if (!isset($_GET['id'])) {
    header("Location: dashboard.php"); // Redirect if no post ID is provided
    exit;
}

$id = $_GET['id'];

// Fetch the post to update
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = :id");
$stmt->execute(['id' => $id]);
$post = $stmt->fetch();

if (!$post) {
    header("Location: dashboard.php"); // Redirect if the post doesn't exist
    exit;
}

// Handle image deletion
if (isset($_GET['delete_image'])) {
    $deleteImage = $_GET['delete_image'];
    $currentImages = $post['images'] ? explode(',', $post['images']) : [];

    // Remove the image from the array
    $currentImages = array_filter($currentImages, function($image) use ($deleteImage) {
        return basename($image) !== $deleteImage;
    });

    // Delete the image file from the server
    $uploadsDir = 'uploads/images/';
    $imagePath = $uploadsDir . $deleteImage;
    if (file_exists($imagePath)) {
        unlink($imagePath); // Delete the image from the server
    }

    // Update the images in the database
    $updatedImages = implode(',', $currentImages);
    $stmt = $pdo->prepare("UPDATE posts SET images = :images WHERE id = :id");
    $stmt->execute(['images' => $updatedImages, 'id' => $id]);

    header("Location: update_post.php?id=$id"); // Redirect after deletion
    exit;
}

// Handle form submission for updating a post
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];

    // Get the current image paths from the database
    $currentImages = $post['images'] ? explode(',', $post['images']) : [];

    // Handle file upload
    $imagePaths = $currentImages; // Keep existing images
    if (!empty($_FILES['images']['name'][0])) {
        $uploadsDir = 'uploads/images/';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0777, true); // Create the directory if it doesn't exist
        }

        // Process the new image upload
        foreach ($_FILES['images']['name'] as $key => $imageName) {
            if ($_FILES['images']['error'][$key] == 0) {
                $tmpName = $_FILES['images']['tmp_name'][$key];
                $newName = time() . '_' . $imageName;
                $targetPath = $uploadsDir . $newName;

                // Move the uploaded file to the uploads directory
                if (move_uploaded_file($tmpName, $targetPath)) {
                    $imagePaths[] = $newName; // Add the new image path to the array
                }
            }
        }
    }

    // Convert the image paths array to a string and update the post
    $images = implode(',', $imagePaths);

    // Update the post in the database
    $stmt = $pdo->prepare("UPDATE posts SET title = :title, content = :content, images = :images WHERE id = :id");
    $stmt->execute(['title' => $title, 'content' => $content, 'images' => $images, 'id' => $id]);

    header("Location: dashboard.php"); // Redirect after update
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Post</title>
    
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            flex-direction: column;
            text-align: center;
        }

        .container {
            width: 100%;
            max-width: 800px;
            padding: 30px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-size: 36px;
            color: #0288d1;
            margin-bottom: 20px;
        }

        .input-group {
            margin-bottom: 15px;
        }

        .input-group input, .input-group textarea {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #0288d1;
        }

        .textarea {
            height: 200px;
        }

        .submit-button {
            padding: 12px;
            font-size: 18px;
            color: white;
            background-color: #0288d1;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .submit-button:hover {
            background-color: #0277bd;
        }

        .footer {
            margin-top: 30px;
            font-size: 14px;
            color: #777;
        }

        .footer a {
            color: #0288d1;
            text-decoration: none;
        }

        .footer a:hover {
            color: #0277bd;
        }

        .image-preview {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .image-preview img {
            max-width: 150px;
            max-height: 150px;
            border-radius: 5px;
            position: relative;
        }

        .image-preview a.delete {
            position: absolute;
            top: 0;
            right: 0;
            color: red;
            font-size: 18px;
            text-decoration: none;
        }

        .image-preview a.delete:hover {
            color: darkred;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Update Post</h1>

        <form method="POST" enctype="multipart/form-data">
            <div class="input-group">
                <input type="text" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>
            </div>
            <div class="input-group">
                <textarea class="textarea" name="content" required><?php echo htmlspecialchars($post['content']); ?></textarea>
            </div>

            <div class="input-group">
                <label for="images">Upload Images</label>
                <input type="file" name="images[]" accept="image/*" multiple>
            </div>

            <?php
            // Display current images if available
            if (!empty($post['images'])):
                $images = explode(',', $post['images']);
                echo '<div class="image-preview">';
                foreach ($images as $image):
                    $imageName = basename($image);
                    echo '<div>';
                    echo '<img src="uploads/images/' . htmlspecialchars($imageName) . '" alt="Post Image">';
                    echo '<a href="update_post.php?id=' . $post['id'] . '&delete_image=' . urlencode($imageName) . '" class="delete" onclick="return confirm(\'Are you sure you want to delete this image?\')">X</a>';
                    echo '</div>';
                endforeach;
                echo '</div>';
            endif;
            ?>

            <button type="submit" class="submit-button">Update Post</button>
        </form>

        <div class="footer">
            <p><a href="index.php">Back to Home</a></p>
        </div>
    </div>

</body>
</html>
