<?php
session_start();
include 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Check if this is a POST request with required data
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || 
    !isset($_POST['selected_items']) || 
    !isset($_POST['item_positions']) || 
    !isset($_POST['total_price'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// Get data from POST
$userId = $_SESSION['user_id'];
$selectedItems = json_decode($_POST['selected_items'], true);
$itemPositions = json_decode($_POST['item_positions'], true);
$totalPrice = floatval($_POST['total_price']);
$customerMessage = isset($_POST['customer_message']) ? $_POST['customer_message'] : null;
$wrapperType = isset($_POST['wrapper_type']) ? $_POST['wrapper_type'] : 'satin';

// Validate the data
if (!is_array($selectedItems) || empty($selectedItems) || !is_array($itemPositions) || $totalPrice <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid data provided']);
    exit;
}

// Further validate item positions for required properties
foreach ($selectedItems as $item) {
    $itemKey = isset($item['instanceId']) ? 
                "{$item['type']}_{$item['id']}_{$item['instanceId']}" : 
                "{$item['type']}_{$item['id']}";
                
    if (!isset($itemPositions[$itemKey])) {
        echo json_encode(['success' => false, 'message' => "Missing position data for item: {$item['name']}"]);
        exit;
    }
    
    $position = $itemPositions[$itemKey];
    // Check if all required properties exist
    $requiredProps = ['x', 'y', 'width', 'height', 'rotation', 'zIndex'];
    foreach ($requiredProps as $prop) {
        if (!isset($position[$prop])) {
            echo json_encode(['success' => false, 'message' => "Missing '{$prop}' property for item: {$item['name']}"]);
            exit;
        }
    }
}

// Begin transaction
$conn->begin_transaction();

try {
    // Insert into products table first to get product_id
    $productName = "Custom Bouquet";
    $productDescription = "Custom bouquet created using the bouquet customizer with " . ucfirst($wrapperType) . " wrapper";
    
    $insertProduct = $conn->prepare("INSERT INTO products (product_name, price, product_price, product_description, product_image, category_id) VALUES (?, ?, ?, ?, 'default.jpg', 8)");
    $insertProduct->bind_param("sdss", $productName, $totalPrice, $totalPrice, $productDescription);
    
    if (!$insertProduct->execute()) {
        throw new Exception("Error inserting product: " . $conn->error);
    }
    
    $productId = $conn->insert_id;
    
    // Store selected items and their positions in customized_products table
    $insertCustom = $conn->prepare("INSERT INTO customized_products (product_id, product_name, product_price, product_description, add_ons, message, category_id) VALUES (?, ?, ?, ?, ?, ?, 8)");
    
    // Enhanced JSON structure with full item details and positioning
    $customBouquetData = [
        'items' => $selectedItems,
        'positions' => $itemPositions,
        'wrapper_type' => $wrapperType,
        'canvas' => [
            'width' => 500, // Default canvas width
            'height' => 500, // Default canvas height
        ]
    ];
    
    $addOnsJson = json_encode($customBouquetData);
    
    $insertCustom->bind_param("isdsss", $productId, $productName, $totalPrice, $productDescription, $addOnsJson, $customerMessage);
    
    if (!$insertCustom->execute()) {
        throw new Exception("Error saving customized product: " . $conn->error);
    }
    
    $customizedId = $conn->insert_id;
    
    // Add to cart
    $insertCart = $conn->prepare("INSERT INTO cart (user_id, product_id, product_name, product_price, quantity, is_customized, customer_message, addons) VALUES (?, ?, ?, ?, 1, 1, ?, ?)");
    $insertCart->bind_param("iisdss", $userId, $productId, $productName, $totalPrice, $customerMessage, $addOnsJson);
    
    if (!$insertCart->execute()) {
        throw new Exception("Error adding to cart: " . $conn->error);
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Custom bouquet added to cart successfully',
        'product_id' => $productId,
        'customized_id' => $customizedId
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?> 