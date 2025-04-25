<?php
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_id = $_POST['order_id'];
    $flower_type = mysqli_real_escape_string($conn, $_POST['flower_type']);
    // ... other fields ...
    $selected_addons = isset($_POST['add_ons']) ? $_POST['add_ons'] : [];

    // Update customized_orders table
    $update_query = "UPDATE customized_orders SET
                     flower_type = '$flower_type',
                     -- ... other fields ...
                     WHERE id = $order_id";
    mysqli_query($conn, $update_query);

    // Update add-ons (remove existing and add new)
    mysqli_query($conn, "DELETE FROM customized_order_add_ons WHERE order_id = $order_id");
    if (!empty($selected_addons)) {
        foreach ($selected_addons as $add_on_id) {
            mysqli_query($conn, "INSERT INTO customized_order_add_ons (order_id, add_on_id) VALUES ($order_id, $add_on_id)");
        }
    }

    // Handle image uploads
    $upload_dir = "uploads/customized_orders/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    if (isset($_FILES['images'])) {
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            if (!empty($_FILES['images']['name'][$key])) {
                $image_name = basename($_FILES['images']['name'][$key]);
                $target_path = $upload_dir . $image_name;
                if (move_uploaded_file($tmp_name, $target_path)) {
                    mysqli_query($conn, "INSERT INTO customized_order_images (order_id, image_path) VALUES ($order_id, '$target_path')");
                } else {
                    echo "Error uploading image: " . $_FILES['images']['name'][$key] . "<br>";
                }
            }
        }
    }

    header("Location: seller_customized_orders.php?message=Order updated successfully");
    exit();
} elseif (isset($_GET['action']) && $_GET['action'] == 'delete_image' && isset($_GET['image_path']) && isset($_GET['order_id'])) {
    $image_path_to_delete = $_GET['image_path'];
    $order_id = $_GET['order_id'];

    // Delete from the database
    $delete_query = "DELETE FROM customized_order_images WHERE order_id = $order_id AND image_path = '$image_path_to_delete'";
    if (mysqli_query($conn, $delete_query)) {
        // Optionally delete the file from the server
        if (file_exists($image_path_to_delete)) {
            unlink($image_path_to_delete);
        }
        header("Location: seller_customized_orders.php?id=$order_id&message=Image deleted");
        exit();
    } else {
        echo "Error deleting image: " . mysqli_error($conn);
    }
} else {
    header("Location: seller_customized_orders.php");
    exit();
}

mysqli_close($conn);
?>