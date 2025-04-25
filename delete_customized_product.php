<?php
session_start();
include 'db_connection.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Function to soft delete a customized product
function softDeleteCustomizedProduct($productId) {
    global $conn;
    
    try {
        // Prepare the update statement
        $stmt = $conn->prepare("UPDATE customized_products SET is_deleted = 1 WHERE id = ?");
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        // Bind the product ID parameter
        $stmt->bind_param("i", $productId);
        
        // Execute the statement
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        // Check if any row was actually updated
        if ($stmt->affected_rows > 0) {
            return true;
        } else {
            return false;
        }
        
    } catch (Exception $e) {
        error_log("Error in softDeleteCustomizedProduct: " . $e->getMessage());
        return false;
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
    }
}

// Check if product ID is provided
if (isset($_POST['product_id'])) {
    $productId = (int)$_POST['product_id'];
    
    // Call the soft delete function
    if (softDeleteCustomizedProduct($productId)) {
        echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete product']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Product ID not provided']);
}
?>

