<?php
/**
 * Bouquet Preview Component
 * 
 * This component displays a realistic bouquet preview with:
 * - Folded wrapper
 * - Multiple flowers
 * - Ribbon tied around the wrapper
 * - Add-ons
 */

// Database connection required for fetching item data
include_once 'db_connection.php';

/**
 * Renders a bouquet preview based on selected items
 * 
 * @param array $selectedItems Array of selected items with their details
 * @param string $wrapperType The type of wrapper (satin, kraft, tissue, burlap)
 * @param string $ribbonColor The color of the ribbon
 * @param array $flowerItems Array of flower items
 * @param array $addonItems Array of addon items
 * @return string HTML output for the bouquet preview
 */
function renderBouquetPreview($selectedItems = [], $wrapperType = 'satin', $ribbonColor = 'pink', $flowerItems = [], $addonItems = []) {
    // Define wrapper colors based on type
    $wrapperColors = [
        'satin' => '#fff0f5',
        'kraft' => '#d2b48c',
        'tissue' => '#f0ffff',
        'burlap' => '#deb887'
    ];
    
    // Get wrapper color or use default if not found
    $wrapperColor = isset($wrapperColors[$wrapperType]) ? $wrapperColors[$wrapperType] : '#fff0f5';
    
    // Organize selected items by type
    $selectedFlowers = array_filter($selectedItems, function($item) {
        return $item['type'] === 'flower';
    });
    
    $selectedRibbon = current(array_filter($selectedItems, function($item) {
        return $item['type'] === 'ribbon';
    }));
    
    $selectedAddons = array_filter($selectedItems, function($item) {
        return $item['type'] === 'addon';
    });
    
    // Generate HTML for the preview
    $html = '<div class="bouquet-preview-container">';
    
    // Add styles specific to this preview
    $html .= '<style>
        .bouquet-preview-container {
            position: relative;
            width: 300px;
            height: 400px;
            margin: 0 auto;
        }
        
        .preview-stem {
            position: absolute;
            width: 20px;
            height: 100px;
            background-color: #3a7d44;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1;
            border-radius: 0 0 5px 5px;
        }
        
        .preview-wrapper {
            position: absolute;
            width: 260px;
            height: 280px;
            bottom: 45px;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 130px 130px 5px 5px;
            z-index: 2;
            background-color: ' . $wrapperColor . ';
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
            overflow: hidden;
        }
        
        .preview-wrapper-fold {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 60%;
            background-color: ' . $wrapperColor . ';
            opacity: 0.85;
            z-index: 3;
        }
        
        .preview-wrapper-left-fold {
            position: absolute;
            top: 50%;
            left: 0;
            width: 50%;
            height: 50%;
            background-color: ' . $wrapperColor . ';
            transform-origin: right top;
            transform: rotate(-15deg);
            z-index: 4;
            clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%);
            opacity: 0.9;
        }
        
        .preview-wrapper-right-fold {
            position: absolute;
            top: 50%;
            right: 0;
            width: 50%;
            height: 50%;
            background-color: ' . $wrapperColor . ';
            transform-origin: left top;
            transform: rotate(15deg);
            z-index: 4;
            clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%);
            opacity: 0.9;
        }
        
        .preview-ribbon {
            position: absolute;
            width: 280px;
            height: 35px;
            bottom: 130px;
            left: 50%;
            transform: translateX(-50%);
            background-color: ' . getColorHex($ribbonColor) . ';
            z-index: 5;
            opacity: 0.9;
        }
        
        .preview-ribbon-knot {
            position: absolute;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: ' . getColorHex($ribbonColor) . ';
            bottom: 127px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 6;
            box-shadow: 0 3px 5px rgba(0,0,0,0.2);
        }
        
        .preview-ribbon-end {
            position: absolute;
            width: 20px;
            height: 70px;
            background-color: ' . getColorHex($ribbonColor) . ';
            bottom: 60px;
            z-index: 5;
        }
        
        .preview-ribbon-end.left {
            left: calc(50% - 30px);
            transform: rotate(-15deg);
            border-radius: 0 0 0 10px;
        }
        
        .preview-ribbon-end.right {
            right: calc(50% - 30px);
            transform: rotate(15deg);
            border-radius: 0 0 10px 0;
        }
        
        .preview-flowers-container {
            position: absolute;
            width: 220px;
            height: 170px;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 7;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-content: center;
        }
        
        .preview-flower {
            position: absolute;
            background-size: cover;
            background-position: center;
            border-radius: 50%;
            box-shadow: 0 3px 8px rgba(0,0,0,0.1);
        }
        
        .preview-addons-container {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 8;
        }
        
        .preview-addon {
            position: absolute;
            background-size: cover;
            background-position: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        /* Add texture and paper creases */
        .paper-texture {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0.1;
            background-image: url("data:image/svg+xml,%3Csvg width=\'100\' height=\'100\' viewBox=\'0 0 100 100\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cpath d=\'M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5z\' fill=\'%23000000\' fill-opacity=\'0.05\' fill-rule=\'evenodd\'/%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 9;
        }
        
        .preview-crease {
            position: absolute;
            background: linear-gradient(90deg, rgba(255,255,255,0.1) 0%, rgba(0,0,0,0.05) 50%, rgba(255,255,255,0.1) 100%);
            height: 1px;
            z-index: 10;
            transform: rotate(var(--angle, 0deg));
        }
    </style>';
    
    // Build the bouquet structure
    $html .= '
    <div class="preview-stem"></div>
    <div class="preview-wrapper">
        <div class="paper-texture"></div>
        <div class="preview-crease" style="width: 100px; top: 30%; left: 20%; --angle: 20deg;"></div>
        <div class="preview-crease" style="width: 120px; top: 60%; right: 10%; --angle: -25deg;"></div>
        <div class="preview-wrapper-fold"></div>
        <div class="preview-wrapper-left-fold"></div>
        <div class="preview-wrapper-right-fold"></div>
    </div>
    
    <div class="preview-ribbon"></div>
    <div class="preview-ribbon-knot"></div>
    <div class="preview-ribbon-end left"></div>
    <div class="preview-ribbon-end right"></div>
    
    <div class="preview-flowers-container">';
    
    // Add flowers
    if (!empty($selectedFlowers)) {
        $flowerPositions = [
            ['top' => '10%', 'left' => '20%', 'size' => 70, 'z-index' => 1],
            ['top' => '5%', 'left' => '50%', 'size' => 80, 'z-index' => 2],
            ['top' => '15%', 'left' => '75%', 'size' => 65, 'z-index' => 1],
            ['top' => '35%', 'left' => '30%', 'size' => 75, 'z-index' => 3],
            ['top' => '30%', 'left' => '60%', 'size' => 70, 'z-index' => 2],
            ['top' => '50%', 'left' => '45%', 'size' => 60, 'z-index' => 4],
            ['top' => '60%', 'left' => '25%', 'size' => 50, 'z-index' => 3],
            ['top' => '55%', 'left' => '70%', 'size' => 55, 'z-index' => 3]
        ];
        
        $flowerCount = min(count($selectedFlowers), count($flowerPositions));
        
        for ($i = 0; $i < $flowerCount; $i++) {
            $flower = $selectedFlowers[$i];
            $position = $flowerPositions[$i];
            
            // Get image path for this flower
            $imagePath = '';
            if (isset($flower['image_path']) && !empty($flower['image_path'])) {
                $imagePath = $flower['image_path'];
            } else {
                // Use a placeholder if image path not available
                $imagePath = "https://via.placeholder.com/100/ff69b4/ffffff?text=Flower";
            }
            
            $html .= '
            <div class="preview-flower" 
                 style="top: ' . $position['top'] . '; 
                        left: ' . $position['left'] . '; 
                        width: ' . $position['size'] . 'px; 
                        height: ' . $position['size'] . 'px; 
                        background-image: url(\'' . $imagePath . '\');
                        z-index: ' . $position['z-index'] . ';">
            </div>';
        }
    }
    
    $html .= '</div>';
    
    // Add add-ons
    if (!empty($selectedAddons)) {
        $html .= '<div class="preview-addons-container">';
        
        $addonPositions = [
            ['top' => '60%', 'right' => '15%', 'size' => 40, 'rotate' => '15deg'],
            ['top' => '65%', 'left' => '15%', 'size' => 35, 'rotate' => '-10deg'],
            ['bottom' => '20%', 'right' => '25%', 'size' => 45, 'rotate' => '5deg'],
            ['bottom' => '25%', 'left' => '20%', 'size' => 40, 'rotate' => '-5deg']
        ];
        
        $addonCount = min(count($selectedAddons), count($addonPositions));
        
        for ($i = 0; $i < $addonCount; $i++) {
            $addon = $selectedAddons[$i];
            $position = $addonPositions[$i];
            
            // Get image path for this addon
            $imagePath = '';
            if (isset($addon['image_path']) && !empty($addon['image_path'])) {
                $imagePath = $addon['image_path'];
            } else {
                // Use a placeholder if image path not available
                $imagePath = "https://via.placeholder.com/50/8b4513/ffffff?text=Addon";
            }
            
            $positionStyle = '';
            foreach ($position as $prop => $value) {
                if ($prop != 'size' && $prop != 'rotate') {
                    $positionStyle .= $prop . ': ' . $value . '; ';
                }
            }
            
            $html .= '
            <div class="preview-addon" 
                 style="' . $positionStyle . '
                        width: ' . $position['size'] . 'px; 
                        height: ' . $position['size'] . 'px; 
                        background-image: url(\'' . $imagePath . '\');
                        transform: rotate(' . $position['rotate'] . ');">
            </div>';
        }
        
        $html .= '</div>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Helper function to get color hex code from color name
 */
function getColorHex($colorName) {
    $colorMap = [
        'red' => '#ff0000',
        'pink' => '#ff69b4',
        'white' => '#ffffff',
        'black' => '#000000',
        'blue' => '#0000ff',
        'lavender' => '#e6e6fa',
        'gold' => '#ffd700',
        'silver' => '#c0c0c0',
        'green' => '#008000',
        'purple' => '#800080',
        'yellow' => '#ffff00',
        'orange' => '#ffa500',
        'brown' => '#a52a2a',
        'navy' => '#000080'
    ];
    
    return isset($colorMap[$colorName]) ? $colorMap[$colorName] : '#cccccc';
}
?> 