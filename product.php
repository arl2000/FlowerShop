<?php
include 'navbar.php';
include 'db_connection.php';

// Fetch regular products where stock is greater than zero and is not deleted
$products_result = mysqli_query($conn, "
    SELECT products.*, categories.name AS category_name
    FROM products
    LEFT JOIN categories ON products.category_id = categories.id
    WHERE products.is_deleted = 0
") or die("Query Error (Regular): " . mysqli_error($conn));

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Products - Heavenly Bloom</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #fff6f2;
            margin: 0;
            padding: 0;
        }

        h2 {
            text-align: center;
            color: #d15e97;
            margin-top: 30px;
        }

        .add-product-btn {
            display: block;
            background-color: #d15e97;
            color: white;
            text-align: center;
            padding: 12px;
            font-size: 16px;
            text-decoration: none;
            border-radius: 10px;
            width: 250px;
            margin: 20px auto;
        }

        .add-product-btn:hover {
            background-color: #bb407c;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            padding: 40px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .product-card {
            background: #ffe8ec;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.2s ease;
            position: relative;
        }

        .product-card:hover {
            transform: scale(1.03);
        }

        .product-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            background-color: #fcdde6;
        }

        .product-details {
            padding: 15px;
        }

        .product-details h3 {
            font-size: 16px;
            margin: 5px 0;
            color: #d15e97;
        }

        .product-details p {
            font-size: 14px;
            margin: 5px 0;
            color: #555;
        }

        .category {
            font-size: 13px;
            color: #666;
            margin: 5px 0;
        }

        .price {
            font-weight: bold;
            color: #a83e6d;
            margin-top: 8px;
        }

        .action-btns {
            margin-top: 12px;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .action-btns a {
            background-color: #d15e97;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: background 0.3s ease;
        }

        .action-btns a:hover {
            background-color: #bb407c;
        }

        .action-btns a i {
            margin-right: 6px;
        }


        .customized-label {
            background-color: #f9a8d4; /* A different color to distinguish */
            color: white;
            padding: 3px 5px;
            border-radius: 5px;
            font-size: 12px;
            margin-bottom: 5px;
            display: inline-block;
        }

        .sold-out-label {
            background-color: #ff4d4d;
            color: white;
            padding: 3px 5px;
            border-radius: 5px;
            font-size: 20px;
            margin-bottom: 5px;
            display: inline-block;
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1;
        }

        .inventory-link {
            display: block;
            background-color: #4dabf7;
            color: white;
            text-align: center;
            padding: 12px;
            font-size: 16px;
            text-decoration: none;
            border-radius: 10px;
            width: 250px;
            margin: 20px auto;
        }

        .inventory-link:hover {
            background-color: #3c99e6;
        }

        .add-items-btn{
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        @media (max-width: 600px) {
            .add-product-btn, .inventory-link {
                width: 90%;
                font-size: 14px;
            }

            .product-card img {
                height: 160px;
            }

            .product-details h3,
            .product-details p,
            .category {
                font-size: 14px;
            }

            .price {
                font-size: 14px;
            }

            .action-btns {
                flex-direction: column;
                align-items: stretch;
            }

            .action-btns a {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

    <div class="add-items-btn">

        <a href="add_product.php" class="add-product-btn">
            <i class="fas fa-plus-circle"></i> ADD PRODUCTS
        </a>

        <a href="admin_customized_products.php" class="add-product-btn">
            <i class="fas fa-plus-circle"></i> VIEW CUSTOMIZED PRODUCTS
        </a>

        <a href="admin_customizations.php" class="add-product-btn">
            <i class="fas fa-plus-circle"></i> ADD SIDE ITEMS
        </a>
    </div>

<h2><i class="fas fa-seedling"></i> Product List</h2>

<div class="product-grid">
    <?php while($row = mysqli_fetch_assoc($products_result)): ?>
        <div class="product-card">
            <?php if ($row['stock_count'] <= 0): ?>
                <span class="sold-out-label"><i class="fas fa-ban"></i> SOLD OUT</span>
            <?php endif; ?>
            <img src="uploads/<?= htmlspecialchars($row['product_image']) ?: 'default.jpg' ?>" alt="Product Image">
            <div class="product-details">
                <h3><?= htmlspecialchars($row['product_name']) ?></h3>
                <p class="description">
                    <?= substr(htmlspecialchars($row['product_description']), 0, 100) ?>...
                    <span class="dots"> </span>
                    <span class="more-text" style="display:none;"><?= substr(htmlspecialchars($row['product_description']), 100) ?></span>
                    <a href="javascript:void(0);" class="read-more">Read more</a>
                </p>
                <p class="category"><strong>Category:</strong> <?= htmlspecialchars($row['category_name'] ?? 'None') ?></p>
                <div class="price">₱<?= number_format($row['product_price'], 2) ?></div>
                <p><strong>Stocks:</strong> <?= htmlspecialchars($row['stock_count']) ?></p>
                <div class="action-btns">
                    <a href="edit_product.php?id=<?= $row['product_id'] ?>" title="Edit Product"><i class="fas fa-edit"></i> Edit</a>
                    <a href="delete_product.php?id=<?= $row['product_id'] ?>" onclick="return confirm('Delete this product?');" title="Delete Product"><i class="fas fa-trash"></i> Delete</a>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>

</body>
</html>
