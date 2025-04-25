<?php
header('Content-Type: application/json');
include 'db_connection.php';

$chart = $_GET['chart'] ?? '';
$period = $_GET['period'] ?? '';

function getDateRange($period) {
    switch($period) {
        case 'week':
            return "AND order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        case 'month':
            return "AND order_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
        case 'year':
            return "AND order_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
        default:
            return "";
    }
}

$response = ['labels' => [], 'data' => []];

switch($chart) {
    case 'orders':
        $query = "
            SELECT 
                CASE 
                    WHEN '{$period}' = 'week' THEN DATE_FORMAT(order_date, '%a')
                    WHEN '{$period}' = 'month' THEN DATE_FORMAT(order_date, '%d')
                    ELSE DATE_FORMAT(order_date, '%b')
                END as label,
                COUNT(*) as count
            FROM orders
            WHERE 1=1 " . getDateRange($period) . "
            GROUP BY label
            ORDER BY order_date
        ";
        break;

    case 'products':
        $query = "
            SELECT 
                p.product_name as label,
                COUNT(oi.order_id) as count
            FROM products p
            LEFT JOIN order_items oi ON p.product_id = oi.product_id
            LEFT JOIN orders o ON oi.order_id = o.order_id
            WHERE 1=1 " . getDateRange($period) . "
            GROUP BY p.product_id
            ORDER BY count DESC
            LIMIT 5
        ";
        break;

    case 'custom':
        $query = "
            SELECT 
                cp.product_name as label,
                COUNT(o.order_id) as count
            FROM customized_products cp
            LEFT JOIN orders o ON cp.order_id = o.order_id
            WHERE 1=1 " . getDateRange($period) . "
            GROUP BY cp.product_id
            ORDER BY count DESC
            LIMIT 5
        ";
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid chart type']);
        exit;
}

$result = $conn->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $response['labels'][] = $row['label'];
        $response['data'][] = (int)$row['count'];
    }
}

echo json_encode($response);
?> 