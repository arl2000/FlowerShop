<?php
// Include the database connection
include('db_connection.php');

// Check if an ID is passed in the URL and is numeric
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = $_GET['id'];

    // Prepare SQL statement
    $sql = "DELETE FROM products WHERE product_id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $product_id);

        if ($stmt->execute()) {
            // Redirect after successful deletion
            header("Location: product.php?message=Product deleted successfully");
            exit();
        } else {
            error_log("Error: Could not delete product with ID $product_id");
            echo "Error: Could not delete product.";
        }

        $stmt->close();
    } else {
        error_log("Error: Could not prepare SQL statement for product ID $product_id");
        echo "Error: Could not prepare the SQL statement.";
    }
} else {
    // Redirect if ID is missing or invalid
    header("Location:  product.php?message=Invalid product ID.");
    exit();
}

$conn->close();
?>
