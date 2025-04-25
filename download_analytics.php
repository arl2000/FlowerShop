<?php
ob_start();
require_once __DIR__ . '/vendor/autoload.php';
include 'db_connection.php';

// Generate charts first
if (extension_loaded('gd')) {
    include 'generate_charts.php'; // Must generate: weekly_sales_chart.png, monthly_revenue_chart.png
} else {
    error_log("⚠ GD extension is not enabled. Charts cannot be generated.");
}

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;

$phpWord = new PhpWord();
$phpWord->setDefaultFontName('Arial');
$phpWord->setDefaultFontSize(12);
$section = $phpWord->addSection();

$phpWord->addTableStyle('AnalyticsTable', [
    'borderSize' => 6,
    'borderColor' => '999999',
    'cellMargin' => 80
]);

// Main Title
// Define title style
$phpWord->addTitleStyle(
    1,
    array('bold' => true, 'size' => 20, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER),
    array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER)
);

// Add the title
$section->addTitle("🏵 HeavenlyBloom Financial Sales Report", 1);

// Revenue Summary
$revenue = 0;
$orderCount = 0;

$revenueQuery = $conn->query("SELECT SUM(total_amount) AS total FROM orders WHERE order_status = 'completed'");
if ($revenueQuery && $row = $revenueQuery->fetch_assoc()) {
    $revenue = $row['total'];
}

$orderCountQuery = $conn->query("SELECT COUNT(*) AS count FROM orders WHERE order_status = 'completed'");
if ($orderCountQuery && $row = $orderCountQuery->fetch_assoc()) {
    $orderCount = $row['count'];
}

$avgRevenue = $orderCount > 0 ? $revenue / $orderCount : 0;

$section->addText("💰 Total Revenue: ₱" . number_format($revenue, 2));
$section->addText("📦 Order Count: $orderCount");
$section->addText("🧾 Average Revenue per Order: ₱" . number_format($avgRevenue, 2));

// Weekly Sales
$section->addTextBreak(1);
$section->addText("📅 Sales in the Last 7 Days", ['bold' => true]);

$sales = $conn->query("
    SELECT DATE(order_date) AS date, SUM(total_amount) AS revenue 
    FROM orders 
    WHERE order_status = 'completed' 
    AND order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
    GROUP BY DATE(order_date)
");

if ($sales) {
    $table = $section->addTable('AnalyticsTable');
    $table->addRow();
    $table->addCell()->addText('Date');
    $table->addCell()->addText('Revenue');

    $rowIndex = 0;
    while ($row = $sales->fetch_assoc()) {
        $rowIndex++;
        $rowStyle = ($rowIndex % 2 === 0) ? ['bgColor' => 'F2F2F2'] : [];
        $table->addRow(null, $rowStyle);
        $table->addCell()->addText($row['date']);
        $table->addCell()->addText('₱' . number_format($row['revenue'], 2));
    }
} else {
    $section->addText("❌ Failed to fetch weekly sales: " . $conn->error);
}

// Monthly Revenue
$section->addTextBreak(1);
$section->addText("📆 Monthly Revenue", ['bold' => true]);

$monthly = $conn->query("
    SELECT MONTH(order_date) AS month, SUM(total_amount) AS revenue 
    FROM orders 
    WHERE order_status = 'completed' 
    AND YEAR(order_date) = YEAR(CURDATE()) 
    GROUP BY MONTH(order_date)
");

if ($monthly) {
    $table = $section->addTable('AnalyticsTable');
    $table->addRow();
    $table->addCell()->addText('Month');
    $table->addCell()->addText('Revenue');

    $rowIndex = 0;
    while ($row = $monthly->fetch_assoc()) {
        $rowIndex++;
        $rowStyle = ($rowIndex % 2 === 0) ? ['bgColor' => 'F2F2F2'] : [];
        $monthName = date("F", mktime(0, 0, 0, $row['month'], 1));
        $table->addRow(null, $rowStyle);
        $table->addCell()->addText($monthName);
        $table->addCell()->addText('₱' . number_format($row['revenue'], 2));
    }
} else {
    $section->addText("❌ Failed to fetch monthly revenue: " . $conn->error);
}

// Top 5 Customers by Spending
$section->addTextBreak(1);
$section->addText("🏆 Top 5 Customers by Spending", ['bold' => true]);

$topUsers = $conn->query("
    SELECT customer_name, SUM(total_amount) AS total_spent
    FROM orders
    WHERE order_status = 'completed'
    GROUP BY customer_name
    ORDER BY total_spent DESC
    LIMIT 5
");

if ($topUsers) {
    $table = $section->addTable('AnalyticsTable');
    $table->addRow();
    $table->addCell()->addText('Customer');
    $table->addCell()->addText('Total Spent');

    $rowIndex = 0;
    while ($row = $topUsers->fetch_assoc()) {
        $rowIndex++;
        $rowStyle = ($rowIndex % 2 === 0) ? ['bgColor' => 'F2F2F2'] : [];
        $table->addRow(null, $rowStyle);
        $table->addCell()->addText($row['customer_name']);
        $table->addCell()->addText('₱' . number_format($row['total_spent'], 2));
    }
} else {
    $section->addText("❌ Failed to fetch top customers: " . $conn->error);
}

// Charts Section
$section->addTextBreak(1);
$section->addText("📈 Charts and Visuals", ['bold' => true]);

$charts = ['weekly_sales_chart.png', 'monthly_revenue_chart.png'];
foreach ($charts as $chart) {
    if (file_exists($chart)) {
        $section->addImage($chart, [
            'width' => 500,
            'height' => 300,
            'alignment' => Jc::CENTER
        ]);
        $section->addTextBreak(1);
    } else {
        $section->addText("⚠️ Chart not found: $chart", ['italic' => true, 'color' => 'FF0000']);
    }
}

// Output File
header("Content-Description: File Transfer");
header('Content-Disposition: attachment; filename="heavenlybloom_sales_report.docx"');
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Expires: 0');

ob_end_clean();
$objWriter = IOFactory::createWriter($phpWord, 'Word2007');
$objWriter->save("php://output");
exit;
