<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'db_connection.php';

$search_query = $_GET['search_query'] ?? '';
$result = null;
$error = '';

// Check if search query is provided
if (!empty($search_query)) {
    $sql = "SELECT * FROM products
            WHERE product_name LIKE ? 
            OR category_id IN (
                SELECT category_id FROM categories WHERE name LIKE ?
            )";

    if ($stmt = $conn->prepare($sql)) {
        $searchQueryWithWildcard = "%" . $search_query . "%";
        $stmt->bind_param("ss", $searchQueryWithWildcard, $searchQueryWithWildcard);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
        } else {
            $error = "Error executing SQL query: " . $stmt->error;
        }
    } else {
        $error = "Error preparing SQL query: " . $conn->error;
    }
} else {
    $error = "Please enter a search query.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Results</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            background-color: #fce4ec;
            color: #5a3e36;
            font-family: sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .container {
            max-width: 1200px;
            margin-top: 20px;
            padding: 0 20px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .products {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            padding: 20px;
            justify-content: center;
        }
        .product {
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background: #f9f9f9;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .product img {
            max-width: 100%;
            height: auto;
            margin-bottom: 10px;
        }
        .product h3 {
            font-size: 1.2em;
            margin: 0 0 5px 0;
        }
        .product .price {
            font-size: 1em;
            color: #c94f7c;
            margin-bottom: 10px;
        }
        .error, .no-results {
            padding: 20px;
            text-align: center;
            font-weight: bold;
        }
        .error { color: red; }
        .no-results { color: #5a3e36; }
        .product:hover {
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
            transition: box-shadow 0.3s ease, transform 0.3s ease;
        }
    </style>
</head>
<body>
<?php include 'navi.php'; ?>

<div class="container">
    <h2>Search Results</h2>

    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="products">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($product = $result->fetch_assoc()): ?>
                <div class="product">
                    <img src="uploads/<?= htmlspecialchars($product['product_image']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>">
                    <h3><?= htmlspecialchars($product['product_name']) ?></h3>
                    <div class="price">Price: ₱<?= number_format($product['product_price'], 2) ?></div>
                    <button>Add to Cart</button>
                </div>
            <?php endwhile; ?>
        <?php elseif (!$error): ?>
            <p class="no-results">No products found matching your search.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
