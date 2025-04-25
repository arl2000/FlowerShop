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
            justify-content: space-between;
            margin: 30px 0;
            position: relative;
        }

        .status-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
            width: 60px;
        }

        .status-circle {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background-color: #d8d8d8;
            margin-bottom: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .status-circle.active {
            background-color: #d15e97;
        }

        .status-line {
            position: absolute;
            top: 12px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: #d8d8d8;
            z-index: 1;
        }

        .status-line-progress {
            position: absolute;
            top: 12px;
            left: 0;
            height: 2px;
            background-color: #d15e97;
            z-index: 1;
        }

        .status-text {
            font-size: 0.8em;
            text-align: center;
            color: #888;
            max-width: 80px;
        }

        .status-text.active {
            color: #333;
            font-weight: bold;
        }

        .status-icon {
            color: white;
            font-size: 12px;
        }

        .order-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .order-number {
            font-weight: bold;
            font-size: 1.2em;
        }

        .order-expected {
            text-align: right;
            color: #666;
            font-size: 0.9em;
        }

        .order-product {
            color: #888;
            font-size: 0.8em;
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
                    <div class="order-info">
                        <div>
                            <div class="order-number">ORDER #<?= htmlspecialchars($order_detail['order']['order_id']) ?></div>
                            <div class="order-product">
                                <?php if (!empty($order_detail['items']) && count($order_detail['items']) > 0): ?>
                                    <?= htmlspecialchars($order_detail['items'][0]['product_name']) ?>
                                    <?php if (count($order_detail['items']) > 1): ?>
                                        and <?= count($order_detail['items']) - 1 ?> more item(s)
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="order-expected">
                            <div>Expected Arrival <?= date("m/d/y", strtotime("+7 days", strtotime($order_detail['order']['order_date']))) ?></div>
                        </div>
                    </div>

                    <?php
                        // Define the order status sequence
                        $statuses = ['pending', 'approved', 'shipped', 'in_route', 'delivered'];
                        $statusLabels = ['Order Processed', 'Order Designing', 'Order Shipped', 'Order In Route', 'Order Arrived'];
                        $icons = ['✓', '✓', '✓', '✓', '✓'];
                        
                        // Determine the current status index
                        $currentStatus = $order_detail['order']['order_status'];
                        $currentIndex = 0;
                        
                        if ($currentStatus == 'pending') $currentIndex = 0;
                        else if ($currentStatus == 'approved') $currentIndex = 1;
                        else if ($currentStatus == 'shipped') $currentIndex = 2;
                        else if ($currentStatus == 'delivered') $currentIndex = 4;
                        
                        // Calculate progress percentage
                        $progressWidth = ($currentIndex / (count($statuses) - 1)) * 100;
                    ?>

                    <div class="order-status-bar">
                        <div class="status-line"></div>
                        <div class="status-line-progress" style="width: <?= $progressWidth ?>%;"></div>
                        
                        <?php for($i = 0; $i < count($statuses); $i++): ?>
                            <div class="status-item">
                                <div class="status-circle <?= ($i <= $currentIndex) ? 'active' : '' ?>">
                                    <?php if ($i <= $currentIndex): ?>
                                        <span class="status-icon"><?= $icons[$i] ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="status-text <?= ($i <= $currentIndex) ? 'active' : '' ?>">
                                    <?= $statusLabels[$i] ?>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>

                    <div class="order-details">
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
