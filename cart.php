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
    customer_message, ribbon_color_name, wrapper_color_name, leaves, addons, flowers
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

// Helper: fetch item info from DB by group and id
function fetchItemInfo($conn, $group, $id) {
    $id = (int)$id;
    switch ($group) {
        case 'flowers':
        case 'flower':
            $sql = "SELECT name, price FROM flowers WHERE id = $id LIMIT 1";
            break;
        case 'addons':
        case 'add_ons':
        case 'addon':
            $sql = "SELECT name, price FROM add_ons WHERE id = $id LIMIT 1";
            break;
        case 'ribbons':
        case 'ribbon':
            $sql = "SELECT name, price FROM ribbon_colors WHERE id = $id LIMIT 1";
            break;
        case 'wrappers':
        case 'wrapper':
            $sql = "SELECT color AS name, price FROM wrappers WHERE id = $id LIMIT 1";
            break;
        case 'leaves':
        case 'leaf':
            $sql = "SELECT name, price FROM leaves WHERE id = $id LIMIT 1";
            break;
        default:
            return ['name' => 'Unknown', 'price' => 0];
    }
    $res = $conn->query($sql);
    if ($res && $row = $res->fetch_assoc()) {
        return ['name' => $row['name'], 'price' => $row['price']];
    }
    return ['name' => 'Unknown', 'price' => 0];
}

// Enhanced display function: fill missing names/prices from DB
function displaySelectedItems($custom) {
    global $conn;
    $groups = [
        'ribbons'  => 'Ribbons',
        'wrappers' => 'Wrappers',
        'leaves'   => 'Leaves',
        'addons'   => 'Add-ons',
        'flowers'  => 'Flowers'
    ];
    foreach ($groups as $key => $label) {
        if (!empty($custom[$key])) {
            echo "<p>{$label}:</p><ul class='addons-list'>";
            foreach ($custom[$key] as $item) {
                // Skip if 'id' is not set (prevents warning)
                if (!isset($item['id'])) {
                    $name = 'Unknown';
                    $price = 0;
                    $qty = isset($item['qty']) ? (int)$item['qty'] : 1;
                    $price_fmt = number_format($price, 2);
                    echo "<li>{$name} x{$qty} (₱{$price_fmt} each)</li>";
                    continue;
                }
                $name  = isset($item['name']) && $item['name'] ? htmlspecialchars($item['name']) : null;
                $price = isset($item['price']) ? $item['price'] : null;
                $qty   = isset($item['qty']) ? (int)$item['qty'] : 1;
                // If name or price missing, fetch from DB
                if (!$name || $price === null) {
                    $info = fetchItemInfo($conn, $key, $item['id']);
                    if (!$name)  $name  = htmlspecialchars($info['name']);
                    if ($price === null) $price = $info['price'];
                }
                if (!$name) $name = 'Unknown';
                if ($price === null) $price = 0;
                $price_fmt = number_format($price, 2);
                echo "<li>{$name} x{$qty} (₱{$price_fmt} each)</li>";
            }
            echo "</ul>";
        }
    }
}

// Fetch and return selected items as a formatted string for modal
function getSelectedItemsSummary($custom) {
    $summary = [];
    $groups = [
        'ribbons'  => 'Ribbon',
        'wrappers' => 'Wrapper',
        'leaves'   => 'Leaf',
        'addons'   => 'Add-on',
        'flowers'  => 'Flower'
    ];
    foreach ($groups as $key => $label) {
        if (!empty($custom[$key])) {
            foreach ($custom[$key] as $item) {
                $name  = isset($item['name']) ? $item['name'] : "Unknown {$label}";
                $qty   = isset($item['qty']) ? (int)$item['qty'] : 1;
                $price = isset($item['price']) ? number_format($item['price'], 2) : "0.00";
                $summary[] = "{$label}: {$name} x{$qty} (₱{$price} each)";
            }
        }
    }
    return implode(", ", $summary);
}

// Prepare base64-encoded cart data for JavaScript
ob_start();
$jsonData = json_encode(array_map(function($item) {
    return [
        'product_name' => $item['product_name'],
        'product_image' => $item['product_image'],
        'product_price' => $item['product_price'],
        'quantity' => $item['quantity'],
        'subtotal' => $item['subtotal'],
        'is_customized' => $item['is_customized'],
        'customer_message' => $item['customer_message'],
        'ribbons'  => $item['ribbons'],
        'wrappers' => $item['wrappers'],
        'leaves'   => $item['leaves'],
        'addons'   => $item['addons'],
        'flowers'  => $item['flowers'],
    ];
}, $cartItems), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
if ($jsonData === false) {
    $jsonData = '[]';
}
$base64Data = base64_encode($jsonData);
ob_end_clean();
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
                            <?php foreach ($cartItems as $idx => $item): ?>
                                <tr class="cart-item-row" data-idx="<?= $idx ?>" style="cursor:pointer;">
                                    <td data-label="Select">
                                        <input type="checkbox" name="selected_items[]" value="<?= $item['product_id'] ?>" checked onclick="event.stopPropagation();">
                                    </td>
                                    <td data-label="Product">
                                        <div class="product-info">
                                            <div class="product-name"><?= htmlspecialchars($item['product_name']) ?></div>
                                            <?php if ($item['is_customized']): ?>
                                                <div class="customization-details">
                                                    <h4>Customization Details</h4>
                                                    <?php
                                                    if ($item['is_customized']) {
                                                        $custom = json_decode($item['addons'], true);
                                                        // Add empty arrays for any missing groups
                                                        $groups = ['flowers', 'ribbons', 'wrappers', 'addons', 'leaves'];
                                                        foreach ($groups as $group) {
                                                            if (!isset($custom[$group])) {
                                                                $custom[$group] = [];
                                                            }
                                                        }
                                                        displaySelectedItems($custom);
                                                    } else {
                                                        $custom = [
                                                            'ribbons'  => json_decode($item['ribbon_color_name'], true),
                                                            'wrappers' => json_decode($item['wrapper_color_name'], true),
                                                            'leaves'   => json_decode($item['leaves'], true),
                                                            'addons'   => json_decode($item['addons'], true),
                                                            'flowers'  => json_decode($item['flowers'], true),
                                                        ];
                                                        displaySelectedItems($custom);
                                                    }
                                                    ?>
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
                                            <input type="number" name="quantities[<?= $item['product_id'] ?>]" value="<?= $item['quantity'] ?>" min="1" onclick="event.stopPropagation();">
                                        </div>
                                    </td>
                                    <td data-label="Subtotal" class="product-subtotal">₱<?= number_format($item['subtotal'], 2) ?></td>
                                    <td data-label="Action">
                                        <button type="button" class="remove-btn" onclick="event.stopPropagation();removeItem(<?= $item['product_id'] ?>)">
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
                            <button type="button" class="checkout-btn" id="proceed-to-checkout-btn">Proceed to Checkout</button>
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
    
    <!-- Hidden container for cart data -->
    <div id="cart-data" style="display:none;"><?= $base64Data ?></div>
    
    <!-- Modal for cart item details -->
    <div id="cartItemModal" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.4);align-items:center;justify-content:center;">
        <div style="background:#fff;max-width:500px;width:90%;margin:auto;padding:2rem;position:relative;border-radius:10px;">
            <button id="closeCartModal" style="position:absolute;top:10px;right:15px;background:none;border:none;font-size:1.5rem;cursor:pointer;">&times;</button>
            <div id="cartModalContent"></div>
        </div>
    </div>

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
        // Function to handle checkout
        function proceedToCheckout() {
            const selectedItems = Array.from(document.querySelectorAll('input[name="selected_items[]"]:checked'))
                .map(checkbox => checkbox.value);
            
            if (selectedItems.length === 0) {
                alert('Please select at least one item to checkout');
                return;
            }

            // Reuse the existing cart form
            const cartForm = document.getElementById('cart-form');
            
            // Change form action to checkout.php
            cartForm.action = 'checkout.php';
            
            // Create a hidden input to indicate this is a checkout request
            const checkoutInput = document.createElement('input');
            checkoutInput.type = 'hidden';
            checkoutInput.name = 'checkout';
            checkoutInput.value = '1';
            cartForm.appendChild(checkoutInput);
            
            // Submit the form
            cartForm.submit();
        }

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

        // Get cart data from hidden div
        const base64String = document.getElementById('cart-data').textContent;
        const jsonString = atob(base64String);
        const cartItemsData = JSON.parse(jsonString);

        // Modal logic
        document.querySelectorAll('.cart-item-row').forEach(function(row) {
            row.addEventListener('click', function(e) {
                // Prevent click on checkbox or remove button from opening modal
                if (e.target.tagName === 'INPUT' || e.target.classList.contains('remove-btn') || e.target.closest('.remove-btn')) return;
                const idx = parseInt(row.getAttribute('data-idx'));
                showCartItemModal(idx);
            });
        });

        function showCartItemModal(idx) {
            const item = cartItemsData[idx];
            let html = `<h3 style="margin-top:0">${escapeHtml(item.product_name)}</h3>`;
            if (item.is_customized) {
                html += `<hr><h4>Customization Details</h4>`;
                let names = [];
                // Ribbons
                let ribbons = [];
                try { ribbons = JSON.parse(item.ribbons); } catch(e) {}
                if (ribbons && ribbons.length) {
                    html += `<div style="margin-top:8px;"><strong>Ribbons:</strong><ul>`;
                    ribbons.forEach(function(ribbon) {
                        let name = ribbon.name || 'Unknown Ribbon';
                        if (!ribbon.name && ribbon.id) name += ' (ID: ' + ribbon.id + ')';
                        names.push(name);
                        html += `<li>${escapeHtml(name)} x${ribbon.qty || 1}</li>`;
                    });
                    html += `</ul></div>`;
                }
                // Wrappers
                let wrappers = [];
                try { wrappers = JSON.parse(item.wrappers); } catch(e) {}
                if (wrappers && wrappers.length) {
                    html += `<div style="margin-top:8px;"><strong>Wrappers:</strong><ul>`;
                    wrappers.forEach(function(wrapper) {
                        let name = wrapper.name || 'Unknown Wrapper';
                        if (!wrapper.name && wrapper.id) name += ' (ID: ' + wrapper.id + ')';
                        names.push(name);
                        html += `<li>${escapeHtml(name)} x${wrapper.qty || 1}</li>`;
                    });
                    html += `</ul></div>`;
                }
                // Leaves
                let leaves = [];
                try { leaves = JSON.parse(item.leaves); } catch(e) {}
                if (leaves && leaves.length) {
                    html += `<div style="margin-top:8px;"><strong>Leaves:</strong><ul>`;
                    leaves.forEach(function(leaf) {
                        let name = leaf.name || 'Unknown Leaf';
                        if (!leaf.name && leaf.id) name += ' (ID: ' + leaf.id + ')';
                        names.push(name);
                        html += `<li>${escapeHtml(name)} x${leaf.qty || 1}</li>`;
                    });
                    html += `</ul></div>`;
                }
                // Add-ons
                let addons = [];
                try { addons = JSON.parse(item.addons); } catch(e) {}
                if (addons && addons.length) {
                    html += `<div style="margin-top:8px;"><strong>Add-ons:</strong><ul>`;
                    addons.forEach(function(addon) {
                        let name = addon.name || 'Unknown Add-on';
                        if (!addon.name && addon.id) name += ' (ID: ' + addon.id + ')';
                        names.push(name);
                        html += `<li>${escapeHtml(name)} x${addon.qty || 1}</li>`;
                    });
                    html += `</ul></div>`;
                }
                // Flowers
                let flowers = [];
                try { flowers = JSON.parse(item.flowers); } catch(e) {}
                if (flowers && flowers.length) {
                    html += `<div style="margin-top:8px;"><strong>Flowers:</strong><ul>`;
                    flowers.forEach(function(flower) {
                        let name = flower.name || 'Unknown Flower';
                        if (!flower.name && flower.id) name += ' (ID: ' + flower.id + ')';
                        names.push(name);
                        html += `<li>${escapeHtml(name)} x${flower.qty || 1}</li>`;
                    });
                    html += `</ul></div>`;
                }
                if (item.customer_message) {
                    html += `<div style="margin-top:8px;"><strong>Message:</strong> ${escapeHtml(item.customer_message)}</div>`;
                }
                // Debug: show raw JSON if names are missing
                if (!names.length) {
                    html += `<pre style="margin-top:10px;background:#f8f8f8;padding:8px;border-radius:4px;">${escapeHtml(JSON.stringify(item, null, 2))}</pre>`;
                }
            }
            document.getElementById('cartModalContent').innerHTML = html;
            document.getElementById('cartItemModal').style.display = 'flex';
        }
        document.getElementById('closeCartModal').onclick = function() {
            document.getElementById('cartItemModal').style.display = 'none';
        };
        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/[&<>"']/g, function(m) {
                return ({
                    '&':'&','<':'<','>':'>','"':'"',"'":'&#39;'
                })[m];
            });
        }
        document.getElementById('cartItemModal').addEventListener('click', function(e) {
            if (e.target === this) this.style.display = 'none';
        });
        
        // Add event listeners for checkboxes
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('input[name="selected_items[]"]').forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectedTotal);
            });
            
            // Add event listener for checkout button
            document.getElementById('proceed-to-checkout-btn').addEventListener('click', proceedToCheckout);
        });
    </script>
</body>
</html>
