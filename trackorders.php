<?php
session_start();
include 'db_connection.php'; // Include your database connection

// Ensure the user is logged in
// if (!isset($_SESSION['id'])) {
//     header("Location: login.php");
//     exit();
// }

// Retrieve the email from session
if (isset($_SESSION['email'])) {
    $customer_email = filter_var($_SESSION['email'], FILTER_SANITIZE_EMAIL); // Sanitize email
} else {
    echo "No email found in session.";
    exit();
}

$orders = [];

// Fetch ALL orders for the logged-in user's email
$stmt_orders = $conn->prepare("SELECT order_id, order_date, total_amount, order_status FROM orders WHERE customer_email = ? ORDER BY order_date DESC");
$stmt_orders->bind_param("s", $customer_email);
$stmt_orders->execute();
$result_orders = $stmt_orders->get_result();
$orders = $result_orders->fetch_all(MYSQLI_ASSOC);
$stmt_orders->close();

// Grouping items by order_id
$order_details = [];

foreach ($orders as $order) {
    $order_id = $order['order_id'];
    
    // Fetch the items for each order
    $stmt_items = $conn->prepare("SELECT product_name, quantity, price_per_item FROM order_items WHERE order_id = ?");
    $stmt_items->bind_param("i", $order_id);
    $stmt_items->execute();
    $result_items = $stmt_items->get_result();
    $items = $result_items->fetch_all(MYSQLI_ASSOC);
    $stmt_items->close();

    // Store the order with its items
    $order_details[] = [
        'order' => $order,
        'items' => $items
    ];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders - Track Order</title>
    <style>
        .order-container {
            border: 1px solid #ddd;
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            color: #555;
            font-size: 0.9em;
        }

        .order-status-bar {
            display: flex;
            justify-content: space-around;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 6px;
        }

        .status-item {
            flex: 1;
            text-align: center;
            padding: 8px;
            border-radius: 4px;
            color: #888;
            font-size: 0.8em;
            position: relative;
        }

        .status-item:not(:last-child)::after {
            content: "";
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 1px;
            height: 1.2em;
            background-color: #eee;
        }

        .status-item.active {
            background-color: #d15e97;
            color: white;
        }

        .order-details {
            margin-top: 15px;
            line-height: 1.6;
        }

        .order-details strong {
            font-weight: bold;
            color: #333;
        }

        .order-item {
            margin-bottom: 8px;
            padding-left: 15px;
            font-size: 0.95em;
            color: #666;
        }

        .declined-order {
            border-color: #f44336;
            background-color: #ffebee;
        }

        .container {
            max-width: 960px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fefefe;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        h2 {
            color: #d15e97;
            margin-bottom: 20px;
            text-align: center;
        }

        p {
            color: #777;
            text-align: center;
        }
    </style>
</head>
<body>
    <?php include 'navi.php'; ?>

    <div class="container">
        <h2>Track My Orders</h2>

        <?php if (empty($order_details)): ?>
            <p>You haven't placed any orders yet.</p>
        <?php else: ?>
            <?php foreach ($order_details as $order_detail): ?>
                <div class="order-container <?= ($order_detail['order']['order_status'] == 'declined') ? 'declined-order' : '' ?>" data-order-id="<?= htmlspecialchars($order_detail['order']['order_id']) ?>">
                    <div class="order-header">
                        <div>Order ID: <?= htmlspecialchars($order_detail['order']['order_id']) ?></div>
                        <div>Date: <?= date("F j, Y", strtotime($order_detail['order']['order_date'])) ?></div>
                        <div>Total: ₱<?= number_format($order_detail['order']['total_amount'], 2) ?></div>
                    </div>

                    <div class="order-status-bar">
                        <div class="status-item <?= ($order_detail['order']['order_status'] == 'pending') ? 'active' : '' ?>">Order Placed</div>
                        <div class="status-item <?= ($order_detail['order']['order_status'] == 'approved') ? 'active' : '' ?>">Approved</div>
                        <div class="status-item <?= ($order_detail['order']['order_status'] == 'shipped') ? 'active' : '' ?>">Shipped</div>
                        <div class="status-item <?= ($order_detail['order']['order_status'] == 'delivered') ? 'active' : '' ?>">Delivered</div>
                        <div class="status-item <?= ($order_detail['order']['order_status'] == 'declined') ? 'active' : '' ?>">Declined</div>
                        <div class="status-item <?= ($order_detail['order']['order_status'] == 'cancelled') ? 'active' : '' ?>">Cancelled</div>
                        <div class="status-item <?= ($order_detail['order']['order_status'] == 'completed') ? 'active' : '' ?>">Completed</div>
                    </div>

                    <div class="order-details">
                        <p><strong>Status:</strong> <span class="order-status-text"><?= ucfirst(htmlspecialchars($order_detail['order']['order_status'])) ?></span></p>
                        <?php if (!empty($order_detail['items'])): ?>
                            <p><strong>Items:</strong></p>
                            <?php foreach ($order_detail['items'] as $item): ?>
                                <div class="order-item">
                                    <?= htmlspecialchars($item['product_name']) ?> (Qty: <?= htmlspecialchars($item['quantity']) ?>) - ₱<?= number_format($item['price_per_item'], 2) ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
