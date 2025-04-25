<?php
session_start();
include 'db_connection.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    
    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Generate 6-digit token
        $token = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Store token in database
        $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $token, $expires);
        
        if ($stmt->execute()) {
            // Send email using PHPMailer
            require 'vendor/autoload.php';
            
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'grixia400@gmail.com'; // Replace with your email
                $mail->Password = 'fwpx upvb sjbv weve'; // Replace with your app password
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                
                // Recipients
                $mail->setFrom('heavenlybloom@gmail.com', 'Heavenly Bloom');
                $mail->addAddress($email);
                
                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request';
                $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/verify_token.php";
                $mail->Body = "Your password reset token is: <strong>$token</strong><br>
                             Please go to <a href='$resetLink'>$resetLink</a> and enter this token along with your email address to reset your password.<br>
                             This token will expire in 1 hour.";
                
                $mail->send();
                $message = "Password reset link has been sent to your email.";
                header("Location: verify_token.php");
                exit();
            } catch (Exception $e) {
                $message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $message = "Error generating reset token.";
        }
    } else {
        $message = "Email not found in our system.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="header-text">
        <section class="sign-in">
            <div class="container">
                <div class="signin-content">
                    <div class="signin-image">
                        <figure><img src="images/image.png" alt="forgot password image"></figure>
                    </div>
                    <div class="signin-form">
                        <h2 class="form-title">Forgot Password</h2>
                        <?php if ($message) echo "<p style='color:green;'>$message</p>"; ?>
                        <form method="POST" class="register-form">
                            <div class="form-group">
                                <label for="email"><i class="zmdi zmdi-email"></i></label>
                                <input type="email" name="email" required placeholder="Your Email"/>
                            </div>
                            <div class="form-group form-button">
                                <input type="submit" class="form-submit" value="Send Reset Link"/>
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