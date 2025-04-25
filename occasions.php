<?php
session_start();
include 'db_connection.php';

// Fetch categories from the categories table
$queryCategories = "SELECT * FROM categories";
$resultCategories = $conn->query($queryCategories);

// Get the selected category if any (via GET request)
$selectedCategoryId = isset($_GET['category_id']) ? $_GET['category_id'] : null;
$selectedCategoryName = null;
$productResult = null;

if ($selectedCategoryId) {
    // Get selected category name to display in heading
    $stmtCategoryName = $conn->prepare("SELECT name FROM categories WHERE id = ?");
    $stmtCategoryName->bind_param("i", $selectedCategoryId);
    $stmtCategoryName->execute();
    $stmtCategoryName->bind_result($categoryName);
    $stmtCategoryName->fetch();
    $selectedCategoryName = $categoryName;
    $stmtCategoryName->close();

    // Fetch products based on selected category ID
    $queryProducts = "SELECT * FROM products WHERE category_id = ?";
    $productStmt = $conn->prepare($queryProducts);
    $productStmt->bind_param("i", $selectedCategoryId);
    $productStmt->execute();
    $productResult = $productStmt->get_result();

    if ($productResult) {
        $productResult->data_seek(0); // Reset the pointer to the beginning of the result set
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Occasions - Heavenly Bloom</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display&display=swap" rel="stylesheet">

    <style>
        body {
            background-color: #fce4ec; /* Soft pink background */
            color: #5a3e36; /* soft brown for text */
            font-family: sans-serif; /* Or your preferred font */
            margin: 0; /* Remove default body margin */
            padding: 0; /* Remove default body padding */
        }
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); /* Responsive columns with a minimum width */
            gap: 20px;
            padding: 20px;
        }

        .product-grid.single-product {
            grid-template-columns: 1fr; /* Single column */
            justify-items: start; /* Align the single item to the start (left) */
        }

        .product-grid.single-product .product-card {
            width: auto; /* Adjust width as needed for single product view */
        }

        .product-card {
            border: 1px solid #ddd;
            padding: 15px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center; /* Center items horizontally */
            justify-content: space-between; /* Distribute space between elements */
        }

        .product-card img {
            max-width: 100%;
            height: 150px; /* Fixed height for images */
            object-fit: cover; /* Maintain aspect ratio and cover the container */
            margin-bottom: 10px;
        }

        .product-card h3 {
            font-size: 1.2em;
            margin-bottom: 5px;
            min-height: 3em; /* Ensure consistent height for product names */
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .product-card p {
            color: #555;
            margin-bottom: 10px;
        }

        .product-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 10px; /* Add some space above the buttons */
        }

        .product-buttons button,
        .product-buttons a button {
            padding: 8px 15px;
            border: none;
            background-color: #f0f0f0;
            color: #333;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9em; /* Adjust button font size if needed */
        }

        .product-buttons button:hover,
        .product-buttons a button:hover {
            background-color: #ddd;
        }
        .page-title h1 {
            text-align: center;
            color: #d180a3; /* A slightly darker pink for the title */
            margin-top: 20px;
            margin-bottom: 20px;
            font-family: 'Playfair Display', serif; /* Elegant font for the title */
            font-size: 2.5em;
        }
        .products h2 {
            text-align: center;
            color: #d180a3;
            margin-bottom: 15px;
            font-family: 'Playfair Display', serif;
            font-size: 1.8em;
        }
        .products p {
            text-align: center;
            font-style: italic;
            color: #777;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <?php include 'navi.php'; ?>

    <section class="page-title">
        <h1>Choose Your Occasion</h1>
    </section>

    <section class="products">
        <?php if ($selectedCategoryId && $selectedCategoryName): ?>
            <h2>Flowers for <?php echo htmlspecialchars(ucwords($selectedCategoryName)); ?></h2>
            <div class="product-grid <?php if ($productResult && $productResult->num_rows === 1) echo 'single-product'; ?>">
                <?php
                if ($productResult && $productResult->num_rows > 0) {
                    while ($product = $productResult->fetch_assoc()) {
                        echo "<div class='product-card' data-id='{$product['product_id']}'>
                                    <img src='uploads/{$product['product_image']}' alt='" . htmlspecialchars($product['product_name']) . "' loading='lazy'>
                                    <h3>" . htmlspecialchars($product['product_name']) . "</h3>
                                    <p>₱" . number_format($product['product_price'], 2) . "</p>
                                    <div class='product-buttons'>
                                        <form action='homepage.php' method='GET'>
                                            <input type='hidden' name='add' value='{$product['product_id']}'>
                                            <button type='submit'>Add to Cart</button>
                                        </form>
                                        <a href='checkout.php'><button>Checkout</button></a>
                                    </div>
                                </div>";
                    }
                } else {
                    echo "<p>No products available for this occasion.</p>";
                }
                ?>
            </div>
        <?php else: ?>
            <p>Please select a category from the navigation to view the products.</p>
        <?php endif; ?>
    </section>

</body>
</html>