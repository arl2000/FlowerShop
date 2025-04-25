<?php
session_start();
include 'db_connection.php';

// Count orders
$order_query = "SELECT COUNT(*) as total_orders FROM orders";
$order_result = mysqli_query($conn, $order_query);
$order_data = mysqli_fetch_assoc($order_result);
$total_orders = $order_data['total_orders'];

// Count products
$product_query = "SELECT COUNT(*) as total_products FROM products";
$product_result = mysqli_query($conn, $product_query);
$product_data = mysqli_fetch_assoc($product_result);
$total_products = $product_data['total_products'];

// Count customized products
$custom_query = "SELECT COUNT(*) as total_customized FROM customized_products";
$custom_result = mysqli_query($conn, $custom_query);
$custom_data = mysqli_fetch_assoc($custom_result);
$total_customized = $custom_data['total_customized'];

// Weekly Orders
$weekly_data = [];
$weekly_query = $conn->query("
    SELECT DATE(order_date) AS order_day, COUNT(*) AS order_count 
    FROM orders 
    WHERE order_date >= CURDATE() - INTERVAL 6 DAY 
    GROUP BY order_day
");
while ($row = $weekly_query->fetch_assoc()) {
    $weekly_data[] = $row;
}

// Monthly Orders
$monthly_data = [];
$monthly_query = $conn->query("
    SELECT MONTHNAME(order_date) AS month, COUNT(*) AS order_count 
    FROM orders 
    WHERE YEAR(order_date) = YEAR(CURDATE()) 
    GROUP BY MONTH(order_date)
");
while ($row = $monthly_query->fetch_assoc()) {
    $monthly_data[] = $row;
}

// Yearly Orders
$yearly_data = [];
$yearly_query = $conn->query("
    SELECT YEAR(order_date) AS year, COUNT(*) AS order_count 
    FROM orders 
    GROUP BY YEAR(order_date)
");
while ($row = $yearly_query->fetch_assoc()) {
    $yearly_data[] = $row;
}

// Most Sold Flower (Product)
$most_sold_flower_query = $conn->query("
    SELECT p.product_name AS flower_name, MONTHNAME(o.order_date) AS month, YEAR(o.order_date) AS year, SUM(oi.quantity) AS total_sold
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    JOIN orders o ON oi.order_id = o.order_id
    GROUP BY oi.product_id, MONTH(o.order_date), YEAR(o.order_date)
    ORDER BY total_sold DESC
    LIMIT 1
");
$most_sold_flower = $most_sold_flower_query->fetch_assoc();

// Top Buyers
$top_buyers_query = $conn->query("
    SELECT customer_name, COUNT(*) AS orders_count 
    FROM orders 
    GROUP BY customer_name 
    ORDER BY orders_count DESC 
    LIMIT 3
");
$top_buyers = [];
while ($row = $top_buyers_query->fetch_assoc()) {
    $top_buyers[] = $row;
}

// Top Address
$top_address_query = $conn->query("
    SELECT customer_address, COUNT(*) AS total_orders 
    FROM orders 
    GROUP BY customer_address 
    ORDER BY total_orders DESC 
    LIMIT 1
");
$top_address = $top_address_query->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --purple-gradient: linear-gradient(135deg, #8e44ad 0%, #9b59b6 100%);
            --orange-gradient: linear-gradient(135deg, #e67e22 0%, #f39c12 100%);
            --blue-gradient: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            --green-gradient: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
        }

        body {
            background-color: #f5f6fa;
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }

        .dashboard {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .dashboard-header h1 {
            color: #2c3e50;
            font-size: 1.8rem;
            font-weight: 600;
            margin: 0;
        }

        .boxes {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .box {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s;
        }

        .box:hover {
            transform: translateY(-5px);
        }

        .stat-card {
            display: flex;
            flex-direction: column;
        }

        .stat-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.2rem;
        }

        .stat-title {
            font-size: 0.9rem;
            color: #7f8c8d;
            margin: 0;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 600;
            color: #2c3e50;
            margin: 0.5rem 0;
        }

        .stat-change {
            font-size: 0.9rem;
            color: #27ae60;
        }

        .chart-container {
            position: relative;
            height: 200px;
            margin-top: 1rem;
        }

        canvas {
            width: 100% !important;
            height: 100% !important;
        }

        .purple-gradient { background: var(--purple-gradient); }
        .orange-gradient { background: var(--orange-gradient); }
        .blue-gradient { background: var(--blue-gradient); }
        .green-gradient { background: var(--green-gradient); }

        .top-buyers-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .top-buyers-list li {
            display: flex;
            justify-content: space-between;
            padding: 0.8rem 0;
            border-bottom: 1px solid #ecf0f1;
        }

        .top-buyers-list li:last-child {
            border-bottom: none;
        }

        .chart-controls {
            display: flex;
            gap: 10px;
            margin-bottom: 1rem;
        }

        .chart-controls select {
            padding: 6px 12px;
            border: 1px solid #e1e1e1;
            border-radius: 8px;
            background: white;
            font-size: 0.9rem;
            color: #2c3e50;
            cursor: pointer;
            transition: all 0.2s;
        }

        .chart-controls select:hover {
            border-color: #8e44ad;
        }

        .chart-controls select:focus {
            outline: none;
            border-color: #8e44ad;
            box-shadow: 0 0 0 2px rgba(142, 68, 173, 0.1);
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="dashboard">
    <div class="dashboard-header">
        <h1>Dashboard Overview</h1>
    </div>

    <a href="inventory.php" class="inventory-link">
        <i class="fas fa-boxes"></i> VIEW INVENTORY
    </a>

    <!-- Main Stats -->
    <div class="boxes">
        <div class="box">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon purple-gradient">📦</div>
                    <h3 class="stat-title">Total Orders</h3>
                </div>
                <div class="stat-value"><?= $total_orders ?></div>
                <div class="chart-controls">
                    <select class="chart-type" data-chart="orders">
                        <option value="line">Line Chart</option>
                        <option value="bar">Bar Chart</option>
                        <option value="pie">Pie Chart</option>
                    </select>
                    <select class="time-period" data-chart="orders">
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                        <option value="year">This Year</option>
                    </select>
                </div>
                <div class="chart-container">
                    <canvas id="weeklyChart"></canvas>
                </div>
            </div>
        </div>

        <div class="box">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon orange-gradient">🌺</div>
                    <h3 class="stat-title">Total Products</h3>
                </div>
                <div class="stat-value"><?= $total_products ?></div>
                <div class="chart-controls">
                    <select class="chart-type" data-chart="products">
                        <option value="line">Line Chart</option>
                        <option value="bar">Bar Chart</option>
                        <option value="pie">Pie Chart</option>
                    </select>
                    <select class="time-period" data-chart="products">
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                        <option value="year">This Year</option>
                    </select>
                </div>
                <div class="chart-container">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>

        <div class="box">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon blue-gradient">✨</div>
                    <h3 class="stat-title">Customized Products</h3>
                </div>
                <div class="stat-value"><?= $total_customized ?></div>
                <div class="chart-controls">
                    <select class="chart-type" data-chart="custom">
                        <option value="line">Line Chart</option>
                        <option value="bar">Bar Chart</option>
                        <option value="pie">Pie Chart</option>
                    </select>
                    <select class="time-period" data-chart="custom">
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                        <option value="year">This Year</option>
                    </select>
                </div>
                <div class="chart-container">
                    <canvas id="yearlyChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Stats -->
    <div class="boxes">
        <div class="box">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon green-gradient">🌸</div>
                    <h3 class="stat-title">Most Sold Flower</h3>
                </div>
                <div class="stat-value"><?= $most_sold_flower ? $most_sold_flower['flower_name'] : 'No data' ?></div>
                <div class="stat-change"><?= $most_sold_flower ? "{$most_sold_flower['month']} {$most_sold_flower['year']}" : '' ?></div>
            </div>
        </div>

        <div class="box">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon purple-gradient">🏠</div>
                    <h3 class="stat-title">Top Delivery Location</h3>
                </div>
                <div class="stat-value"><?= $top_address ? $top_address['customer_address'] : 'No data' ?></div>
            </div>
        </div>

        <div class="box">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon orange-gradient">👑</div>
                    <h3 class="stat-title">Top Buyers</h3>
                </div>
                <ul class="top-buyers-list">
                    <?php foreach ($top_buyers as $buyer): ?>
                        <li>
                            <span><?= htmlspecialchars($buyer['customer_name']) ?></span>
                            <span class="stat-change">+<?= $buyer['orders_count'] ?> orders</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    // Chart configuration factory
    function createChartConfig(type, data, labels, colors) {
        const ctx = document.createElement('canvas').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 200);
        gradient.addColorStop(0, `${colors.primary}20`);
        gradient.addColorStop(1, `${colors.primary}00`);

        const config = {
            type: type,
            data: {
                labels: labels,
                datasets: [{
                    label: 'Data',
                    data: data,
                    fill: true,
                    backgroundColor: type === 'pie' ? colors.pieColors : gradient,
                    borderColor: type === 'pie' ? 'white' : colors.primary,
                    borderWidth: 2,
                    tension: type === 'line' ? 0.4 : 0,
                    pointRadius: type === 'line' ? 0 : null,
                    pointHoverRadius: type === 'line' ? 4 : null,
                    pointHoverBackgroundColor: colors.primary
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: type === 'pie' }
                },
                scales: type !== 'pie' ? {
                    x: {
                        grid: { display: false },
                        ticks: { display: true }
                    },
                    y: {
                        grid: { 
                            borderDash: [5, 5],
                            drawBorder: false
                        },
                        ticks: { display: true }
                    }
                } : {}
            }
        };

        return config;
    }

    // Chart instances
    let ordersChart, productsChart, customChart;

    // Color schemes
    const chartColors = {
        orders: {
            primary: '#8e44ad',
            pieColors: ['#8e44ad', '#9b59b6', '#ac6cc1', '#bd7fc9', '#ce92d1']
        },
        products: {
            primary: '#e74c3c',
            pieColors: ['#e74c3c', '#ec7063', '#f19f97', '#f5b7b1', '#f9cfcc']
        },
        custom: {
            primary: '#3498db',
            pieColors: ['#3498db', '#5faee3', '#8bc4ea', '#b7daf2', '#e3f2fa']
        }
    };

    // Initialize charts
    function initializeCharts() {
        // Orders Chart
        const ordersCtx = document.getElementById('weeklyChart').getContext('2d');
        ordersChart = new Chart(ordersCtx, createChartConfig('line', 
            <?= json_encode(array_column($weekly_data, 'order_count')) ?>, 
            <?= json_encode(array_column($weekly_data, 'order_day')) ?>, 
            chartColors.orders
        ));

        // Products Chart
        const productsCtx = document.getElementById('monthlyChart').getContext('2d');
        productsChart = new Chart(productsCtx, createChartConfig('line',
            <?= json_encode(array_column($monthly_data, 'order_count')) ?>,
            <?= json_encode(array_column($monthly_data, 'month')) ?>,
            chartColors.products
        ));

        // Custom Products Chart
        const customCtx = document.getElementById('yearlyChart').getContext('2d');
        customChart = new Chart(customCtx, createChartConfig('line',
            <?= json_encode(array_column($yearly_data, 'order_count')) ?>,
            <?= json_encode(array_column($yearly_data, 'year')) ?>,
            chartColors.custom
        ));
    }

    // Handle chart type changes
    document.querySelectorAll('.chart-type').forEach(select => {
        select.addEventListener('change', (e) => {
            const chartType = e.target.value;
            const chartId = e.target.dataset.chart;
            let chart, data, labels, colors;

            switch(chartId) {
                case 'orders':
                    chart = ordersChart;
                    data = <?= json_encode(array_column($weekly_data, 'order_count')) ?>;
                    labels = <?= json_encode(array_column($weekly_data, 'order_day')) ?>;
                    colors = chartColors.orders;
                    break;
                case 'products':
                    chart = productsChart;
                    data = <?= json_encode(array_column($monthly_data, 'order_count')) ?>;
                    labels = <?= json_encode(array_column($monthly_data, 'month')) ?>;
                    colors = chartColors.products;
                    break;
                case 'custom':
                    chart = customChart;
                    data = <?= json_encode(array_column($yearly_data, 'order_count')) ?>;
                    labels = <?= json_encode(array_column($yearly_data, 'year')) ?>;
                    colors = chartColors.custom;
                    break;
            }

            const newConfig = createChartConfig(chartType, data, labels, colors);
            chart.destroy();
            chart = new Chart(chart.canvas.getContext('2d'), newConfig);
        });
    });

    // Handle time period changes
    document.querySelectorAll('.time-period').forEach(select => {
        select.addEventListener('change', async (e) => {
            const period = e.target.value;
            const chartId = e.target.dataset.chart;
            
            // Fetch new data based on selected period
            const response = await fetch(`get_chart_data.php?chart=${chartId}&period=${period}`);
            const newData = await response.json();
            
            let chart;
            switch(chartId) {
                case 'orders':
                    chart = ordersChart;
                    break;
                case 'products':
                    chart = productsChart;
                    break;
                case 'custom':
                    chart = customChart;
                    break;
            }

            // Update chart with new data
            chart.data.labels = newData.labels;
            chart.data.datasets[0].data = newData.data;
            chart.update();
        });
    });

    // Initialize charts when the page loads
    document.addEventListener('DOMContentLoaded', initializeCharts);
</script>
</body>
</html>
