<?php
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $password = $_POST['pass'];
    $repassword = $_POST['re_pass'];
    
    // Phone number validation
    if(!preg_match('/^09\d{9}$/', $phone)) {
        $error = "Phone number must be 11 digits and start with 09.";
    }
    // Password validation
    else {
        $uppercase = preg_match('/[A-Z]/', $password);
        $lowercase = preg_match('/[a-z]/', $password);
        $special_char = preg_match('/[^A-Za-z0-9]/', $password);
        
        // Password requirements check
        if(!$uppercase || !$lowercase || !$special_char) {
            $error = "Password must contain at least 1 uppercase letter, 1 lowercase letter, and 1 special character.";
        }
        // Confirm passwords match
        else if($password != $repassword) {
            $error = "Passwords do not match.";
        }
        else {
            // Hash password only if it passes validation
            $password = password_hash($password, PASSWORD_DEFAULT);
            
            // Step 1: Check if username exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            if (!$stmt) {
                die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
            }

            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error = "Username or email already exists!";
            } else {
                // Step 2: Insert new user
                $insert_stmt = $conn->prepare("INSERT INTO users (username, name, email, password, address, phone) VALUES (?, ?, ?, ?, ?, ?)");
                if (!$insert_stmt) {
                    die("Insert prepare failed: (" . $conn->errno . ") " . $conn->error);
                }

                $insert_stmt->bind_param("ssssss", $username, $name, $email, $password, $address, $phone);
                if ($insert_stmt->execute()) {
                    header("Location: user_login.php?success=registered");
                    exit();
                } else {
                    $error = "Error: " . $insert_stmt->error;
                }

                $insert_stmt->close();
            }

            $stmt->close();
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
    

   
            <section class="signup">
                <div class="container">
                    <div class="signup-content">
                        <div class="signup-form">
                            <h2 class="form-title">Register</h2>
                            <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
                            <form method="POST" class="register-form" id="register-form">
                                <div class="form-group">
                                    <label for="username"><i class="zmdi zmdi-account material-icons-name"></i></label>
                                    <input type="text" name="username" id="username" placeholder="Username"/>
                                </div>
                                <div class="form-group">
                                    <label for="name"><i class="zmdi zmdi-account material-icons-name"></i></label>
                                    <input type="text" name="name" id="name" placeholder="Your Name"/>
                                </div>
                                <div class="form-group">
                                    <label for="email"><i class="zmdi zmdi-email"></i></label>
                                    <input type="email" name="email" id="email" placeholder="Email"/>
                                </div>
                                <div class="form-group">
                                    <label for="address"><i class="zmdi zmdi-pin"></i></label>
                                    <input type="text" name="address" id="address" placeholder="Address"/>
                                </div>
                                <div class="form-group">
                                    <label for="phone"><i class="zmdi zmdi-phone"></i></label>
                                    <input type="text" name="phone" id="phone" placeholder="Phone (11 digits starting with 09)"/>
                                </div>
                                <div class="form-group">
                                    <label type="password" name="password"><i class="zmdi zmdi-lock"></i></label>
                                    <input type="password" name="pass" id="pass" placeholder="Password"/>
                                    <small id="passwordHelp" class="form-text text-muted">Must contain at least 1 uppercase letter, 1 lowercase letter, and 1 special character.</small>
                                </div>
                                <div class="form-group">
                                    <label for="re-pass"><i class="zmdi zmdi-lock-outline"></i></label>
                                    <input type="password" name="re_pass" id="re_pass" placeholder="Repeat your password"/>
                                </div>
                                <div class="form-group form-button">
                                    <input type="submit" name="signup" id="signup" class="form-submit" value="Register"/>
                                </div>
                            </form>
                        </div>
                        <div class="signup-image">
                            <figure><img src="images/signup-image.jpg" alt="sing up image"></figure>
                            <a href="user_login.php" class="signup-image-link">Already have an account</a>
                        </div>
                    </div>
                </div>
            </section>




   
    
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
    
    <!-- Password validation script -->
    <script>
        document.getElementById('register-form').addEventListener('submit', function(event) {
            const password = document.getElementById('pass').value;
            const rePass = document.getElementById('re_pass').value;
            const phone = document.getElementById('phone').value;
            
            // Validate phone number
            const phoneRegex = /^09\d{9}$/;
            if (!phoneRegex.test(phone)) {
                event.preventDefault();
                alert('Phone number must be 11 digits and start with 09');
                return false;
            }
            
            const hasUpperCase = /[A-Z]/.test(password);
            const hasLowerCase = /[a-z]/.test(password);
            const hasSpecialChar = /[^A-Za-z0-9]/.test(password);
            
            if (!hasUpperCase || !hasLowerCase || !hasSpecialChar) {
                event.preventDefault();
                alert('Password must contain at least 1 uppercase letter, 1 lowercase letter, and 1 special character');
                return false;
            }
            
            if (password !== rePass) {
                event.preventDefault();
                alert('Passwords do not match');
                return false;
            }
        });
    </script>

  </body>
</html>