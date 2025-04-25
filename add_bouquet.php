<?php
include 'navbar.php';
include 'db_connection.php';

// Fetch regular products
$products_result = mysqli_query($conn, "
    SELECT products.*, categories.name AS category_name
    FROM products
    LEFT JOIN categories ON products.category_id = categories.id
");

// Fetch customized products with related details
$customized_result = mysqli_query($conn, "
    SELECT cp.*, cat.name AS category_name,
           bs.name AS bouquet_size_name, bs.price AS bouquet_size_price,
           rc.name AS ribbon_color_name, rc.price AS ribbon_color_price
    FROM customized_products cp
    LEFT JOIN categories cat ON cp.category_id = cat.id
    LEFT JOIN bouquet_sizes bs ON cp.bouquet_sizes = bs.id  -- Use 'bouquet_sizes' here
    LEFT JOIN ribbon_colors rc ON cp.ribbon_colors = rc.id  -- Use 'ribbon_colors' here
") or die("Query Error (Customized): " . mysqli_error($conn));
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Bouquet - Heavenly Bloom</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #fff6f2;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 800px; /* Increased width for image preview */
            margin: 20px auto;
            background-color: #ffe8ec;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #d15e97;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }

        input[type="text"],
        input[type="number"],
        input[type="file"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 16px;
        }

        textarea {
            resize: vertical;
        }

        select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url('data:image/svg+xml;charset=UTF-8,<svg fill="%23555" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z"/></svg>');
            background-repeat: no-repeat;
            background-position-x: 98%;
            background-position-y: 50%;
            padding-right: 30px;
        }

        .btn-submit {
            background-color: #d15e97;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            cursor: pointer;
            display: block;
            width: 100%;
            text-align: center;
            text-decoration: none;
        }

        .btn-submit:hover {
            background-color: #bb407c;
        }

        /* Add-ons Styling with Image Preview */
        #add-ons-container {
            margin-top: 20px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }

        .add-on-item {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            align-items: center;
        }

        .add-on-item label {
            width: 120px;
        }

        .add-on-item input[type="text"] {
            flex-grow: 1;
        }

        .add-on-item button {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }

        .add-on-item button:hover {
            background-color: #d32f2f;
        }

        #add-add-on-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }

        #add-add-on-btn:hover {
            background-color: #45a049;
        }

        .add-on-image-preview {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
            margin-left: 10px;
            border: 1px solid #ddd;
        }

        .add-on-upload-button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
        }

        .add-on-upload-button:hover {
            background-color: #0056b3;
        }
    </style>
    <script>
        let addOnCounter = 0;

        function addAddOn() {
            addOnCounter++;
            const container = document.getElementById('add-ons-container');
            const addOnDiv = document.createElement('div');
            addOnDiv.classList.add('add-on-item');
            addOnDiv.innerHTML = `
                <label for="add_on_name_${addOnCounter}">Add-on Name:</label>
                <input type="text" id="add_on_name_${addOnCounter}" name="add_on_names[]" placeholder="e.g., Teddy Bear">
                <input type="file" id="add_on_image_${addOnCounter}" name="add_on_images[]" accept="image/*" style="display: none;" onchange="previewAddOnImage(event, ${addOnCounter})">
                <button type="button" class="add-on-upload-button" onclick="document.getElementById('add_on_image_${addOnCounter}').click()">Upload Image</button>
                <img id="add_on_preview_${addOnCounter}" src="#" alt="Add-on Preview" class="add-on-image-preview" style="display: none;">
                <button type="button" onclick="removeAddOn(this)">Remove</button>
            `;
            container.appendChild(addOnDiv);
        }

        function removeAddOn(button) {
            const addOnItem = button.parentNode;
            addOnItem.remove();
        }

        function previewAddOnImage(event, counter) {
            const previewImage = document.getElementById(`add_on_preview_${counter}`);
            const file = event.target.files[0];
            if (file) {
                previewImage.style.display = 'block';
                previewImage.src = URL.createObjectURL(file);
            } else {
                previewImage.style.display = 'none';
                previewImage.src = '#';
            }
        }

        function collectAddOns() {
            const addOnNameInputs = document.querySelectorAll('#add-ons-container input[name="add_on_names[]"]');
            const addOnsArray = [];
            addOnNameInputs.forEach(input => {
                if (input.value.trim() !== '') {
                    addOnsArray.push(input.value.trim());
                }
            });
            document.getElementById('add_ons').value = addOnsArray.join(', ');
            return true; // Allow form submission
        }
    </script>
</head>
<body>
    <div class="container">
        <h2><i class="fas fa-plus-circle"></i> Add New Bouquet</h2>
        <form action="process_add_bouquet.php" method="post" enctype="multipart/form-data" onsubmit="return collectAddOns()">

            <div class="form-group">
                <label for="product_name">Bouquet Name:</label>
                <input type="text" id="product_name" name="product_name" required>
            </div>

            <div class="form-group">
                <label for="product_description">Description:</label>
                <textarea id="product_description" name="product_description" rows="4"></textarea>
            </div>

            <div class="form-group">
                <label for="category_id">Category:</label>
                <select id="category_id" name="category_id">
                    <option value="">Select Category (Optional)</option>
                    <?php
                    include 'db_connection.php';
                    $categories_result = mysqli_query($conn, "SELECT id, name FROM categories");
                    while ($cat = mysqli_fetch_assoc($categories_result)):
                    ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="product_price">Base Price (₱):</label>
                <input type="number" id="product_price" name="product_price" step="0.01" required>
            </div>

            <div class="form-group">
                <label for="product_image">Product Image:</label>
                <input type="file" id="product_image" name="product_image" accept="image/*">
                <small>Optional: Allowed formats - JPG, JPEG, PNG, GIF</small>
            </div>

            <div class="form-group">
                <label for="occasion_type">Occasion Type:</label>
                <input type="text" id="occasion_type" name="occasion_type">
            </div>

            <div class="form-group">
                <label for="color">Color:</label>
                <input type="text" id="color" name="color">
            </div>

            <div class="form-group">
                <label for="size">Size:</label>
                <input type="text" id="size" name="size">
            </div>

            <h3>Add-ons</h3>
            <div id="add-ons-container">
                </div>
            <button type="button" id="add-add-on-btn" onclick="addAddOn()">Add Another Add-on</button>

            <div class="form-group" style="display: none;">
                <label for="add_ons">Add-ons (Comma Separated):</label>
                <textarea id="add_ons" name="add_ons" readonly></textarea>
            </div>

            <div class="form-group">
                <label for="message">Message (Optional):</label>
                <textarea id="message" name="message" rows="2"></textarea>
            </div>

            <div class="form-group">
                <label for="bouquet_sizes">Bouquet Sizes:</label>
                <input type="text" id="bouquet_sizes" name="bouquet_sizes" placeholder="e.g., Small, Medium, Large">
            </div>

            <div class="form-group">
                <label for="ribbon_colors">Ribbon Colors:</label>
                <input type="text" id="ribbon_colors" name="ribbon_colors" placeholder="e.g., Red, Blue, Gold">
            </div>

            <div class="form-group">
                <label for="message_price">Message Price (₱):</label>
                <input type="number" id="message_price" name="message_price" step="0.01" value="0.00">
            </div>

            <button type="submit" class="btn-submit">Add Bouquet</button>
        </form>
    </div>
</body>
</html>

<?php mysqli_close($conn); ?>