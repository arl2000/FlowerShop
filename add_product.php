<?php
include 'db_connection.php';
include 'navbar.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $product_price = $_POST['product_price'];
    $product_description = mysqli_real_escape_string($conn, $_POST['product_description']);
    $original_stock = (int)$_POST['original_stock'];

    // Handle image upload
    $image = $_FILES['product_image']['name'];
    $image_tmp = $_FILES['product_image']['tmp_name'];
    $upload_dir = "uploads/";

    if (!empty($image)) {
        move_uploaded_file($image_tmp, $upload_dir . $image);

        $sql = "INSERT INTO products (product_name, product_price, product_description, product_image, original_stock, stock_count) 
                VALUES ('$product_name', '$product_price', '$product_description', '$image', '$original_stock', '$original_stock')";

        if (mysqli_query($conn, $sql)) {
            header("Location: product.php");
            exit();
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    } else {
        echo "Image is required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product - Heavenly Bloom</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #fff6f2;
            margin: 0;
            padding: 0;
        }

        .form-container {
            max-width: 600px;
            background: #ffe8ec;
            padding: 30px;
            margin: 50px auto;
            border-radius: 20px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #d15e97;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
            color: #5c5c5c;
        }

        input[type="text"],
        input[type="number"],
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            margin-top: 5px;
            font-size: 16px;
        }

        input[type="file"] {
            margin-top: 10px;
        }

        button {
            margin-top: 25px;
            padding: 12px 20px;
            background-color: #d15e97;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background-color: #c04b84;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #d15e97;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div style="position: absolute; top: 20px; right: 20px;">
    <button type="button" style="padding: 6px 12px; font-size: 12px; border-radius: 6px; background-color: #d15e97; color: white; border: none; cursor: pointer;" onclick="window.location.href='add_bouquet.php'">✨ Customized ✨</button>
</div>

<div class="form-container">
    <h2><i class="fas fa-plus"></i> Add New Product</h2>
    <form method="POST" enctype="multipart/form-data">
        <label for="product_name">Product Name</label>
        <input type="text" name="product_name" required>

        <label for="product_price">Price (₱)</label>
        <input type="number" step="0.01" name="product_price" required>

        <label for="original_stock">Original Stock</label>
        <input type="number" name="original_stock" min="0" required>

        <label for="product_description">Description</label>
        <textarea name="product_description" rows="4" required></textarea>

        <label for="product_image">Upload Image</label>
        <input type="file" name="product_image" accept="image/*" required>

        <button type="submit"><i class="fas fa-check-circle"></i> Save Product</button>
        <a class="back-link" href="product.php">← Back to Product List</a>
    </form>
</div>

</body>
</html>
