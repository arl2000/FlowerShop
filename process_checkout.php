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

    // Get selected items from session
    $selectedItems = isset($_SESSION['selected_items']) ? $_SESSION['selected_items'] : [];
    
    if (empty($selectedItems)) {
        echo "Error: No items selected for checkout.";
        exit;
    }

    // Convert selected items to integers for safety
    $selectedItems = array_map('intval', $selectedItems);

    // Create placeholders for the IN clause
    $placeholders = str_repeat('?,', count($selectedItems) - 1) . '?';
    $types = str_repeat('i', count($selectedItems));

    // Fetch only the selected items from the cart
    $stmt = $conn->prepare("SELECT product_id, product_name, product_price, quantity, is_customized FROM cart WHERE user_id = ? AND product_id IN ($placeholders)");
    $params = array_merge([$userId], $selectedItems);
    $stmt->bind_param("i" . $types, ...$params);
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
        echo "Error: No items found for checkout.";
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
            $is_customized = $item['is_customized'] ?? 0;

            // If it's a customized product, first insert it into products table
            if ($is_customized) {
                $insert_product = $conn->prepare("INSERT INTO products (product_name, price, product_price, product_description, product_image) VALUES (?, ?, ?, ?, ?)");
                $default_desc = "Customized product";
                $default_image = "default.jpg";
                $insert_product->bind_param("sddss", $product_name, $price_per_item, $price_per_item, $default_desc, $default_image);
                $insert_product->execute();
                $product_id = $conn->insert_id;
                $insert_product->close();
            }

            $stmt_items->bind_param("iisdi", $order_id, $product_id, $product_name, $price_per_item, $quantity);
            $stmt_items->execute();
        }
        $stmt_items->close();

        // Clear only the selected items from cart
        $placeholders = str_repeat('?,', count($selectedItems) - 1) . '?';
        $types = str_repeat('i', count($selectedItems));
        $clear_cart = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id IN ($placeholders)");
        $params = array_merge([$userId], $selectedItems);
        $clear_cart->bind_param("i" . $types, ...$params);
        $clear_cart->execute();
        $clear_cart->close();

        echo "<script>alert('Order placed successfully! Waiting for admin approval.'); window.location.href='trackorders.php';</script>";
    } else {
        echo "Error creating order: " . $stmt_orders->error;
    }

    $stmt_orders->close();
    $conn->close();
}
?>
