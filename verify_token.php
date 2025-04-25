<?php
session_start();
include 'db_connection.php';

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['verify_token'])) {
        // Combine all token digits
        $token = $_POST['token1'] . $_POST['token2'] . $_POST['token3'] . 
                 $_POST['token4'] . $_POST['token5'] . $_POST['token6'];

        // Debug: Log the received token
        error_log("Received token: " . $token);

        // Verify token with more detailed error handling
        $stmt = $conn->prepare("SELECT email, expires_at FROM password_resets WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $expires_at = strtotime($row['expires_at']);
            $current_time = time();
            
            // Debug: Log token expiration info
            error_log("Token expires at: " . $row['expires_at']);
            error_log("Current time: " . date('Y-m-d H:i:s', $current_time));
            
            if ($current_time < $expires_at) {
                $_SESSION['reset_email'] = $row['email'];
                $_SESSION['reset_token'] = $token;
                header("Location: reset_password.php");
                exit();
            } else {
                $error = "Token has expired. Please request a new one.";
                // Clean up expired token
                $stmt = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
                $stmt->bind_param("s", $token);
                $stmt->execute();
            }
        } else {
            $error = "Invalid token. Please check the token and try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Reset Token</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
        }

        .header-text {
            width: 100%;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .sign-in {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 40px;
            backdrop-filter: blur(10px);
        }

        .signin-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 40px;
        }

        .signin-image {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .signin-image img {
            max-width: 100%;
            height: auto;
            object-fit: contain;
            border-radius: 10px;
        }

        .signin-form {
            flex: 1;
            padding: 20px;
        }

        .form-title {
            font-size: 28px;
            margin-bottom: 30px;
            color: #2c3e50;
            font-weight: 600;
            text-align: center;
        }

        .token-inputs {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin: 30px 0;
        }

        .token-inputs input {
            width: 60px;
            height: 70px;
            text-align: center;
            font-size: 32px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            background-color: #f8f9fa;
            color: #2c3e50;
            transition: all 0.3s ease;
        }

        .token-inputs input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            transform: translateY(-2px);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-button {
            text-align: center;
            margin-top: 30px;
        }

        .form-submit {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
            width: 100%;
            max-width: 300px;
        }

        .form-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }

        .signup-image-link {
            display: block;
            text-align: center;
            margin-top: 25px;
            color: #666;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .signup-image-link:hover {
            color: #3498db;
        }

        .token-group {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 30px;
        }

        .token-group label {
            margin-bottom: 15px;
            font-size: 16px;
            font-weight: 500;
            color: #2c3e50;
        }

        @media (max-width: 768px) {
            .signin-content {
                flex-direction: column;
            }
            
            .token-inputs input {
                width: 50px;
                height: 60px;
                font-size: 28px;
            }
            
            .sign-in {
                padding: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="header-text">
        <section class="sign-in">
            <div class="container">
                <div class="signin-content">
                    <div class="signin-image">
                        <figure><img src="images/image.png" alt="verify token image"></figure>
                    </div>
                    <div class="signin-form">
                        <h2 class="form-title">Verify Reset Token</h2>
                        <?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>
                        <form method="POST" class="register-form">
                        <div class="form-group token-group">
                            <div class="token-inputs">
                                <input type="text" id="token1" name="token1" maxlength="1" pattern="[0-9]" required autofocus>
                                <input type="text" name="token2" maxlength="1" pattern="[0-9]" required>
                                <input type="text" name="token3" maxlength="1" pattern="[0-9]" required>
                                <input type="text" name="token4" maxlength="1" pattern="[0-9]" required>
                                <input type="text" name="token5" maxlength="1" pattern="[0-9]" required>
                                <input type="text" name="token6" maxlength="1" pattern="[0-9]" required>
                            </div>
                        </div>

                            <div class="form-group form-button">
                                <input type="submit" name="verify_token" class="form-submit" value="Verify Token"/>
                            </div>
                            <a href="login.php" class="signup-image-link">Back to Login</a>
                        </form>

                    </div>
                </div>
            </div>
        </section>
    </div>
    <script>
        // Auto-focus next input when a digit is entered
        document.querySelectorAll('.token-inputs input').forEach((input, index, inputs) => {
    input.addEventListener('input', function() {
        const val = this.value.replace(/[^0-9]/g, '');
        if (val) {
            this.value = val;
            if (index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        } else {
            this.value = '';
        }
    });

    input.addEventListener('keydown', function(e) {
        if (e.key === 'Backspace' && this.value === '' && index > 0) {
            inputs[index - 1].focus();
        }
    });

    input.addEventListener('paste', function(e) {
        e.preventDefault(); // Prevent pasting
    });
});

    </script>
</body>
</html> 