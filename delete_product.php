<?php
include('db_connection.php');

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = $_GET['id'];

    $sql = "UPDATE products SET is_deleted = 1 WHERE product_id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $product_id);

        if ($stmt->execute()) {
            header("Location: product.php?message=Product deleted successfully");
            exit();
        } else {
            error_log("Error: Could not soft delete product with ID $product_id");
            echo "Error: Could not soft delete product.";
        }

        $stmt->close();
    } else {
        error_log("Error: Could not prepare SQL statement for soft delete");
        echo "Error: Could not prepare the SQL statement.";
    }
} else {
    header("Location: product.php?message=Invalid product ID.");
    exit();
}

$conn->close();
?>
