<?php
include 'db_connection.php';
include 'navbar.php';

if (!isset($_GET['id'])) {
    header("Location: product.php");
    exit();
}

$product_id = $_GET['id'];

// Fetch current product data
$query = mysqli_query($conn, "SELECT * FROM products WHERE product_id = $product_id");
$product = mysqli_fetch_assoc($query);

if (!$product) {
    echo "Product not found.";
    exit();
}

// Handle update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $product_price = $_POST['product_price'];
    $product_description = mysqli_real_escape_string($conn, $_POST['product_description']);
    $bouquet_sizes = isset($_POST['bouquet_sizes']) ? mysqli_real_escape_string($conn, $_POST['bouquet_sizes']) : null;
    $ribbon_colors = isset($_POST['ribbon_colors']) ? mysqli_real_escape_string($conn, $_POST['ribbon_colors']) : null;
    $bouquet_category = isset($_POST['bouquet_category']) ? mysqli_real_escape_string($conn, $_POST['bouquet_category']) : null;

    $image = $product['product_image'];
    if (!empty($_FILES['product_image']['name'])) {
        $new_image = $_FILES['product_image']['name'];
        $tmp = $_FILES['product_image']['tmp_name'];
        move_uploaded_file($tmp, "uploads/" . $new_image);
        $image = $new_image;
    }

    $sql = "UPDATE products SET 
                product_name = '$product_name',
                product_price = '$product_price',
                product_description = '$product_description',
                product_image = '$image'";

    if (!is_null($bouquet_sizes)) $sql .= ", bouquet_sizes = '$bouquet_sizes'";
    if (!is_null($ribbon_colors)) $sql .= ", ribbon_colors = '$ribbon_colors'";
   if (!is_null($bouquet_category)) $sql .= ", category_id = '$bouquet_category'";


    $sql .= " WHERE product_id = $product_id";

    if (mysqli_query($conn, $sql)) {
        header("Location: product.php");
        exit();
    } else {
        echo "Error updating product: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Product - Heavenly Bloom</title>
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

    img.preview {
      margin-top: 10px;
      width: 100%;
      max-height: 250px;
      object-fit: cover;
      border-radius: 12px;
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
    select {
  width: 100%;
  padding: 10px;
  margin-top: 5px;
  font-size: 16px;
  border: 1px solid #ccc;
  border-radius: 8px;
  background-color: #fff;
  color: #333;
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
  background-image: url("data:image/svg+xml;utf8,<svg fill='gray' height='24' viewBox='0 0 24 24' width='24' xmlns='http://www.w3.org/2000/svg'><path d='M7 10l5 5 5-5z'/></svg>");
  background-repeat: no-repeat;
  background-position: right 10px center;
  background-size: 20px;
}

@media (max-width: 600px) {
  select {
    font-size: 15px;
  }
}

  </style>
</head>
<body>

<div class="form-container">
  <h2><i class="fas fa-edit"></i> Edit Product</h2>
  <form method="POST" enctype="multipart/form-data">
    <label for="product_name">Product Name</label>
    <input type="text" name="product_name" value="<?= htmlspecialchars($product['product_name']) ?>" required>

    <label for="product_price">Price (₱)</label>
    <input type="number" step="0.01" name="product_price" value="<?= htmlspecialchars($product['product_price']) ?>" required>

    <label for="product_description">Description</label>
    <textarea name="product_description" rows="4" required><?= htmlspecialchars($product['product_description']) ?></textarea>

<label for="bouquet_sizes">Bouquet Sizes</label>
<input type="text" name="bouquet_sizes" value="<?= htmlspecialchars($product['bouquet_sizes'] ?? '') ?>" placeholder="e.g., Small, Medium, Large">

<label for="ribbon_colors">Ribbon Colors</label>
<input type="text" name="ribbon_colors" value="<?= htmlspecialchars($product['ribbon_colors'] ?? '') ?>" placeholder="e.g., Red, White, Lavender">

<label for="bouquet_category">Category</label>
<select name="bouquet_category">
    <option value="">-- Select Category --</option>
    <?php
    $cat_result = mysqli_query($conn, "SELECT * FROM categories");
    while ($cat = mysqli_fetch_assoc($cat_result)) {
        $selected = ($product['category'] ?? '') == $cat['id'] ? 'selected' : '';
        echo '<option value="' . $cat['id'] . '" ' . $selected . '>' . htmlspecialchars($cat['name']) . '</option>';
    }
    ?>
</select>


    <label for="product_image">Update Image (optional)</label>
    <input type="file" name="product_image" accept="image/*">
    <img class="preview" src="uploads/<?= htmlspecialchars($product['product_image']) ?>" alt="Current Image">

    <button type="submit"><i class="fas fa-save"></i> Update Product</button>
    <a class="back-link" href="product.php">← Back to Product List</a>
  </form>
</div>

</body>
</html>
