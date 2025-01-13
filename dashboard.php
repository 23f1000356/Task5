<?php
session_start();
include('db_connection.php');

// Search functionality
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Pagination logic
$postsPerPage = 4; // Number of posts per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $postsPerPage;

// Modify the query to include the search term if provided
$query = "SELECT * FROM posts WHERE title LIKE :searchTerm OR content LIKE :searchTerm ORDER BY created_at DESC LIMIT :start, :postsPerPage";
$stmt = $pdo->prepare($query);
$stmt->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':postsPerPage', $postsPerPage, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll();

// Get the total number of posts for pagination
$totalPostsStmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE title LIKE :searchTerm OR content LIKE :searchTerm");
$totalPostsStmt->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
$totalPostsStmt->execute();
$totalPosts = $totalPostsStmt->fetchColumn();
$totalPages = ceil($totalPosts / $postsPerPage);

// Check if there are no posts available
$noPosts = count($posts) == 0;

// Handle image deletion
if (isset($_GET['delete_image']) && isset($_GET['post_id'])) {
    $postId = $_GET['post_id'];
    $imageName = $_GET['delete_image'];

    // Get the current images from the database
    $stmt = $pdo->prepare("SELECT images FROM posts WHERE id = :id");
    $stmt->bindValue(':id', $postId, PDO::PARAM_INT);
    $stmt->execute();
    $post = $stmt->fetch();

    // Remove the image from the images string
    $images = explode(',', $post['images']);
    if (($key = array_search($imageName, $images)) !== false) {
        unset($images[$key]);
    }
    $updatedImages = implode(',', $images);

    // Update the database to remove the image
    $updateStmt = $pdo->prepare("UPDATE posts SET images = :images WHERE id = :id");
    $updateStmt->bindValue(':images', $updatedImages, PDO::PARAM_STR);
    $updateStmt->bindValue(':id', $postId, PDO::PARAM_INT);
    $updateStmt->execute();

    // Delete the image from the file system
    $imagePath = 'uploads/images/' . basename($imageName);
    if (file_exists($imagePath)) {
        unlink($imagePath);
    }

    // Redirect to the post view page
    header("Location: dashboard.php?id=$postId");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Posts</title>

    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }

        .container {
            padding: 30px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-size: 36px;
            color: #0288d1;
        }

        .post {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #0288d1;
            border-radius: 8px;
            background-color: #fff;
        }

        .post h2 {
            font-size: 24px;
            color: #0288d1;
        }

        .post p {
            font-size: 16px;
            color: #777;
        }

        .post img {
            max-width: 100%;
            height: auto;
            margin-top: 10px;
        }

        .image-preview {
            position: relative;
            margin-top: 10px;
        }

        .delete-image-btn {
            position: absolute;
            top: 0;
            right: 0;
            color: red;
            font-size: 20px;
            cursor: pointer;
        }

        .pagination {
            margin-top: 20px;
            display: flex;
            justify-content: center;
        }

        .pagination li {
            margin: 0 5px;
        }

        .pagination a {
            text-decoration: none;
            color: #0288d1;
            padding: 8px 15px;
            border: 1px solid #0288d1;
            border-radius: 5px;
        }

        .pagination a:hover {
            background-color: #0288d1;
            color: white;
        }

        .footer p a {
            padding: 10px 20px;
            color: white;
            background-color: #0288d1;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 20px;
            display: inline-block;
        }

        .footer p a:hover {
            background-color: #0277bd;
        }

        .edit-delete-buttons a {
            padding: 8px 15px;
            font-size: 14px;
            color: white;
            background-color: #0288d1;
            border-radius: 5px;
            text-decoration: none;
            margin-right: 10px;
            display: inline-block;
        }

        .edit-delete-buttons a:hover {
            background-color: #0277bd;
        }

        /* Sidebar Styles */
        #sidebar {
            position: fixed;
            top: 0;
            left: -250px; /* Sidebar starts hidden */
            width: 250px;
            height: 100%;
            background-color: #0288d1;
            color: white;
            padding-top: 20px;
            transition: left 0.3s;
            z-index: 1000;
        }

        #sidebar a {
            display: block;
            color: white;
            padding: 15px;
            text-decoration: none;
            font-size: 18px;
        }

        #sidebar a:hover {
            background-color: #0277bd;
        }

        .navbar-brand {
            cursor: pointer;
        }
        .edit-delete-buttons a {
    padding: 8px 15px;
    font-size: 14px;
    color: white;
    background-color: #0288d1;
    border-radius: 5px;
    text-decoration: none;
    margin-right: 10px;
    display: inline-block;
}

.edit-delete-buttons a:hover {
    background-color: #0277bd;
}

        /* When the sidebar is visible */
        #sidebar.show {
            left: 0;
        }

        .container {
            margin-left: 0;
            transition: margin-left 0.3s;
        }

        /* When sidebar is visible, content shifts to the right */
        body.sidebar-open .container {
            margin-left: 250px;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" id="toggleSidebar" href="#">BlogApp</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="create_post.php">Create Post</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
                <form method="GET" action="" class="d-flex ms-auto">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>" class="form-control me-2" placeholder="Search posts" style="width: 300px;">
                    <button class="btn btn-outline-light" type="submit">Search</button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div id="sidebar">
        <a href="dashboard.php">Dashboard</a>
        <a href="edit_profile.php">Edit Profile</a>
        <a href="user_details.php">User Details</a>
        <a href="admin.php">Admin</a>
    </div>

    <!-- Main Content -->
    <div class="container mt-4">
        <h1>Blog Posts</h1>

        <?php if ($noPosts): ?>
            <p>No posts available.</p>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <div class="post">
                    <h2><?php echo htmlspecialchars($post['title']); ?></h2>
                    <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>

                    <?php
                    $images = explode(',', $post['images']);
                    foreach ($images as $image):
                        if ($image):
                    ?>
                            <div class="image-preview">
                                <img src="uploads/images/<?php echo htmlspecialchars($image); ?>" alt="Image">
                                <a href="?delete_image=<?php echo urlencode($image); ?>&post_id=<?php echo $post['id']; ?>" class="delete-image-btn" onclick="return confirm('Are you sure you want to delete this image?')">&times;</a>
                            </div>
                    <?php endif; endforeach; ?>

                    <div class="edit-delete-buttons">
                        <a href="update_post.php?id=<?php echo $post['id']; ?>">Edit</a>
                        <a href="delete_post.php?id=<?php echo $post['id']; ?>" onclick="return confirm('Are you sure you want to delete this post?')">Delete</a>
                        <a href="comments.php?post_id=<?php echo $post['id']; ?>">Comments</a>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Pagination -->
            <div class="pagination">
                <ul class="pagination">
                    <li class="page-item <?php if ($page == 1) echo 'disabled'; ?>">
                        <a class="page-link" href="?page=1&search=<?php echo urlencode($searchTerm); ?>">First</a>
                    </li>
                    <li class="page-item <?php if ($page == 1) echo 'disabled'; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($searchTerm); ?>">Previous</a>
                    </li>
                    <li class="page-item <?php if ($page == $totalPages) echo 'disabled'; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($searchTerm); ?>">Next</a>
                    </li>
                    <li class="page-item <?php if ($page == $totalPages) echo 'disabled'; ?>">
                        <a class="page-link" href="?page=<?php echo $totalPages; ?>&search=<?php echo urlencode($searchTerm); ?>">Last</a>
                    </li>
                </ul>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <div class="footer text-center mt-4">
        <p>&copy; <?php echo date("Y"); ?> BlogApp | <a href="create_post.php">Create Post</a></p>
    </div>

    <!-- Bootstrap JS, Popper.js, and jQuery CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Sidebar Toggle Script -->
    <script>
        document.getElementById('toggleSidebar').addEventListener('click', function () {
            var sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
            document.body.classList.toggle('sidebar-open');
        });
    </script>

</body>
</html>
