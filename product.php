<?php
include 'navbar.php';
include 'db_connection.php';

// Fetch regular products with category name where stock is greater than zero
$products_result = mysqli_query($conn, "
    SELECT products.*, categories.name AS category_name
    FROM products
    LEFT JOIN categories ON products.category_id = categories.id
") or die("Query Error (Regular): " . mysqli_error($conn));

// Fetch customized products with related details
$customized_result = mysqli_query($conn, "
    SELECT cp.*, cp.stock_count, cat.name AS category_name,
       bs.name AS bouquet_size_name, bs.price AS bouquet_size_price,
       rc.name AS ribbon_color_name, rc.price AS ribbon_color_price
FROM customized_products cp
    LEFT JOIN categories cat ON cp.category_id = cat.id
    LEFT JOIN bouquet_sizes bs ON cp.bouquet_sizes = bs.id
    LEFT JOIN ribbon_colors rc ON cp.ribbon_colors = rc.id
") or die("Query Error (Customized): " . mysqli_error($conn));
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

<a href="add_product.php" class="add-product-btn">
    <i class="fas fa-plus-circle"></i> ADD PRODUCTS
</a>

<a href="inventory.php" class="inventory-link">
    <i class="fas fa-boxes"></i> VIEW INVENTORY
</a>

<h2><i class="fas fa-seedling"></i> Product List</h2>

<div class="product-grid">
    <?php
    // Display regular products
    while($row = mysqli_fetch_assoc($products_result)): ?>
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
    <?php endwhile;
    

    // Display customized products
    while($custom = mysqli_fetch_assoc($customized_result)):
        $productImage = !empty($custom['product_image']) && file_exists("uploads/" . $custom['product_image'])
            ? "uploads/" . htmlspecialchars($custom['product_image'])
            : "uploads/default.jpg";
    ?>
        <div class="product-card">
            <?php if ($custom['stock_count'] <= 0): ?>
                <span class="sold-out-label"><i class="fas fa-ban"></i> SOLD OUT</span>
            <?php endif; ?>
            <span class="customized-label"><i class="fas fa-gift"></i> Customized</span>
            <img src="<?= $productImage ?>" alt="Customized Bouquet">
            <div class="product-details">
                <h3><?= htmlspecialchars($custom['product_name']) ?></h3>
                <p class="description">
                    <?= substr(htmlspecialchars($custom['product_description']), 0, 100) ?>...
                    <span class="dots"> </span>
                    <span class="more-text" style="display:none;"><?= substr(htmlspecialchars($custom['product_description']), 100) ?></span>
                    <a href="javascript:void(0);" class="read-more">Read more</a>
                </p>

                <p><strong>Category:</strong> <?= htmlspecialchars($custom['category_name'] ?? 'Uncategorized') ?></p>
                <p><strong>Size:</strong> <?= htmlspecialchars($custom['bouquet_size_name'] ?? 'N/A') ?> (₱<?= number_format($custom['bouquet_size_price'] ?? 0, 2) ?>)</p>
                <p><strong>Ribbon:</strong> <?= htmlspecialchars($custom['ribbon_color_name'] ?? 'N/A') ?> (₱<?= number_format($custom['ribbon_color_price'] ?? 0, 2) ?>)</p>
                <p><strong>Message:</strong> <?= htmlspecialchars($custom['message'] ?? 'N/A') ?> (₱<?= number_format($custom['message_price'] ?? 0, 2) ?>)</p>
                <div class="price">Base Price: ₱<?= number_format($custom['product_price'], 2) ?></div>
                <p><strong>Stocks:</strong> <?= htmlspecialchars($custom['stock_count']) ?></p>
                <div class="action-btns">
                    <a href="edit_customized_product.php?id=<?= $custom['id'] ?>" title="Edit Customized Bouquet"><i class="fas fa-edit"></i> Edit</a>
                    <a href="delete_customized_product.php?id=<?= $custom['id'] ?>" onclick="return confirm('Delete this customized bouquet?');" title="Delete Customized Bouquet"><i class="fas fa-trash"></i> Delete</a>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
    
</div>

<script>
// Function to check stock status
function checkStockStatus() {
    fetch('check_stock.php')
        .then(response => response.json())
        .then(data => {
            data.forEach(product => {
                const productCard = document.querySelector(`[data-product-id="${product.id}"]`);
                if (productCard) {
                    const soldOutLabel = productCard.querySelector('.sold-out-label');
                    if (product.stock_count <= 0 && !soldOutLabel) {
                        const label = document.createElement('span');
                        label.className = 'sold-out-label';
                        label.innerHTML = '<i class="fas fa-ban"></i> SOLD OUT';
                        productCard.insertBefore(label, productCard.firstChild);
                    } else if (product.stock_count > 0 && soldOutLabel) {
                        soldOutLabel.remove();
                    }
                }
            });
        })
        .catch(error => console.error('Error checking stock:', error));
}

// Check stock status every 30 seconds
setInterval(checkStockStatus, 30000);

// Initial check
checkStockStatus();


//read more toggle
document.querySelectorAll('.read-more').forEach(btn => {
    btn.addEventListener('click', function () {
        const moreText = this.previousElementSibling;
        const dots = this.previousElementSibling.previousElementSibling;
        if (moreText.style.display === "none") {
            moreText.style.display = "inline";
            dots.style.display = "none";
            this.textContent = "Read less";
        } else {
            moreText.style.display = "none";
            dots.style.display = "inline";
            this.textContent = "Read more";
        }
    });
});
</script>

</body>
</html>