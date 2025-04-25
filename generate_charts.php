<?php
require 'db_connection.php';

// Create image for weekly sales
$weeklyData = [];
$result = $conn->query("
    SELECT DATE(order_date) AS date, SUM(total_amount) AS revenue 
    FROM orders 
    WHERE order_status = 'completed' 
    AND order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
    GROUP BY DATE(order_date)
");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $weeklyData[$row['date']] = $row['revenue'];
    }
} else {
    error_log("❌ Failed to get weekly sales data: " . $conn->error);
}

createBarChart($weeklyData, 'Weekly Sales (₱)', 'weekly_sales_chart.png');

// Create image for monthly revenue
$monthlyData = [];
$result = $conn->query("
    SELECT MONTH(order_date) AS month, SUM(total_amount) AS revenue 
    FROM orders 
    WHERE order_status = 'completed' 
    AND YEAR(order_date) = YEAR(CURDATE()) 
    GROUP BY MONTH(order_date)
");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $month = date("F", mktime(0, 0, 0, $row['month'], 1));
        $monthlyData[$month] = $row['revenue'];
    }
} else {
    error_log("❌ Failed to get monthly revenue data: " . $conn->error);
}

createBarChart($monthlyData, 'Monthly Revenue (₱)', 'monthly_revenue_chart.png');

function createBarChart($data, $title, $filename) {
    $width = 750;
    $height = 400;
    $img = imagecreate($width, $height);

    // Colors
    $white = imagecolorallocate($img, 255, 255, 255);
    $black = imagecolorallocate($img, 0, 0, 0);
    $gray = imagecolorallocate($img, 200, 200, 200);
    $barColor = imagecolorallocate($img, 255, 99, 132); // pinkish red

    // Background
    imagefill($img, 0, 0, $white);

    // Title
    imagestring($img, 5, 10, 10, $title, $black);

    if (empty($data)) {
        imagestring($img, 5, 10, 100, "No data available.", $black);
        imagepng($img, $filename);
        imagedestroy($img);
        return;
    }

    // Bar config
    $barWidth = 40;
    $gap = 20;
    $marginLeft = 50;
    $marginBottom = 80;
    $x = $marginLeft;

    $max = max($data);
    $scale = ($height - 120) / ($max ?: 1); // Avoid divide-by-zero

    // Axis line
    imageline($img, $marginLeft - 10, $height - $marginBottom, $width - 20, $height - $marginBottom, $gray);

    foreach ($data as $label => $val) {
        $barHeight = $val * $scale;
        $y1 = $height - $marginBottom - $barHeight;
        $y2 = $height - $marginBottom;

        // Draw bar
        imagefilledrectangle($img, $x, $y1, $x + $barWidth, $y2, $barColor);

        // Label amount above bar
        imagestring($img, 2, $x + 2, $y1 - 15, '₱' . number_format($val, 0), $black);

        // Label below bar
        $labelX = $x + 2;
        $labelY = $height - $marginBottom + 5;

        // Rotate labels slightly if long
        if (strlen($label) > 5) {
            imagestringup($img, 2, $labelX + 5, $height - 10, $label, $black);
        } else {
            imagestring($img, 2, $labelX, $labelY, $label, $black);
        }

        $x += $barWidth + $gap;
    }

    imagepng($img, $filename);
    imagedestroy($img);
}
