<?php
include 'db_connection.php';
include 'navbar.php';

$update_message = ''; // Variable to store update feedback

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_order'])) {
        $order_id = $_POST['order_id'];
        $order_status = $_POST['order_status'];
        $expected_delivery_date = $_POST['expected_delivery_date'];
        $delivery_service = $_POST['delivery_service'];

        // First check if we have sufficient stock before updating to completed status
        if ($order_status === 'completed') {
            $items_query = "SELECT product_id, quantity FROM order_items WHERE order_id = '$order_id'";
            $items_result = mysqli_query($conn, $items_query);
            
            $insufficient_stock = false;
            $insufficient_products = [];
            
            while ($item = mysqli_fetch_assoc($items_result)) {
                $product_id = $item['product_id'];
                $quantity_ordered = $item['quantity'];
                
                // Check current stock
                $stock_query = "SELECT stock_count, product_name FROM products WHERE product_id = '$product_id'";
                $stock_result = mysqli_query($conn, $stock_query);
                $stock_data = mysqli_fetch_assoc($stock_result);
                
                if ($stock_data['stock_count'] < $quantity_ordered) {
                    $insufficient_stock = true;
                    $insufficient_products[] = $stock_data['product_name'];
                }
            }
            
            if ($insufficient_stock) {
                $update_message = "<p style='color: red;'>Cannot complete order #$order_id due to insufficient stock for: " . implode(', ', $insufficient_products) . "</p>";
                $order_status = 'processing'; // Revert status to processing
            }
        }

        // Update order status
        $sql = "UPDATE orders SET
                order_status='$order_status',
                expected_delivery_date='$expected_delivery_date',
                delivery_service='$delivery_service'
                WHERE order_id='$order_id'";

        if (mysqli_query($conn, $sql)) {
            // Deduct stock if order status is completed and we have sufficient stock
            if ($order_status === 'completed' && !$insufficient_stock) {
                $items_query = "SELECT product_id, quantity FROM order_items WHERE order_id = '$order_id'";
                $items_result = mysqli_query($conn, $items_query);

                while ($item = mysqli_fetch_assoc($items_result)) {
                    $product_id = $item['product_id'];
                    $quantity_ordered = $item['quantity'];

                    // Update stock in products table with proper error handling
                    $update_stock_sql = "
                        UPDATE products 
                        SET stock_count = stock_count - $quantity_ordered 
                        WHERE product_id = '$product_id'";

                    if (!mysqli_query($conn, $update_stock_sql)) {
                        $update_message = "<p style='color: red;'>Error updating stock for product ID $product_id: " . mysqli_error($conn) . "</p>";
                        break;
                    }
                }
            }

            if (!isset($update_message)) {
                $update_message = "<p style='color: green;'>Order #$order_id updated successfully!</p>";
            }
            // Redirect to orders.php
            header("Location: orders.php");
        } else {
            $update_message = "<p style='color: red;'>Error updating order #$order_id: " . mysqli_error($conn) . "</p>";
        }
    } else {
        // Code for new order submission (your original POST block)
        $name           = $_POST['customer_name'];
        $address        = $_POST['customer_address'];
        $email          = $_POST['customer_email'];
        $phone          = $_POST['customer_phone'];
        $message        = $_POST['order_message'];
        $status         = $_POST['order_status'];
        $payment_method = $_POST['payment_method'];
        $product_id     = $_POST['product_id'];
        $quantity       = $_POST['quantity'] ?? 1;
        $proof          = '';
        if (isset($_FILES['proof_of_payment']) && $_FILES['proof_of_payment']['error'] === UPLOAD_ERR_OK) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir);
            }
            $proof = $target_dir . basename($_FILES["proof_of_payment"]["name"]);
            move_uploaded_file($_FILES["proof_of_payment"]["tmp_name"], $proof);
        }
        $product_result = mysqli_query($conn, "SELECT product_name, price FROM products WHERE product_id = '$product_id'");
        $product_row = mysqli_fetch_assoc($product_result);
        $product_name = $product_row['product_name'];
        $price = $product_row['price'];
        $total_amount = $price * $quantity;
        $sql_insert = "INSERT INTO orders
                       (customer_name, customer_address, customer_email, customer_phone, order_message, proof_of_payment, order_status, product_id, quantity, total_amount, product_name, price, payment_method)
                       VALUES
                       ('$name', '$address', '$email', '$phone', '$message', '$proof', '$status', '$product_id', '$quantity', '$total_amount', '$product_name', '$price', '$payment_method')";
        mysqli_query($conn, $sql_insert);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Management</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #fff9fc;
            padding: 20px;
        }
        h1, h2 {
            color: #d15e97;
        }
        form.new-order-form {
            background-color: #fff;
            padding: 25px;
            border-radius: 10px;
            max-width: 700px;
            margin: 20px auto;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        input, textarea, select {
            width: calc(100% - 24px);
            padding: 12px;
            margin-bottom: 18px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
        label {
            font-weight: bold;
            color: #333;
            display: block;
            margin-bottom: 5px;
        }
        button {
            background-color: #d15e97;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 15px;
        }
        button:hover {
            background-color: #b44b7e;
        }
        .order-list {
            margin-top: 50px;
            max-width: 100%;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
        }
        th, td {
            padding: 14px;
            border: 1px solid #eee;
            text-align: center;
            white-space: nowrap;
        }
        th {
            background-color: #f8e6ee;
            color: #d15e97;
        }
        img {
            max-width: 80px;
            border-radius: 8px;
        }
        .status-select {
            width: 120px;
        }
        .product-details {
            text-align: left;
        }
        .product-item {
            margin-bottom: 5px;
            font-size: 0.9em;
            color: #555;
        }
    </style>
</head>
<body>

<div class="container">
   

    <div class="order-list">
        <h2>Order List</h2>
        <?php echo $update_message; ?>
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Name</th>
                    <th>Products</th> <th>Total</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                    <th>Expected Delivery</th>
                    <th>Delivery Brand</th>
                    <th>Proof</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $orders = mysqli_query($conn, "SELECT * FROM orders ORDER BY order_date DESC");
                while ($order = mysqli_fetch_assoc($orders)) {
                    echo "<tr>";
                    echo "<form method='POST' action='orders.php'>";
                    echo "<input type='hidden' name='order_id' value='" . $order['order_id'] . "'>";
                    echo "<td>" . $order['order_id'] . "</td>";
                    echo "<td>" . htmlspecialchars($order['customer_name']) . "</td>";
                    echo "<td class='product-details'>";
                    // Fetch and display order items for the current order
                    $order_id = $order['order_id'];
                    $items_result = mysqli_query($conn, "SELECT product_name, quantity, price_per_item FROM order_items WHERE order_id = '$order_id'");
                    while ($item = mysqli_fetch_assoc($items_result)) {
                        echo "<div class='product-item'>";
                        echo htmlspecialchars($item['product_name']) . " (Qty: " . htmlspecialchars($item['quantity']) . ")";
                        echo "</div>";
                    }
                    echo "</td>";
                    echo "<td><strong>₱" . number_format($order['total_amount'], 2) . "</strong></td>";
                    echo "<td>" . htmlspecialchars($order['payment_method']) . "</td>";
                    echo "<td>
                            <select class='status-select' name='order_status'>
                                <option value='pending'" . ($order['order_status'] == 'pending' ? ' selected' : '') . ">Pending</option>
                                <option value='processing'" . ($order['order_status'] == 'processing' ? ' selected' : '') . ">Processing</option>
                                <option value='approved'" . ($order['order_status'] == 'approved' ? ' selected' : '') . ">Approved</option>
                                <option value='declined'" . ($order['order_status'] == 'declined' ? ' selected' : '') . ">Declined</option>
                                <option value='cancelled'" . ($order['order_status'] == 'cancelled' ? ' selected' : '') . ">Cancelled</option>
                                <option value='completed'" . ($order['order_status'] == 'completed' ? ' selected' : '') . ">Completed</option>
                            </select>
                        </td>";
                    echo "<td><input type='date' name='expected_delivery_date' value='" . $order['expected_delivery_date'] . "'></td>";
                    echo "<td><input type='text' name='delivery_service' value='" . htmlspecialchars($order['delivery_service']) . "'></td>";
                    echo "<td><img src='" . $order['proof_of_payment'] . "' alt='Proof'></td>";
                    echo "<td>" . $order['order_date'] . "</td>";
                    echo "<td><button type='submit' name='update_order'>Update</button></td>";
                    echo "</form>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>