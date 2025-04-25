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

// --- New Queries for Detailed Reports ---

// Total Sales Revenue
$total_revenue_query = $conn->query("SELECT SUM(total_amount) AS total_revenue FROM orders");
$total_revenue_data = $total_revenue_query->fetch_assoc();
$total_revenue = $total_revenue_data['total_revenue'] ?? 0; // Default to 0 if null

// Top 5 Selling Products (for bar chart list)
$top_products_query = $conn->query("
    SELECT p.product_name, SUM(oi.quantity) AS total_quantity 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    GROUP BY p.product_id, p.product_name
    ORDER BY total_quantity DESC 
    LIMIT 5
");
$top_products_report = [];
$max_quantity = 0;
while ($row = $top_products_query->fetch_assoc()) {
    $top_products_report[] = $row;
    if ($row['total_quantity'] > $max_quantity) {
        $max_quantity = $row['total_quantity'];
    }
}
// Calculate width percentage for bars
foreach ($top_products_report as &$product) {
    $product['width_percent'] = ($max_quantity > 0) ? ($product['total_quantity'] / $max_quantity) * 100 : 0;
}
unset($product); // Unset reference

// Top 3 Selling Products (for donut chart)
$donut_data_query = $conn->query("
    SELECT p.product_name, SUM(oi.quantity) AS total_quantity 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    GROUP BY p.product_id, p.product_name
    ORDER BY total_quantity DESC 
    LIMIT 3
");
$donut_data = [];
while ($row = $donut_data_query->fetch_assoc()) {
    $donut_data[] = $row;
}
// --- End New Queries ---

?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

        .inventory-btn {
            display: inline-flex;
            align-items: center;
            padding: 0.8rem 1.5rem;
            background: var(--purple-gradient);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            box-shadow: 0 4px 15px rgba(142, 68, 173, 0.2);
        }

        .inventory-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(142, 68, 173, 0.3);
        }

        .inventory-btn i {
            margin-right: 0.5rem;
            font-size: 1.1rem;
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

        // --- New Styles for Detailed Reports ---
        .detailed-report-card {
            background-color: #fff;
            border-radius: 15px;
            padding: 2rem;
            margin-top: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            display: grid;
            grid-template-columns: 0.8fr 1fr 1fr; /* Adjust column ratios as needed */
            gap: 2rem;
            align-items: start;
            position: relative;
        }

        .report-nav-arrows {
            position: absolute;
            top: 1rem;
            right: 1rem;
            display: flex;
            gap: 0.5rem;
        }

        .report-nav-arrows button {
            background-color: #f0f0f0;
            border: none;
            border-radius: 5px;
            padding: 0.3rem 0.6rem;
            cursor: pointer;
            color: #555;
            transition: background-color 0.2s;
        }

        .report-nav-arrows button:hover {
            background-color: #e0e0e0;
        }

        .report-text-section h2 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .report-total-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: #5a3b8a; /* Purple color */
            margin-bottom: 0.2rem;
        }

        .report-subtitle {
            font-size: 1.2rem;
            font-weight: 500;
            color: #5a3b8a; /* Purple color */
            margin-bottom: 1rem;
        }

        .report-description {
            font-size: 0.85rem;
            color: #7f8c8d;
            line-height: 1.5;
        }

        .report-bar-chart-section h3 {
            font-size: 1rem;
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        .report-bar-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .report-bar-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: #555;
        }

        .report-bar-item-label {
            width: 100px; /* Adjust as needed */
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-right: 1rem;
        }

        .report-bar-container {
            flex-grow: 1;
            height: 10px;
            background-color: #f0f0f0;
            border-radius: 5px;
            overflow: hidden;
            margin-right: 1rem;
        }

        .report-bar {
            height: 100%;
            border-radius: 5px;
            background-color: #5a3b8a; /* Default bar color */
            transition: width 0.5s ease-in-out;
        }
        /* Assign specific colors to bars */
        .report-bar-item:nth-child(1) .report-bar { background-color: #5a3b8a; } /* Dark Purple */
        .report-bar-item:nth-child(2) .report-bar { background-color: #f39c12; } /* Yellow */
        .report-bar-item:nth-child(3) .report-bar { background-color: #e74c3c; } /* Red */
        .report-bar-item:nth-child(4) .report-bar { background-color: #3498db; } /* Blue */
        .report-bar-item:nth-child(5) .report-bar { background-color: #8e44ad; } /* Lighter Purple */

        .report-bar-value {
            min-width: 30px;
            text-align: right;
            font-weight: 500;
        }

        .report-donut-chart-section {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .report-donut-container {
            width: 180px; /* Adjust size as needed */
            height: 180px;
            position: relative;
            margin-bottom: 1.5rem;
        }

        #detailedReportDonutChart {
            width: 100% !important;
            height: 100% !important;
        }

        .report-donut-legend {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 0.8rem;
            align-items: flex-start;
        }

        .report-legend-item {
            display: flex;
            align-items: center;
            font-size: 0.85rem;
            color: #555;
        }

        .report-legend-color {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 0.5rem;
        }
        // --- End New Styles ---

    </style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="dashboard">
    <div class="dashboard-header">
        <h1>Dashboard Overview</h1>
    </div>

    <a href="inventory.php" class="inventory-btn">
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

    <!-- Detailed Reports Section -->
    <div class="detailed-report-card">
        <div class="report-nav-arrows">
            <button>&lt;</button>
            <button>&gt;</button>
        </div>

        <!-- Left Text Section -->
        <div class="report-text-section">
            <h2>Detailed Reports</h2>
            <div class="report-total-value">₱<?= number_format($total_revenue, 2) ?></div>
            <div class="report-subtitle">Sales Overview</div>
            <p class="report-description">
                The total sales revenue within the recorded period. Below is a breakdown by top products.
            </p>
        </div>

        <!-- Middle Bar Chart Section -->
        <div class="report-bar-chart-section">
            <!-- <h3>Top Selling Products</h3> -->
            <ul class="report-bar-list">
                <?php if (!empty($top_products_report)): ?>
                    <?php foreach ($top_products_report as $product): ?>
                        <li class="report-bar-item">
                            <span class="report-bar-item-label" title="<?= htmlspecialchars($product['product_name']) ?>">
                                <?= htmlspecialchars($product['product_name']) ?>
                            </span>
                            <div class="report-bar-container">
                                <div class="report-bar" style="width: <?= $product['width_percent'] ?>%;"></div>
                            </div>
                            <span class="report-bar-value"><?= $product['total_quantity'] ?></span>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No product sales data available.</p>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Right Donut Chart Section -->
        <div class="report-donut-chart-section">
            <div class="report-donut-container">
                <canvas id="detailedReportDonutChart"></canvas>
            </div>
            <ul class="report-donut-legend" id="detailedReportDonutLegend">
                <!-- Legend items will be generated by JavaScript -->
            </ul>
        </div>
    </div>
    <!-- End Detailed Reports Section -->

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
    document.addEventListener('DOMContentLoaded', () => {
        initializeCharts();

        // --- Initialize Detailed Report Donut Chart ---
        const donutData = <?= json_encode($donut_data) ?>;
        const donutLabels = donutData.map(item => item.product_name);
        const donutQuantities = donutData.map(item => item.total_quantity);
        const donutColors = ['#5a3b8a', '#f39c12', '#3498db']; // Purple, Yellow, Blue

        const donutCtx = document.getElementById('detailedReportDonutChart');
        const legendContainer = document.getElementById('detailedReportDonutLegend');

        if (donutCtx && legendContainer && donutQuantities.length > 0) {
            new Chart(donutCtx, {
                type: 'doughnut',
                data: {
                    labels: donutLabels,
                    datasets: [{
                        label: 'Top Product Sales',
                        data: donutQuantities,
                        backgroundColor: donutColors,
                        borderColor: '#ffffff', // White border between segments
                        borderWidth: 2,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%', // Adjust for donut thickness
                    plugins: {
                        legend: {
                            display: false // Disable default legend, we use custom
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed !== null) {
                                        label += context.parsed;
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });

            // Generate custom legend
            legendContainer.innerHTML = ''; // Clear previous legend items
            donutLabels.forEach((label, index) => {
                const li = document.createElement('li');
                li.classList.add('report-legend-item');
                li.innerHTML = `
                    <span class="report-legend-color" style="background-color: ${donutColors[index % donutColors.length]}"></span>
                    <span>${label} (${donutQuantities[index]})</span>
                `;
                legendContainer.appendChild(li);
            });
        } else if (legendContainer) {
             legendContainer.innerHTML = '<li>No data for chart</li>';
        }
        // --- End Detailed Report Donut Chart Initialization ---
    });
</script>
</body>
</html>
