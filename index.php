<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome | BASIC CRUD APP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 50px;
            background-color: #f5f5f5;
            color: #333;
            background-image: url('uploads/images/11w.jpg'); /* Add your image path here */
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        h1 {
            font-size: 2.5rem;
            color: #007BFF;
        }

        p {
            font-size: 1.2rem;
            margin: 20px 0;
        }

        .button {
            padding: 10px 20px;
            font-size: 16px;
            margin: 10px;
            text-decoration: none;
            color: white;
            background-color: #007BFF;
            border: none;
            border-radius: 5px;
            display: inline-block;
            transition: background-color 0.3s ease;
        }

        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1>Welcome to BASIC CRUD APP</h1>
    <p>Please choose an option below to get started:</p>
    <a href="register.php" class="button">Register</a>
    <a href="login.php" class="button">Login</a>
</body>
</html>
