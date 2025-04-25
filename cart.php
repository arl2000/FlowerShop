<?php
session_start();
include 'db_connection.php';

$userId = $_SESSION['user_id']; // assuming this is set on login

// Remove item from cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_product_id'])) {
    $productIdToRemove = (int)$_POST['remove_product_id'];

    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $userId, $productIdToRemove);
    $stmt->execute();
    $stmt->close();

    $_SESSION['cart_message'] = "Item removed from cart.";
    header("Location: cart.php");
    exit();
}

// Update quantities in the cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart']) && isset($_POST['quantities'])) {
    foreach ($_POST['quantities'] as $productId => $quantity) {
        $productId = (int)$productId;
        $quantity = max(1, (int)$quantity);

        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("iii", $quantity, $userId, $productId);
        $stmt->execute();
        $stmt->close();
    }

    $_SESSION['cart_message'] = "Cart updated successfully!";
    header("Location: cart.php");
    exit();
}

// Fetch cart items from database (without using aliases)
$stmt = $conn->prepare("SELECT product_id, product_name, product_image, product_price, quantity, is_customized, 
    ribbon_color_id, ribbon_color_name, ribbon_color_price, 
    wrapper_color_id, wrapper_color_name, wrapper_color_price, 
    customer_message, addons
    FROM cart
    WHERE user_id = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$cartItems = [];
$total = 0;
while ($row = $result->fetch_assoc()) {
    $row['subtotal'] = $row['product_price'] * $row['quantity'];
    $total += $row['subtotal'];
    $cartItems[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Shopping Cart - Heavenly Bloom</title>
    
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
        
        .cart-section {
            padding: 50px 0;
            background-color: #fff;
        }
        
        .cart-container {
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
        }
        
        .section-heading p {
            color: #777;
            font-size: 16px;
        }
        
        .cart-message {
            background-color: #feeef4;
            color: #ff4483;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: center;
            font-weight: 500;
        }
        
        .cart-table {
            width: 100%;
            background-color: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }
        
        .cart-table thead {
            background-color: #fdf6f8;
        }
        
        .cart-table th {
            padding: 16px;
            text-align: left;
            color: #333;
            font-weight: 600;
            border-bottom: 1px solid #f0ece7;
        }
        
        .cart-table td {
            padding: 20px 16px;
            border-bottom: 1px solid #f0ece7;
            vertical-align: middle;
        }
        
        .cart-table tr:last-child td {
            border-bottom: none;
        }
        
        .product-image img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
        }
        
        .product-name {
            font-weight: 500;
            color: #333;
            font-size: 15px;
        }
        
        .product-price, .product-subtotal {
            font-weight: 600;
            color: #ff4483;
        }
        
        .quantity-input {
            display: flex;
            align-items: center;
            max-width: 100px;
        }
        
        .quantity-input input {
            width: 50px;
            height: 36px;
            border: 1px solid #f0ece7;
            border-radius: 6px;
            text-align: center;
            font-size: 14px;
            color: #333;
            padding: 0 5px;
        }
        
        .quantity-input input:focus {
            outline: none;
            border-color: #ff4483;
        }
        
        .remove-btn {
            background-color: transparent;
            color: #ff4483;
            border: none;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .remove-btn:hover {
            color: #e91e63;
            transform: scale(1.1);
        }
        
        .cart-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            padding: 20px 0;
        }
        
        .cart-total {
            font-size: 18px;
            color: #333;
            font-weight: 600;
        }
        
        .cart-total span {
            color: #ff4483;
            font-size: 24px;
            margin-left: 10px;
        }
        
        .cart-buttons {
            display: flex;
            gap: 15px;
        }
        
        .update-cart-btn, .checkout-btn {
            padding: 12px 24px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
        }
        
        .update-cart-btn {
            background-color: #f0f0f0;
            color: #333;
        }
        
        .update-cart-btn:hover {
            background-color: #e0e0e0;
        }
        
        .checkout-btn {
            background-color: #ff4483;
            color: white;
        }
        
        .checkout-btn:hover {
            background-color: #e91e63;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 68, 131, 0.3);
        }
        
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-cart-icon {
            font-size: 60px;
            color: #ffd1dc;
            margin-bottom: 20px;
        }
        
        .empty-cart-message {
            font-size: 18px;
            color: #777;
            margin-bottom: 30px;
        }
        
        .continue-shopping-btn {
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
        
        .continue-shopping-btn:hover {
            background-color: #e91e63;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 68, 131, 0.3);
        }
        
        .customization-details {
            margin-top: 10px;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        
        .customization-details h4 {
            color: #ff4483;
            margin-bottom: 10px;
        }
        
        .customization-details p {
            margin: 5px 0;
            color: #666;
        }
        
        .addons-list {
            list-style: none;
            padding-left: 0;
        }
        
        .addons-list li {
            margin-bottom: 5px;
            padding: 5px;
            background-color: #fff;
            border-radius: 3px;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .cart-table thead {
                display: none;
            }
            
            .cart-table, .cart-table tbody, .cart-table tr, .cart-table td {
                display: block;
                width: 100%;
            }
            
            .cart-table tr {
                margin-bottom: 20px;
                border: 1px solid #f0ece7;
                border-radius: 8px;
                padding: 15px;
            }
            
            .cart-table td {
                text-align: right;
                padding: 10px 0;
                border-bottom: 1px solid #f0ece7;
                position: relative;
                padding-left: 50%;
            }
            
            .cart-table td:before {
                content: attr(data-label);
                position: absolute;
                left: 0;
                width: 45%;
                white-space: nowrap;
                font-weight: 600;
                text-align: left;
            }
            
            .cart-table td:last-child {
                border-bottom: 0;
            }
            
            .product-image img {
                width: 60px;
                height: 60px;
            }
            
            .cart-actions {
                flex-direction: column;
                gap: 20px;
            }
            
            .cart-total {
                text-align: center;
                width: 100%;
            }
            
            .cart-buttons {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <?php include 'navi.php'; ?>
    
    <section class="cart-section">
        <div class="cart-container">
            <div class="section-heading">
                <h2>Your Shopping Cart</h2>
                <p>Review your items and proceed to checkout</p>
            </div>
            
            <?php if (isset($_SESSION['cart_message'])): ?>
                <div class="cart-message">
                    <?= $_SESSION['cart_message'] ?>
                    <?php unset($_SESSION['cart_message']); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="cart.php" id="cart-form">
                <?php if (empty($cartItems)): ?>
                    <div class="empty-cart">
                        <div class="empty-cart-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <p class="empty-cart-message">Your cart is currently empty. Perhaps some beautiful blooms are calling your name?</p>
                        <a href="homepage.php" class="continue-shopping-btn">Continue Shopping</a>
                    </div>
                <?php else: ?>
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Select</th>
                                <th>Product</th>
                                <th>Image</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cartItems as $item): ?>
                                <tr>
                                    <td data-label="Select">
                                        <input type="checkbox" name="selected_items[]" value="<?= $item['product_id'] ?>" checked>
                                    </td>
                                    <td data-label="Product">
                                        <div class="product-info">
                                            <div class="product-name"><?= htmlspecialchars($item['product_name']) ?></div>
                                            <?php if ($item['is_customized']): ?>
                                                <div class="customization-details">
                                                    <h4>Customization Details</h4>
                                                    <?php if ($item['ribbon_color_name']): ?>
                                                        <p>Ribbon Color: <?= htmlspecialchars($item['ribbon_color_name']) ?> 
                                                            (₱<?= number_format($item['ribbon_color_price'], 2) ?>)</p>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($item['wrapper_color_name']): ?>
                                                        <p>Wrapper Color: <?= htmlspecialchars($item['wrapper_color_name']) ?> 
                                                            (₱<?= number_format($item['wrapper_color_price'], 2) ?>)</p>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($item['addons']): ?>
                                                        <p>Add-ons:</p>
                                                        <ul class="addons-list">
                                                            <?php 
                                                            $addons = json_decode($item['addons'], true);
                                                            if ($addons) {
                                                                foreach ($addons as $addon) {
                                                                    echo '<li>' . htmlspecialchars($addon['name']) . ' (₱' . number_format($addon['price'], 2) . ')</li>';
                                                                }
                                                            }
                                                            ?>
                                                        </ul>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($item['customer_message']): ?>
                                                        <p>Message: <?= htmlspecialchars($item['customer_message']) ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td data-label="Image" class="product-image">
                                        <img src="uploads/<?= htmlspecialchars($item['product_image']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>">
                                    </td>
                                    <td data-label="Price" class="product-price">₱<?= number_format($item['product_price'], 2) ?></td>
                                    <td data-label="Quantity" class="product-quantity">
                                        <div class="quantity-input">
                                            <input type="number" name="quantities[<?= $item['product_id'] ?>]" value="<?= $item['quantity'] ?>" min="1">
                                        </div>
                                    </td>
                                    <td data-label="Subtotal" class="product-subtotal">₱<?= number_format($item['subtotal'], 2) ?></td>
                                    <td data-label="Action">
                                        <button type="button" class="remove-btn" onclick="removeItem(<?= $item['product_id'] ?>)">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div class="cart-actions">
                        <div class="cart-total">
                            Total: <span>₱<span id="selected-total"><?= number_format($total, 2) ?></span></span>
                        </div>
                        <div class="cart-buttons">
                            <button type="submit" name="update_cart" class="update-cart-btn">Update Cart</button>
                            <button type="button" class="checkout-btn" onclick="proceedToCheckout()">Proceed to Checkout</button>
                        </div>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </section>
    
    <!-- Hidden form for product removal -->
    <form id="remove-form" action="cart.php" method="POST" style="display: none;">
        <input type="hidden" id="remove_product_id" name="remove_product_id" value="">
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
        function removeItem(productId) {
            if (confirm("Are you sure you want to remove this item from your cart?")) {
                document.getElementById('remove_product_id').value = productId;
                document.getElementById('remove-form').submit();
            }
        }

        // Function to update total based on selected items
        function updateSelectedTotal() {
            let total = 0;
            document.querySelectorAll('input[name="selected_items[]"]').forEach(checkbox => {
                if (checkbox.checked) {
                    const row = checkbox.closest('tr');
                    const subtotal = parseFloat(row.querySelector('.product-subtotal').textContent.replace('₱', '').replace(',', ''));
                    total += subtotal;
                }
            });
            document.getElementById('selected-total').textContent = total.toFixed(2);
        }

        // Function to handle checkout
        function proceedToCheckout() {
            const selectedItems = Array.from(document.querySelectorAll('input[name="selected_items[]"]:checked'))
                .map(checkbox => checkbox.value);
            
            if (selectedItems.length === 0) {
                alert('Please select at least one item to checkout');
                return;
            }

            // Create a new form for checkout
            const checkoutForm = document.createElement('form');
            checkoutForm.method = 'POST';
            checkoutForm.action = 'checkout.php';
            
            // Add selected items to the form
            selectedItems.forEach(itemId => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected_items[]';
                input.value = itemId;
                checkoutForm.appendChild(input);
            });
            
            // Add the form to the document and submit it
            document.body.appendChild(checkoutForm);
            checkoutForm.submit();
        }

        // Add event listeners for checkboxes
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('input[name="selected_items[]"]').forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectedTotal);
            });
        });
    </script>
</body>
</html>