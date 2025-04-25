<?php
include 'db_connection.php';
include 'navbar.php';

// Initialization
$totalRevenue = 0;
$orderCount = 0;
$mostSoldProducts = [];
$salesByDay = [];
$topCustomers = [];
$monthlyRevenue = array_fill(1, 12, 0);

// Total Revenue
$revenueQuery = $conn->query("SELECT SUM(total_amount) AS revenue FROM orders WHERE order_status = 'completed'");
if ($revenueQuery && $row = $revenueQuery->fetch_assoc()) {
    $totalRevenue = $row['revenue'] ?? 0;
}

// Order Count
$orderQuery = $conn->query("SELECT COUNT(*) AS order_count FROM orders WHERE order_status = 'completed'");
if ($orderQuery && $row = $orderQuery->fetch_assoc()) {
    $orderCount = $row['order_count'] ?? 0;
}

// Top 5 Products (fixed to pull from order_items)
$productQuery = $conn->query("
    SELECT oi.product_name, SUM(oi.quantity) AS total_sold
    FROM order_items oi
    INNER JOIN orders o ON oi.order_id = o.order_id
    WHERE o.order_status = 'completed'
    GROUP BY oi.product_name
    ORDER BY total_sold DESC
    LIMIT 5
");
if ($productQuery) {
    while ($row = $productQuery->fetch_assoc()) {
        $mostSoldProducts[] = $row;
    }
}

// Sales Last 7 Days
$dailySalesQuery = $conn->query("
    SELECT DATE(order_date) AS date, SUM(total_amount) AS revenue
    FROM orders
    WHERE order_status = 'completed' AND order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(order_date)
    ORDER BY date ASC
");
if ($dailySalesQuery) {
    while ($row = $dailySalesQuery->fetch_assoc()) {
        $salesByDay[$row['date']] = $row['revenue'];
    }
}

$highestDay = !empty($salesByDay) ? array_keys($salesByDay, max($salesByDay))[0] : null;
$lowestDay  = !empty($salesByDay) ? array_keys($salesByDay, min($salesByDay))[0] : null;
$avgRevenue = $orderCount > 0 ? $totalRevenue / $orderCount : 0;

// Monthly Revenue
$monthlyQuery = $conn->query("
    SELECT MONTH(order_date) AS month, SUM(total_amount) AS revenue
    FROM orders
    WHERE order_status = 'completed' AND YEAR(order_date) = YEAR(CURDATE())
    GROUP BY MONTH(order_date)
");
if ($monthlyQuery) {
    while ($row = $monthlyQuery->fetch_assoc()) {
        $monthlyRevenue[(int)$row['month']] = (float)$row['revenue'];
    }
}

// Top Customers
$customerQuery = $conn->query("
    SELECT customer_name, SUM(total_amount) AS total_spent
    FROM orders
    WHERE order_status = 'completed'
    GROUP BY customer_name
    ORDER BY total_spent DESC
    LIMIT 5
");
if ($customerQuery) {
    while ($row = $customerQuery->fetch_assoc()) {
        $topCustomers[] = $row;
    }
}
?>

<!-- HTML + Chart.js -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Analytics</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* styles from your previous code for layout, colors, and buttons */
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #fff8f4;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 900px;
            margin: auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(255, 192, 203, 0.3);
        }
        .card {
            background: #fdf0e7;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 10px solid #f8b6b6;
            border-radius: 12px;
        }
        h1 {
            text-align: center;
            color: #d77a7a;
        }
        .highlight {
            color: #d77a7a;
            font-weight: bold;
        }
        .chart-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .chart-grid .card {
            flex: 1 1 45%;
        }
        table {
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
        }
        td, th {
            padding: 6px;
        }
        th {
            text-align: left;
        }
        .download-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #d77a7a;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            margin: 20px 20px
        }
    </style>
</head>
<body>
<div class="container">
    <h1>📊 Sales Analytics</h1>

    <div style="text-align: center;">
        <a href="download_analytics.php" class="download-btn">⬇️ Download Analytics (Word)</a>
    </div>

    <div class="card">
        <strong>📊 Summary:</strong><br><br>
        🔹 <span class="highlight">Avg Revenue/Order:</span> ₱<?= number_format($avgRevenue, 2) ?><br>
        🔺 <span class="highlight">Highest Day:</span> <?= $highestDay ? "$highestDay (₱" . number_format($salesByDay[$highestDay], 2) . ")" : 'N/A' ?><br>
        🔻 <span class="highlight">Lowest Day:</span> <?= $lowestDay ? "$lowestDay (₱" . number_format($salesByDay[$lowestDay], 2) . ")" : 'N/A' ?>
    </div>

    <div class="card">
        ✅ <span class="highlight">Completed Orders:</span> <?= $orderCount ?><br>
        💰 <span class="highlight">Total Revenue:</span> ₱<?= number_format($totalRevenue, 2) ?>
    </div>

    <div class="chart-grid">
        <!-- Sales Chart -->
        <div class="card">
            <strong>🗓️ Last 7 Days Sales</strong>
            <canvas id="salesChart"></canvas>
        </div>

        <!-- Monthly Revenue -->
        <div class="card">
            <strong>📆 Monthly Revenue</strong>
            <canvas id="monthlyChart"></canvas>
        </div>

        <!-- Top Customers -->
        <div class="card">
            <strong>👥 Top Customers</strong>
            <canvas id="customerChart"></canvas>
        </div>

        <!-- Top Products -->
        <div class="card">
            <strong>🏆 Top Products</strong>
            <canvas id="productChart"></canvas>
            <table>
                <thead><tr><th>Product</th><th>Sold</th></tr></thead>
                <tbody>
                <?php foreach ($mostSoldProducts as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['product_name']) ?></td>
                        <td><?= $p['total_sold'] ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($mostSoldProducts)) echo "<tr><td colspan='2'>No data.</td></tr>"; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    const salesByDay = <?= json_encode($salesByDay) ?>;
    const monthlyRevenue = <?= json_encode(array_values($monthlyRevenue)) ?>;
    const topCustomers = <?= json_encode($topCustomers) ?>;
    const topProducts = <?= json_encode($mostSoldProducts) ?>;

    new Chart(document.getElementById('salesChart'), {
        type: 'bar',
        data: {
            labels: Object.keys(salesByDay),
            datasets: [{
                label: '₱ Revenue',
                data: Object.values(salesByDay),
                backgroundColor: '#f8b6b6'
            }]
        }
    });

    new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: '₱ Revenue',
                data: monthlyRevenue,
                backgroundColor: '#ffcd56'
            }]
        }
    });

    new Chart(document.getElementById('customerChart'), {
        type: 'bar',
        data: {
            labels: topCustomers.map(c => c.customer_name),
            datasets: [{
                label: '₱ Spent',
                data: topCustomers.map(c => c.total_spent),
                backgroundColor: '#4bc0c0'
            }]
        },
        options: { indexAxis: 'y' }
    });

    new Chart(document.getElementById('productChart'), {
        type: 'pie',
        data: {
            labels: topProducts.map(p => p.product_name),
            datasets: [{
                data: topProducts.map(p => p.total_sold),
                backgroundColor: ['#ff6384', '#36a2eb', '#ff9f40', '#9966ff', '#00d4c3']
            }]
        }
    });
</script>
</body>
</html>
