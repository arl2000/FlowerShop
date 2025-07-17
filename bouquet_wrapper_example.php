<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bouquet Wrapper Example</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 20px;
        }
        
        h1 {
            color: #333;
        }
        
        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            margin-top: 30px;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            padding: 15px;
            text-align: center;
            width: 330px;
        }
        
        .card h2 {
            margin-top: 10px;
            color: #444;
        }
        
        .card p {
            color: #666;
        }
        
        .price {
            font-weight: bold;
            font-size: 1.2em;
            margin: 10px 0;
            color: #2a9d8f;
        }
        
        .button {
            background-color: #2a9d8f;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 10px;
            transition: background-color 0.3s;
        }
        
        .button:hover {
            background-color: #218579;
        }
    </style>
</head>
<body>
    <h1>Bouquet Wrappers</h1>
    <p>Beautiful flower wrappers for your DIY flower arrangements</p>
    
    <div class="container">
        <div class="card">
            <?php include 'bouquet_wrapper_component.php'; ?>
            <h2>Classic White Wrapper</h2>
            <p>Elegant white paper wrapper perfect for any bouquet</p>
            <div class="price">$3.99</div>
            <button class="button">Add to Cart</button>
        </div>
        
        <div class="card">
            <div class="bouquet-wrapper-component">
                <style>
                    /* This demonstrates how you can customize the wrapper with different colors */
                    .custom-color .wrapper,
                    .custom-color .wrapper-fold,
                    .custom-color .stem-wrapper {
                        background-color: #f8efe6;
                    }
                </style>
                
                <!-- Include the wrapper with custom class -->
                <div class="wrapper-container custom-color">
                    <div class="wrapper"></div>
                    <div class="wrapper-fold fold-left"></div>
                    <div class="wrapper-fold fold-right"></div>
                    <div class="wrapper-fold fold-bottom"></div>
                    <div class="stem-wrapper"></div>
                    
                    <div class="paper-texture"></div>
                    <div class="wrinkle wrinkle-1"></div>
                    <div class="wrinkle wrinkle-2"></div>
                    <div class="wrinkle wrinkle-3"></div>
                    <div class="wrinkle wrinkle-4"></div>
                    
                    <div class="fold-crease fold-crease-1"></div>
                    <div class="fold-crease fold-crease-2"></div>
                    <div class="fold-crease fold-crease-3"></div>
                    
                    <div class="paper-edge edge-top-left"></div>
                    <div class="paper-edge edge-top-right"></div>
                </div>
            </div>
            <h2>Natural Cream Wrapper</h2>
            <p>Soft cream wrapper for a rustic, natural look</p>
            <div class="price">$4.29</div>
            <button class="button">Add to Cart</button>
        </div>
    </div>
    
    <div style="margin-top: 50px;">
        <h2>How to Use</h2>
        <p>To use the bouquet wrapper in your own project, simply include the component file:</p>
        <code>
            &lt;?php include 'bouquet_wrapper_component.php'; ?&gt;
        </code>
    </div>
</body>
</html> 