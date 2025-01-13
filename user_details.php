<?php
session_start();
include('db_connection.php');

// Initialize variables
$name = $email = $phone = $address = "";

// Handle form submission for user details
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve user data from the form
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    // Insert user data into the user_details table in the database
    $query = "INSERT INTO user_details (name, email, phone, address) 
              VALUES (:name, :email, :phone, :address)";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->bindValue(':phone', $phone, PDO::PARAM_STR);
    $stmt->bindValue(':address', $address, PDO::PARAM_STR);
    
    // Execute the statement and check if the insertion was successful
    if ($stmt->execute()) {
        // Clear form values after successful submission
        $name = $email = $phone = $address = "";
    } else {
        // Handle any errors during the insertion
        echo "Error: Unable to save user details.";
    }
}

// Retrieve all user details from the database
$query = "SELECT * FROM user_details ORDER BY created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$userDetailsList = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details</title>
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

        .edit-btn {
            background-color: #0288d1;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
        }

        .edit-btn:hover {
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
    <h1>User Details</h1>

    <!-- User details form -->
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
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>

    <!-- Display all user details -->
    <h3 class="mt-4">All User Information</h3>
    <?php if (count($userDetailsList) > 0): ?>
        <ul class="list-group">
            <?php foreach ($userDetailsList as $user): ?>
                <li class="list-group-item">
                    <strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?><br>
                    <strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?><br>
                    <strong>Phone Number:</strong> <?php echo htmlspecialchars($user['phone']); ?><br>
                    <strong>Address:</strong> <?php echo nl2br(htmlspecialchars($user['address'])); ?><br>
                    <!-- Edit Button -->
                    <a href="edit_profile.php?user_id=<?php echo $user['id']; ?>" class="btn btn-primary btn-sm mt-2">Edit</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No user details found.</p>
    <?php endif; ?>

    <!-- Add New User button (Blue color) -->
    <a href="user_details.php" class="btn btn-primary mt-3">Add New User Details</a>
</div>

<!-- Footer -->
<div class="footer text-center mt-5">
    <p>&copy; 2025 Blogs | All Rights Reserved</p>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>
