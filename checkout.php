<?php
session_start();
include 'db_connection.php';

// Check if the user is logged in
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    header("Location: login.php");
    exit();
}

// Fetch cart items from the database
$selectedItems = isset($_POST['selected_items']) ? $_POST['selected_items'] : [];

if (empty($selectedItems)) {
    $_SESSION['checkout_message'] = "Please select at least one item to checkout.";
    header("Location: cart.php");
    exit();
}

// Convert selected items to integers for safety
$selectedItems = array_map('intval', $selectedItems);

// Create placeholders for the IN clause
$placeholders = str_repeat('?,', count($selectedItems) - 1) . '?';
$types = str_repeat('i', count($selectedItems));

// Fetch only the selected items from the cart
$stmt = $conn->prepare("SELECT product_id, product_name, product_image, product_price, quantity, is_customized, 
    ribbon_color_id, ribbon_color_name, ribbon_color_price, 
    wrapper_color_id, wrapper_color_name, wrapper_color_price, 
    customer_message, addons
    FROM cart WHERE user_id = ? AND product_id IN ($placeholders)");
$params = array_merge([$userId], $selectedItems);
$stmt->bind_param("i" . $types, ...$params);
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

// Store selected items in session for process_checkout.php
$_SESSION['selected_items'] = $selectedItems;

// Handle checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $name = $_POST['customer_name'];
    $address = $_POST['customer_address'];
    $email = $_POST['customer_email'];
    $phone = $_POST['customer_phone'];
    $message = $_POST['order_message'];
    $paymentMethod = $_POST['payment_method'];
    $proofOfPayment = $_FILES['proof_of_payment']['name'] ?? null;

    if (empty($name) || empty($address) || empty($email) || empty($phone) || empty($paymentMethod)) {
        $_SESSION['checkout_message'] = "Please fill in all the required fields.";
        header("Location: checkout.php");
        exit();
    }

    // Upload proof if exists
    if ($proofOfPayment) {
        $uploadPath = "uploads/" . basename($proofOfPayment);
        move_uploaded_file($_FILES['proof_of_payment']['tmp_name'], $uploadPath);
    }

    // Insert a single order record
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, shipping_name, shipping_address, shipping_phone, order_message, proof_of_payment, payment_method, order_status, order_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
    $stmt->bind_param("idssssss", $userId, $total, $name, $address, $phone, $message, $proofOfPayment, $paymentMethod);
    $stmt->execute();
    $orderId = $stmt->insert_id;
    $stmt->close();

    // Insert each item into order_items table
    foreach ($cartItems as $item) {
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, product_name) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiids", $orderId, $item['product_id'], $item['quantity'], $item['product_price'], $item['product_name']);
        $stmt->execute();
        $stmt->close();
    }

    // Clear only the selected items from cart
    $placeholders = str_repeat('?,', count($selectedItems) - 1) . '?';
    $types = str_repeat('i', count($selectedItems));
    $clear_cart = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id IN ($placeholders)");
    $params = array_merge([$userId], $selectedItems);
    $clear_cart->bind_param("i" . $types, ...$params);
    $clear_cart->execute();
    $clear_cart->close();

    $_SESSION['checkout_message'] = "Thank you for your order! Your order has been placed successfully.";
    header("Location: orders.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #fff0f5;
            padding: 20px;
        }
        form {
            background: white;
            padding: 20px;
            border-radius: 10px;
            max-width: 600px;
            margin: auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        input, textarea, select {
            width: 100%;
            margin-bottom: 15px;
            padding: 10px;
        }
        label {
            font-weight: bold;
        }
        .gcash-info {
            background-color: #f0f8ff;
            padding: 10px;
            border-left: 5px solid #6a5acd;
            margin-bottom: 20px;
        }
        .order-summary {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        .summary-item {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #eee;
        }
        .summary-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .total-amount {
            font-size: 1.2em;
            font-weight: bold;
            margin-top: 15px;
            text-align: right;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <?php include 'navi.php'; ?>

    <div class="delivery-notice-container" style="text-align: center; margin-top: 20px;">
        <div class="delivery-notice" style="display: inline-block; background-color: #f9f9f9; padding: 15px; border-radius: 8px; border: 1px solid #ddd;">
            <span style="position: relative; top: 2px; margin-right: 8px;">
                <i class="fas fa-info-circle" style="color: #007bff;"></i>
            </span>
            <p style="font-size: 0.9em; color: #555; margin: 0;">
                Delivery within Western Visayas may take 3-5 business days. For other locations in the Philippines, please allow 5-7 business days, depending on the specific area.
            </p>
        </div>
    </div>

    <h2 style="text-align:center;">Checkout</h2>

    <form action="process_checkout.php" method="POST" enctype="multipart/form-data">
        <label>Full Name:</label>
        <input type="text" name="customer_name" required>

        <label>Address:</label>
        <textarea name="customer_address" required></textarea>

        <label>Email:</label>
        <input type="email" name="customer_email" required>

        <label>Phone Number:</label>
        <input type="text" name="customer_phone" required>

        <label>Order Message (optional):</label>
        <textarea name="order_message"></textarea>

        <label>Payment Method:</label>
        <select name="payment_method" required onchange="toggleProof(this.value)">
            <option value="cod">Cash on Delivery</option>
            <option value="gcash">GCash</option>
        </select>

        <div id="gcashDetails" style="display:none;" class="gcash-info">
            <p><strong>Send Payment to:</strong></p>
            <p>GCash Name: <strong>Heavenly Bloom</strong></p>
            <img src="uploads/scan_me_qr_code.jpg" alt="GCash QR Code" style="width: 100px; height: 100px;">
            <p>GCash Number: <strong>09123456789</strong></p>
            <label>Upload GCash Proof of Payment (screenshot):</label>
            <input type="file" name="proof_of_payment" accept="image/*">
        </div>

        <!-- Cart Summary -->
        <h3>Cart Summary</h3>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cartItems as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                        <td>₱<?= number_format($item['product_price'], 2) ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td>₱<?= number_format($item['subtotal'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3>Total: ₱<?= number_format($total, 2) ?></h3>

        <button type="submit">Place Order</button>
    </form>

    <script>
    function toggleProof(value) {
        document.getElementById('gcashDetails').style.display = (value === 'gcash') ? 'block' : 'none';
    }
</script>
</body>
</html>
