<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_email'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$customer_email = $_SESSION['user_email'];

$stmt_orders = $conn->prepare("SELECT order_id, order_status FROM orders WHERE customer_email = ? ORDER BY order_date DESC");
$stmt_orders->bind_param("s", $customer_email);
$stmt_orders->execute();
$result_orders = $stmt_orders->get_result();
$updated_orders = $result_orders->fetch_all(MYSQLI_ASSOC);
$stmt_orders->close();

header('Content-Type: application/json');
echo json_encode($updated_orders);
?>