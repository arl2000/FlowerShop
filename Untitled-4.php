<?php
include 'db_connection.php';
include 'navbar.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $product_price = $_POST['product_price'];
    $product_description = mysqli_real_escape_string($conn, $_POST['product_description']);
    $product_image_name = 'default.jpg'; // Default value
    $bouquet_sizes = mysqli_real_escape_string($conn, $_POST['bouquet_sizes']);
    $ribbon_colors = mysqli_real_escape_string($conn, $_POST['ribbon_colors']);
    $bouquet_category = (int) $_POST['bouquet_category'];
    $occasion_type = mysqli_real_escape_string($conn, $_POST['occasion_type']);
    $color = mysqli_real_escape_string($conn, $_POST['color']);
    $size = mysqli_real_escape_string($conn, $_POST['size']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    $image = $_FILES['product_image']['name'];
    $image_tmp = $_FILES['product_image']['tmp_name'];
    $upload_dir = "uploads/";

    if (!empty($image)) {
        $product_image_name = uniqid() . '_' . basename($image);
        $upload_path = $upload_dir . $product_image_name;
        if (!move_uploaded_file($image_tmp, $upload_path)) {
            echo "Failed to upload product image.";
            exit();
        }
    }

    $add_ons = '';
    if (!empty($_POST['addon_name'])) {
        $addons_arr = [];
        foreach ($_POST['addon_name'] as $key => $addon_name) {
            $addon_quantity = $_POST['addon_quantity'][$key];
            $addon_image = $_FILES['addon_image']['name'][$key] ?? '';
            $addon_tmp = $_FILES['addon_image']['tmp_name'][$key] ?? '';
            $addon_filename = '';
            if (!empty($addon_image)) {
                $addon_filename = uniqid() . '_' . basename($addon_image);
                move_uploaded_file($addon_tmp, $upload_dir . $addon_filename);
            }
            if (!empty($addon_name) && !empty($addon_quantity)) {
                $addons_arr[] = [
                    'name' => $addon_name,
                    'quantity' => $addon_quantity,
                    'image' => $addon_filename
                ];
            }
        }
        $add_ons = mysqli_real_escape_string($conn, json_encode($addons_arr));
    }

    $insert_customized = "INSERT INTO customized_products (
        product_name, product_price, product_description, product_image,
        occasion_type, color, size, add_ons, message,
        bouquet_sizes, ribbon_colors,
        created_at, updated_at
    ) VALUES (
        '$product_name', '$product_price', '$product_description', '$product_image_name',
        '$occasion_type', '$color', '$size', '$add_ons', '$message',
        '$bouquet_sizes', '$ribbon_colors',
        CURRENT_TIMESTAMP(), CURRENT_TIMESTAMP()
    )";

    if (mysqli_query($conn, $insert_customized)) {
        header("Location: product.php"); // Redirect as needed
        exit();
    } else {
        echo "Error inserting customized product: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Seller Customize - Heavenly Bloom</title>
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
        textarea,
        select {
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

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .product-card {
            background-color: #fff;
            border: 1px solid #eee;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }

        .product-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .product-details {
            padding: 15px;
        }

        .product-details h3 {
            margin: 0 0 10px;
            font-size: 18px;
            color: #d15e97;
        }

        .product-details p {
            margin: 0 0 8px;
            font-size: 14px;
        }

        .price {
            font-weight: bold;
            color: #333;
            margin-top: 10px;
        }

        .action-btns a {
            margin-right: 10px;
            color: #d15e97;
            text-decoration: none;
        }

        .action-btns a:hover {
            color: #b44c7b;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2><i class="fas fa-plus"></i> Add New Customized Product</h2>
        <h2 style="text-align:center; color:#d15e97; margin-top: 20px;"><i class="fas fa-gift"></i> Customized Bouquets</h2>
        <form method="POST" enctype="multipart/form-data">
            <label for="product_name">Product Name</label>
            <input type="text" name="product_name" required>

            <label for="product_price">Price (₱)</label>
            <input type="number" step="0.01" name="product_price" required>

            <label for="product_description">Description</label>
            <textarea name="product_description" rows="4" required></textarea>

            <label for="product_image">Upload Image</label>
            <input type="file" name="product_image" accept="image/*" required>

            <label for="bouquet_sizes">Available Bouquet Sizes (Separate by comma)</label>
            <input type="text" name="bouquet_sizes" placeholder="e.g., Small, Medium, Large, XL" required>

            <label for="ribbon_colors">Available Ribbon Colors (Separate by comma)</label>
            <input type="text" name="ribbon_colors" placeholder="e.g., Red, White, Lavender" required>

            <label for="bouquet_category">Category</label>
            <select name="bouquet_category" required>
                <option value="">-- Select Category --</option>
                <?php
                $cat_result = mysqli_query($conn, "SELECT * FROM categories");
                while ($cat = mysqli_fetch_assoc($cat_result)) {
                    echo '<option value="' . $cat['id'] . '">' . htmlspecialchars($cat['name']) . '</option>';
                }
                ?>
            </select>

            <label for="occasion_type">Occasion Type</label>
            <input type="text" name="occasion_type" required>

            <label for="color">Bouquet Color</label>
            <input type="text" name="color" required>

            <label for="size">Bouquet Size</label>
            <input type="text" name="size" required>

           

            <div id="addon-wrapper">
                <label>Add-ons (Optional)</label>
                <div class="addon-group">
                    <input type="text" name="addon_name[]" placeholder="Add-on Name">
                    <input type="number" name="addon_quantity[]" placeholder="Quantity" min="1">
                    <input type="file" name="addon_image[]" accept="image/*">
                </div>
            </div>
            <button type="button" onclick="addAddon()">➕ Add Another Add-on</button>

            <button type="submit"><i class="fas fa-check-circle"></i> Save Product</button>
        </form>
    </div>

    <div class="product-grid">
        <?php
        // Fetch customized products from the customized_products table
        $customized_result = mysqli_query($conn, "
        SELECT cp.*, cat.name AS category_name
        FROM customized_products cp
        LEFT JOIN categories cat ON cp.bouquet_category = cat.id
        ") or die("Query Error (Customized): " . mysqli_error($conn));

        while($custom = mysqli_fetch_assoc($customized_result)):
            $productImage = !empty($custom['product_image']) && file_exists("uploads/" . $custom['product_image'])
                                ? "uploads/" . htmlspecialchars($custom['product_image'])
                                : "uploads/default.jpg";
        ?>
            <div class="product-card">
                <img src="<?= $productImage ?>" alt="Customized Bouquet">
                <div class="product-details">
                    <h3><?= htmlspecialchars($custom['product_name']) ?></h3>
                    <p><?= htmlspecialchars($custom['product_description']) ?></p>
                    <p><strong>Category:</strong> <?= htmlspecialchars($custom['category_name'] ?? 'Uncategorized') ?></p>
                    <div class="price">₱<?= number_format($custom['product_price'], 2) ?></div>
                    <div class="action-btns">
                        <a href="edit_product.php?id=<?= $custom['id'] ?>"><i class="fas fa-edit"></i></a>
                        <a href="delete_product.php?id=<?= $custom['id'] ?>" onclick="return confirm('Delete this product?');"><i class="fas fa-trash"></i></a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <script>
    function addAddon() {
        const wrapper = document.getElementById("addon-wrapper");
        const group = document.createElement("div");
        group.classList.add("addon-group");
        group.innerHTML = `
            <input type="text" name="addon_name[]" placeholder="Add-on Name">
            <input type="number" name="addon_quantity[]" placeholder="Quantity" min="1">
            <input type="file" name="addon_image[]" accept="image/*">
        `;
        wrapper.appendChild(group);
    }
    </script>

</body>
</html>