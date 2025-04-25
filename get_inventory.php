<?php
include 'db_connection.php';

// Function to get real-time inventory data
function getInventoryData($conn) {
    $inventory_query = "SELECT 
        p.product_id, 
        p.product_name, 
        p.stock_count,
        c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY p.product_name";
    
    $result = mysqli_query($conn, $inventory_query);
    $inventory_data = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $inventory_data[] = $row;
        }
    }
    
    return $inventory_data;
}

// Get inventory data
$inventory_data = getInventoryData($conn);

// Set header to return JSON
header('Content-Type: application/json');

// Return the inventory data as JSON
echo json_encode($inventory_data);
?> 