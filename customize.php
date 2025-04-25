<?php
include 'db_connection.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $product_price = $_POST['product_price'];
    $product_description = mysqli_real_escape_string($conn, $_POST['product_description']);
    $bouquet_sizes = mysqli_real_escape_string($conn, $_POST['bouquet_sizes']);
    $ribbon_colors = mysqli_real_escape_string($conn, $_POST['ribbon_colors']);
    $bouquet_category = (int) $_POST['bouquet_category'];

    $image = $_FILES['product_image']['name'];
    $image_tmp = $_FILES['product_image']['tmp_name'];
    $upload_dir = "uploads/";

    if (!empty($image)) {
        move_uploaded_file($image_tmp, $upload_dir . $image);

        $sql = "INSERT INTO customized_orders (product_name, product_price, product_description, product_image, bouquet_sizes, ribbon_colors, category_id)
                VALUES ('$product_name', '$product_price', '$product_description', '$image', '$bouquet_sizes', '$ribbon_colors', '$bouquet_category')";

        if (mysqli_query($conn, $sql)) {
            $order_id = mysqli_insert_id($conn);

            // Save add-ons
            if (!empty($_POST['addon_name'])) {
                foreach ($_POST['addon_name'] as $key => $addon_name) {
                    $addon_quantity = $_POST['addon_quantity'][$key];
                    $addon_image = $_FILES['addon_image']['name'][$key];
                    $addon_tmp = $_FILES['addon_image']['tmp_name'][$key];

                    if (!empty($addon_name) && !empty($addon_quantity)) {
                        $addon_filename = uniqid() . '_' . $addon_image;
                        move_uploaded_file($addon_tmp, $upload_dir . $addon_filename);

                        $addon_sql = "INSERT INTO customized_addons (order_id, addon_name, quantity, image_path)
                                      VALUES ('$order_id', '$addon_name', '$addon_quantity', '$addon_filename')";
                        mysqli_query($conn, $addon_sql);
                    }
                }
            }

            header("Location: thank_you.php");
            exit();
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    } else {
        echo "Please upload a product image.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customize Your Bouquet - Heavenly Bloom</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #fff6f2; padding: 0; margin: 0; }
        .form-container {
            max-width: 600px;
            margin: 40px auto;
            background: #ffe8ec;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        h2 { text-align: center; color: #d15e97; }
        label { display: block; margin-top: 15px; font-weight: bold; color: #444; }
        input, textarea, select {
            width: 100%; padding: 10px; margin-top: 5px;
            border-radius: 8px; border: 1px solid #ccc;
        }
        input[type="file"] { padding: 5px; }
        button {
            margin-top: 25px; width: 100%;
            background-color: #d15e97; color: white;
            padding: 12px; font-size: 16px; border: none; border-radius: 10px;
        }
        button:hover { background-color: #c04b84; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>🌸 Customize Your Bouquet</h2>
    <form method="POST" enctype="multipart/form-data">
        <label>Bouquet Name</label>
        <input type="text" name="product_name" required>

        <label>Price (₱)</label>
        <input type="number" step="0.01" name="product_price" required>

        <label>Description / Personalized Message</label>
        <textarea name="product_description" rows="4" required></textarea>

        <label>Upload Image (Optional Inspiration)</label>
        <input type="file" name="product_image" accept="image/*">

        <label>Choose Bouquet Size</label>
        <input type="text" name="bouquet_sizes" placeholder="e.g., Medium, Large" required>

        <label>Choose Ribbon Colors</label>
        <input type="text" name="ribbon_colors" placeholder="e.g., Red, Gold, Lavender" required>

        <label>Select Occasion</label>
        <select name="bouquet_category" required>
            <option value="">-- Select Occasion --</option>
            <?php
            $cat_result = mysqli_query($conn, "SELECT * FROM categories");
            while ($cat = mysqli_fetch_assoc($cat_result)) {
                echo '<option value="' . $cat['id'] . '">' . htmlspecialchars($cat['name']) . '</option>';
            }
            ?>
        </select>

        <label>Add-ons (Optional)</label>
        <div id="addon-wrapper">
            <div class="addon-group">
                <input type="text" name="addon_name[]" placeholder="Add-on Name">
                <input type="number" name="addon_quantity[]" placeholder="Quantity" min="1">
                <input type="file" name="addon_image[]" accept="image/*">
            </div>
        </div>
        <button type="button" onclick="addAddon()">➕ Add Another Add-on</button>

        <button type="submit">🛒 Place Order</button>
    </form>
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
