<?php
include 'db_connection.php'; // Ensure this file correctly connects to your database

// 1. Sanitize and Validate Input Data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Basic validation function
function validate_input($data, $field_name, &$errors) {
    if (empty($data)) {
        $errors[$field_name] = ucfirst($field_name) . " is required";
        return false;
    }
    return true;
}

$errors = [];

// Validate product name
$product_name = sanitize_input($_POST['product_name']);
validate_input($product_name, 'product_name', $errors);

// Validate product price
$product_price = sanitize_input($_POST['product_price']);
validate_input($product_price, 'product_price', $errors);
if (!is_numeric($product_price) || $product_price < 0) {
    $errors['product_price'] = "Price must be a non-negative number";
}

$product_description = sanitize_input($_POST['product_description']);
$category_id = sanitize_input($_POST['category_id']);
$occasion_type = sanitize_input($_POST['occasion_type']);
$color = sanitize_input($_POST['color']);
$size = sanitize_input($_POST['size']);
$message = sanitize_input($_POST['message']);
$bouquet_sizes = sanitize_input($_POST['bouquet_sizes']);
$ribbon_colors = sanitize_input($_POST['ribbon_colors']);
$message_price = sanitize_input($_POST['message_price']);

if (!is_numeric($message_price) || $message_price < 0) {
    $errors['message_price'] = "Message Price must be a non-negative number.";
}

$add_ons = sanitize_input($_POST['add_ons']);

// 2. Handle Image Upload (for the main product image)
$product_image_name = 'default.jpg'; // Default image name
if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    $file_name = $_FILES['product_image']['name'];
    $file_size = $_FILES['product_image']['size'];
    $file_tmp = $_FILES['product_image']['tmp_name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    if (in_array($file_ext, $allowed_extensions)) {
        if ($file_size <= 2 * 1024 * 1024) { // 2MB max file size
            $upload_dir = 'uploads/'; // Create an 'uploads' directory in your project root
            $product_image_name = uniqid() . '.' . $file_ext; // Unique name
            $upload_path = $upload_dir . $product_image_name;

            if (move_uploaded_file($file_tmp, $upload_path)) {
                // Image uploaded successfully
            } else {
                $errors['product_image'] = "Failed to upload image";
                $product_image_name = 'default.jpg'; // Ensure default is used on error
            }
        } else {
            $errors['product_image'] = "Image size too large (max 2MB)";
            $product_image_name = 'default.jpg';
        }
    } else {
        $errors['product_image'] = "Invalid file type (only jpg, jpeg, png, gif allowed)";
        $product_image_name = 'default.jpg';
    }
} elseif (isset($_FILES['product_image']) && $_FILES['product_image']['error'] != 4) {
    // Handle other upload errors ( помимо UPLOAD_ERR_NO_FILE )
    $errors['product_image'] = "Error uploading image: " . $_FILES['product_image']['error'];
    $product_image_name = 'default.jpg';
}

// 3. Insert Data into Database
if (empty($errors)) {
    // Use prepared statements to prevent SQL injection
    $sql = "INSERT INTO products (product_name, product_description, category_id, product_price, product_image)
            VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssdss", $product_name, $product_description, $category_id, $product_price, $product_image_name);
        $product_insert_result = mysqli_stmt_execute($stmt);
        $product_id = mysqli_insert_id($conn); // Get the ID of the inserted product
        mysqli_stmt_close($stmt);

        if ($product_insert_result) {
             $customized_sql = "INSERT INTO customized_products (product_id, occasion_type, color, size, add_ons, message, bouquet_sizes, ribbon_colors, message_price, product_name, product_price, product_description, product_image, category_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $customized_stmt = mysqli_prepare($conn, $customized_sql);
            if($customized_stmt){
                mysqli_stmt_bind_param($customized_stmt, "isssssssssdsss", $product_id, $occasion_type, $color, $size, $add_ons, $message, $bouquet_sizes, $ribbon_colors, $message_price, $product_name, $product_price, $product_description, $product_image_name, $category_id);
                $customized_insert_result = mysqli_stmt_execute($customized_stmt);
                mysqli_stmt_close($customized_stmt);
            }

            if ($customized_insert_result) {
                // Redirect to a success page or display a success message
                header("Location: products.php?msg=Bouquet added successfully"); // Redirect with a message
                exit();
            } else {
                $errors['database'] = "Failed to add customized bouquet: " . mysqli_error($conn);
            }

        } else {
            $errors['database'] = "Failed to add product: " . mysqli_error($conn);
        }
    } else {
        $errors['database'] = "Failed to prepare statement: " . mysqli_error($conn);
    }
}

// 4. Handle Errors
if (!empty($errors)) {
    // Display errors to the user (you can customize this part)
    echo "<div style='color:red; margin: 20px; padding: 10px; border: 1px solid red; border-radius: 8px;'>";
    echo "<h3>The following errors occurred:</h3>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li>$error</li>";
    }
    echo "</ul>";
    echo "<p>Please go back and correct the errors.</p>";
    echo "<a href='add_bouquet.php'>Go Back</a>"; // Provide a link back to the form
    echo "</div>";
}

mysqli_close($conn);
?>
