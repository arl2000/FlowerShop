<?php
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_id = $_POST['order_id'];
    $order_status = $_POST['order_status'];
    $expected_delivery_date = $_POST['expected_delivery_date'];
    $delivery_service = $_POST['delivery_service'];
    $quantity = $_POST['quantity'];

    $stmt = $conn->prepare("UPDATE orders SET order_status=?, expected_delivery_date=?, delivery_service=?, quantity=? WHERE order_id=?");
    $stmt->bind_param("sssii", $order_status, $expected_delivery_date, $delivery_service, $quantity, $order_id);

    if ($stmt->execute()) {
        header("Location: " . $_SERVER['HTTP_REFERER']); // go back to the page
    } else {
        echo "Error updating order: " . $stmt->error;
    }
}
?>
