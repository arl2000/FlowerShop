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

// Count the total number of items in the cart
$cartCount = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cartCount += $item['quantity']; // Count the quantity of each product
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Search Results - Heavenly Bloom</title>
    
    <link href="https://fonts.googleapis.com/css?family=Raleway:100,300,400,500,700,900" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- Additional CSS Files -->
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/font-awesome.css">
    <link rel="stylesheet" href="assets/css/templatemo-softy-pinko.css">
    <link rel="stylesheet" href="home.css">
    
    <style>
        body {
            font-family: 'Raleway', sans-serif;
            background-color: #fff;
            color: #636e72;
            margin: 0;
            padding: 0;
        }
        
        .search-section {
            padding: 50px 0;
            background-color: #fff;
        }
        
        .search-container {
            max-width: 1140px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        .section-heading {
            margin-bottom: 40px;
            text-align: center;
        }
        
        .section-heading h2 {
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            color: #333;
            margin-bottom: 15px;
            font-weight: 600;
            position: relative;
            padding-bottom: 15px;
        }
        
        .section-heading h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(45deg, #ff4483, #ff6b6b);
            border-radius: 5px;
        }
        
        .section-heading p {
            color: #777;
            font-size: 16px;
            max-width: 700px;
            margin: 0 auto;
        }
        
        .search-query {
            text-align: center;
            margin-bottom: 30px;
            font-size: 18px;
            color: #636e72;
        }
        
        .search-query span {
            color: #ff4483;
            font-weight: 600;
        }
        
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }
        
        .product-card {
            background-color: #fff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            position: relative;
        }
        
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .product-image {
            height: 200px;
            overflow: hidden;
            position: relative;
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .product-card:hover .product-image img {
            transform: scale(1.1);
        }
        
        .product-details {
            padding: 20px;
            text-align: center;
        }
        
        .product-name {
            font-family: 'Playfair Display', serif;
            font-size: 18px;
            color: #333;
            font-weight: 600;
            margin-bottom: 10px;
            line-height: 1.4;
        }
        
        .product-price {
            color: #ff4483;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 15px;
        }
        
        .add-to-cart-btn {
            background-color: #ff4483;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .add-to-cart-btn:hover {
            background-color: #e91e63;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 68, 131, 0.3);
        }
        
        .add-to-cart-btn i {
            margin-right: 8px;
        }
        
        .error-message, .no-results-message {
            text-align: center;
            padding: 40px 20px;
            background-color: #feeef4;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        
        .error-message {
            color: #e91e63;
            border: 1px solid #ffccd5;
        }
        
        .no-results-message {
            color: #636e72;
            border: 1px solid #e8e8e8;
        }
        
        .no-results-message i, .error-message i {
            font-size: 48px;
            margin-bottom: 20px;
            display: block;
        }
        
        .back-to-home {
            text-align: center;
            margin-top: 30px;
        }
        
        .back-to-home-btn {
            display: inline-block;
            background-color: #ff4483;
            color: white;
            padding: 12px 24px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            text-decoration: none;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        
        .back-to-home-btn:hover {
            background-color: #e91e63;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 68, 131, 0.3);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 20px;
            }
            
            .section-heading h2 {
                font-size: 30px;
            }
        }
        
        @media (max-width: 576px) {
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 15px;
            }
            
            .product-details {
                padding: 15px;
            }
            
            .product-name {
                font-size: 16px;
            }
            
            .product-price {
                font-size: 18px;
            }
            
            .section-heading h2 {
                font-size: 26px;
            }
        }
    </style>
</head>

<body>
    <?php include 'navi.php'; ?>
    
    <section class="search-section">
        <div class="search-container">
            <div class="section-heading">
                <h2>Search Results</h2>
                <p>Discover the perfect blooms for your special moments</p>
            </div>
            
            <?php if (!empty($search_query)): ?>
                <div class="search-query">
                    Showing results for: <span>"<?= htmlspecialchars($search_query) ?>"</span>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php elseif ($result && $result->num_rows > 0): ?>
                <div class="product-grid">
                    <?php while ($product = $result->fetch_assoc()): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <img src="uploads/<?= htmlspecialchars($product['product_image']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>">
                            </div>
                            <div class="product-details">
                                <h3 class="product-name"><?= htmlspecialchars($product['product_name']) ?></h3>
                                <div class="product-price">₱<?= number_format($product['product_price'], 2) ?></div>
                                <button class="add-to-cart-btn" onclick="addToCart(<?= $product['product_id'] ?>)">
                                    <i class="fas fa-shopping-cart"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-results-message">
                    <i class="fas fa-search"></i>
                    <p>No products found matching your search.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Add to cart form -->
    <form id="add-to-cart-form" action="add_to_cart.php" method="POST" style="display: none;">
        <input type="hidden" id="product_id" name="product_id" value="">
        <input type="hidden" name="quantity" value="1">
    </form>
    
    <!-- jQuery -->
    <script src="assets/js/jquery-2.1.0.min.js"></script>

    <!-- Bootstrap -->
    <script src="assets/js/popper.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>

    <!-- Plugins -->
    <script src="assets/js/scrollreveal.min.js"></script>
    <script src="assets/js/waypoints.min.js"></script>
    <script src="assets/js/jquery.counterup.min.js"></script>
    <script src="assets/js/imgfix.min.js"></script> 
    
    <!-- Global Init -->
    <script src="assets/js/custom.js"></script>
    
    <script>
        function addToCart(productId) {
            document.getElementById('product_id').value = productId;
            document.getElementById('add-to-cart-form').submit();
        }
    </script>
</body>
</html>
