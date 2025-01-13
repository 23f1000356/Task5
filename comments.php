<?php
session_start();
include('db_connection.php');

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

// Get the user's role from the session
$user_role = $_SESSION['user']['role'] ?? null;

// Check if the user is an admin
if ($user_role !== 'user') {
    echo "Access denied: Users only.";
    exit();
}

// Get post ID
$postId = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;

// Check if the post exists
$postStmt = $pdo->prepare("SELECT * FROM posts WHERE id = :post_id");
$postStmt->bindValue(':post_id', $postId, PDO::PARAM_INT);
$postStmt->execute();
$post = $postStmt->fetch();

if (!$post) {
    echo "Post not found!";
    exit();
}

// Handle adding a new comment (only for users)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $comment = trim($_POST['comment']);
    $userId = $_SESSION['user']['id']; // Use the user ID from the session

    if ($comment !== '') {
        $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, comment, created_at) VALUES (:post_id, :user_id, :comment, NOW())");
        $stmt->bindValue(':post_id', $postId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':comment', $comment, PDO::PARAM_STR);
        $stmt->execute();
    }
}

// Fetch all comments for the post
$commentsStmt = $pdo->prepare("SELECT comments.*, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE post_id = :post_id ORDER BY created_at DESC");
$commentsStmt->bindValue(':post_id', $postId, PDO::PARAM_INT);
$commentsStmt->execute();
$comments = $commentsStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
        }
        .container {
            margin-top: 30px;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .comment {
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
        }
        .comment:last-child {
            border-bottom: none;
        }
        .comment .meta {
            font-size: 12px;
            color: #666;
        }
        .btn-custom {
            background-color: #007bff;
            color: white;
            border: none;
        }
        .btn-custom:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Navigation buttons -->
    <div class="d-flex justify-content-between mb-4">
        <a href="dashboard.php" class="btn btn-custom">Home</a>
        <a href="logout.php" class="btn btn-custom">Logout</a>
    </div>

    <h1>Comments for: <?php echo htmlspecialchars($post['title']); ?></h1>

    <!-- Add Comment Form -->
    <form method="POST" action="" class="mt-3 mb-3">
        <textarea name="comment" class="form-control" placeholder="Write your comment here..." rows="3" required></textarea>
        <button type="submit" class="btn btn-primary mt-2">Add Comment</button>
    </form>

    <!-- Display All Comments -->
    <h2>All Comments</h2>
    <?php if (count($comments) > 0): ?>
        <?php foreach ($comments as $comment): ?>
            <div class="comment">
                <p><?php echo htmlspecialchars($comment['comment']); ?></p>
                <div class="meta">
                    By <?php echo htmlspecialchars($comment['username']); ?> on <?php echo date('d M Y, h:i A', strtotime($comment['created_at'])); ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No comments yet. Be the first to comment!</p>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
