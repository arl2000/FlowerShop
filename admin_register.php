<?php
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // ✅ hash password like in user registration

    // Step 1: Check if admin username exists
    $stmt = $conn->prepare("SELECT admin_id FROM admins WHERE username = ?");
    if (!$stmt) {
        die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $error = "Admin username already exists!";
    } else {
        // Step 2: Insert new admin
        $insert_stmt = $conn->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
        if (!$insert_stmt) {
            die("Insert prepare failed: (" . $conn->errno . ") " . $conn->error);
        }

        $insert_stmt->bind_param("ss", $username, $password);
        if ($insert_stmt->execute()) {
            header("Location: user_login.php?success=admin_registered");
            exit();
        } else {
            $error = "Error: " . $insert_stmt->error;
        }

        $insert_stmt->close();
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Register - Heavenly Bloom</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #fff6f2;
            margin: 0;
            padding: 0;
        }

        .form-container {
            max-width: 400px;
            background: #ffe8ec;
            padding: 30px;
            margin: 60px auto;
            border-radius: 20px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #d15e97;
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
            color: #5c5c5c;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            font-size: 16px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            background-color: #d15e97;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
        }

        button:hover {
            background-color: #c04b84;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #d15e97;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="form-container">
    <h2>Register Admin</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST">
        <label>Username</label>
        <input type="text" name="username" required>
        <label>Password</label>
        <input type="password" name="password" required>
        <button type="submit">Register</button>
        <a href="user_login.php" class="back-link">Back to Login</a>
    </form>
</div>
</body>
</html>
