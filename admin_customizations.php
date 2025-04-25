<?php
session_start();
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Add-on
    if (isset($_POST['submit_addon'])) {
        $name = $_POST['addon_name'];
        $price = $_POST['addon_price'];
        if (isset($_POST['edit_id'])) {
            $id = $_POST['edit_id'];
            mysqli_query($conn, "UPDATE addo_ns SET name='$name', price='$price' WHERE id=$id");
        } else {
            mysqli_query($conn, "INSERT INTO add_ons (name, price) VALUES ('$name', '$price')");
        }
    }

    // Wrapper
    if (isset($_POST['submit_wrapper'])) {
        $color = $_POST['wrapper_color'];
        $price = $_POST['wrapper_price'];
        if (isset($_POST['edit_id'])) {
            $id = $_POST['edit_id'];
            mysqli_query($conn, "UPDATE wrappers SET color='$color', price='$price' WHERE id=$id");
        } else {
            mysqli_query($conn, "INSERT INTO wrappers (color, price) VALUES ('$color', '$price')");
        }
    }

    // Ribbon
    if (isset($_POST['submit_ribbon'])) {
        $color = $_POST['ribbon_color'];
        $price = $_POST['ribbon_price'];
        if (isset($_POST['edit_id'])) {
            $id = $_POST['edit_id'];
            mysqli_query($conn, "UPDATE ribbon_colors SET color='$color', price='$price' WHERE id=$id");
        } else {
            mysqli_query($conn, "INSERT INTO ribbon_colors (color, price) VALUES ('$color', '$price')");
        }
    }

    header("Location: admin_customizations.php");
    exit();
}

if (isset($_GET['delete'])) {
    $type = $_GET['type'];
    $id = $_GET['id'];
    $table = "";

    if ($type == 'addon') $table = 'addons';
    elseif ($type == 'wrapper') $table = 'wrappers';
    elseif ($type == 'ribbon') $table = 'ribbons';

    if ($table !== "") {
        mysqli_query($conn, "DELETE FROM $table WHERE id=$id");
    }

    header("Location: admin_customizations.php");
    exit();
}

if (isset($_GET['delete_customized'])) {
    $productId = (int)$_GET['id'];
    include 'delete_customized_product.php';
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Customizations</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        h2 { margin-top: 30px; }
        form, .item-list { margin-bottom: 20px; background: #fff; padding: 20px; border-radius: 8px; }
        input[type=text], input[type=number] { padding: 8px; margin-right: 10px; width: 200px; }
        button { padding: 8px 15px; background: green; color: white; border: none; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        a { margin: 0 5px; color: #007BFF; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .delete-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        .delete-btn:hover {
            background: #c82333;
        }
    </style>
</head>
<body>

<h1>Admin Customizations Panel</h1>

<!-- ADD-ONS -->
<h2>Add-ons</h2>
<form method="POST">
    <input type="text" name="addon_name" placeholder="Add-on name" required>
    <input type="number" step="0.01" name="addon_price" placeholder="Price (₱)" required>
    <button name="submit_addon" type="submit">Add Add-on</button>
</form>
<table>
    <tr><th>Name</th><th>Price</th><th>Actions</th></tr>
    <?php
    $result = mysqli_query($conn, "SELECT * FROM add_ons");
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
            <td>{$row['name']}</td>
            <td>₱{$row['price']}</td>
            <td>
                <form method='POST' style='display:inline-block;'>
                    <input type='hidden' name='edit_id' value='{$row['id']}'>
                    <input type='text' name='addon_name' value='{$row['name']}' required>
                    <input type='number' step='0.01' name='addon_price' value='{$row['price']}' required>
                    <button name='submit_addon' type='submit'>Update</button>
                </form>
                <a href='?delete=1&type=addon&id={$row['id']}' onclick='return confirm(\"Delete this add-on?\")'>Delete</a>
            </td>
        </tr>";
    }
    ?>
</table>

<!-- WRAPPERS -->
<h2>Wrapper Colors</h2>
<form method="POST">
    <input type="text" name="wrapper_color" placeholder="Color (e.g. Red or #FF0000)" required>
    <input type="number" step="0.01" name="wrapper_price" placeholder="Price (₱)" required>
    <button name="submit_wrapper" type="submit">Add Wrapper</button>
</form>
<table>
    <tr><th>Color</th><th>Price</th><th>Actions</th></tr>
    <?php
    $result = mysqli_query($conn, "SELECT * FROM wrappers");
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
            <td>{$row['color']}</td>
            <td>₱{$row['price']}</td>
            <td>
                <form method='POST' style='display:inline-block;'>
                    <input type='hidden' name='edit_id' value='{$row['id']}'>
                    <input type='text' name='wrapper_color' value='{$row['color']}' required>
                    <input type='number' step='0.01' name='wrapper_price' value='{$row['price']}' required>
                    <button name='submit_wrapper' type='submit'>Update</button>
                </form>
                <a href='?delete=1&type=wrapper&id={$row['id']}' onclick='return confirm(\"Delete this wrapper?\")'>Delete</a>
            </td>
        </tr>";
    }
    ?>
</table>

<!-- RIBBONS -->
<h2>Ribbon Colors</h2>
<form method="POST">
    <input type="text" name="ribbon_color" placeholder="Color (e.g. Pink or #FFC0CB)" required>
    <input type="number" step="0.01" name="ribbon_price" placeholder="Price (₱)" required>
    <button name="submit_ribbon" type="submit">Add Ribbon</button>
</form>
<table>
    <tr><th>Color</th><th>Price</th><th>Actions</th></tr>
    <?php
    $result = mysqli_query($conn, "SELECT * FROM ribbon_colors");
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
            <td>{$row['name']}</td>
            <td>₱{$row['price']}</td>
            <td>
                <form method='POST' style='display:inline-block;'>
                    <input type='hidden' name='edit_id' value='{$row['id']}'>
                    <input type='text' name='ribbon_color' value='{$row['name']}' required>
                    <input type='number' step='0.01' name='ribbon_price' value='{$row['price']}' required>
                    <button name='submit_ribbon' type='submit'>Update</button>
                </form>
                <a href='?delete=1&type=ribbon&id={$row['id']}' onclick='return confirm(\"Delete this ribbon?\")'>Delete</a>
            </td>
        </tr>";
    }
    ?>
</table>

<!-- CUSTOMIZED PRODUCTS -->
<h2>Customized Products</h2>
<table>
    <tr><th>Product Name</th><th>Price</th><th>Actions</th></tr>
    <?php
    $result = mysqli_query($conn, "SELECT * FROM customized_products WHERE is_deleted = 0");
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
            <td>{$row['product_name']}</td>
            <td>₱{$row['product_price']}</td>
            <td>
                <button class='delete-btn' onclick='deleteCustomizedProduct({$row['id']})'>Delete</button>
            </td>
        </tr>";
    }
    ?>
</table>

<script>
function deleteCustomizedProduct(productId) {
    if (confirm('Are you sure you want to delete this product?')) {
        window.location.href = `admin_customizations.php?delete_customized=1&id=${productId}`;
    }
}
</script>

</body>
</html>
