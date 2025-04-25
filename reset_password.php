<?php
session_start();
include 'db_connection.php';

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashed_password, $email);
        
        if ($stmt->execute()) {
            $message = "Password has been reset successfully. You can now login with your new password.";
            header("Location: login.php");
            exit();
        } else {
            $error = "Error updating password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="header-text">
        <section class="sign-in">
            <div class="container">
                <div class="signin-content">
                    <div class="signin-image">
                        <figure><img src="images/image.png" alt="reset password image"></figure>
                    </div>
                    <div class="signin-form">
                        <h2 class="form-title">Reset Password</h2>
                        <?php if ($message) echo "<p style='color:green;'>$message</p>"; ?>
                        <?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>
                        <form method="POST" class="register-form">
                            <div class="form-group">
                                <label for="email"><i class="zmdi zmdi-email"></i></label>
                                <input type="email" name="email" required placeholder="Your Email"/>
                            </div>
                            <div class="form-group">
                                <label for="new_password"><i class="zmdi zmdi-lock"></i></label>
                                <input type="password" name="new_password" required placeholder="New Password"/>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password"><i class="zmdi zmdi-lock"></i></label>
                                <input type="password" name="confirm_password" required placeholder="Confirm New Password"/>
                            </div>
                            <div class="form-group form-button">
                                <input type="submit" class="form-submit" value="Reset Password"/>
                            </div>
                            <a href="login.php" class="signup-image-link">Back to Login</a>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
</body>
</html> 