<?php
include 'db_connection.php';

header('Content-Type: application/json');

// Fetch regular products stock
$products_query = "SELECT product_id as id, stock_count FROM products";
$products_result = mysqli_query($conn, $products_query);

// Fetch customized products stock
$customized_query = "SELECT id, stock_count FROM customized_products";
$customized_result = mysqli_query($conn, $customized_query);

$stock_data = array();

// Add regular products to stock data
while ($row = mysqli_fetch_assoc($products_result)) {
    $stock_data[] = array(
        'id' => $row['id'],
        'stock_count' => $row['stock_count']
    );
}

// Add customized products to stock data
while ($row = mysqli_fetch_assoc($customized_result)) {
    $stock_data[] = array(
        'id' => $row['id'],
        'stock_count' => $row['stock_count']
    );
}

echo json_encode($stock_data);
?> 