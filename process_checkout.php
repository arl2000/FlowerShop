<?php
session_start();
include 'db_connection.php'; // Make sure this connects properly

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get customer input
    $customer_name = $_POST['customer_name'];
    $customer_address = $_POST['customer_address'];
    $customer_email = $_POST['customer_email'];
    $customer_phone = $_POST['customer_phone'];
    $order_message = $_POST['order_message'] ?? null;
    $payment_method = $_POST['payment_method'] ?? 'cod';
    $order_status = 'pending';
    $proof_of_payment = null;

    // Get user ID from session
    if (!isset($_SESSION['user_id'])) {
        echo "Error: User not logged in.";
        exit;
    }
    $userId = $_SESSION['user_id'];

    // Fetch cart items from the database
    $stmt = $conn->prepare("SELECT product_id, product_name, product_price, quantity FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $cartItems = [];
    $total_amount = 0;
    while ($row = $result->fetch_assoc()) {
        $subtotal = $row['product_price'] * $row['quantity'];
        $total_amount += $subtotal;
        $cartItems[] = $row;
    }
    $stmt->close();

    if (empty($cartItems)) {
        echo "Error: Your cart is empty during order processing.";
        exit;
    }

    // Handle GCash file upload
    if ($payment_method === 'gcash' && isset($_FILES['proof_of_payment'])) {
        $file = $_FILES['proof_of_payment'];
        if ($file['error'] == 0) {
            $targetDir = "uploads/";
            if (!is_dir($targetDir)) mkdir($targetDir);
            $filename = time() . "_" . basename($file["name"]);
            $targetFile = $targetDir . $filename;

            if (move_uploaded_file($file["tmp_name"], $targetFile)) {
                $proof_of_payment = $targetFile;
            }
        }
    }

    // Insert order
    $stmt_orders = $conn->prepare("INSERT INTO orders (customer_name, customer_address, customer_email, customer_phone, order_message, proof_of_payment, order_status, payment_method, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt_orders->bind_param("ssssssssd", $customer_name, $customer_address, $customer_email, $customer_phone, $order_message, $proof_of_payment, $order_status, $payment_method, $total_amount);

    if ($stmt_orders->execute()) {
        $order_id = $conn->insert_id;

        // Insert each cart item into order_items
        $stmt_items = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, price_per_item, quantity) VALUES (?, ?, ?, ?, ?)");
        foreach ($cartItems as $item) {
            $product_id = $item['product_id'];
            $product_name = $item['product_name'];
            $price_per_item = $item['product_price'];
            $quantity = $item['quantity'];

            $stmt_items->bind_param("iisdi", $order_id, $product_id, $product_name, $price_per_item, $quantity);
            $stmt_items->execute();
        }
        $stmt_items->close();

        // Clear the user's cart after checkout
        $clear_cart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $clear_cart->bind_param("i", $userId);
        $clear_cart->execute();
        $clear_cart->close();

        echo "<script>alert('Order placed successfully! Waiting for admin approval.'); window.location.href='thank_you.php';</script>";
    } else {
        echo "Error creating order: " . $stmt_orders->error;
    }

    $stmt_orders->close();
    $conn->close();
}
?>
