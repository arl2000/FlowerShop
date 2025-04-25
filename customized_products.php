<?php
session_start();
include 'db_connection.php';

// Initialize cart count
$cartCount = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;

// Add to cart functionality
if (isset($_GET['add']) && is_numeric($_GET['add']) && isset($_SESSION['user_id'])) {
    $productId = (int) $_GET['add'];
    $stmt = $conn->prepare("SELECT id, product_name, product_price, product_image FROM customized_products WHERE id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($product) {
        // Get customization details
        $customizationDetails = [];
        
        // Get ribbon color if selected
        if (isset($_GET['selected_ribbon_color'])) {
            $ribbonColorData = json_decode($_GET['selected_ribbon_color'], true);
            if ($ribbonColorData) {
                $customizationDetails['ribbon_color'] = $ribbonColorData;
            }
        }
        
        // Get wrapper color if selected
        if (isset($_GET['selected_wrapper_color'])) {
            $wrapperColorData = json_decode($_GET['selected_wrapper_color'], true);
            if ($wrapperColorData) {
                $customizationDetails['wrapper_color'] = $wrapperColorData;
            }
        }
        
        // Get add-ons if selected
        if (isset($_GET['selected_addons']) && is_array($_GET['selected_addons'])) {
            $customizationDetails['addons'] = [];
            foreach ($_GET['selected_addons'] as $addonData) {
                $addon = json_decode($addonData, true);
                if ($addon) {
                    $customizationDetails['addons'][] = $addon;
                }
            }
        }
        
        // Get customer message if provided
        if (isset($_GET['customer_message']) && !empty($_GET['customer_message'])) {
            $customizationDetails['customer_message'] = $_GET['customer_message'];
        }
        
        // Calculate total price with customizations
        $totalPrice = $product['product_price'];
        
        // Add ribbon color price
        if (isset($customizationDetails['ribbon_color']) && isset($customizationDetails['ribbon_color']['price'])) {
            $totalPrice += floatval($customizationDetails['ribbon_color']['price']);
        }
        
        // Add wrapper color price
        if (isset($customizationDetails['wrapper_color']) && isset($customizationDetails['wrapper_color']['price'])) {
            $totalPrice += floatval($customizationDetails['wrapper_color']['price']);
        }
        
        // Add add-ons prices
        if (isset($customizationDetails['addons']) && is_array($customizationDetails['addons'])) {
            foreach ($customizationDetails['addons'] as $addon) {
                if (isset($addon['price'])) {
                    $totalPrice += floatval($addon['price']);
                }
            }
        }
        
        // Check if the product is already in the cart
        $checkStmt = $conn->prepare("SELECT cart_id, quantity FROM cart WHERE user_id = ? AND product_id = ? AND is_customized = 1");
        $checkStmt->bind_param("ii", $_SESSION['user_id'], $productId);
        $checkStmt->execute();
        $existingItem = $checkStmt->get_result()->fetch_assoc();
        $checkStmt->close();
        
        if ($existingItem) {
            // Update quantity if product exists
            $updateStmt = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE cart_id = ?");
            $updateStmt->bind_param("i", $existingItem['cart_id']);
            $updateStmt->execute();
            $updateStmt->close();
        } else {
            // Insert new cart item with customization details
            $insertStmt = $conn->prepare("INSERT INTO cart (user_id, product_id, product_name, product_price, product_image, quantity, is_customized, 
                ribbon_color_id, ribbon_color_name, ribbon_color_price, 
                wrapper_color_id, wrapper_color_name, wrapper_color_price, 
                customer_message, addons) 
                VALUES (?, ?, ?, ?, ?, 1, 1, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $ribbonColorId = isset($customizationDetails['ribbon_color']['id']) ? $customizationDetails['ribbon_color']['id'] : null;
            $ribbonColorName = isset($customizationDetails['ribbon_color']['color']) ? $customizationDetails['ribbon_color']['color'] : null;
            $ribbonColorPrice = isset($customizationDetails['ribbon_color']['price']) ? $customizationDetails['ribbon_color']['price'] : 0;
            
            $wrapperColorId = isset($customizationDetails['wrapper_color']['id']) ? $customizationDetails['wrapper_color']['id'] : null;
            $wrapperColorName = isset($customizationDetails['wrapper_color']['color']) ? $customizationDetails['wrapper_color']['color'] : null;
            $wrapperColorPrice = isset($customizationDetails['wrapper_color']['price']) ? $customizationDetails['wrapper_color']['price'] : 0;
            
            $customerMessage = isset($customizationDetails['customer_message']) ? $customizationDetails['customer_message'] : null;
            $addonsJson = isset($customizationDetails['addons']) ? json_encode($customizationDetails['addons']) : null;
            
            $insertStmt->bind_param("iisssisdssdss", 
                $_SESSION['user_id'], 
                $productId, 
                $product['product_name'], 
                $totalPrice, 
                $product['product_image'],
                $ribbonColorId,
                $ribbonColorName,
                $ribbonColorPrice,
                $wrapperColorId,
                $wrapperColorName,
                $wrapperColorPrice,
                $customerMessage,
                $addonsJson
            );
            
            $insertStmt->execute();
            $insertStmt->close();
        }
    }
    header("Location: cart.php");
    exit();
} elseif (isset($_GET['add']) && is_numeric($_GET['add']) && !isset($_SESSION['user_id'])) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('loginModal').style.display = 'flex';
        });
    </script>";
}

// Fetch wrappers
$wrappers = [];
$wrapperQuery = $conn->query("SELECT id, color, price FROM wrappers");
if ($wrapperQuery && $wrapperQuery->num_rows > 0) {
    while ($row = $wrapperQuery->fetch_assoc()) {
        $wrappers[] = $row;
    }
}

// Fetch ribbon colors
$ribbonColors = [];
$ribbonQuery = $conn->query("SELECT id, name, price FROM ribbon_colors");
if ($ribbonQuery && $ribbonQuery->num_rows > 0) {
    while ($row = $ribbonQuery->fetch_assoc()) {
        $ribbonColors[] = $row;
    }
}

// Fetch add-ons
$addOns = [];
$addOnQuery = $conn->query("SELECT * FROM add_ons");
if ($addOnQuery && $addOnQuery->num_rows > 0) {
    while ($row = $addOnQuery->fetch_assoc()) {
        $addOns[] = $row;
    }
}

// Convert PHP arrays to JSON for JavaScript
$wrappersJson = json_encode($wrappers);
$ribbonColorsJson = json_encode($ribbonColors);
$addOnsJson = json_encode($addOns);

// Function to soft delete a customized product
function softDeleteCustomizedProduct($productId) {
    global $conn;
    
    // Update the is_deleted flag to 1 instead of actually deleting the record
    $query = "UPDATE customized_products SET is_deleted = 1 WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $productId);
    mysqli_stmt_execute($stmt);
    
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        return true;
    }
    return false;
}

// Fetch customized products that are not deleted
$customized_result = mysqli_query($conn, "
    SELECT cp.*, cp.stock_count, cat.name AS category_name,
           bs.name AS bouquet_size_name, bs.price AS bouquet_size_price,
           rc.name AS ribbon_color_name, rc.price AS ribbon_color_price
    FROM customized_products cp
    LEFT JOIN categories cat ON cp.category_id = cat.id
    LEFT JOIN bouquet_sizes bs ON cp.bouquet_sizes = bs.id
    LEFT JOIN ribbon_colors rc ON cp.ribbon_colors = rc.id
    WHERE cp.is_deleted = 0
") or die("Query Error (Customized): " . mysqli_error($conn));

// Check if a delete request is made
if (isset($_GET['delete_id'])) {
    $productId = $_GET['delete_id'];
    // Call the soft delete function
    softDeleteCustomizedProduct($productId);

    // Redirect back to the customized products page after deletion
    header("Location: customized_products.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Customized Products - Heavenly Bloom</title>
    
    <link href="https://fonts.googleapis.com/css?family=Raleway:100,300,400,500,700,900" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- Additional CSS Files -->
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/font-awesome.css">
    <link rel="stylesheet" href="assets/css/templatemo-softy-pinko.css">
    <link rel="stylesheet" href="home.css">
    
    <style>
        /* Product card styles */
        .product-card {
            background-color: white;
            border-radius: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        
        .product-image-wrapper {
            height: 250px;
            overflow: hidden;
            position: relative;
        }
        
        .product-image-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .product-card:hover .product-image-wrapper img {
            transform: scale(1.1);
        }
        
        .product-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 138, 195, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .product-card:hover .product-overlay {
            opacity: 1;
        }
        
        .product-info {
            padding: 20px;
        }
        
        .product-info h3 {
            font-size: 18px;
            margin-bottom: 10px;
            color: #333;
        }
        
        .product-price {
            font-size: 20px;
            font-weight: 700;
            color: #ff4483;
        }
        
        .customized-view-btn {
            background-color: white;
            color: #ff4483;
            border: none;
            border-radius: 30px;
            padding: 10px 20px;
            font-weight: 600;
            box-shadow: 0 3px 10px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .customized-view-btn:hover {
            background-color: #ff4483;
            color: white;
            transform: scale(1.05);
        }
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.75);
            overflow-y: auto;
        }
        
        .modal-content {
            display: flex;
            flex-direction: row;
            background-color: white;
            margin: 5% auto;
            padding: 0;
            width: 90%;
            max-width: 1000px;
            position: relative;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            overflow: hidden;
            height: auto;
        }
        
        .modal-content .row {
            width: 100%;
            margin: 0;
        }
        
        .modal-content .col-md-6 {
            padding: 0;
        }
        
        .modal-product-details {
            padding: 40px;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .close, .close-button {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 28px;
            color: #ff4483;
            background: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 100;
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .close:hover, .close-button:hover {
            background-color: #ff4483;
            color: white;
            transform: rotate(90deg);
        }
        
        .modal-image-container {
            padding: 0;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: white;
            overflow: hidden;
        }
        
        .modal-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 0;
            box-shadow: none;
            transition: transform 0.3s ease;
        }
        
        .modal-product-details h2 {
            font-family: 'Playfair Display', serif;
            color: #333;
            font-weight: 600;
            font-size: 2.5rem;
            margin-bottom: 20px;
            text-transform: uppercase;
        }
        
        .description {
            color: #666;
            line-height: 1.6;
            font-size: 1rem;
            margin-bottom: 30px;
        }
        
        .price-container {
            margin-bottom: 30px;
        }
        
        .price {
            font-size: 2.2rem;
            font-weight: 700;
            color: #ff4483;
            margin: 0;
        }
        
        .modal-actions {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .modal-actions .main-button {
            background-color: #ff4483;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 15px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .modal-actions .main-button:hover {
            background-color: #e91e63;
        }
        
        .modal-actions .main-button-secondary {
            background-color: transparent;
            color: #ff4483;
            border: 1px solid #ff4483;
            border-radius: 4px;
            padding: 14px;
            font-weight: 500;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
        }
        
        .modal-actions .main-button-secondary:hover {
            background-color: #fff0f5;
        }
        
        .modal-details {
            padding: 35px 30px;
            border-left: 1px solid #f0ece7;
            height: 100%;
        }
        
        .modal-options-group {
            margin-bottom: 25px;
        }
        
        .modal-options-group label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            font-family: 'Playfair Display', serif;
        }
        
        .form-check {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        
        .form-check-input {
            margin-right: 10px;
            width: 10px;
        }
        
        .form-check-label {
            color: #636e72;
        }
        
        .customer-message label {
            font-weight: 600;
            color: #333;
            font-family: 'Playfair Display', serif;
        }
        
        .customer-message textarea {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 10px;
            resize: none;
            transition: border-color 0.3s ease;
        }
        
        .customer-message textarea:focus {
            border-color: #ff4483;
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 68, 131, 0.1);
        }
        
        /* Responsive adjustments */
        @media (max-width: 767px) {
            .modal-content {
                margin: 10% auto;
                width: 95%;
                flex-direction: column;
            }
            
            .modal-image-container {
                height: 350px;
            }
            
            .modal-product-details {
                padding: 30px 20px;
            }
            
            .price {
                font-size: 1.5rem;
                margin: 15px 0;
            }
        }
        
        /* Customized Product Modal Enhancements */
        .custom-modal {
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .custom-modal .row {
            height: 100%;
            margin: 0;
            flex: 1;
        }
        
        .custom-modal .col-md-5,
        .custom-modal .col-md-7 {
            padding: 0;
            height: 100%;
        }
        
        .custom-scroll {
            overflow-y: auto;
            max-height: 90vh;
            scrollbar-width: thin;
            scrollbar-color: #ff4483 #f5f5f5;
        }
        
        .custom-scroll::-webkit-scrollbar {
            width: 8px;
        }
        
        .custom-scroll::-webkit-scrollbar-track {
            background: #f5f5f5;
            border-radius: 10px;
        }
        
        .custom-scroll::-webkit-scrollbar-thumb {
            background-color: #ff4483;
            border-radius: 10px;
            border: 2px solid #f5f5f5;
        }
        
        .custom-title {
            font-family: 'Playfair Display', serif;
            color: #ff4483;
            font-size: 1.6rem;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f0ece7;
        }
        
        .custom-options-container {
            padding-right: 15px;
            padding-left: 5px;
        }
        
        .options-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }
        
        .options-group-content {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            gap: 10px;
        }
        
        .modal-options-group {
            background-color: #fdf6f8;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .modal-options-group:hover {
            box-shadow: 0 5px 15px rgba(255, 68, 131, 0.1);
        }
        
        .form-check {
            margin-bottom: 8px;
            background: white;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid #f0ece7;
            transition: all 0.2s ease;
            display: flex;
            align-items: flex-start;
            position: relative;
            overflow: hidden;
        }
        
        .form-check-input {
            margin-right: 10px;
            margin-top: 3px;
            flex-shrink: 0;
        }
        
        .form-check:hover {
            border-color: #ff4483;
            transform: translateY(-2px);
        }
        
        .form-check-input:checked + .form-check-label {
            color: #ff4483;
            font-weight: 500;
        }
        
        .form-check-label {
            color: #636e72;
            font-size: 0.9rem;
            line-height: 1.4;
            display: block;
            width: 100%;
        }
        
        .text-pink {
            color: #ff4483;
            font-weight: 500;
            font-size: 0.85rem;
        }
        
        #modal-addons-checkboxes .form-check {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            padding: 12px;
        }
        
        #modal-addons-checkboxes .form-check-input {
            margin-bottom: 5px;
        }
        
        #modal-addons-checkboxes .form-check-label {
            display: flex;
            flex-direction: column;
        }
        
        #modal-addons-checkboxes .text-pink {
            margin-top: 4px;
        }
        
        .customer-message textarea {
            border: 1px solid #f0ece7;
            background-color: white;
            border-radius: 10px;
            padding: 12px;
            font-size: 0.9rem;
            resize: none;
            box-shadow: none;
            transition: all 0.3s ease;
        }
        
        .customer-message textarea:focus {
            border-color: #ff4483;
            box-shadow: 0 0 0 3px rgba(255, 68, 131, 0.1);
        }
        
        /* Responsive adjustments for custom modal */
        @media (max-width: 767px) {
            .custom-modal {
                overflow-y: auto;
            }
            
            .custom-modal .row {
                display: block;
            }
            
            .modal-image-container {
                max-height: none;
                border-radius: 20px 20px 0 0;
            }
            
            .modal-details.custom-scroll {
                max-height: none;
                overflow: visible;
            }
            
            .options-group-content {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            }
        }
        
        /* Adjust spacing for better organization */
        #modal-size-options .options-group-content,
        #modal-ribbon-color-options .options-group-content,
        #modal-wrapper-color-options .options-group-content {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 15px;
        }
        
        /* Separate styling for add-ons that might have longer text */
        #modal-addons-checkboxes {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 12px;
        }
        
        /* More targeted fixes for radio buttons */
        #modal-size-options .form-check,
        #modal-ribbon-color-options .form-check,
        #modal-wrapper-color-options .form-check {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: flex-start;
            padding: 8px 10px;
            min-height: 36px;
            text-align: left;
        }
        
        #modal-size-options .form-check-input,
        #modal-ribbon-color-options .form-check-input,
        #modal-wrapper-color-options .form-check-input {
            margin-right: 8px;
            margin-top: 0;
            margin-left: 0;
            position: relative;
            flex: 0 0 auto;
        }
        
        #modal-size-options .form-check-label,
        #modal-ribbon-color-options .form-check-label,
        #modal-wrapper-color-options .form-check-label {
            display: inline;
            text-align: left;
            white-space: nowrap;
            color: #333;
            font-weight: normal;
            font-size: 0.9rem;
            margin: 0;
        }
        
        /* Radio buttons specific styling */
        input[type="radio"] {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            border-radius: 50%;
            width: 10px;
            height: 10px;
            border: 1px solid #ddd;
            transition: 0.2s all linear;
            outline: none;
            margin-right: 8px;
            position: relative;
            display: inline-block;
            vertical-align: middle;
        }
        
        input[type="radio"]:checked {
            border: 3px solid #ff4483;
            background-color: white;
        }
        
        /* Dropdown styling */
        .options-group-content select.form-control {
            border: 1px solid #f0ece7;
            border-radius: 10px;
            padding: 10px 15px;
            background-color: white;
            color: #333;
            font-size: 0.95rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            width: 100%;
            margin-bottom: 10px;
        }
        
        .options-group-content select.form-control:focus {
            border-color: #ff4483;
            box-shadow: 0 0 0 3px rgba(255, 68, 131, 0.1);
            outline: none;
        }
        
        #addons-select {
            height: auto;
            min-height: 120px;
        }
        
        #addons-select option {
            padding: 8px 10px;
            border-bottom: 1px solid #f0ece7;
        }
        
        #addons-select option:last-child {
            border-bottom: none;
        }
        
        #addons-select option:checked {
            background-color: #ff4483;
            color: white;
        }
        
        /* Login Modal Styles */
        .login-register-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
        }
        
        .login-register-content {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            text-align: center;
            position: relative;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .modal-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }
        
        .btn-login, .btn-register {
            padding: 10px 25px;
            border-radius: 30px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-login {
            background-color: #ff4483;
            color: white;
        }
        
        .btn-register {
            background-color: transparent;
            color: #ff4483;
            border: 1px solid #ff4483;
        }
        
        .btn-login:hover, .btn-register:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        /* Add-ons Grid Layout */
        .addons-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            padding: 15px 0;
        }

        .addon-item {
            background: white;
            border: 1px solid #f0ece7;
            border-radius: 10px;
            padding: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: all 0.3s ease;
            position: relative;
        }

        .addon-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-color: #ff4483;
        }

        .addon-image {
            width: 100%;
            height: 150px;
            margin-bottom: 10px;
            border-radius: 8px;
            overflow: hidden;
        }

        .addon-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .addon-item:hover .addon-image img {
            transform: scale(1.1);
        }

        .addon-details {
            width: 100%;
            text-align: center;
        }

        .addon-name {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .addon-price {
            display: block;
            color: #ff4483;
            font-weight: 700;
            font-size: 1.1em;
            margin-bottom: 10px;
        }

        .addon-checkbox {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 20px;
            height: 20px;
            cursor: pointer;
            opacity: 0;
        }

        .addon-item:hover .addon-checkbox {
            opacity: 1;
        }

        .addon-checkbox:checked + .addon-details {
            background-color: rgba(255, 68, 131, 0.1);
        }

        @media (max-width: 767px) {
            .addons-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 15px;
            }

            .addon-image {
                height: 120px;
            }
        }
    </style>
</head>

<body>
    <!-- ***** Preloader Start ***** -->
    <div id="preloader">
        <div class="jumper">
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>  
    <!-- ***** Preloader End ***** -->
    
    <!-- ***** Header Area Start ***** -->
    <?php include 'navi.php'; ?>
    <!-- ***** Header Area End ***** -->

    <!-- ***** Customized Products Section Start ***** -->
    <section class="section padding-top-70 padding-bottom-70" id="customized-products">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="center-heading">
                        <h2 class="section-title">Customized Arrangements</h2>
                    </div>
                    <div class="center-text">
                        <p>Create your perfect arrangement with our customizable options</p>
                    </div>
                </div>
                </div>

            <div class="row">
                <!-- Customized Product Cards -->
                <?php
                $query = "SELECT cp.* FROM customized_products cp";
                $result = $conn->query($query);

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $productId = $row['id'];
                        $productName = htmlspecialchars($row['product_name'] ?? 'Product Name Unavailable');
                        $productPrice = $row['product_price'] ?? 0.00;
                        $productDescShort = strlen($row['product_description']) > 50 ? htmlspecialchars(substr($row['product_description'], 0, 50)) . '...' : htmlspecialchars($row['product_description']);
                        $productDescFull = htmlspecialchars($row['product_description'] ?? '');
                        $productImage = $row['product_image'];
                        $bouquetSizes = htmlspecialchars($row['bouquet_sizes'] ?? '');
                        $ribbonColors = htmlspecialchars($row['ribbon_colors'] ?? '');
                        $wrapperColors = htmlspecialchars($row['wrapper_colors'] ?? '');
                        $addonsData = json_decode($row['add_ons'], true);

                        $imagePath = !empty($productImage) && file_exists("uploads/" . $productImage)
                            ? "uploads/" . htmlspecialchars($productImage)
                            : "uploads/default.jpg";

                        echo '<div class="col-lg-4 col-md-6 col-sm-12" data-scroll-reveal="enter bottom move 50px over 0.6s after 0.2s">';
                        echo '<div class="product-card" data-id="' . $productId . '"
                            data-name="' . $productName . '"
                            data-image="' . $imagePath . '"
                            data-price="' . $productPrice . '"
                            data-description-full="' . $productDescFull . '"
                            data-bouquet-sizes="' . $bouquetSizes . '"
                            data-ribbon-colors="' . $ribbonColors . '"
                            data-wrapper-colors="' . $wrapperColors . '"
                            data-addons="' . htmlspecialchars(json_encode($addonsData)) . '">';
                        
                        echo '<div class="product-image-wrapper">';
                        echo '<img src="' . $imagePath . '" alt="' . $productName . '" class="product-image">';
                        echo '<div class="product-overlay">';
                        echo '<button type="button" class="customized-view-btn" data-product-id="' . $productId . '">';
                        echo '<i class="fas fa-magic"></i> Customize';
                        echo '</button>';
                        echo '</div>'; // End product-overlay
                        echo '</div>'; // End product-image-wrapper
                        
                        echo '<div class="product-info">';
                        echo '<h3>' . $productName . '</h3>';
                        echo '<p class="short-description">' . $productDescShort . '</p>';
                        echo '<p class="product-price">₱' . number_format($productPrice, 2) . '</p>';
                        echo '</div>'; // End product-info
                        
                        echo '</div>'; // End product-card
                        echo '</div>'; // End col
                    }
                } else {
                    echo '<div class="col-12"><p class="text-center">No customized products available at the moment.</p></div>';
                }
                ?>
            </div>
        </div>
    </section>
    <!-- ***** Customized Products Section End ***** -->

    <!-- ***** Customized Product Modal ***** -->
    <div id="product-details-modal" class="modal">
        <div class="modal-content custom-modal">
            <span class="close-button">&times;</span>
            <div class="row">
                <div class="col-md-5">
                    <div class="modal-image-container">
                        <img id="modal-product-image" src="" alt="Product Image" class="img-fluid rounded">
                        <h3 id="modal-product-name" class="mt-3"></h3>
                        <p id="modal-product-description" class="description"></p>
                        <p class="price mt-2">₱<span id="modal-product-price"></span></p>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="modal-details custom-scroll">
                        <h4 class="custom-title">Customize Your Arrangement</h4>
                        
                        <div class="custom-options-container">
                            <div id="modal-size-options" class="modal-options-group mb-3"></div>
                            <div id="modal-ribbon-color-options" class="modal-options-group mb-3"></div>
                            <div id="modal-wrapper-color-options" class="modal-options-group mb-3"></div>
                            <div id="modal-addon-options-container" class="modal-options-group mb-3">
                                <h5 class="options-title">Add-ons:</h5>
                                <div id="modal-addons-checkboxes" class="options-group-content"></div>
                            </div>
                            <div class="customer-message mb-3">
                                <h5 class="options-title">Personalized Message (Optional):</h5>
                                <textarea id="modal-customer-message" name="customer_message" class="form-control" rows="3"></textarea>
                            </div>
                            <form id="modal-add-to-cart-form" action="customized_products.php" method="GET" class="customization-form">
                                <div id="modal-selected-addons"></div>
                                <input type="hidden" name="add" id="modal-product-id-input" value="">
                                <div class="modal-actions">
                                    <button type="submit" class="main-button">Add to Cart</button>
                                    <a href="checkout.php" class="main-button-secondary mt-3 d-inline-block">Proceed to Checkout</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ***** Login Modal ***** -->
    <?php if (!isset($_SESSION['user_id'])): ?>
    <div class="login-register-modal" id="loginModal" role="dialog" aria-labelledby="loginModalTitle" aria-hidden="true">
        <div class="login-register-content">
            <span class="close" onclick="document.getElementById('loginModal').style.display='none'" aria-label="Close">&times;</span>
            <h2 id="loginModalTitle" class="mb-3">Welcome to Heavenly Bloom</h2>
            <p class="mb-4">Please login or register to place your order</p>
            <div class="modal-buttons">
                <a href="user_login.php" class="btn-login">Login</a>
                <a href="user_register.php" class="btn-register">Register</a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- jQuery -->
    <script src="assets/js/jquery-2.1.0.min.js"></script>

    <!-- Bootstrap -->
    <script src="assets/js/popper.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>

    <!-- Plugins -->
    <script src="assets/js/scrollreveal.min.js"></script>
    <script src="assets/js/waypoints.min.js"></script>
    <script src="assets/js/jquery.counterup.min.js"></script>
    <script src="assets/js/imgfix.min.js"></script> 
    
    <!-- Global Init -->
    <script src="assets/js/custom.js"></script>
    
    <!-- Custom JavaScript for customized products -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get PHP data for wrappers, ribbon colors, and add-ons
        const allWrappers = <?php echo $wrappersJson; ?>;
        const allRibbonColors = <?php echo $ribbonColorsJson; ?>;
        const allAddOns = <?php echo $addOnsJson; ?>;
        
        // Customized product modal functionality
        const customModal = document.getElementById('product-details-modal');
        const modalProductName = document.getElementById('modal-product-name');
        const modalProductImage = document.getElementById('modal-product-image');
        const modalProductDescription = document.getElementById('modal-product-description');
        const modalProductPrice = document.getElementById('modal-product-price');
        const modalSizeOptions = document.getElementById('modal-size-options');
        const modalRibbonColorOptions = document.getElementById('modal-ribbon-color-options');
        const modalWrapperColorOptions = document.getElementById('modal-wrapper-color-options');
        const modalAddonOptionsContainer = document.getElementById('modal-addon-options-container');
        const modalAddonsCheckboxes = document.getElementById('modal-addons-checkboxes');
        const modalProductIdInput = document.getElementById('modal-product-id-input');
        const closeModalButton = customModal.querySelector('.close-button');
        
        // Customized product view buttons
        document.querySelectorAll('.customized-view-btn').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.dataset.productId;
                const productCard = this.closest('.product-card');
                const productName = productCard.dataset.name;
                const productImage = productCard.dataset.image;
                const productPrice = productCard.dataset.price;
                const productDescriptionFull = productCard.dataset.descriptionFull;
                const bouquetSizes = productCard.dataset.bouquetSizes ? productCard.dataset.bouquetSizes.split(',') : [];
                const ribbonColors = productCard.dataset.ribbonColors ? productCard.dataset.ribbonColors.split(',') : [];
                const wrapperColors = productCard.dataset.wrapperColors ? productCard.dataset.wrapperColors.split(',') : [];
                const addonsData = JSON.parse(productCard.dataset.addons || '[]');

                modalProductName.textContent = productName;
                modalProductImage.src = productImage;
                modalProductImage.alt = productName;
                modalProductDescription.textContent = productDescriptionFull;
                modalProductPrice.textContent = parseFloat(productPrice).toFixed(2);
                modalProductIdInput.value = productId;

                // Generate size options
                modalSizeOptions.innerHTML = '';
                if (bouquetSizes.length > 0) {
                    let sizeOptionsHTML = '<h5 class="options-title">Bouquet Size:</h5><div class="options-group-content">';
                    bouquetSizes.forEach((size, index) => {
                        const trimmedSize = size.trim();
                        sizeOptionsHTML += `
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="bouquet_size" id="size-${trimmedSize}" value="${trimmedSize}" ${index === 0 ? 'checked' : ''}>
                            <label class="form-check-label" for="size-${trimmedSize}">${trimmedSize}</label>
                        </div>`;
                    });
                    sizeOptionsHTML += '</div>';
                    modalSizeOptions.innerHTML = sizeOptionsHTML;
                } else {
                    modalSizeOptions.style.display = 'none';
                }

                // Generate ribbon color options
                modalRibbonColorOptions.innerHTML = '';
                if (allRibbonColors.length > 0) {
                    let ribbonOptionsHTML = '<h5 class="options-title">Ribbon Color:</h5><div class="options-group-content">';
                    ribbonOptionsHTML += '<select class="form-control" name="ribbon_color" id="ribbon-color-select">';
                    allRibbonColors.forEach((color, index) => {
                        const colorName = color.name;
                        ribbonOptionsHTML += `
                        <option value="${color.id}" 
                                data-price="${color.price || 0}" 
                                data-color="${colorName}">
                            ${colorName} ${color.price ? `(+₱${parseFloat(color.price).toFixed(2)})` : ''}
                        </option>`;
                    });
                    ribbonOptionsHTML += '</select></div>';
                    modalRibbonColorOptions.innerHTML = ribbonOptionsHTML;
                } else {
                    modalRibbonColorOptions.style.display = 'none';
                }

                // Generate wrapper color options
                modalWrapperColorOptions.innerHTML = '';
                if (allWrappers.length > 0) {
                    let wrapperOptionsHTML = '<h5 class="options-title">Wrapper Color:</h5><div class="options-group-content">';
                    wrapperOptionsHTML += '<select class="form-control" name="wrapper_color" id="wrapper-color-select">';
                    allWrappers.forEach((wrapper, index) => {
                        const wrapperName = wrapper.color || 'Wrapper ' + (index + 1);
                        wrapperOptionsHTML += `
                        <option value="${wrapper.id}" 
                                data-price="${wrapper.price || 0}" 
                                data-color="${wrapperName}">
                            ${wrapperName} ${wrapper.price ? `(+₱${parseFloat(wrapper.price).toFixed(2)})` : ''}
                        </option>`;
                    });
                    wrapperOptionsHTML += '</select></div>';
                    modalWrapperColorOptions.innerHTML = wrapperOptionsHTML;
                } else {
                    modalWrapperColorOptions.style.display = 'none';
                }

                // Generate add-on checkboxes
                modalAddonsCheckboxes.innerHTML = '';
                if (allAddOns.length > 0) {
                    let addonsHTML = '<div class="addons-grid">';
                    allAddOns.forEach(addon => {
                        addonsHTML += `
                            <div class="addon-item">
                                <div class="addon-image">
                                    <img src="${addon.image_path || 'uploads/default.jpg'}" alt="${addon.name}">
                                </div>
                                <div class="addon-details">
                                    <label class="addon-name">${addon.name}</label>
                                    <span class="addon-price">₱${parseFloat(addon.price || 0).toFixed(2)}</span>
                                    <input type="checkbox" name="addons[]" value="${addon.id}" 
                                        data-price="${addon.price || 0}" 
                                        data-name="${addon.name}"
                                        class="addon-checkbox">
                                </div>
                            </div>`;
                    });
                    addonsHTML += '</div>';
                    modalAddonsCheckboxes.innerHTML = addonsHTML;
                } else {
                    modalAddonOptionsContainer.style.display = 'none';
                }

                customModal.style.display = 'flex';
            });
        });
        
        // Close customized product modal
        if (closeModalButton) {
            closeModalButton.addEventListener('click', function() {
                customModal.style.display = 'none';
            });
        }
        
        // Handle add-ons selection and price calculation
        document.addEventListener('change', function(e) {
            if (e.target && (e.target.classList.contains('addon-checkbox') || e.target.id === 'ribbon-color-select' || e.target.id === 'wrapper-color-select')) {
                updateTotalPrice();
            }
        });
        
        // Function to update total price based on all selections
        function updateTotalPrice() {
            const basePrice = parseFloat(modalProductPrice.textContent);
            let totalPrice = basePrice;
            
            // Add ribbon color price if selected
            const ribbonSelect = document.getElementById('ribbon-color-select');
            if (ribbonSelect && ribbonSelect.selectedOptions.length > 0) {
                const selectedOption = ribbonSelect.selectedOptions[0];
                const ribbonPrice = parseFloat(selectedOption.dataset.price || 0);
                if (!isNaN(ribbonPrice)) {
                    totalPrice += ribbonPrice;
                }
            }
            
            // Add wrapper color price if selected
            const wrapperSelect = document.getElementById('wrapper-color-select');
            if (wrapperSelect && wrapperSelect.selectedOptions.length > 0) {
                const selectedOption = wrapperSelect.selectedOptions[0];
                const wrapperPrice = parseFloat(selectedOption.dataset.price || 0);
                if (!isNaN(wrapperPrice)) {
                    totalPrice += wrapperPrice;
                }
            }
            
            // Add selected add-ons prices
            const addonCheckboxes = document.querySelectorAll('.addon-checkbox:checked');
            addonCheckboxes.forEach(checkbox => {
                const addonPrice = parseFloat(checkbox.dataset.price || 0);
                if (!isNaN(addonPrice)) {
                    totalPrice += addonPrice;
                }
            });
            
            // Update the displayed price
            modalProductPrice.textContent = totalPrice.toFixed(2);
        }
        
        // Handle form submission
        const modalAddToCartForm = document.getElementById('modal-add-to-cart-form');
        if (modalAddToCartForm) {
            modalAddToCartForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const productId = document.getElementById('modal-product-id-input').value;
                
                // Add ribbon color if selected
                const ribbonSelect = document.getElementById('ribbon-color-select');
                if (ribbonSelect && ribbonSelect.selectedOptions.length > 0) {
                    const selectedOption = ribbonSelect.selectedOptions[0];
                    formData.append('selected_ribbon_color', JSON.stringify({
                        id: selectedOption.value,
                        color: selectedOption.dataset.color,
                        price: selectedOption.dataset.price
                    }));
                }
                
                // Add wrapper color if selected
                const wrapperSelect = document.getElementById('wrapper-color-select');
                if (wrapperSelect && wrapperSelect.selectedOptions.length > 0) {
                    const selectedOption = wrapperSelect.selectedOptions[0];
                    formData.append('selected_wrapper_color', JSON.stringify({
                        id: selectedOption.value,
                        color: selectedOption.dataset.color,
                        price: selectedOption.dataset.price
                    }));
                }
                
                // Add selected add-ons
                const addonCheckboxes = document.querySelectorAll('.addon-checkbox:checked');
                addonCheckboxes.forEach(checkbox => {
                    formData.append('selected_addons[]', JSON.stringify({
                        id: checkbox.value,
                        name: checkbox.dataset.name,
                        price: checkbox.dataset.price
                    }));
                });
                
                // Add customer message if provided
                const customerMessage = document.getElementById('modal-customer-message');
                if (customerMessage && customerMessage.value.trim()) {
                    formData.append('customer_message', customerMessage.value.trim());
                }
                
                // Add product ID
                formData.append('add', productId);
                
                // Submit the form
                const queryString = new URLSearchParams(formData).toString();
                window.location.href = `customized_products.php?${queryString}`;
            });
        }
        
        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            if (customModal && event.target === customModal) {
                customModal.style.display = 'none';
            }
        });
        
        // Handle login modal
        const loginModal = document.getElementById('loginModal');
        if (loginModal) {
            const loginCloseBtn = loginModal.querySelector('.close');
            
            loginCloseBtn.addEventListener('click', function() {
                loginModal.style.display = 'none';
            });
            
            window.addEventListener('click', function(event) {
                if (event.target === loginModal) {
                    loginModal.style.display = 'none';
                }
            });
        }
    });
    </script>
</body>
</html>
