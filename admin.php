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
if ($user_role !== 'admin') {
    echo "Access denied: Admins only.";
    exit();
}

// Handle role update form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_role'])) {
    $user_id = $_POST['user_id'];
    $new_role = $_POST['role'];

    // Validate the role
    if (in_array($new_role, ['admin', 'editor', 'user'])) {
        try {
            $query = "UPDATE users SET role = :role WHERE id = :user_id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':role', $new_role, PDO::PARAM_STR);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();

            echo "<p style='color: green;'>User role updated successfully.</p>";
        } catch (PDOException $e) {
            echo "<p style='color: red;'>Error updating role: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color: red;'>Invalid role selected.</p>";
    }
}

// Fetch all users for role management
$query = "SELECT id, username, role FROM users";
$stmt = $pdo->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f0f4f7;
            color: #333;
        }

        .container {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 800px;
        }

        .navbar {
            background-color: #0288d1;
            padding: 10px;
            color: white;
            position: fixed;
            top: 0;
            left: 200px;
            right: 0;
            z-index: 100;
        }

        .sidebar {
            width: 200px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #007BFF;
            padding-top: 20px;
            height: 100%;
        }

        .sidebar a {
            padding: 10px 15px;
            text-decoration: none;
            color: white;
            display: block;
            margin: 10px 0;
        }

        .sidebar a:hover {
            background-color: #0056b3;
        }

        .navbar h3 {
            margin: 0;
        }

        .footer p {
            text-align: center;
            padding: 10px;
            background-color: #0288d1;
            color: white;
        }

        select, button {
            font-size: 16px;
            padding: 10px;
            border-radius: 5px;
            width: 100%;
        }

        select {
            border: 2px solid #0288d1;
        }

        button {
            background-color: #0288d1;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #0277bd;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <a href="dashboard.php">Dashboard</a>
    <a href="edit_profile.php">Edit Profile</a>
    <a href="user_details.php">User Details</a>
    <a href="admin.php">Admin</a>
</div>

<!-- Navbar -->
<div class="navbar">
    <h3>Admin Panel</h3>
</div>

<div class="container mt-5" style="margin-left: 220px;">
    <h2>Manage User Roles</h2>

    <!-- Role Update Form -->
    <form method="POST" action="">
        <div class="mb-3">
            <label for="user_id" class="form-label">Select User</label>
            <select name="user_id" id="user_id" class="form-select" required>
                <option value="">Select a user</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['role']); ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="mb-3">
            <label for="role" class="form-label">Select New Role</label>
            <select name="role" id="role" class="form-select" required>
                <option value="admin">Admin</option>
                <option value="editor">Editor</option>
                <option value="user">User</option>
            </select>
        </div>
        
        <button type="submit" name="update_role">Update Role</button>
    </form>
</div>

<!-- Footer -->
<div class="footer">
    <p>&copy; 2025 Blogs | All Rights Reserved</p>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
