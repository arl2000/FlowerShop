<?php
session_start();
include 'db_connection.php';

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header("Location: user_login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars(trim($_POST["name"]));
    $address = htmlspecialchars(trim($_POST["address"]));
    $phone = $_POST["phone"];

    // Validate phone number format
    if (!preg_match("/^\+63\d{9}$/", $phone)) {
        $error = "Phone number must start with +63 and be exactly 11 digits.";
    } else {
        // Update user info in the database
        if (!empty($_FILES["profile_image"]["name"])) {
            $imageName = basename($_FILES["profile_image"]["name"]);
            $targetDir = "uploads/profile_images/";
            $targetFile = $targetDir . $imageName;

            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($_FILES["profile_image"]["type"], $allowedTypes)) {
                $error = "Only JPEG, PNG, and GIF files are allowed.";
            } elseif ($_FILES["profile_image"]["size"] > 2 * 1024 * 1024) {
                $error = "File size should not exceed 2MB.";
            } else {
                if (!move_uploaded_file($_FILES["profile_image"]["tmp_name"], $targetFile)) {
                    $error = "Error uploading the file.";
                } else {
                    $sql = "UPDATE users SET name=?, address=?, phone=?, profile_image=? WHERE id=?";
                    if ($stmt = $conn->prepare($sql)) {
                        $stmt->bind_param("ssssi", $name, $address, $phone, $targetFile, $user_id);
                        $stmt->execute();
                        $success = "Profile updated successfully!";
                    } else {
                        $error = "Error preparing the SQL query: " . $conn->error;
                    }
                }
            }
        } else {
            $sql = "UPDATE users SET name=?, address=?, phone=? WHERE id=?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("sssi", $name, $address, $phone, $user_id);
                $stmt->execute();
                $success = "Profile updated successfully!";
            } else {
                $error = "Error preparing the SQL query: " . $conn->error;
            }
        }
    }
}

// Always re-fetch updated user info
$sql = "SELECT * FROM users WHERE id=?";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    if (!$user) {
        die("User not found.");
    }
} else {
    $error = "Error preparing the SQL query: " . $conn->error;
}

// Fallback for profile image
$profileImage = (!empty($user['profile_image']) && file_exists($user['profile_image']))
    ? $user['profile_image']
    : 'uploads/default_profile.png';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #fff8f2;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 10px #ffdde1;
        }

        h2 {
            text-align: center;
            color: #c94f7c;
        }

        input[type="text"], input[type="file"] {
            width: 100%;
            padding: 10px;
            margin: 12px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        label {
            font-weight: bold;
            color: #555;
        }

        .profile-pic {
            text-align: center;
            margin: 20px 0;
        }

        .profile-pic img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #ffd3e0;
        }

        button {
            background-color: #c94f7c;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }

        .success {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        input[disabled] {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>
<?php include 'navi.php'; ?>

<div class="container">
    <h2>My Profile</h2>
    <?php if (isset($success)) echo "<div class='success'>$success</div>"; ?>
    <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>

    <form method="POST" enctype="multipart/form-data" id="profileForm">
        <div class="profile-pic">
        <img src="<?= htmlspecialchars($profileImage) ?>" alt="Profile Picture">

        </div>

        <label for="name">Full Name</label>
        <input type="text" name="name" id="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" disabled>
        <label for="username">Username</label>
<input type="text" id="username" value="<?= htmlspecialchars($user['username'] ?? '') ?>" disabled>

        <label for="address">Address (House Number, Street Name, Apartment/Unit Number (if applicable), Barangay/Neighborhood, City/Municipality, Province, Postal Code)</label>
        <input type="text" name="address" id="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>" disabled>

        <label for="phone">Phone Number</label>
        <input type="text" name="phone" id="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" disabled>

        <label for="profile_image">Profile Picture</label>
        <input type="file" name="profile_image" id="profile_image" disabled>

        <div id="buttonContainer">
            <button type="button" id="editButton" onclick="enableEditing()">Edit</button>
            <button type="submit" id="saveButton" style="display: none;">Save Changes</button>
        </div>
    </form>
</div>

<script>
    function enableEditing() {
        document.getElementById("name").disabled = false;
        document.getElementById("address").disabled = false;
        document.getElementById("phone").disabled = false;
        document.getElementById("profile_image").disabled = false;
        document.getElementById("editButton").style.display = "none";
        document.getElementById("saveButton").style.display = "block";
    }
</script>

</body>
</html>
