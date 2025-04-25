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
    <title>Your Blooming Cart</title>
    <style>
  body {
        font-family: 'Crimson Text', serif;
        background-color: #f8f0e3;
        margin: 0;
        display: flex;
        flex-direction: column; /* Stack items vertically in the body */
        justify-content: flex-start; /* Align items to the top initially */
        align-items: center; /* Center items horizontally in the body */
        min-height: 100vh;
    }
    .cart-container {
        max-width: 900px;
        background: #fff;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 8px 20px rgba(179, 93, 118, 0.15);
        margin-top: 20px; /* Adjust this value if needed for spacing below the navigation */
    }

    .cart-container h2 {
        text-align: center;
        color: #b86987;
        margin-bottom: 30px;
        font-size: 2.2em;
        font-weight: bold;
    }

    .cart-message {
        color: #4CAF50;
        text-align: center;
        margin-bottom: 20px;
        font-style: italic;
    }

    .cart-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 30px;
        border-spacing: 0;
    }

    .cart-table th {
        background-color: #f3dfe6;
        color: #777;
        padding: 15px;
        text-align: left;
        border-bottom: 2px solid #e8c4d0;
        font-weight: normal;
    }

    .cart-table td {
        padding: 15px;
        border-bottom: 1px solid #f3dfe6;
        text-align: center;
    }

    .cart-table td:first-child {
        text-align: left;
    }

    .cart-table img {
        height: 70px;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }

    .cart-table input[type="number"] {
        width: 70px;
        text-align: center;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 5px;
        appearance: none;
    }

    .cart-table input[type="number"]::-webkit-outer-spin-button,
    .cart-table input[type="number"]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .cart-table button {
        background-color: #e98fa9;
        color: white;
        padding: 10px 18px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        font-size: 0.9em;
    }

    .cart-table button:hover {
        background-color: #d15f7e;
    }

    .cart-actions {
        text-align: right;
        padding-top: 30px;
    }

    .cart-actions strong {
        font-size: 1.4em;
        color: #b86987;
        margin-right: 20px;
    }

    .cart-actions button {
        font-size: 1.1em;
        padding: 12px 24px;
        margin-left: 15px;
        border-radius: 10px;
    }

    .cart-actions a button {
        background-color: #a78bfa;
    }

    .cart-actions a button:hover {
        background-color: #8661d1;
    }

    .empty-cart {
        text-align: center;
        padding: 30px;
        color: #999;
        font-style: italic;
        font-size: 1.1em;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .cart-table th, .cart-table td {
            padding: 10px;
            font-size: 0.9em;
        }

        .cart-table img {
            height: 60px;
        }

        .cart-actions strong {
            font-size: 1.2em;
        }

        .cart-actions button {
            font-size: 1em;
            padding: 10px 20px;
        }
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
</style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Text:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    </head>
<body>
<?php include 'navi.php'; ?>

    <div class="cart-container">
        <h2>Your Blooming Cart</h2>
        <?php if (isset($_SESSION['cart_message'])): ?>
            <p class="cart-message"><?= $_SESSION['cart_message'] ?></p>
            <?php unset($_SESSION['cart_message']); ?>
        <?php endif; ?>

        <form method="POST" action="cart.php">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Image</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($cartItems)): ?>
                        <tr>
                            <td colspan="6" class="empty-cart">
                                Your cart is currently empty. Perhaps some beautiful blooms are calling your name?
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($cartItems as $item): ?>
                            <tr>
                                <td>
                                    <div class="product-info">
                                        <img src="uploads/<?= htmlspecialchars($item['product_image']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>">
                                        <div>
                                            <h3><?= htmlspecialchars($item['product_name']) ?></h3>
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
                                    </div>
                                </td>
                                <td>₱<?= number_format($item['product_price'], 2) ?></td>
                                <td>
                                    <input type="number" name="quantities[<?= $item['product_id'] ?>]" value="<?= $item['quantity'] ?>" min="1">
                                </td>
                                <td>₱<?= number_format($item['subtotal'], 2) ?></td>
                                <td>
                                    <button type="button" onclick="removeItem(<?= $item['product_id'] ?>)">Remove</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if (!empty($cartItems)): ?>
                <div class="cart-actions">
                    <strong>Total: ₱<?= number_format($total, 2) ?></strong>
                    <button type="submit" name="update_cart">Update Cart</button>
                    <a href="checkout.php"><button type="button">Proceed to Checkout</button></a>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <form id="removeForm" method="POST" action="cart.php" style="display:none;">
        <input type="hidden" name="remove_product_id" id="remove_product_id">
    </form>

    <script>
        function removeItem(productId) {
            document.getElementById('remove_product_id').value = productId;
            document.getElementById('removeForm').submit();
        }
    </script>

</body>
</html>