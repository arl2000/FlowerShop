<?php
include 'navbar.php';
include 'db_connection.php';

// Fetch flower items
$flowers_query = $conn->query("SELECT id, name, price, image_path FROM flowers WHERE name LIKE '%flower%' OR name LIKE '%rose%' OR name LIKE '%tulip%'");
$flowers = [];
if ($flowers_query && $flowers_query->num_rows > 0) {
    while ($row = $flowers_query->fetch_assoc()) {
        $flowers[] = $row;
    }
}

// Fetch ribbon colors
$ribbons_query = $conn->query("SELECT id, name, price FROM ribbon_colors");
$ribbons = [];
if ($ribbons_query && $ribbons_query->num_rows > 0) {
    while ($row = $ribbons_query->fetch_assoc()) {
        $ribbons[] = $row;
    }
}

// Fetch wrappers
$wrappers_query = $conn->query("SELECT id, color as name, price FROM wrappers");
$wrappers = [];
if ($wrappers_query && $wrappers_query->num_rows > 0) {
    while ($row = $wrappers_query->fetch_assoc()) {
        $wrappers[] = $row;
    }
}

// Fetch chocolates and other add-ons
$addons_query = $conn->query("SELECT id, name, price, image_path FROM add_ons WHERE name LIKE '%chocolate%' OR name LIKE '%candy%' OR name LIKE '%snicker%' OR name LIKE '%m&m%'");
$addons = [];
if ($addons_query && $addons_query->num_rows > 0) {
    while ($row = $addons_query->fetch_assoc()) {
        $addons[] = $row;
    }
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
        $productName = "Custom Bouquet";
        $totalPrice = $_POST['total_price'];
        $selectedItems = json_decode($_POST['selected_items'], true);
        $itemPositions = json_decode($_POST['item_positions'], true);
        
        // Get customer message if provided
        $customerMessage = isset($_POST['customer_message']) ? $_POST['customer_message'] : null;
        
        // Insert into products table first to get product_id
        $insertProduct = $conn->prepare("INSERT INTO products (product_name, price, product_price, product_description, product_image, category_id) VALUES (?, ?, ?, 'Customized product', 'default.jpg', 8)");
        $insertProduct->bind_param("sdd", $productName, $totalPrice, $totalPrice);
        
        if ($insertProduct->execute()) {
            $productId = $conn->insert_id;
            
            // Store selected items and their positions in customized_products table
            $insertCustom = $conn->prepare("INSERT INTO customized_products (product_id, product_name, product_price, product_description, add_ons, message, category_id) VALUES (?, ?, ?, 'Custom Bouquet', ?, ?, 8)");
            
            $addOnsJson = json_encode([
                'items' => $selectedItems,
                'positions' => $itemPositions
            ]);
            
            $insertCustom->bind_param("isdss", $productId, $productName, $totalPrice, $addOnsJson, $customerMessage);
            
            if ($insertCustom->execute()) {
                // Add to cart
                $insertCart = $conn->prepare("INSERT INTO cart (user_id, product_id, product_name, product_price, quantity, is_customized, customer_message, addons) VALUES (?, ?, ?, ?, 1, 1, ?, ?)");
                $insertCart->bind_param("iisdss", $userId, $productId, $productName, $totalPrice, $customerMessage, $addOnsJson);
                
                if ($insertCart->execute()) {
                    header("Location: cart.php");
                    exit;
                } else {
                    echo "Error adding to cart: " . $conn->error;
                }
            } else {
                echo "Error saving customized product: " . $conn->error;
            }
        } else {
            echo "Error creating product: " . $conn->error;
        }
    } else {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('loginModal').style.display = 'flex';
            });
        </script>";
    }
}

// Convert PHP arrays to JSON for JavaScript
$flowersJson = json_encode($flowers);
$ribbonsJson = json_encode($ribbons);
$wrappersJson = json_encode($wrappers);
$addonsJson = json_encode($addons);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bouquet Customizer - Heavenly Bloom</title>
    <link href="https://fonts.googleapis.com/css?family=Raleway:100,300,400,500,700,900" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #d15e97;
            --secondary-color: #ffe8ec;
            --text-color: #333;
            --light-bg: #fff6f2;
            --border-radius: 15px;
            --shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Raleway', sans-serif;
            background-color: var(--light-bg);
            color: var(--text-color);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        h1, h2 {
            text-align: center;
            font-family: 'Playfair Display', serif;
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        
        .customizer-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
        }
        
        .item-selection {
            flex: 1;
            min-width: 300px;
            background-color: white;
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--shadow);
        }
        
        .preview-area {
            flex: 1.5;
            min-width: 400px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .canvas-container {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            position: relative;
            height: 500px;
            overflow: hidden;
        }
        
        #preview-canvas {
            width: 100%;
            height: 100%;
            background-color: var(--secondary-color);
            border-radius: var(--border-radius);
            position: relative;
        }
        
        .order-summary {
            background-color: white;
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--shadow);
        }
        
        .selection-category {
            margin-bottom: 20px;
        }
        
        .selection-category h3 {
            color: var(--primary-color);
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid var(--secondary-color);
        }
        
        .item-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 15px;
            margin-top: 10px;
        }
        
        .item {
            background-color: var(--secondary-color);
            border-radius: 10px;
            padding: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .item.selected {
            background-color: var(--primary-color);
            color: white;
        }
        
        .item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 5px;
        }
        
        .item-name {
            font-size: 14px;
            font-weight: 500;
        }
        
        .item-price {
            font-size: 12px;
            color: #555;
        }
        
        .item.selected .item-price {
            color: white;
        }
        
        .preview-item {
            position: absolute;
            cursor: move;
            z-index: 1;
        }
        
        .preview-item img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        /* Quantity controls for item selection */
        .item-quantity {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 5px;
        }
        
        .quantity-btn {
            width: 24px;
            height: 24px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50%;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        
        .quantity-btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        
        .quantity-value {
            margin: 0 8px;
            font-weight: 600;
            min-width: 20px;
            text-align: center;
        }
        
        /* Wrapper type selection styles */
        .wrapper-type-options {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 10px;
        }
        
        .wrapper-option {
            flex: 1;
            min-width: 60px;
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
            border-radius: 8px;
            padding: 10px;
            transition: all 0.3s ease;
            background-color: #f8f8f8;
        }
        
        .wrapper-option:hover {
            background-color: #f0f0f0;
        }
        
        .wrapper-option input[type="radio"] {
            position: absolute;
            opacity: 0;
        }
        
        .wrapper-option input[type="radio"]:checked + .wrapper-name + .wrapper-preview,
        .wrapper-option input[type="radio"]:checked + .wrapper-name {
            border-color: var(--primary-color);
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .wrapper-option input[type="radio"]:checked ~ .wrapper-name {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .wrapper-name {
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .wrapper-preview {
            width: 50px;
            height: 50px;
            border-radius: 50% 50% 5px 5px;
            border: 2px solid #ddd;
        }
        
        /* Stepper styles */
        .stepper-container {
            margin-bottom: 30px;
        }
        
        .stepper {
            display: flex;
            justify-content: space-between;
            max-width: 800px;
            margin: 0 auto;
            position: relative;
        }
        
        .stepper::before {
            content: '';
            position: absolute;
            top: 30px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: #ddd;
            z-index: 1;
        }
        
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
            text-align: center;
            flex: 1;
        }
        
        .step-number {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: #f5f5f5;
            border: 2px solid #ddd;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 24px;
            transition: all 0.3s ease;
        }
        
        .step-label {
            font-size: 14px;
            font-weight: 500;
            color: #666;
            transition: all 0.3s ease;
        }
        
        .step.active .step-number {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }
        
        .step.active .step-label {
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .step.completed .step-number {
            background-color: #4CAF50;
            border-color: #4CAF50;
            color: white;
        }
        
        /* Step content styles */
        .step-content {
            display: none;
        }
        
        .step-content.active {
            display: block;
        }
        
        .step-navigation {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        
        /* Bouquet structure enhancements */
        .bouquet-structure {
            position: absolute;
            width: 100%;
            height: 100%;
            pointer-events: none;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .bouquet-wrapper {
            position: relative;
            width: 80%;
            height: 80%;
            margin: 0 auto;
            z-index: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            background-image: url('images/wrapper.png');
            background-position: center;
            background-repeat: no-repeat;
            background-size: contain;
        }
        
        .bouquet-stem {
            position: absolute;
            width: 15px;
            height: 100px;
            background-color: #3a7d44;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            z-index: -1;
        }
        
        .bouquet-ribbon {
            position: absolute;
            bottom: 20%;
            width: 100%;
            height: 30px;
            z-index: 2;
            transform: rotate(-5deg);
            background-size: contain !important;
            background-position: center !important;
            background-repeat: no-repeat !important;
        }
        
        .preview-bouquet {
            width: 300px;
            height: 380px;
            margin: 0 auto;
            position: relative;
        }
        
        .preview-wrapper {
            position: absolute;
            width: 260px;
            height: 280px;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1;
            background-size: contain !important;
            background-position: center !important;
            background-repeat: no-repeat !important;
        }
        
        .preview-ribbon {
            position: absolute;
            width: 100%;
            height: 35px;
            bottom: 80px;
            left: 0;
            z-index: 3;
            transform: rotate(-8deg);
            background-size: contain !important;
            background-position: center !important;
            background-repeat: no-repeat !important;
        }
        
        /* Remove these classes as they're no longer needed */
        .preview-wrapper-inner,
        .preview-wrapper-fold,
        .preview-ribbon-knot {
            display: none;
        }
        
        .preview-flowers-container {
            position: absolute;
            width: 220px;
            height: 220px;
            top: 30px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 2;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .preview-flower {
            position: absolute;
            border-radius: 50%;
            background-size: cover;
            background-position: center;
            box-shadow: 0 3px 8px rgba(0,0,0,0.1);
        }
        
        .preview-stem {
            position: absolute;
            width: 20px;
            height: 100px;
            background-color: #3a7d44;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            z-index: 0;
        }
        
        .preview-ribbon {
            position: absolute;
            width: 100%;
            height: 35px;
            bottom: 80px;
            left: 0;
            z-index: 3;
            transform: rotate(-8deg);
        }
        
        .preview-ribbon-knot {
            position: absolute;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            bottom: 80px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 4;
            box-shadow: 0 3px 5px rgba(0,0,0,0.2);
        }
        
        .preview-addons-container {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 3;
        }
        
        .preview-addon {
            position: absolute;
            border-radius: 8px;
            background-size: cover;
            background-position: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        /* Wrapper Type Styles for Preview */
        .preview-wrapper-satin .preview-wrapper-inner {
            background-color: #fff0f5;
            border: 8px solid rgba(255, 240, 245, 0.8);
        }
        
        .preview-wrapper-kraft .preview-wrapper-inner {
            background-color: #d2b48c;
            border: 8px solid rgba(210, 180, 140, 0.8);
        }
        
        .preview-wrapper-tissue .preview-wrapper-inner {
            background-color: #f0ffff;
            border: 8px solid rgba(240, 255, 255, 0.8);
        }
        
        .preview-wrapper-burlap .preview-wrapper-inner {
            background-color: #deb887;
            border: 8px solid rgba(222, 184, 135, 0.8);
        }
        
        /* Modal styles for bouquet preview */
        .bouquet-preview-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.75);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(3px);
        }
        
        .bouquet-preview-content {
            background-color: white;
            border-radius: 15px;
            padding: 25px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            animation: slideInUp 0.4s ease-out;
        }
        
        @keyframes slideInUp {
            from {
                transform: translateY(30px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .bouquet-preview-close {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 24px;
            color: #999;
            cursor: pointer;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            transition: all 0.2s ease;
        }
        
        .bouquet-preview-close:hover {
            background-color: #f0f0f0;
            color: #666;
        }
        
        .bouquet-preview-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 15px;
        }
        
        .bouquet-preview-body {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
        }
        
        .bouquet-preview-image {
            flex: 1;
            min-width: 300px;
            display: flex;
            justify-content: center;
        }
        
        .bouquet-preview-details {
            flex: 1;
            min-width: 300px;
        }
        
        .bouquet-preview-section {
            margin-bottom: 20px;
        }
        
        .bouquet-preview-section h3 {
            color: var(--primary-color);
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px dashed var(--secondary-color);
        }
        
        .bouquet-preview-items {
            list-style-type: none;
            padding: 0;
        }
        
        .bouquet-preview-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dotted #f0f0f0;
        }
        
        .bouquet-preview-item:last-child {
            border-bottom: none;
        }
        
        .bouquet-preview-footer {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .bouquet-stem {
            position: absolute;
            bottom: -10px;
            width: 15px;
            height: 100px;
            background-color: #3a7d44;
            z-index: -1;
        }
        
        .bouquet-ribbon {
            position: absolute;
            bottom: 20%;
            width: 100%;
            height: 30px;
            background-color: #ff69b4;
            z-index: 2;
            transform: rotate(-5deg);
            opacity: 0.8;
        }
        
        .tools-panel {
            background-color: white;
            border-radius: 10px;
            padding: 10px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
            margin-bottom: 15px;
            box-shadow: var(--shadow);
        }
        
        .tool-btn {
            padding: 8px 12px;
            background-color: #f5f5f5;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            font-size: 14px;
            gap: 5px;
            transition: all 0.2s ease;
        }
        
        .tool-btn:hover {
            background-color: #e0e0e0;
        }
        
        .tool-btn.active {
            background-color: var(--primary-color);
            color: white;
        }
        
        /* Rotation control */
        .rotation-control {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
        }
        
        .rotation-slider {
            flex: 1;
        }
        
        .rotation-value {
            width: 50px;
            text-align: center;
        }
        
        .total-price {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-color);
            text-align: right;
            margin-top: 10px;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 30px;
            font-family: 'Raleway', sans-serif;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            flex: 1;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #bb407c;
        }
        
        .btn-secondary {
            background-color: #f5f5f5;
            color: var(--text-color);
        }
        
        .btn-secondary:hover {
            background-color: #e0e0e0;
        }
        
        .message-box {
            margin-top: 20px;
        }
        
        .message-box textarea {
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 10px;
            resize: vertical;
            font-family: 'Raleway', sans-serif;
        }
        
        @media (max-width: 768px) {
            .customizer-wrapper {
                flex-direction: column;
            }
            
            .canvas-container {
                height: 350px;
            }
            
            .item-grid {
                grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            }
            
            .tools-panel {
                flex-direction: row;
                overflow-x: auto;
                padding: 8px;
            }
            
            .tool-btn {
                padding: 6px 10px;
                font-size: 12px;
            }
        }
        
        /* Bouquet Preview Specific Styles */
        .preview-bouquet {
            width: 300px;
            height: 380px;
            margin: 0 auto;
            position: relative;
        }
        
        .preview-wrapper {
            position: absolute;
            width: 260px;
            height: 280px;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 120px 120px 0 0;
            z-index: 1;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
            background-image: url('images/wrapper.png');
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center;
        }
        
        .preview-wrapper-inner {
            position: absolute;
            width: 100%;
            height: 100%;
            bottom: 0;
            left: 0;
            border-radius: 120px 120px 0 0;
            opacity: 0.9;
        }
        
        .preview-wrapper-fold {
            position: absolute;
            width: 100%;
            height: 60%;
            bottom: 0;
            left: 0;
            background-color: inherit;
            opacity: 0.7;
            transform-origin: center bottom;
            clip-path: polygon(0 40%, 20% 100%, 80% 100%, 100% 40%);
        }
        
        .preview-flowers-container {
            position: absolute;
            width: 220px;
            height: 220px;
            top: 30px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 2;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .preview-flower {
            position: absolute;
            border-radius: 50%;
            background-size: cover;
            background-position: center;
            box-shadow: 0 3px 8px rgba(0,0,0,0.1);
        }
        
        .preview-stem {
            position: absolute;
            width: 20px;
            height: 100px;
            background-color: #3a7d44;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            z-index: 0;
        }
        
        .preview-ribbon {
            position: absolute;
            width: 100%;
            height: 35px;
            bottom: 80px;
            left: 0;
            z-index: 3;
            transform: rotate(-8deg);
        }
        
        .preview-ribbon-knot {
            position: absolute;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            bottom: 80px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 4;
            box-shadow: 0 3px 5px rgba(0,0,0,0.2);
        }
        
        .preview-addons-container {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 3;
        }
        
        .preview-addon {
            position: absolute;
            border-radius: 8px;
            background-size: cover;
            background-position: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        /* Wrapper Type Styles for Preview */
        .preview-wrapper-satin .preview-wrapper-inner {
            background-color: #fff0f5;
            border: 8px solid rgba(255, 240, 245, 0.8);
        }
        
        .preview-wrapper-kraft .preview-wrapper-inner {
            background-color: #d2b48c;
            border: 8px solid rgba(210, 180, 140, 0.8);
        }
        
        .preview-wrapper-tissue .preview-wrapper-inner {
            background-color: #f0ffff;
            border: 8px solid rgba(240, 255, 255, 0.8);
        }
        
        .preview-wrapper-burlap .preview-wrapper-inner {
            background-color: #deb887;
            border: 8px solid rgba(222, 184, 135, 0.8);
        }
        
        /* Modal styles for bouquet preview */
        .bouquet-preview-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.75);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(3px);
        }
        
        .bouquet-preview-content {
            background-color: white;
            border-radius: 15px;
            padding: 25px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            animation: slideInUp 0.4s ease-out;
        }
        
        @keyframes slideInUp {
            from {
                transform: translateY(30px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .bouquet-preview-close {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 24px;
            color: #999;
            cursor: pointer;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            transition: all 0.2s ease;
        }
        
        .bouquet-preview-close:hover {
            background-color: #f0f0f0;
            color: #666;
        }
        
        .bouquet-preview-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 15px;
        }
        
        .bouquet-preview-body {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
        }
        
        .bouquet-preview-image {
            flex: 1;
            min-width: 300px;
            display: flex;
            justify-content: center;
        }
        
        .bouquet-preview-details {
            flex: 1;
            min-width: 300px;
        }
        
        .bouquet-preview-section {
            margin-bottom: 20px;
        }
        
        .bouquet-preview-section h3 {
            color: var(--primary-color);
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px dashed var(--secondary-color);
        }
        
        .bouquet-preview-items {
            list-style-type: none;
            padding: 0;
        }
        
        .bouquet-preview-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dotted #f0f0f0;
        }
        
        .bouquet-preview-item:last-child {
            border-bottom: none;
        }
        
        .bouquet-preview-footer {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .bouquet-stem {
            position: absolute;
            bottom: -10px;
            width: 15px;
            height: 100px;
            background-color: #3a7d44;
            z-index: -1;
        }
        
        .bouquet-ribbon {
            position: absolute;
            bottom: 20%;
            width: 100%;
            height: 30px;
            background-color: #ff69b4;
            z-index: 2;
            transform: rotate(-5deg);
            opacity: 0.8;
        }
        
        /* New styles for selected items preview */
        .bouquet-preview-main {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .bouquet-preview-image {
            flex: 1;
            min-width: 300px;
        }
        
        .selected-items-preview {
            flex: 1;
            min-width: 200px;
            background-color: var(--light-bg);
            border-radius: var(--border-radius);
            padding: 15px;
        }
        
        .selected-items-preview h3 {
            color: var(--primary-color);
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px dashed var(--secondary-color);
        }
        
        .selected-items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            gap: 10px;
            max-height: 400px;
            overflow-y: auto;
            padding: 5px;
        }
        
        .selected-item-preview {
            background-color: white;
            border-radius: 8px;
            padding: 8px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .selected-item-preview img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 5px;
        }
        
        .selected-item-preview .item-name {
            font-size: 12px;
            color: var(--text-color);
            word-break: break-word;
        }
        
        .selected-item-preview .item-count {
            font-size: 11px;
            color: var(--primary-color);
            font-weight: bold;
        }
        
        /* Adjust modal content for new layout */
        .bouquet-preview-content {
            width: 95%;
            max-width: 1200px;
        }
        
        .bouquet-preview-body {
            flex-direction: column;
        }
        
        .bouquet-preview-details {
            flex: 1;
            min-width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🌸 Create Your Own Bouquet</h1>
        <p class="text-center" style="margin-bottom: 30px;">Build your perfect bouquet by following the steps below.</p>
        
        <div class="stepper-container">
            <div class="stepper">
                <div class="step active" data-step="1">
                    <div class="step-number">1</div>
                    <div class="step-label">Select Flowers</div>
                </div>
                <div class="step" data-step="2">
                    <div class="step-number">2</div>
                    <div class="step-label">Choose Wrapper</div>
                </div>
                <div class="step" data-step="3">
                    <div class="step-number">3</div>
                    <div class="step-label">Add Ribbon</div>
                </div>
                <div class="step" data-step="4">
                    <div class="step-number">4</div>
                    <div class="step-label">Add Extras</div>
                </div>
            </div>
        </div>
        
        <div class="customizer-wrapper">
            <div class="item-selection">
                <!-- Step 1: Flowers Selection -->
                <div class="selection-category step-content active" id="step-1-content">
                    <h3>Step 1: Choose Flowers</h3>
                    <p>Select flowers to include in your bouquet.</p>
                    <div class="item-grid" id="flowers-grid">
                        <!-- Flowers will be loaded here via JavaScript -->
                    </div>
                    <div class="step-navigation">
                        <button type="button" class="btn btn-primary next-step" data-goto="2">Next: Choose Wrapper</button>
                    </div>
                </div>
                
                <!-- Step 2: Wrapper Selection -->
                <div class="selection-category step-content" id="step-2-content">
                    <h3>Step 2: Choose Wrapper</h3>
                    <p>Select a wrapper type for your bouquet.</p>
                    <div class="wrapper-type-selection">
                        <div class="wrapper-type-options">
                            <label class="wrapper-option">
                                <input type="radio" name="wrapper-type" value="satin" checked>
                                <span class="wrapper-name">Satin</span>
                                <div class="wrapper-preview wrapper-type-satin"></div>
                            </label>
                            <label class="wrapper-option">
                                <input type="radio" name="wrapper-type" value="kraft">
                                <span class="wrapper-name">Kraft</span>
                                <div class="wrapper-preview wrapper-type-kraft"></div>
                            </label>
                            <label class="wrapper-option">
                                <input type="radio" name="wrapper-type" value="tissue">
                                <span class="wrapper-name">Tissue</span>
                                <div class="wrapper-preview wrapper-type-tissue"></div>
                            </label>
                            <label class="wrapper-option">
                                <input type="radio" name="wrapper-type" value="burlap">
                                <span class="wrapper-name">Burlap</span>
                                <div class="wrapper-preview wrapper-type-burlap"></div>
                            </label>
                        </div>
                    </div>
                    <div class="step-navigation">
                        <button type="button" class="btn btn-secondary prev-step" data-goto="1">Back</button>
                        <button type="button" class="btn btn-primary next-step" data-goto="3">Next: Add Ribbon</button>
                    </div>
                </div>
                
                <!-- Step 3: Ribbon Selection -->
                <div class="selection-category step-content" id="step-3-content">
                    <h3>Step 3: Choose Ribbons</h3>
                    <p>Select a ribbon to complement your bouquet.</p>
                    <div class="item-grid" id="ribbons-grid">
                        <!-- Ribbons will be loaded here via JavaScript -->
                    </div>
                    <div class="step-navigation">
                        <button type="button" class="btn btn-secondary prev-step" data-goto="2">Back</button>
                        <button type="button" class="btn btn-primary next-step" data-goto="4">Next: Add Extras</button>
                    </div>
                </div>
                
                <!-- Step 4: Add-ons Selection -->
                <div class="selection-category step-content" id="step-4-content">
                    <h3>Step 4: Choose Add-ons</h3>
                    <p>Enhance your bouquet with extras like chocolates or decorative items.</p>
                    <div class="item-grid" id="addons-grid">
                        <!-- Add-ons will be loaded here via JavaScript -->
                    </div>
                    <div class="message-box">
                        <h3>Add Your Message</h3>
                        <textarea id="customer-message" rows="3" placeholder="Enter your personalized message here..."></textarea>
                    </div>
                    <div class="step-navigation">
                        <button type="button" class="btn btn-secondary prev-step" data-goto="3">Back</button>
                        <button type="button" class="btn btn-primary" id="preview-bouquet-btn">Preview Bouquet</button>
                    </div>
                </div>
            </div>
            
            <div class="preview-area">
                <div class="tools-panel">
                    <button type="button" class="tool-btn" id="move-up-btn" title="Move layer up">
                        <i class="fas fa-arrow-up"></i> Layer Up
                    </button>
                    <button type="button" class="tool-btn" id="move-down-btn" title="Move layer down">
                        <i class="fas fa-arrow-down"></i> Layer Down
                    </button>
                    <button type="button" class="tool-btn" id="rotate-btn" title="Rotate item">
                        <i class="fas fa-sync-alt"></i> Rotate
                    </button>
                    <button type="button" class="tool-btn" id="resize-btn" title="Resize item">
                        <i class="fas fa-expand-arrows-alt"></i> Resize
                    </button>
                    <button type="button" class="tool-btn" id="delete-btn" title="Delete item">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
                
                <!-- Removed the canvas-container and replaced with selected items list -->
                <div class="selected-items-preview" style="background: #fff; border-radius: 10px; box-shadow: var(--shadow); padding: 20px; margin-bottom: 20px;">
                    <h2 style="color: var(--primary-color); margin-bottom: 15px;">Selected Items</h2>
                    <div id="selected-items-list">
                        <table id="selected-items-table" style="width:100%; border-collapse:collapse;">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Type</th>
                                    <th>Qty</th>
                                    <th>Price</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Items will be inserted here -->
                            </tbody>
                        </table>
                        <div style="text-align:right; font-weight:bold; margin-top:10px;">
                            Total: ₱<span id="selected-items-total">0.00</span>
                        </div>
                    </div>
                </div>
                
                <div class="rotation-control" style="display: none;">
                    <label for="rotation-slider">Rotation:</label>
                    <input type="range" id="rotation-slider" class="rotation-slider" min="0" max="360" value="0">
                    <span id="rotation-value" class="rotation-value">0°</span>
                </div>
                
                <div class="total-price">
                    Total: ₱<span id="total-price">0.00</span>
                </div>
                <div class="action-buttons">
                    <button class="btn btn-secondary" id="reset-btn">Reset</button>
                    <form id="customization-form" method="POST" action="save_custom_bouquet.php">
                        <input type="hidden" name="selected_items" id="selected-items-input">
                        <input type="hidden" name="item_positions" id="item-positions-input">
                        <input type="hidden" name="total_price" id="total-price-input">
                        <input type="hidden" name="customer_message" id="customer-message-input">
                        <input type="hidden" name="wrapper_type" id="wrapper-type-input">
                        <input type="hidden" name="item_quantities" id="item-quantities-input">
                        <button type="submit" class="btn btn-primary" id="add-to-cart-btn">Add to Cart</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Login Modal -->
    <div id="loginModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); align-items: center; justify-content: center;">
        <div class="modal-content" style="background-color: white; margin: auto; padding: 20px; border-radius: 10px; max-width: 400px; width: 90%;">
            <span class="close" onclick="document.getElementById('loginModal').style.display='none'" style="color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer;">&times;</span>
            <h2 style="color: var(--primary-color); text-align: center; margin-bottom: 20px;">Login Required</h2>
            <p style="text-align: center; margin-bottom: 20px;">Please log in to add items to your cart.</p>
            <form action="user_login.php" method="post" style="display: flex; flex-direction: column; gap: 15px;">
                <div>
                    <label for="username" style="display: block; margin-bottom: 5px; font-weight: bold;">Username</label>
                    <input type="text" id="username" name="username" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ddd;">
                </div>
                <div>
                    <label for="password" style="display: block; margin-bottom: 5px; font-weight: bold;">Password</label>
                    <input type="password" id="password" name="password" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ddd;">
                </div>
                <button type="submit" style="background-color: var(--primary-color); color: white; padding: 12px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">Login</button>
                <p style="text-align: center; margin-top: 10px;">Don't have an account? <a href="user_register.php" style="color: var(--primary-color); text-decoration: none;">Register</a></p>
            </form>
        </div>
    </div>
    
    <!-- Bouquet Preview Modal -->
    <div id="bouquetPreviewModal" class="bouquet-preview-modal">
        <div class="bouquet-preview-content">
            <span class="bouquet-preview-close" id="previewCloseBtn">&times;</span>
            <div class="bouquet-preview-header">
                <h2>Your Custom Bouquet</h2>
            </div>
            <div class="bouquet-preview-body">
                <div class="bouquet-preview-details">
                    <div class="bouquet-preview-section">
                        <h3>Bouquet Details</h3>
                        <div class="bouquet-preview-items" id="previewItemsList">
                            <!-- Selected items will be listed here -->
                        </div>
                    </div>
                    <div class="bouquet-preview-section">
                        <h3>Your Message</h3>
                        <p id="previewMessage">No message added</p>
                    </div>
                    <div class="bouquet-preview-section">
                        <h3>Total Price</h3>
                        <div class="total-price">₱<span id="previewTotalPrice">0.00</span></div>
                    </div>
                </div>
            </div>
            <div class="bouquet-preview-footer">
                <button id="continueEditingBtn" class="btn btn-secondary">Continue Editing</button>
                <button id="previewAddToCartBtn" class="btn btn-primary">Add to Cart</button>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Parse data from PHP
            const flowers = <?php echo $flowersJson; ?>;
            const ribbons = <?php echo $ribbonsJson; ?>;
            const wrappers = <?php echo $wrappersJson; ?>;
            const addons = <?php echo $addonsJson; ?>;
            
            // State management
            let selectedItems = [];
            let itemPositions = {};
            let totalPrice = 0;
            let highestZIndex = 1;
            let currentSelectedItem = null;
            let isResizing = false;
            let isRotating = false;
            let currentResizeHandle = null;
            let itemQuantities = {}; // Track quantities for each item
            let currentWrapperType = 'satin'; // Default wrapper type
            let currentStep = 1; // Current step in the process
            let stepsCompleted = {
                1: false,
                2: false,
                3: false,
                4: false
            };
            
            // DOM elements for tools
            const moveUpBtn = document.getElementById('move-up-btn');
            const moveDownBtn = document.getElementById('move-down-btn');
            const rotateBtn = document.getElementById('rotate-btn');
            const resizeBtn = document.getElementById('resize-btn');
            const deleteBtn = document.getElementById('delete-btn');
            const rotationControl = document.querySelector('.rotation-control');
            const rotationSlider = document.getElementById('rotation-slider');
            const rotationValue = document.getElementById('rotation-value');
            const bouquetWrapper = document.getElementById('bouquet-wrapper');
            const wrapperTypeInputs = document.querySelectorAll('input[name="wrapper-type"]');
            const previewBouquetBtn = document.getElementById('preview-bouquet-btn');
            
            // DOM elements
            const previewCanvas = document.getElementById('preview-canvas');
            const flowersGrid = document.getElementById('flowers-grid');
            const ribbonsGrid = document.getElementById('ribbons-grid');
            const addonsGrid = document.getElementById('addons-grid');
            const resetBtn = document.getElementById('reset-btn');
            const addToCartBtn = document.getElementById('add-to-cart-btn');
            const customizationForm = document.getElementById('customization-form');
            const selectedItemsInput = document.getElementById('selected-items-input');
            const itemPositionsInput = document.getElementById('item-positions-input');
            const totalPriceInput = document.getElementById('total-price-input');
            const customerMessage = document.getElementById('customer-message');
            const customerMessageInput = document.getElementById('customer-message-input');
            const wrapperTypeInput = document.getElementById('wrapper-type-input');
            const itemQuantitiesInput = document.getElementById('item-quantities-input');
            
            // Step navigation elements
            const stepElements = document.querySelectorAll('.step');
            const stepContents = document.querySelectorAll('.step-content');
            const nextButtons = document.querySelectorAll('.next-step');
            const prevButtons = document.querySelectorAll('.prev-step');
            
            // Populate item grids
            populateGrid(flowers, flowersGrid, 'flower');
            populateGrid(ribbons, ribbonsGrid, 'ribbon');
            populateGrid(addons, addonsGrid, 'addon');
            
            // Step Navigation Functions
            function goToStep(stepNumber) {
                // Update current step
                currentStep = parseInt(stepNumber);
                
                // Update step indicators
                stepElements.forEach(step => {
                    const stepNum = parseInt(step.dataset.step);
                    step.classList.remove('active', 'completed');
                    
                    if (stepNum < currentStep) {
                        step.classList.add('completed');
                    } else if (stepNum === currentStep) {
                        step.classList.add('active');
                    }
                });
                
                // Show/hide step content
                stepContents.forEach(content => {
                    content.classList.remove('active');
                });
                document.getElementById(`step-${currentStep}-content`).classList.add('active');
                
                // Update bouquet structure based on current step
                updateBouquetStructure();
            }
            
            function checkStepCompletion(stepNumber) {
                switch(stepNumber) {
                    case 1: // Flowers
                        stepsCompleted[1] = selectedItems.filter(item => item.type === 'flower').length > 0;
                        return stepsCompleted[1];
                    case 2: // Wrapper
                        stepsCompleted[2] = true; // Always complete since there's a default selection
                        return true;
                    case 3: // Ribbons
                        stepsCompleted[3] = selectedItems.filter(item => item.type === 'ribbon').length > 0;
                        return stepsCompleted[3];
                    case 4: // Add-ons - optional
                        stepsCompleted[4] = true;
                        return true;
                    default:
                        return false;
                }
            }
            
            // Event listeners for step navigation
            nextButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const nextStep = parseInt(this.dataset.goto);
                    
                    // Check if current step is completed
                    if (!checkStepCompletion(currentStep)) {
                        switch(currentStep) {
                            case 1:
                                alert('Please select at least one flower for your bouquet.');
                                break;
                            case 3:
                                alert('Please select a ribbon for your bouquet.');
                                break;
                        }
                        return;
                    }
                    
                    goToStep(nextStep);
                });
            });
            
            prevButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const prevStep = parseInt(this.dataset.goto);
                    goToStep(prevStep);
                });
            });
            
            // Initialize the step navigation
            function initStepNavigation() {
                goToStep(1); // Start at step 1
            }
            
            // Functions
            function populateGrid(items, gridElement, itemType) {
                items.forEach(item => {
                    const itemElement = document.createElement('div');
                    itemElement.classList.add('item');
                    itemElement.dataset.id = item.id;
                    itemElement.dataset.name = item.name;
                    itemElement.dataset.price = item.price;
                    itemElement.dataset.type = itemType;
                    
                    // Initialize quantity tracking
                    const itemKey = `${itemType}_${item.id}`;
                    itemQuantities[itemKey] = 0;
                    
                    let imagePath = '';
                    if (itemType === 'ribbon') {
                        // Use color-based ribbons
                        const ribbonColor = item.name.toLowerCase();
                        imagePath = `https://via.placeholder.com/60/${getColorHex(ribbonColor)}/ffffff?text=R`;
                    } else if (itemType === 'wrapper') {
                        // Use color-based wrappers
                        const wrapperColor = item.name.toLowerCase();
                        imagePath = `https://via.placeholder.com/60/${getColorHex(wrapperColor)}/333333?text=W`;
                    } else if (item.image_path) {
                        imagePath = item.image_path;
                    } else {
                        imagePath = `https://via.placeholder.com/60/cccccc/ffffff?text=${item.name.charAt(0)}`;
                    }
                    
                    itemElement.innerHTML = `
                        <img src="${imagePath}" alt="${item.name}">
                        <div class="item-name">${item.name}</div>
                        <div class="item-price">₱${parseFloat(item.price).toFixed(2)}</div>
                        <div class="item-quantity">
                            <button type="button" class="quantity-btn minus-btn" disabled>-</button>
                            <span class="quantity-value">0</span>
                            <button type="button" class="quantity-btn plus-btn">+</button>
                        </div>
                    `;
                    
                    // Get the quantity buttons
                    const minusBtn = itemElement.querySelector('.minus-btn');
                    const plusBtn = itemElement.querySelector('.plus-btn');
                    const quantityDisplay = itemElement.querySelector('.quantity-value');
                    
                    // Add event listeners for quantity buttons
                    minusBtn.addEventListener('click', (e) => {
                        e.stopPropagation(); // Prevent item selection
                        decreaseItemQuantity(item, itemType, quantityDisplay, minusBtn);
                    });
                    
                    plusBtn.addEventListener('click', (e) => {
                        e.stopPropagation(); // Prevent item selection
                        increaseItemQuantity(item, itemType, itemElement, quantityDisplay, minusBtn);
                    });
                    
                    gridElement.appendChild(itemElement);
                });
            }
            
            function increaseItemQuantity(item, itemType, itemElement, quantityDisplay, minusBtn) {
                const itemKey = `${itemType}_${item.id}`;
                itemQuantities[itemKey]++;
                // Update the quantity display
                quantityDisplay.textContent = itemQuantities[itemKey];
                // Enable the minus button
                if (itemQuantities[itemKey] > 0) {
                    minusBtn.disabled = false;
                }
                // Add the item to the preview
                addItemInstance(item, itemType);
                // Update total price and selected items list
                updateTotalPrice();
                updateSelectedItemsList();
            }
            
            function decreaseItemQuantity(item, itemType, quantityDisplay, minusBtn) {
                const itemKey = `${itemType}_${item.id}`;
                if (itemQuantities[itemKey] > 0) {
                    itemQuantities[itemKey]--;
                    // Update the quantity display
                    quantityDisplay.textContent = itemQuantities[itemKey];
                    // Disable the minus button if quantity is 0
                    if (itemQuantities[itemKey] === 0) {
                        minusBtn.disabled = true;
                    }
                    // Remove one instance of this item from the preview
                    removeItemInstance(item, itemType);
                    // Update total price and selected items list
                    updateTotalPrice();
                    updateSelectedItemsList();
                }
            }
            
            function addItemInstance(item, itemType) {
                // Add to selected items
                const selectedItem = {
                    id: item.id,
                    name: item.name,
                    price: parseFloat(item.price),
                    type: itemType,
                    instanceId: Date.now() // Unique identifier for this instance
                };
                selectedItems.push(selectedItem);
                addItemToPreview(selectedItem);
                updateSelectedItemsList();
            }
            
            function removeItemInstance(item, itemType) {
                // Find the last added instance of this item
                const index = findLastIndex(selectedItems, i => 
                    i.id === item.id && i.type === itemType
                );
                if (index !== -1) {
                    const removedItem = selectedItems.splice(index, 1)[0];
                    removeItemFromPreview(removedItem);
                    updateSelectedItemsList();
                }
            }
            
            function findLastIndex(array, predicate) {
                for (let i = array.length - 1; i >= 0; i--) {
                    if (predicate(array[i])) {
                        return i;
                    }
                }
                return -1;
            }
            
            function addItemToPreview(item) {
                const previewItem = document.createElement('div');
                previewItem.classList.add('preview-item');
                previewItem.dataset.id = item.id;
                previewItem.dataset.type = item.type;
                if (item.instanceId) {
                    previewItem.dataset.instanceId = item.instanceId;
                }
                
                // Assign a unique z-index
                highestZIndex++;
                previewItem.style.zIndex = highestZIndex;
                
                let imagePath = '';
                if (item.type === 'ribbon') {
                    // Use ribbon.png from images folder
                    imagePath = 'images/ribbon.png';
                    // Apply color to maintain color styling
                    previewItem.style.backgroundColor = `#${getColorHex(item.name.toLowerCase())}`;
                    previewItem.style.mixBlendMode = 'multiply';
                } else if (item.type === 'wrapper') {
                    // Use wrapper.png from images folder
                    imagePath = 'images/wrapper.png';
                    // Apply color styling
                    previewItem.style.backgroundColor = `#${getColorHex(item.name.toLowerCase())}`;
                    previewItem.style.opacity = '0.9';
                } else {
                    // For flowers and add-ons, find the original item to get image path
                    let originalItem;
                    switch (item.type) {
                        case 'flower':
                            originalItem = flowers.find(f => f.id === item.id);
                            break;
                        case 'addon':
                            originalItem = addons.find(a => a.id === item.id);
                            break;
                    }
                    
                    imagePath = originalItem && originalItem.image_path 
                        ? originalItem.image_path 
                        : `https://via.placeholder.com/80/cccccc/ffffff?text=${item.name.charAt(0)}`;
                }
                
                // Set initial size based on item type
                let width, height;
                switch (item.type) {
                    case 'flower':
                        width = 100;
                        height = 100;
                        break;
                    case 'ribbon':
                        width = 80;
                        height = 30;
                        break;
                    case 'wrapper':
                        width = 200;
                        height = 200;
                        break;
                    case 'addon':
                        width = 60;
                        height = 60;
                        break;
                }
                
                previewItem.style.width = `${width}px`;
                previewItem.style.height = `${height}px`;
                
                // Set initial position (centered)
                const initialX = (previewCanvas.offsetWidth - width) / 2;
                const initialY = (previewCanvas.offsetHeight - height) / 2;
                previewItem.style.left = `${initialX}px`;
                previewItem.style.top = `${initialY}px`;
                
                // Add transformation properties
                previewItem.style.transform = 'rotate(0deg)';
                
                // Save initial position and properties
                const positionKey = item.instanceId ? 
                    `${item.type}_${item.id}_${item.instanceId}` : 
                    `${item.type}_${item.id}`;
                
                itemPositions[positionKey] = {
                    x: initialX,
                    y: initialY,
                    width: width,
                    height: height,
                    rotation: 0,
                    zIndex: highestZIndex
                };
                
                previewItem.innerHTML = `<img src="${imagePath}" alt="${item.name}" style="width: 100%; height: 100%; object-fit: contain;">`;
                previewCanvas.appendChild(previewItem);
                
                // Make item selectable and draggable
                makeItemInteractive(previewItem);
            }
            
            function removeItemFromPreview(item) {
                // Use instanceId if available to find the exact item instance
                const selector = item.instanceId ? 
                    `.preview-item[data-id="${item.id}"][data-type="${item.type}"][data-instance-id="${item.instanceId}"]` : 
                    `.preview-item[data-id="${item.id}"][data-type="${item.type}"]`;
                
                const previewItem = previewCanvas.querySelector(selector);
                
                if (previewItem) {
                    // If this was the selected item, clear selection
                    if (currentSelectedItem === previewItem) {
                        unselectAllItems();
                    }
                    
                    previewCanvas.removeChild(previewItem);
                    
                    // Remove from positions as well
                    const positionKey = item.instanceId ? 
                        `${item.type}_${item.id}_${item.instanceId}` : 
                        `${item.type}_${item.id}`;
                    
                    delete itemPositions[positionKey];
                }
            }
            
            function makeItemInteractive(element) {
                // Handle item selection
                element.addEventListener('mousedown', function(e) {
                    // If we're clicking on a handle, don't treat as a selection
                    if (e.target.classList.contains('resize-handle') || 
                        e.target.classList.contains('rotate-handle')) {
                        return;
                    }
                    
                    // Select this item
                    selectItem(element);
                    
                    // Prepare for dragging if we're not in resize or rotate mode
                    if (!isResizing && !isRotating) {
                        startDragging(e, element);
                    }
                });
            }
            
            function updateTotalPrice() {
                totalPrice = selectedItems.reduce((sum, item) => sum + item.price, 0);
                document.getElementById('total-price').textContent = `$${totalPrice.toFixed(2)}`;
                const previewTotalPriceElement = document.getElementById('preview-total-price');
                if (previewTotalPriceElement) {
                    previewTotalPriceElement.textContent = `$${totalPrice.toFixed(2)}`;
                }
            }
            
            function capitalizeFirstLetter(string) {
                return string.charAt(0).toUpperCase() + string.slice(1);
            }

            // The consolidated and corrected updateSelectedItemsList function.
            // This function now incorporates the table display logic from the previously enhanced function.
            function updateSelectedItemsList() {
                const tableBody = document.querySelector('#selected-items-table tbody');
                const totalSpan = document.getElementById('selected-items-total');
                if (!tableBody || !totalSpan) return;

                if (!window.selectedItems || selectedItems.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="5" style="text-align:center;">No items selected yet.</td></tr>';
                    totalSpan.textContent = '0.00';
                    return;
                }

                // Group items by id+type and count quantities
                const itemMap = {};
                selectedItems.forEach(item => {
                    const key = `${item.type}_${item.id}`;
                    if (!itemMap[key]) {
                        itemMap[key] = { ...item, quantity: 1 };
                    } else {
                        itemMap[key].quantity++;
                    }
                });

                let total = 0;
                let rows = '';
                Object.values(itemMap).forEach(item => {
                    const subtotal = item.price * item.quantity;
                    total += subtotal;
                    rows += `<tr>
                        <td>${item.name}</td>
                        <td>${item.type.charAt(0).toUpperCase() + item.type.slice(1)}</td>
                        <td style="text-align:center;">${item.quantity}</td>
                        <td style="text-align:right;">₱${item.price.toFixed(2)}</td>
                        <td style="text-align:right;">₱${subtotal.toFixed(2)}</td>
                    </tr>`;
                });

                tableBody.innerHTML = rows;
                totalSpan.textContent = total.toFixed(2);
            }

            function resetCustomization() {
                // Unselect any selected item
                unselectAllItems();
                
                // Clear selected items
                selectedItems = [];
                itemPositions = {};
                totalPrice = 0;
                highestZIndex = 1;
                
                // Reset item quantities
                for (const key in itemQuantities) {
                    itemQuantities[key] = 0;
                }
                
                // Reset quantity displays in the grid
                document.querySelectorAll('.quantity-value').forEach(el => {
                    el.textContent = '0';
                });
                
                // Disable all minus buttons
                document.querySelectorAll('.minus-btn').forEach(btn => {
                    btn.disabled = true;
                });
                
                // Reset tool states
                rotateBtn.classList.remove('active');
                resizeBtn.classList.remove('active');
                rotationControl.style.display = 'none';
                
                // Reset wrapper type
                document.querySelector('input[name="wrapper-type"][value="satin"]').checked = true;
                currentWrapperType = 'satin';
                bouquetWrapper.className = 'bouquet-wrapper wrapper-type-satin';
                
                // Clear UI
                document.querySelectorAll('.item.selected').forEach(item => {
                    item.classList.remove('selected');
                });
                
                // Remove all items from preview canvas (except the bouquet structure)
                document.querySelectorAll('.preview-item').forEach(item => {
                    item.remove();
                });
                
                updateTotalPrice();
                updateSelectedItemsList();
                customerMessage.value = '';
            }
            
            // Event listeners
            resetBtn.addEventListener('click', resetCustomization);
            
            customizationForm.addEventListener('submit', function(e) {
                e.preventDefault(); // Always prevent default form submission
                
                if (selectedItems.length === 0) {
                    alert('Please select at least one item for your bouquet.');
                    return;
                }
                
                // If user is not logged in, show login modal
                <?php if (!isset($_SESSION['user_id'])): ?>
                document.getElementById('loginModal').style.display = 'flex';
                return;
                <?php endif; ?>
                
                // Ensure we unselect any selected item to clean up the data
                unselectAllItems();
                
                // Prepare form data
                const formData = new FormData(customizationForm);
                formData.set('selected_items', JSON.stringify(selectedItems));
                formData.set('item_positions', JSON.stringify(itemPositions));
                formData.set('total_price', totalPrice);
                formData.set('customer_message', customerMessage.value);
                formData.set('wrapper_type', currentWrapperType);
                formData.set('item_quantities', JSON.stringify(itemQuantities));
                
                // Change button text to indicate loading
                const addToCartBtn = document.getElementById('add-to-cart-btn');
                const originalBtnText = addToCartBtn.innerHTML;
                addToCartBtn.innerHTML = 'Adding to Cart...';
                addToCartBtn.disabled = true;
                
                // Submit form via AJAX
                fetch('save_custom_bouquet.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Success - redirect to cart
                        alert('Custom bouquet added to cart successfully!');
                        window.location.href = 'cart.php';
                    } else {
                        // Error
                        alert('Error: ' + (data.message || 'Could not add to cart. Please try again.'));
                        addToCartBtn.innerHTML = originalBtnText;
                        addToCartBtn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                    addToCartBtn.innerHTML = originalBtnText;
                    addToCartBtn.disabled = false;
                });
            });
            
            customerMessage.addEventListener('input', function() {
                customerMessageInput.value = this.value;
            });
            
            // Helper function to get color hex codes
            function getColorHex(colorName) {
                const colorMap = {
                    'red': 'ff0000',
                    'pink': 'ff69b4',
                    'white': 'ffffff',
                    'black': '000000',
                    'blue': '0000ff',
                    'lavender': 'e6e6fa',
                    'gold': 'ffd700',
                    'silver': 'c0c0c0',
                    'green': '008000',
                    'purple': '800080',
                    'yellow': 'ffff00',
                    'orange': 'ffa500',
                    'brown': 'a52a2a',
                    'navy': '000080'
                };
                
                return colorMap[colorName] || 'cccccc'; // Default to gray if color not found
            }
            
            function selectItem(element) {
                // Unselect all items first
                unselectAllItems();
                
                // Update current selected item
                currentSelectedItem = element;
                element.classList.add('selected-item');
                
                // Add manipulation handles
                addManipulationHandles(element);
                
                // Bring the element to front for active editing
                element.style.zIndex = highestZIndex + 1;
                
                // Enable tools for this item
                enableToolsForSelectedItem();
                
                // Update rotation slider
                const itemKey = `${element.dataset.type}_${element.dataset.id}`;
                const itemData = itemPositions[itemKey];
                if (itemData) {
                    rotationSlider.value = itemData.rotation;
                    rotationValue.textContent = `${itemData.rotation}°`;
                }
            }
            
            function unselectAllItems() {
                // Clear any previous selection
                document.querySelectorAll('.preview-item').forEach(item => {
                    item.classList.remove('selected-item');
                    
                    // Remove handles
                    const handles = item.querySelectorAll('.resize-handle, .rotate-handle');
                    handles.forEach(handle => handle.remove());
                });
                
                // Reset the selected item state
                currentSelectedItem = null;
                
                // Hide tools that require selection
                disableToolsWhenNoSelection();
                
                // Hide rotation control
                rotationControl.style.display = 'none';
            }
            
            function addManipulationHandles(element) {
                // Add resize handles to the corners
                const resizeHandles = ['nw', 'ne', 'sw', 'se'];
                resizeHandles.forEach(position => {
                    const handle = document.createElement('div');
                    handle.classList.add('resize-handle', position);
                    element.appendChild(handle);
                    
                    // Add resize event listeners
                    handle.addEventListener('mousedown', function(e) {
                        e.stopPropagation();
                        isResizing = true;
                        currentResizeHandle = position;
                        startResizing(e, element, position);
                    });
                });
                
                // Add rotate handle
                const rotateHandle = document.createElement('div');
                rotateHandle.classList.add('rotate-handle');
                rotateHandle.style.top = '-20px';
                rotateHandle.style.left = '50%';
                rotateHandle.style.transform = 'translateX(-50%)';
                element.appendChild(rotateHandle);
                
                // Add rotation event listener
                rotateHandle.addEventListener('mousedown', function(e) {
                    e.stopPropagation();
                    isRotating = true;
                    startRotating(e, element);
                });
            }
            
            function startDragging(e, element) {
                e.preventDefault();
                
                // Get initial mouse position
                const startX = e.clientX;
                const startY = e.clientY;
                
                // Get initial element position
                const startLeft = parseInt(element.style.left || 0);
                const startTop = parseInt(element.style.top || 0);
                
                // Create mousemove and mouseup handlers
                function dragMove(e) {
                    if (isResizing || isRotating) return;
                    
                    const newLeft = startLeft + (e.clientX - startX);
                    const newTop = startTop + (e.clientY - startY);
                    
                    // Keep the element within bounds
                    const maxX = previewCanvas.offsetWidth - element.offsetWidth;
                    const maxY = previewCanvas.offsetHeight - element.offsetHeight;
                    
                    element.style.left = `${Math.max(0, Math.min(maxX, newLeft))}px`;
                    element.style.top = `${Math.max(0, Math.min(maxY, newTop))}px`;
                    
                    // Update position in state
                    const id = element.dataset.id;
                    const type = element.dataset.type;
                    const key = `${type}_${id}`;
                    
                    if (itemPositions[key]) {
                        itemPositions[key].x = parseInt(element.style.left);
                        itemPositions[key].y = parseInt(element.style.top);
                    }
                }
                
                function dragEnd() {
                    document.removeEventListener('mousemove', dragMove);
                    document.removeEventListener('mouseup', dragEnd);
                }
                
                document.addEventListener('mousemove', dragMove);
                document.addEventListener('mouseup', dragEnd);
            }
            
            function startResizing(e, element, handlePosition) {
                e.preventDefault();
                
                // Get initial mouse position
                const startX = e.clientX;
                const startY = e.clientY;
                
                // Get initial element size and position
                const startWidth = element.offsetWidth;
                const startHeight = element.offsetHeight;
                const startLeft = parseInt(element.style.left);
                const startTop = parseInt(element.style.top);
                
                // Create mousemove and mouseup handlers
                function resizeMove(e) {
                    if (!isResizing) return;
                    
                    const dx = e.clientX - startX;
                    const dy = e.clientY - startY;
                    
                    let newWidth, newHeight, newLeft, newTop;
                    
                    // Calculate new dimensions based on which handle is being dragged
                    switch (handlePosition) {
                        case 'se':
                            newWidth = startWidth + dx;
                            newHeight = startHeight + dy;
                            newLeft = startLeft;
                            newTop = startTop;
                            break;
                        case 'sw':
                            newWidth = startWidth - dx;
                            newHeight = startHeight + dy;
                            newLeft = startLeft + dx;
                            newTop = startTop;
                            break;
                        case 'ne':
                            newWidth = startWidth + dx;
                            newHeight = startHeight - dy;
                            newLeft = startLeft;
                            newTop = startTop + dy;
                            break;
                        case 'nw':
                            newWidth = startWidth - dx;
                            newHeight = startHeight - dy;
                            newLeft = startLeft + dx;
                            newTop = startTop + dy;
                            break;
                    }
                    
                    // Set minimum size
                    const minSize = 20;
                    newWidth = Math.max(minSize, newWidth);
                    newHeight = Math.max(minSize, newHeight);
                    
                    // Apply new dimensions and position
                    element.style.width = `${newWidth}px`;
                    element.style.height = `${newHeight}px`;
                    element.style.left = `${newLeft}px`;
                    element.style.top = `${newTop}px`;
                    
                    // Keep within bounds
                    const maxX = previewCanvas.offsetWidth - newWidth;
                    const maxY = previewCanvas.offsetHeight - newHeight;
                    
                    element.style.left = `${Math.max(0, Math.min(maxX, newLeft))}px`;
                    element.style.top = `${Math.max(0, Math.min(maxY, newTop))}px`;
                    
                    // Update state
                    const id = element.dataset.id;
                    const type = element.dataset.type;
                    const key = `${type}_${id}`;
                    
                    if (itemPositions[key]) {
                        itemPositions[key].width = newWidth;
                        itemPositions[key].height = newHeight;
                        itemPositions[key].x = parseInt(element.style.left);
                        itemPositions[key].y = parseInt(element.style.top);
                    }
                }
                
                function resizeEnd() {
                    isResizing = false;
                    currentResizeHandle = null;
                    document.removeEventListener('mousemove', resizeMove);
                    document.removeEventListener('mouseup', resizeEnd);
                }
                
                document.addEventListener('mousemove', resizeMove);
                document.addEventListener('mouseup', resizeEnd);
            }
            
            function startRotating(e, element) {
                e.preventDefault();
                
                // Get element center
                const rect = element.getBoundingClientRect();
                const centerX = rect.left + rect.width / 2;
                const centerY = rect.top + rect.height / 2;
                
                // Get initial angle
                const startAngle = Math.atan2(e.clientY - centerY, e.clientX - centerX) * (180 / Math.PI);
                
                // Get current rotation
                const currentRotation = getCurrentRotation(element);
                
                function rotateMove(e) {
                    if (!isRotating) return;
                    
                    // Calculate new angle
                    const newAngle = Math.atan2(e.clientY - centerY, e.clientX - centerX) * (180 / Math.PI);
                    const angleDiff = newAngle - startAngle;
                    
                    // Apply rotation
                    const newRotation = (currentRotation + angleDiff) % 360;
                    if (newRotation < 0) newRotation += 360;
                    
                    element.style.transform = `rotate(${newRotation}deg)`;
                    
                    // Update state and slider
                    const id = element.dataset.id;
                    const type = element.dataset.type;
                    const key = `${type}_${id}`;
                    
                    if (itemPositions[key]) {
                        itemPositions[key].rotation = Math.round(newRotation);
                        rotationSlider.value = Math.round(newRotation);
                        rotationValue.textContent = `${Math.round(newRotation)}°`;
                    }
                }
                
                function rotateEnd() {
                    isRotating = false;
                    document.removeEventListener('mousemove', rotateMove);
                    document.removeEventListener('mouseup', rotateEnd);
                }
                
                document.addEventListener('mousemove', rotateMove);
                document.addEventListener('mouseup', rotateEnd);
            }
            
            function getCurrentRotation(element) {
                const style = window.getComputedStyle(element);
                const matrix = new DOMMatrix(style.transform);
                
                // Get the rotation from the transform matrix
                return Math.round(Math.atan2(matrix.b, matrix.a) * (180 / Math.PI));
            }
            
            function moveLayerUp() {
                if (!currentSelectedItem) return;
                
                const currentZ = parseInt(currentSelectedItem.style.zIndex);
                currentSelectedItem.style.zIndex = currentZ + 1;
                highestZIndex = Math.max(highestZIndex, currentZ + 1);
                
                // Update state
                const id = currentSelectedItem.dataset.id;
                const type = currentSelectedItem.dataset.type;
                const key = `${type}_${id}`;
                
                if (itemPositions[key]) {
                    itemPositions[key].zIndex = currentZ + 1;
                }
            }
            
            function moveLayerDown() {
                if (!currentSelectedItem) return;
                
                const currentZ = parseInt(currentSelectedItem.style.zIndex);
                if (currentZ > 1) {
                    currentSelectedItem.style.zIndex = currentZ - 1;
                    
                    // Update state
                    const id = currentSelectedItem.dataset.id;
                    const type = currentSelectedItem.dataset.type;
                    const key = `${type}_${id}`;
                    
                    if (itemPositions[key]) {
                        itemPositions[key].zIndex = currentZ - 1;
                    }
                }
            }
            
            function deleteSelectedItem() {
                if (!currentSelectedItem) return;
                
                const id = currentSelectedItem.dataset.id;
                const type = currentSelectedItem.dataset.type;
                const instanceId = currentSelectedItem.dataset.instanceId;
                
                // Find the corresponding item in selectedItems
                const index = selectedItems.findIndex(item => {
                    if (instanceId) {
                        return item.id === id && item.type === type && item.instanceId == instanceId;
                    }
                    return item.id === id && item.type === type;
                });
                
                if (index !== -1) {
                    // Update the item quantity
                    const itemKey = `${type}_${id}`;
                    if (itemQuantities[itemKey] > 0) {
                        itemQuantities[itemKey]--;
                        
                        // Update quantity display in the grid
                        const gridItem = document.querySelector(`.item[data-id="${id}"][data-type="${type}"]`);
                        if (gridItem) {
                            const quantityDisplay = gridItem.querySelector('.quantity-value');
                            const minusBtn = gridItem.querySelector('.minus-btn');
                            
                            if (quantityDisplay) {
                                quantityDisplay.textContent = itemQuantities[itemKey];
                            }
                            
                            if (minusBtn) {
                                minusBtn.disabled = itemQuantities[itemKey] === 0;
                            }
                        }
                    }
                    
                    // Remove from selectedItems array
                    const removedItem = selectedItems.splice(index, 1)[0];
                    
                    // Remove from preview
                    removeItemFromPreview(removedItem);
                    
                    // Update total price
                    updateTotalPrice();
                    updateSelectedItemsList();
                }
            }
            
            function enableToolsForSelectedItem() {
                moveUpBtn.disabled = false;
                moveDownBtn.disabled = false;
                rotateBtn.disabled = false;
                resizeBtn.disabled = false;
                deleteBtn.disabled = false;
            }
            
            function disableToolsWhenNoSelection() {
                moveUpBtn.disabled = true;
                moveDownBtn.disabled = true;
                rotateBtn.disabled = true;
                resizeBtn.disabled = true;
                deleteBtn.disabled = true;
                
                // Reset any active tool states
                rotateBtn.classList.remove('active');
                resizeBtn.classList.remove('active');
            }
            
            // Initialize tool button event listeners
            moveUpBtn.addEventListener('click', moveLayerUp);
            moveDownBtn.addEventListener('click', moveLayerDown);
            deleteBtn.addEventListener('click', deleteSelectedItem);
            
            rotateBtn.addEventListener('click', function() {
                if (!currentSelectedItem) return;
                
                // Toggle rotation mode
                rotateBtn.classList.toggle('active');
                
                if (rotateBtn.classList.contains('active')) {
                    // Show rotation controls
                    rotationControl.style.display = 'flex';
                    
                    // Hide resize handles during rotation
                    currentSelectedItem.querySelectorAll('.resize-handle').forEach(handle => {
                        handle.style.display = 'none';
                    });
                } else {
                    // Hide rotation controls
                    rotationControl.style.display = 'none';
                    
                    // Show resize handles again
                    currentSelectedItem.querySelectorAll('.resize-handle').forEach(handle => {
                        handle.style.display = 'block';
                    });
                }
            });
            
            resizeBtn.addEventListener('click', function() {
                if (!currentSelectedItem) return;
                
                // Toggle resize mode
                resizeBtn.classList.toggle('active');
                
                // Show/hide resize handles
                currentSelectedItem.querySelectorAll('.resize-handle').forEach(handle => {
                    handle.style.display = resizeBtn.classList.contains('active') ? 'block' : 'none';
                });
                
                // Hide rotation controls during resize
                if (resizeBtn.classList.contains('active')) {
                    rotationControl.style.display = 'none';
                    rotateBtn.classList.remove('active');
                }
            });
            
            // Handle rotation slider changes
            rotationSlider.addEventListener('input', function() {
                if (!currentSelectedItem) return;
                
                const rotation = parseInt(this.value);
                rotationValue.textContent = `${rotation}°`;
                
                // Apply rotation
                currentSelectedItem.style.transform = `rotate(${rotation}deg)`;
                
                // Update state
                const id = currentSelectedItem.dataset.id;
                const type = currentSelectedItem.dataset.type;
                const key = `${type}_${id}`;
                
                if (itemPositions[key]) {
                    itemPositions[key].rotation = rotation;
                }
            });
            
            // Click outside to deselect
            previewCanvas.addEventListener('click', function(e) {
                // Only if we clicked directly on the canvas (not on an item or handle)
                if (e.target === previewCanvas) {
                    unselectAllItems();
                }
            });
            
            // Add touch support for mobile devices
            function addTouchSupport() {
                // Convert touch events to mouse events for dragging
                previewCanvas.addEventListener('touchstart', function(e) {
                    // Only if touching an item
                    if (e.target.closest('.preview-item')) {
                        const touch = e.touches[0];
                        const target = e.target.closest('.preview-item');
                        
                        // Create a simulated mouse event
                        const mouseEvent = new MouseEvent('mousedown', {
                            clientX: touch.clientX,
                            clientY: touch.clientY,
                            bubbles: true,
                            cancelable: true,
                            view: window
                        });
                        
                        // Dispatch the event on the target
                        target.dispatchEvent(mouseEvent);
                        
                        // Prevent scrolling
                        e.preventDefault();
                    }
                }, { passive: false });
                
                document.addEventListener('touchmove', function(e) {
                    if (currentSelectedItem) {
                        const touch = e.touches[0];
                        
                        // Create a simulated mouse event
                        const mouseEvent = new MouseEvent('mousemove', {
                            clientX: touch.clientX,
                            clientY: touch.clientY,
                            bubbles: true,
                            cancelable: true,
                            view: window
                        });
                        
                        // Dispatch the event
                        document.dispatchEvent(mouseEvent);
                        
                        // Prevent scrolling
                        e.preventDefault();
                    }
                }, { passive: false });
                
                document.addEventListener('touchend', function(e) {
                    if (currentSelectedItem) {
                        // Create a simulated mouse event
                        const mouseEvent = new MouseEvent('mouseup', {
                            bubbles: true,
                            cancelable: true,
                            view: window
                        });
                        
                        // Dispatch the event
                        document.dispatchEvent(mouseEvent);
                    }
                });
                
                // Handle touch events for resize handles
                previewCanvas.addEventListener('touchstart', function(e) {
                    if (e.target.classList.contains('resize-handle')) {
                        const touch = e.touches[0];
                        const target = e.target;
                        
                        // Create a simulated mouse event
                        const mouseEvent = new MouseEvent('mousedown', {
                            clientX: touch.clientX,
                            clientY: touch.clientY,
                            bubbles: true,
                            cancelable: true,
                            view: window
                        });
                        
                        // Dispatch the event on the target
                        target.dispatchEvent(mouseEvent);
                        
                        // Prevent scrolling
                        e.preventDefault();
                    }
                }, { passive: false });
                
                // Handle touch events for rotate handle
                previewCanvas.addEventListener('touchstart', function(e) {
                    if (e.target.classList.contains('rotate-handle')) {
                        const touch = e.touches[0];
                        const target = e.target;
                        
                        // Create a simulated mouse event
                        const mouseEvent = new MouseEvent('mousedown', {
                            clientX: touch.clientX,
                            clientY: touch.clientY,
                            bubbles: true,
                            cancelable: true,
                            view: window
                        });
                        
                        // Dispatch the event on the target
                        target.dispatchEvent(mouseEvent);
                        
                        // Prevent scrolling
                        e.preventDefault();
                    }
                }, { passive: false });
            }
            
            // Handler for wrapper type change
            wrapperTypeInputs.forEach(input => {
                input.addEventListener('change', function() {
                    if (this.checked) {
                        // Update current wrapper type
                        currentWrapperType = this.value;
                        
                        // Update wrapper appearance
                        bouquetWrapper.className = `bouquet-wrapper wrapper-type-${currentWrapperType}`;
                    }
                });
            });
            
            // Initialize the customizer
            function initCustomizer() {
                // Set initial wrapper type
                wrapperTypeInput.value = currentWrapperType;
                itemQuantitiesInput.value = JSON.stringify(itemQuantities);
                
                // Initialize step navigation
                initStepNavigation();
                
                // Disable tools when no selection
                disableToolsWhenNoSelection();
                
                // Add touch support for mobile devices
                addTouchSupport();
                
                // Create initial bouquet structure
                updateBouquetStructure();
            }
            
            // Initialize the customizer when the page loads
            initCustomizer();
            
            function updateBouquetStructure() {
                // Clear any existing structure elements
                bouquetWrapper.innerHTML = '';
                
                // Update wrapper appearance based on selected type
                bouquetWrapper.className = `bouquet-wrapper wrapper-type-${currentWrapperType}`;
                
                // Add wrapper.png as background image with full coverage
                bouquetWrapper.style.backgroundImage = 'url("images/wrapper.png")';
                bouquetWrapper.style.backgroundSize = 'contain';
                bouquetWrapper.style.backgroundRepeat = 'no-repeat';
                
                // Add stem
                const stem = document.createElement('div');
                stem.className = 'bouquet-stem';
                bouquetWrapper.appendChild(stem);
                
                // Add ribbon if step 3 is completed
                if (stepsCompleted[3]) {
                    // Get selected ribbon color
                    const selectedRibbon = selectedItems.find(item => item.type === 'ribbon');
                    if (selectedRibbon) {
                        const ribbon = document.createElement('div');
                        ribbon.className = 'bouquet-ribbon';
                        
                        // Use ribbon.png with proper sizing to ensure full coverage
                        ribbon.style.backgroundImage = 'url("images/ribbon.png")';
                        ribbon.style.backgroundSize = 'contain';
                        ribbon.style.backgroundPosition = 'center';
                        ribbon.style.backgroundRepeat = 'no-repeat';
                        ribbon.style.width = '120%'; // Make ribbon wider than the container
                        ribbon.style.left = '-10%'; // Center the wider ribbon
                        
                        // Apply color as a filter or overlay
                        const ribbonColor = getColorHex(selectedRibbon.name.toLowerCase());
                        ribbon.style.backgroundColor = `#${ribbonColor}`;
                        ribbon.style.mixBlendMode = 'multiply';
                        
                        bouquetWrapper.appendChild(ribbon);
                    }
                }
                
                // Add a visual representation of the bouquet structure based on step
                switch(currentStep) {
                    case 1:
                        // Basic structure for flower selection
                        bouquetWrapper.style.opacity = '0.6';
                        break;
                    case 2:
                        // More defined structure for wrapper selection
                        bouquetWrapper.style.opacity = '0.8';
                        break;
                    case 3:
                        // Add ribbon stage
                        bouquetWrapper.style.opacity = '0.9';
                        break;
                    case 4:
                        // Final stage with all elements
                        bouquetWrapper.style.opacity = '1';
                        break;
                }
            }
            
            // Add dynamic CSS styles once when the page loads
            document.addEventListener('DOMContentLoaded', function() {
                // Add CSS for enhanced bouquet wrapper
                const style = document.createElement('style');
                style.textContent = `
                    .bouquet-wrapper {
                        position: relative;
                        width: 80%;
                        height: 80%;
                        border-radius: 50% 50% 10px 10px;
                        z-index: 0;
                        transition: all 0.3s ease;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        overflow: hidden;
                        background-color: transparent;
                        box-shadow: 0 5px 15px rgba(0,0,0,0.15);
                        background-size: contain !important;
                        background-position: center !important;
                        background-repeat: no-repeat !important;
                    }
                    
                    .bouquet-ribbon {
                        position: absolute;
                        bottom: 20%;
                        width: 100%;
                        height: 30px;
                        z-index: 2;
                        transform: rotate(-5deg);
                        background-size: contain !important;
                        background-position: center !important;
                        background-repeat: no-repeat !important;
                    }
                    
                    .preview-wrapper {
                        position: absolute;
                        width: 260px;
                        height: 280px;
                        bottom: 0;
                        left: 50%;
                        transform: translateX(-50%);
                        z-index: 1;
                        background-size: contain !important;
                        background-position: center !important;
                        background-repeat: no-repeat !important;
                    }
                    
                    .preview-ribbon {
                        position: absolute;
                        width: 100%;
                        height: 35px;
                        bottom: 80px;
                        left: 0;
                        z-index: 3;
                        transform: rotate(-8deg);
                        background-size: contain !important;
                        background-position: center !important;
                        background-repeat: no-repeat !important;
                    }
                `;
                document.head.appendChild(style);
            });
            
            // Create a preview of the finished bouquet
            function createBouquetPreview() {
                const previewBouquetDiv = document.getElementById('previewBouquet');
                previewBouquetDiv.innerHTML = ''; // Clear previous content

                // Create a new Fabric.js canvas for the preview
                const previewCanvas = new fabric.Canvas(document.createElement('canvas'), {
                    width: 300,
                    height: 380,
                    backgroundColor: 'transparent',
                    selection: false
                });
                previewBouquetDiv.appendChild(previewCanvas.getElement());

                // Clone and add items from the main canvas to the preview canvas
                canvas.getObjects().forEach(obj => {
                    if (obj.id !== 'wrapper' && obj.id !== 'ribbon') {
                        obj.clone(function(clonedObj) {
                            previewCanvas.add(clonedObj);
                            previewCanvas.renderAll();
                        });
                    }
                });

                // Add wrapper and ribbon if they exist on the main canvas
                const mainWrapper = canvas.getObjects().find(obj => obj.id === 'wrapper');
                if (mainWrapper) {
                    mainWrapper.clone(function(clonedWrapper) {
                        previewCanvas.add(clonedWrapper);
                        previewCanvas.sendToBack(clonedWrapper); // Ensure wrapper is at the back
                        previewCanvas.renderAll();
                    });
                }

                const mainRibbon = canvas.getObjects().find(obj => obj.id === 'ribbon');
                if (mainRibbon) {
                    mainRibbon.clone(function(clonedRibbon) {
                        previewCanvas.add(clonedRibbon);
                        previewCanvas.bringToFront(clonedRibbon); // Ensure ribbon is in front of wrapper
                        previewCanvas.renderAll();
                    });
                }

                // Populate selected items and total price in the preview modal
                const selectedItemsGrid = document.getElementById('preview-selected-items-grid'); // Corrected ID
                selectedItemsGrid.innerHTML = '';

                if (selectedItems.length === 0) {
                    selectedItemsGrid.innerHTML = '<p>No items selected.</p>';
                } else {
                    selectedItems.forEach(item => {
                        const itemElement = document.createElement('div');
                        itemElement.classList.add('bouquet-preview-item');
                        itemElement.innerHTML = `
                            <span>${capitalizeFirstLetter(item.type)}: ${item.name}</span>
                            <span>$${item.price.toFixed(2)}</span>
                        `;
                        selectedItemsGrid.appendChild(itemElement);
                    });
                }

                // Total price in preview
                document.getElementById('preview-total-price').textContent = `$${totalPrice.toFixed(2)}`;
            }
            
            // Preview bouquet button event listener
            previewBouquetBtn.addEventListener('click', createBouquetPreview);
            
            // Wrapper type change handler
            wrapperTypeInputs.forEach(input => {
                input.addEventListener('change', function() {
                    if (this.checked) {
                        // Update current wrapper type
                        currentWrapperType = this.value;
                        wrapperTypeInput.value = currentWrapperType;
                        
                        // Update bouquet structure
                        updateBouquetStructure();
                    }
                });
            });
            
            // Initialize the customizer
            function initCustomizer() {
                // Set initial wrapper type
                wrapperTypeInput.value = currentWrapperType;
                itemQuantitiesInput.value = JSON.stringify(itemQuantities);
                
                // Initialize step navigation
                initStepNavigation();
                
                // Disable tools when no selection
                disableToolsWhenNoSelection();
                
                // Add touch support for mobile devices
                addTouchSupport();
                
                // Create initial bouquet structure
                updateBouquetStructure();
            }
            
            // Initialize the customizer when the page loads
            initCustomizer();
            
            // Event listeners for bouquet preview modal
            document.getElementById('previewCloseBtn').addEventListener('click', function() {
                document.getElementById('bouquetPreviewModal').style.display = 'none';
            });
            
            document.getElementById('continueEditingBtn').addEventListener('click', function() {
                document.getElementById('bouquetPreviewModal').style.display = 'none';
            });
            
            document.getElementById('previewAddToCartBtn').addEventListener('click', function() {
                // Submit the form
                if (addToCartBtn) {
                    addToCartBtn.click();
                }
            });
            
            // Close modal if user clicks outside the content
            window.addEventListener('click', function(event) {
                const modal = document.getElementById('bouquetPreviewModal');
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });

            // Enhanced selected items list rendering
            function updateSelectedItemsListEnhanced() {
                const container = document.getElementById('selected-items-list-enhanced');
                if (!container) return;
                if (selectedItems.length === 0) {
                    container.innerHTML = '<p>No items selected yet.</p>';
                    document.getElementById('selected-items-total').textContent = '0.00';
                    return;
                }
                // Group items by type and id, count quantities
                const itemMap = {};
                selectedItems.forEach(item => {
                    const key = `${item.type}_${item.id}`;
                    if (!itemMap[key]) {
                        itemMap[key] = { ...item, quantity: 1 };
                    } else {
                        itemMap[key].quantity++;
                    }
                });
                // Build table
                let html = '<table style="width:100%; border-collapse:collapse;">';
                html += '<thead><tr style="background:#f8f8f8;"><th style="text-align:left;padding:6px;">Item</th><th style="text-align:left;padding:6px;">Type</th><th style="text-align:center;padding:6px;">Qty</th><th style="text-align:right;padding:6px;">Price</th><th style="text-align:right;padding:6px;">Subtotal</th></tr></thead><tbody>';
                let total = 0;
                Object.values(itemMap).forEach(item => {
                    const subtotal = item.price * item.quantity;
                    total += subtotal;
                    html += `<tr>
                        <td style="padding:6px;">${item.name}</td>
                        <td style="padding:6px;">${capitalizeFirstLetter(item.type)}</td>
                        <td style="text-align:center;padding:6px;">${item.quantity}</td>
                        <td style="text-align:right;padding:6px;">₱${item.price.toFixed(2)}</td>
                        <td style="text-align:right;padding:6px;">₱${subtotal.toFixed(2)}</td>
                    </tr>`;
                });
                html += '</tbody></table>';
                container.innerHTML = html;
                document.getElementById('selected-items-total').textContent = total.toFixed(2);
            }

            updateSelectedItemsListEnhanced(); // Ensure selected items and total are displayed on load
        });
    </script>
</body>
</html> 