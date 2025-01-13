<?php
session_start();
include('db_connection.php');

// Initialize variables
$name = $email = $phone = $address = "";

// Check if the user ID is passed via the URL
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    // Fetch the user details from the database based on the ID
    $query = "SELECT * FROM user_details WHERE id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // If the user exists, assign values to variables
    if ($user) {
        $name = $user['name'];
        $email = $user['email'];
        $phone = $user['phone'];
        $address = $user['address'];
    } else {
        // If no user is found, redirect or show an error message
        echo "User not found.";
        exit();
    }
}

// Handle form submission for profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Update the user's details in the database
    $query = "UPDATE user_details SET name = :name, email = :email, phone = :phone, address = :address WHERE id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':name', $_POST['name'], PDO::PARAM_STR);
    $stmt->bindValue(':email', $_POST['email'], PDO::PARAM_STR);
    $stmt->bindValue(':phone', $_POST['phone'], PDO::PARAM_STR);
    $stmt->bindValue(':address', $_POST['address'], PDO::PARAM_STR);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        // Redirect back to user details page after successful update
        header("Location: user_details.php");
        exit();
    } else {
        // Handle any errors during the update
        echo "Error: Unable to update user details.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
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

        .form-group {
            margin-bottom: 15px;
        }

        .footer p {
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

        #sidebar {
            width: 200px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #007BFF;
            padding-top: 20px;
            height: 100%;
        }

        #sidebar a {
            padding: 10px 15px;
            text-decoration: none;
            color: white;
            display: block;
            margin: 10px 0;
        }

        #sidebar a:hover {
            background-color: #0056b3;
        }

        .navbar {
            background-color: #007BFF;
            padding: 10px;
            color: white;
            position: fixed;
            top: 0;
            left: 200px;
            right: 0;
            z-index: 100;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div id="sidebar">
    <a href="dashboard.php">Dashboard</a>
    <a href="edit_profile.php">Edit Profile</a>
    <a href="user_details.php">User Details</a>
    <a href="admin.php">Admin</a>

</div>

<!-- Navbar -->
<div class="navbar">
    <h3>BASIC CRUD APP</h3>
</div>

<div class="container mt-4" style="margin-left: 220px;">
    <h1>Edit Profile</h1>

    <form method="POST" action="">
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
        </div>
        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="text" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($phone); ?>" required>
        </div>
        <div class="form-group">
            <label for="address">Address</label>
            <textarea id="address" name="address" class="form-control" rows="4" required><?php echo htmlspecialchars($address); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Update Profile</button>
    </form>
</div>

<!-- Footer -->
<div class="footer text-center mt-5">
    <p>&copy; 2025 Blogs | All Rights Reserved</p>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>
