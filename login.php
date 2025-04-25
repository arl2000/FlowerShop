<?php
session_start();
include 'db_connection.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // --- Try logging in as admin first ---
    $stmt = $conn->prepare("SELECT admin_id, username, password FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $adminResult = $stmt->get_result();

    if ($adminResult && $admin = $adminResult->fetch_assoc()) {
        // ✅ Use password_verify for hashed password
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['username'] = $admin['username'];
            $_SESSION['user_type'] = 'admin';
            header("Location: admin_dashboard.php");
            exit();
        } else {
            $error = "❌ Incorrect password!";
        }
    } else {
        // --- If not admin, try as regular user ---
        $stmt = $conn->prepare("SELECT id, username, email, password FROM users WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $row = $result->fetch_assoc()) {
                if (password_verify($password, $row['password'])) {
                    $_SESSION['user_id'] = $row['id']; // ✅ Use 'id' to match orders page
                    $_SESSION['username'] = $username;
                    $_SESSION['email'] = $row['email']; // Store email in session
                    $_SESSION['user_type'] = 'user';
                    header("Location: homepage.php");
                    exit();
                } else {
                    $error = "❌ Incorrect password!";
                }
            } else {
                $error = "❌ Username not found!";
            }

            $stmt->close();
        } else {
            $error = "❌ SQL Error: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

  <head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link href="https://fonts.googleapis.com/css?family=Raleway:100,300,400,500,700,900" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


    <title>Log in</title>
<!--
SOFTY PINKO
https://templatemo.com/tm-535-softy-pinko
-->

    <!-- Additional CSS Files -->

    <link rel="stylesheet" href="fonts/material-icon/css/material-design-iconic-font.min.css">
    <!-- Main css -->
    <link rel="stylesheet" href="css/style.css">

    </head>
    
    <body>
    

        <!-- ***** Header Text Start ***** -->
        <div class="header-text">
            <section class="sign-in">
                <div class="container">
                    <div class="signin-content">
                        <div class="signin-image">
                            <figure><img src="images/image.png" alt="sing up image"></figure>
                        </div>
    
                        <div class="signin-form">
                            <h2 class="form-title">Sign in</h2>
                            <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
                            <form method="POST" class="register-form" id="login-form">
                                <div class="form-group">
                                    <label for="your_name"><i class="zmdi zmdi-account material-icons-name"></i></label>
                                    <input type="text" name="username" required placeholder="Username"/>
                                </div>
                                <div class="form-group">
                                    <label for="your_pass"><i class="zmdi zmdi-lock"></i></label>
                                    <input type="password" name="password" required placeholder="Password"/>
                                </div>
                                <div class="form-group">
                                    <label class="label-agree-term"><a href="forgot_password.php"><em>Forgot Password?</em></a></label>
                                </div>
                                <div class="form-group form-button">
                                    <input type="submit" name="signin" id="signin" class="form-submit" value="Log in"/>
                                </div>
                                <a href="user_register.php" class="signup-image-link">Create an account</a>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        <!-- ***** Header Text End ***** -->


   
    
    <!-- jQuery -->
    <script src="assets/js/jquery-2.1.0.min.js"></script>

    <!-- Bootstrap -->
    <script src="assets/js/popper.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>

    <!-- Plugins -->
    <script src="assets/js/scrollreveal.min.js"></script>
    <script src="assets/js/waypoints.min.js"></script>
    <script src="assets/js/jquery.counterup.min.js"></script>
    <script src="assets/js/imgfix.min.js"></script> 
    
    <!-- Global Init -->
    <script src="assets/js/custom.js"></script>

  </body>
</html>