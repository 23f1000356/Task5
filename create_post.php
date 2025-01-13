<?php
session_start();
include('db_connection.php');

// Handle form submission for creating posts
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the post data from the form
    $titles = $_POST['titles'];
    $contents = $_POST['contents'];

    // Insert each post into the database
    for ($i = 0; $i < count($titles); $i++) {
        $query = "INSERT INTO posts (title, content) VALUES (:title, :content)";
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':title', $titles[$i], PDO::PARAM_STR);
        $stmt->bindValue(':content', $contents[$i], PDO::PARAM_STR);
        $stmt->execute();
    }

    // Redirect to the blog posts page after successful submission
    header('Location: dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Posts</title>
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

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        .form-group textarea {
            height: 100px;
        }

        .button-group {
            margin-top: 20px;
        }

        .button-group button {
            padding: 12px 25px;
            font-size: 18px;
            color: white;
            background-color: #0288d1;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .button-group button:hover {
            background-color: #0277bd;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Create New Posts</h1>

        <form method="POST" action="">
            <!-- Allow for multiple posts -->
            <div id="post-container">
                <div class="form-group">
                    <label for="titles[]">Title</label>
                    <input type="text" name="titles[]" placeholder="Post Title" required>
                </div>
                <div class="form-group">
                    <label for="contents[]">Content</label>
                    <textarea name="contents[]" placeholder="Post Content" required></textarea>
                </div>
            </div>

            <div class="button-group">
                <button type="submit">Create Posts</button>
            </div>
        </form>
    </div>

    <script>
        // Add functionality to dynamically add more posts to the form
        let postContainer = document.getElementById('post-container');

        // Function to add new post fields
        function addPost() {
            let newPostDiv = document.createElement('div');
            newPostDiv.innerHTML = `
                <div class="form-group">
                    <label for="titles[]">Title</label>
                    <input type="text" name="titles[]" placeholder="Post Title" required>
                </div>
                <div class="form-group">
                    <label for="contents[]">Content</label>
                    <textarea name="contents[]" placeholder="Post Content" required></textarea>
                </div>
            `;
            postContainer.appendChild(newPostDiv);
        }
    </script>

</body>
</html>
