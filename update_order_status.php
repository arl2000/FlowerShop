<?php
include 'db_connection.php';

// Include the function to send email notifications
require_once 'trackorders.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_id = $_POST['order_id'];
    $order_status = $_POST['order_status'];
    $expected_delivery_date = $_POST['expected_delivery_date'];
    $delivery_service = $_POST['delivery_service'];
    $quantity = $_POST['quantity'];

    // Get the previous status to check if there was a change
    $prev_status_query = $conn->prepare("SELECT order_status, customer_email FROM orders WHERE order_id = ?");
    $prev_status_query->bind_param("i", $order_id);
    $prev_status_query->execute();
    $result = $prev_status_query->get_result();
    
    if($row = $result->fetch_assoc()) {
        $previous_status = $row['order_status'];
        $customer_email = $row['customer_email'];
        
        // Update the order status
        $stmt = $conn->prepare("UPDATE orders SET order_status=?, expected_delivery_date=?, delivery_service=?, quantity=? WHERE order_id=?");
        $stmt->bind_param("sssii", $order_status, $expected_delivery_date, $delivery_service, $quantity, $order_id);
        
        if ($stmt->execute()) {
            // If status has changed and is one of the notification states, send an email
            if ($previous_status !== $order_status && in_array($order_status, ['approved', 'shipped', 'in_route', 'delivered'])) {
                // Send email notification
                sendStatusUpdateEmail($customer_email, $order_id, $order_status);
            }
            
            header("Location: " . $_SERVER['HTTP_REFERER']); // go back to the page
        } else {
            echo "Error updating order: " . $stmt->error;
        }
    } else {
        echo "Error: Order not found.";
    }
}
?>
