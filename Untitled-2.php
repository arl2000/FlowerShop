<?php
session_start();
include 'db_connection.php';

// Initialize cart count
$cartCount = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;

// Fetch order count securely using prepared statements
$order_count_query = $conn->prepare("SELECT COUNT(*) AS total_orders FROM orders");
$order_count_query->execute();
$order_count_result = $order_count_query->get_result();
$order_count = $order_count_result->fetch_assoc()['total_orders'] ?? 0;
$order_count_query->close();

// Add to cart functionality (only if user is logged in)
if (isset($_GET['add']) && is_numeric($_GET['add']) && isset($_SESSION['user_id'])) {
    $productId = (int) $_GET['add'];
    $stmt = $conn->prepare("SELECT product_id, product_name, product_price, product_image FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($product) {
        if (!isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] = [
                'name' => $product['product_name'],
                'price' => $product['product_price'],
                'quantity' => 1,
                'image' => $product['product_image']
            ];
        } else {
            $_SESSION['cart'][$productId]['quantity']++;
        }
    }
    header("Location: cart.php");
    exit();
} elseif (isset($_GET['add']) && is_numeric($_GET['add']) && !isset($_SESSION['user_id'])) {
    // If not logged in, trigger the modal
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('loginModal').style.display = 'flex';
        });
    </script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Heavenly Bloom</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .login-register-modal {
            display: none;
            position: fixed;
            z-index: 999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.7);
            justify-content: center;
            align-items: center;
        }

        .login-register-content {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            max-width: 400px;
            width: 90%;
            text-align: center;
            position: relative;
        }

        .login-register-content h2 {
            margin-bottom: 15px;
        }

        .login-register-content a {
            display: inline-block;
            margin: 10px;
            padding: 10px 20px;
            background-color: #e91e63;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .login-register-content .close {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 30px;
            color: #000;
            cursor: pointer;
        }

        /* Cute and Neat Logout Button */
        .logout-button {
            position: absolute;
            top: 15px; /* Adjust top position */
            right: 15px; /* Adjust right position */
            z-index: 1000;
        }

        .logout-button a {
            background-color: #f8f0e3; /* Soft background color */
            color: #d64161; /* Elegant text color */
            padding: 8px 15px; /* Adjusted padding */
            border-radius: 20px; /* Cute rounded corners */
            text-decoration: none;
            font-size: 0.9rem; /* Slightly smaller font */
            border: 1px solid #f4dcd7; /* Delicate border */
            display: flex;
            align-items: center;
            gap: 5px; /* Space between icon and text */
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }

        .logout-button a:hover {
            background-color: #d64161; /* Hover color */
            color: white;
            border-color: #d64161;
        }

        .logout-button a i {
            font-size: 1rem;
        }
    </style>
</head>
<body>
    <?php include 'navi.php'; ?>

<section class="hero">
    <div class="hero-text">
        <h1>Welcome to Heavenly Bloom</h1>
        <p>Discover our exquisite collection of fresh, modern flower arrangements, crafted with passion and designed to convey your deepest emotions for every special moment.</p>
    </div>
    <div class="hero-image">
        <img id="hero-rotating-image" src="uploads/moving.gif" alt="Rotating Modern Flowers" loading="lazy">
    </div>
</section>

<script>
    const rotatingImage = document.getElementById('hero-rotating-image');
    const images = [
        'uploads/moving.gif', // Replace with your modern flower GIFs
        'uploads/animatedgif.gif',
        'uploads/moving.gif',
        'uploads/order.gif'
    ];
    let currentIndex = 0;
    const intervalTime = 4000; // Adjust interval for pacing

    function changeImage() {
        currentIndex = (currentIndex + 1) % images.length;
        rotatingImage.src = images[currentIndex];
    }

    // Preload images for smoother transition (optional but recommended)
    images.forEach(src => {
        const img = new Image();
        img.src = src;
    });

    // Check if the first image exists before starting the interval
    const initialImage = new Image();
    initialImage.onload = () => {
        setInterval(changeImage, intervalTime);
    };
    initialImage.onerror = () => {
        console.error("Error loading the initial hero image.");
    };
    initialImage.src = images[0];
</script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const modal = document.getElementById("product-modal");
            const closeModal = document.querySelector(".close");

            closeModal.addEventListener("click", function () {
                modal.style.display = "none";
            });

            document.querySelectorAll(".view-product").forEach(button => {
                button.addEventListener("click", function (event) {
                    event.stopPropagation();
                    const productCard = this.closest(".product-card");

                    document.getElementById("modal-image").src = productCard.dataset.image;
                    document.getElementById("modal-name").textContent = productCard.dataset.name;
                    document.getElementById("modal-price").textContent = `₱${productCard.dataset.price}`;
                    document.getElementById("modal-description").textContent = productCard.dataset.description;
                    document.getElementById("modal-add-to-cart").value = productCard.dataset.id;

                    modal.style.display = "flex";
                });
            });

            window.addEventListener("click", (e) => {
                if (e.target === modal) modal.style.display = "none";
            });

            function checkLoginAndShowModal() {
                <?php if (!isset($_SESSION['user_id'])): ?>
                    document.getElementById('loginModal').style.display = 'flex';
                    return false; // Prevent default action
                <?php else: ?>
                    return true; // Allow default action
                <?php endif; ?>
            }

            // Intercept clicks on "Add to Cart" buttons in the product listings
            document.querySelectorAll('.product-buttons form').forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!checkLoginAndShowModal()) {
                        event.preventDefault();
                    }
                });
            });

            // Intercept clicks on "Cart" and "Checkout" buttons in the product listings
            document.querySelectorAll('.product-buttons a[href="cart.php"], .product-buttons a[href="checkout.php"]').forEach(function(link) {
                link.addEventListener('click', function(event) {
                    event.preventDefault();
                    checkLoginAndShowModal();
                });
            });

            // Intercept clicks on "Checkout" button in the product modal
            document.querySelectorAll('#modal-actions a[href="checkout.php"]').forEach(function(link) {
                link.addEventListener('click', function(event) {
                    event.preventDefault();
                    checkLoginAndShowModal();
                });
            });

            // Intercept the submit event of the customized product form
            document.querySelectorAll('.customization-form').forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!checkLoginAndShowModal()) {
                        event.preventDefault();
                    }
                });
            });

            <?php if (!isset($_SESSION['user_id'])): ?>
            let modalShown = false;
            function showLoginModalOnPageLoad() {
                if (!modalShown) {
                    document.getElementById("loginModal").style.display = "flex";
                    modalShown = true;
                }
            }

            // Optionally show the modal after a few seconds on page load
            // setTimeout(showLoginModalOnPageLoad, 5000);

            // Optionally show the modal when the user starts scrolling
            // window.addEventListener("scroll", debounce(function () {
            //     if (!modalShown) {
            //         showLoginModalOnPageLoad();
            //     }
            // }, 1000));
            <?php endif; ?>
        });
    </script>

    <main>
        <div class="products">
            <?php
            $query = "SELECT * FROM products";
            $result = $conn->query($query);

            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='product-card' data-id='{$row['product_id']}'
                                  data-name='" . htmlspecialchars($row['product_name']) . "'
                                  data-price='{$row['product_price']}'
                                  data-description='" . htmlspecialchars($row['product_description']) . "'
                                  data-image='uploads/" . htmlspecialchars($row['product_image']) . "'>";
                    echo "<img src='uploads/" . htmlspecialchars($row['product_image']) . "' alt='" . htmlspecialchars($row['product_name']) . "' loading='lazy'>";
                    echo "<div class='product-price'>₱" . number_format($row['product_price'], 2) . "</div>";
                    echo "<h3>" . htmlspecialchars($row['product_name']) . "</h3>";
                    echo "<div class='product-buttons'>
                                  <button class='view-product' data-id='{$row['product_id']}'>View</button>
                                  <form action='homepage.php' method='GET'>
                                      <input type='hidden' name='add' value='{$row['product_id']}'>
                                      <button type='submit'>Add to Cart</button>
                                  </form>
                                  <a href='cart.php'><button>Cart</button></a>
                                  <a href='checkout.php'><button>Checkout</button></a>
                              </div>";
                    echo "</div>";
                }
            } else {
                echo "<p>Unable to fetch products. Please try again later.</p>";
            }
            ?>
                    </div>

                    <section class="customized-products" id="customized-products-section">
    <h2>Customized Products</h2>
    <div class="products">
        <?php
        // Query to fetch customized products
        $query = "SELECT cp.*
                    FROM customized_products cp";
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
                $wrapperColors = htmlspecialchars($row['wrapper_colors'] ?? ''); // Fetch wrapper colors
                $personalizedMessage = htmlspecialchars($row['message'] ?? '');
                $addonsData = json_decode($row['add_ons'], true);

                $imagePath = !empty($productImage) && file_exists("uploads/" . $productImage)
                    ? "uploads/" . htmlspecialchars($productImage)
                    : "uploads/default.jpg";

                $formattedPrice = number_format($productPrice, 2);

                echo "<div class='product-card' data-id='$productId'
                                    data-name='" . htmlspecialchars($productName) . "'
                                    data-image='" . htmlspecialchars($imagePath) . "'
                                    data-price='" . htmlspecialchars($productPrice) . "'
                                    data-description-full='" . htmlspecialchars($productDescFull) . "'
                                    data-bouquet-sizes='" . htmlspecialchars($bouquetSizes) . "'
                                    data-ribbon-colors='" . htmlspecialchars($ribbonColors) . "'
                                    data-wrapper-colors='" . htmlspecialchars($wrapperColors) . "'
                                    data-addons='" . htmlspecialchars(json_encode($addonsData)) . "'>";
                    echo "<img src='$imagePath' alt='$productName' class='product-image'>";
                    echo "<h3>$productName</h3>";
                    echo "<p class='short-description'>$productDescShort</p>";
                    echo "<div class='product-price'>₱$formattedPrice</div>";
                    echo "<button type='button' class='view-product-btn' data-product-id='$productId'>View</button>";
                echo "</div>";
            }
        } else {
            echo "<p>No customized products available.</p>";
        }
        ?>
    </div>

    <div id="product-details-modal" class="modal">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <div class="modal-image-container">
            <img id="modal-product-image" src="" alt="Product Image">
            <h3 id="modal-product-name"></h3>
            <p id="modal-product-description" class="description"></p>
            <p class="price"><span id="modal-product-price"></span></p>
        </div>
        <div class="modal-details">
            <div id="modal-size-options" class="modal-options-group"></div>
            <div id="modal-ribbon-color-options" class="modal-options-group"></div>
            <div id="modal-wrapper-color-options" class="modal-options-group"></div>
            <div id="modal-addon-options-container" class="modal-options-group">

                <div id='modal-addons-checkboxes'></div>
            </div>
            <div class='customer-message'>
                <label for='modal-customer-message'>Personalized Message (Optional):</label>
                <textarea id='modal-customer-message' name='customer_message'></textarea>
            </div>
            <form id="modal-add-to-cart-form" action='homepage.php' method='GET' class='customization-form'>
                <div id="modal-selected-addons"></div>
                <input type='hidden' name='add' id='modal-product-id-input' value=''>
                <div class="modal-actions">
                    <button type='submit' class='add-to-cart-btn'>Add to Cart</button>
                    <a href='checkout.php' class='checkout-btn'><button type='button'>Checkout</button></a>
                </div>
            </form>
        </div>
    </div>
</div>

    <style>
 /* Modal Styles for Flower Product Details (Image Left, Details Right/Below) */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
    justify-content: center;
    align-items: center;
    overflow-y: auto; /* Enable vertical scrolling if content is long */
}

.modal-content {
    background-color: #fff; /* White background for the modal */
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    position: relative;
    width: 90%;
    max-width: 700px; /* Adjusted max width */
    margin: 20px;
    padding: 30px;
    display: grid;
    grid-template-columns: 1fr; /* Single column by default */
    gap: 20px;
}

@media (min-width: 768px) {
    .modal-content {
        grid-template-columns: 1.2fr 1fr; /* Image on the left, details on the right */
        gap: 30px;
    }
}

.close-button {
    color: #aaa;
    position: absolute;
    top: 15px;
    right: 20px;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    transition: color 0.3s ease;
}

.close-button:hover,
.close-button:focus {
    color: #333;
    text-decoration: none;
}

.modal-image-container {
    display: flex;
    flex-direction: column; /* Stack image and basic info on smaller screens */
    align-items: flex-start; /* Align items to the left */
}

@media (min-width: 768px) {
    .modal-image-container {
        align-items: center; /* Center image on larger screens in the left column */
    }
}

.modal-image-container img {
    max-width: 100%;
    height: auto;
    border-radius: 10px;
    object-fit: contain;
    margin-bottom: 15px; /* Space below the image */
}

.modal-image-container h3 {
    font-size: 1.6rem; /* Product name below image */
    color: #333;
    margin-bottom: 8px;
}

.modal-image-container p.description {
    color: #555;
    line-height: 1.6;
    margin-bottom: 10px;
}

.modal-image-container .price {
    font-size: 1.4rem;
    color: #e91e63;
    font-weight: bold;
    margin-bottom: 15px;
}

.modal-details {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

/* On smaller screens, move options below the image and basic info */
@media (max-width: 767px) {
    .modal-details {
        order: 2; /* Move details below image container */
    }
}

.modal-options-group {
    margin-bottom: 15px;
}

.modal-options-group label {
    display: block;
    font-weight: bold;
    color: #444;
    margin-bottom: 8px;
}

.modal-options-group div {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
}

.modal-options-group input[type='radio'],
.modal-options-group input[type='checkbox'] {
    margin-right: 5px;
}

.modal-options-group label[for^='modal-'] {
    font-weight: normal;
    color: #666;
}

.customer-message {
    margin-top: 20px;
}

.customer-message label {
    display: block;
    font-weight: bold;
    color: #444;
    margin-bottom: 8px;
}

.customer-message textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 8px;
    box-sizing: border-box;
    font-size: 1rem;
    min-height: 80px;
}

.modal-actions {
    display: flex;
    gap: 15px;
    margin-top: 25px;
}

.modal-actions button {
    flex-grow: 1;
    padding: 12px 20px;
    border: none;
    border-radius: 25px;
    cursor: pointer;
    font-size: 1rem;
    font-weight: bold;
    text-align: center;
    text-decoration: none;
    transition: background-color 0.3s ease, color 0.3s ease;
}

.modal-actions .add-to-cart-btn {
    background-color: #e91e63;
    color: white;
}

.modal-actions .add-to-cart-btn:hover {
    background-color: #d1115a;
}

.modal-actions .checkout-btn button {
    background-color: #f8f0e3;
    color: #e91e63;
    border: 1px solid #e91e63;
}

.modal-actions .checkout-btn button:hover {
    background-color: #e91e63;
    color: white;
}

#modal-addon-options-container label {
    margin-top: 10px;
}

#modal-addons-checkboxes {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

#modal-addons-checkboxes label {
    font-weight: normal;
}
/* Product Cards */
.products {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr)); /* Exactly 5 columns, distributing space equally */
    gap: 20px; /* Adjust gap as needed */
    padding: 20px; /* Adjust padding as needed */
    overflow-y: auto; /* Enable vertical scrolling if there are more than 5 rows */
    max-height: calc(5 * (300px + 20px)); /* Approximate max height for 5 rows (adjust 300px if needed) */
}

/* Responsive adjustments for smaller screens */
@media (max-width: 1200px) {
    .products {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); /* Adjust for smaller desktops */
        grid-template-rows: repeat(auto, auto); /* Adjust rows automatically */
        max-height: none; /* Remove max-height for flexible rows */
    }
}

@media (max-width: 992px) {
    .products {
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); /* Adjust for tablets */
    }
}

@media (max-width: 768px) {
    .products {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); /* Adjust for smaller tablets */
    }
}

@media (max-width: 576px) {
    .products {
        grid-template-columns: 1fr; /* Single column for mobile */
    }
}

.product-card {
    display: flex;
    flex-direction: column;
    padding: 15px; /* Adjusted padding */
    background-color: #fff;
    border-radius: 10px; /* Adjusted rounded corners */
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    position: relative;
    border: 1px solid #f0ece7;
}

.product-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12);
}

.product-card img {
    width: 100%;
    height: auto;
    aspect-ratio: 1 / 1;
    object-fit: cover;
    border-radius: 6px; /* Adjusted image corners */
    margin-bottom: 10px; /* Adjusted margin */
}

.product-details {
    display: flex;
    flex-direction: column;
    gap: 8px; /* Adjusted gap */
    padding: 0 8px; /* Adjusted horizontal padding */
    justify-content: flex-start;
}

.product-card .product-price {
    font-size: 1rem; /* Adjusted font size */
    color: #333;
    background-color: #f8f0e3;
    padding: 6px 10px; /* Adjusted padding */
    border-radius: 4px;
    font-weight: bold;
    text-align: left;
    width: fit-content;
    margin: 8px; /* Adjusted margin */
    position: absolute;
    top: 0;
    left: 0;
    z-index: 10;
}

.product-card h3 {
    font-family: 'Playfair Display', serif;
    font-size: 1.1rem; /* Adjusted font size */
    margin-bottom: 6px;
    color: #333;
    font-weight: bold;
    text-align: left;
}

.product-card p {
    font-size: 0.85rem; /* Adjusted font size */
    margin-bottom: 6px;
    color: #555;
    line-height: 1.4; /* Adjusted line height */
}

.product-card p strong {
    font-weight: bold;
    color: #e91e63;
}

.product-buttons {
    display: flex;
    flex-direction: row;
    gap: 8px; /* Adjusted gap */
    padding: 8px 0; /* Adjusted vertical padding */
    align-items: center;
    justify-content: flex-start;
    margin-top: 10px; /* Adjusted margin */
}

/* Button Styling */
.product-buttons button,
.product-buttons a button {
    background-color: #fff;
    color: #e91e63;
    border: 1px solid #e91e63;
    padding: 6px 12px; /* Adjusted padding */
    border-radius: 20px; /* Adjusted border radius */
    cursor: pointer;
    font-size: 0.85rem; /* Adjusted font size */
    transition: background-color 0.3s ease-in-out, color 0.3s ease-in-out;
    text-decoration: none;
    text-align: center;
    min-width: 80px; /* Adjusted minimum width */
}

.product-buttons button:hover,
.product-buttons a button:hover {
    background-color: #e91e63;
    color: white;
}

    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const viewButtons = document.querySelectorAll('.view-product-btn');
            const modal = document.getElementById('product-details-modal');
            const modalProductName = document.getElementById('modal-product-name');
            const modalProductImage = document.getElementById('modal-product-image');
            const modalProductDescription = document.getElementById('modal-product-description');
            const modalProductPrice = document.getElementById('modal-product-price');
            const modalSizeOptions = document.getElementById('modal-size-options');
            const modalRibbonColorOptions = document.getElementById('modal-ribbon-color-options');
            const modalWrapperColorOptions = document.getElementById('modal-wrapper-color-options');
            const modalAddonsCheckboxes = document.getElementById('modal-addons-checkboxes');
            const modalCustomerIdInput = document.getElementById('modal-product-id-input');
            const closeModalButton = modal.querySelector('.close-button');

            viewButtons.forEach(button => {
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
                    modalProductPrice.textContent = '₱' + parseFloat(productPrice).toFixed(2);
                    modalCustomerIdInput.value = productId;

                    // Generate size options
                    modalSizeOptions.innerHTML = '';
                    if (bouquetSizes.length > 0) {
                        let sizeOptionsHTML = '<label>Bouquet Size:</label><br>';
                        bouquetSizes.forEach(size => {
                            const trimmedSize = size.trim();
                            sizeOptionsHTML += `<input type='radio' name='bouquet_size' value='${trimmedSize}' required> ${trimmedSize}<br>`;
                        });
                        modalSizeOptions.innerHTML = sizeOptionsHTML;
                    }

                    // Generate ribbon color options
                    modalRibbonColorOptions.innerHTML = '';
                    if (ribbonColors.length > 0) {
                        let ribbonOptionsHTML = '<label>Ribbon Color:</label><br>';
                        ribbonColors.forEach(color => {
                            const trimmedColor = color.trim();
                            ribbonOptionsHTML += `<input type='radio' name='ribbon_color' value='${trimmedColor}'> ${trimmedColor}<br>`;
                        });
                        modalRibbonColorOptions.innerHTML = ribbonOptionsHTML;
                    }

                    // Generate wrapper color options
                    modalWrapperColorOptions.innerHTML = '';
                    if (wrapperColors.length > 0) {
                        let wrapperOptionsHTML = '<label>Wrapper Color:</label><br>';
                        wrapperColors.forEach(color => {
                            const trimmedColor = color.trim();
                            wrapperOptionsHTML += `<input type='radio' name='wrapper_color' value='${trimmedColor}'> ${trimmedColor}<br>`;
                        });
                        modalWrapperColorOptions.innerHTML = wrapperOptionsHTML;
                    }

                    // Generate add-on checkboxes
                    modalAddonsCheckboxes.innerHTML = '';
                    if (addonsData && addonsData.length > 0) {
                        let addonsHTML = '<label>Add-ons:</label><br>';
                        addonsData.forEach(addon => {
                            addonsHTML += `<input type='checkbox' name='addons[]' value='${addon.name}' data-price='${addon.price}'> ${addon.name} (+₱${parseFloat(addon.price).toFixed(2)})<br>`;
                        });
                        modalAddonsCheckboxes.innerHTML = addonsHTML;
                    }

                    modal.style.display = 'flex';
                });
            });

            closeModalButton.addEventListener('click', function() {
                modal.style.display = 'none';
            });

            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });
    </script>
</section>
                 


</section>
<div id="product-modal" class="modal" role="dialog" aria-labelledby="modal-name" aria-hidden="true" style="display: none;">
    <div class="modal-content">
        <span class="close" aria-label="Close">&times;</span> <!- Add this class -->
        <div class="modal-image-container">
            <img id="modal-image" src="" alt="Product Image">
        </div>
        <div class="modal-product-details">
            <h2 id="modal-name"></h2>
            <p class="description" id="modal-description"></p>
            <p class="price">₱<span id="modal-price"></span></p>
        </div>
        <div class="modal-actions">
            <form action="homepage.php" method="GET">
                <input type="hidden" id="modal-add-to-cart" name="add">
                <button type="submit">Add to Cart</button>
            </form>
            <a href="checkout.php"><button>Checkout</button></a>
        </div>
    </div>
</div>

        <?php if (!isset($_SESSION['user_id'])): ?>
        <div class="login-register-modal" id="loginModal" role="dialog" aria-labelledby="loginModalTitle" aria-hidden="true">
            <div class="login-register-content">
                <span class="close" onclick="document.getElementById('loginModal').style.display='none'" aria-label="Close">&times;</span>
                <h2 id="loginModalTitle">Welcome to Heavenly Bloom</h2>
                <p>Please login or register to place your order</p>
                <a href="user_login.php">Login</a>
                <a href="user_register.php">Register</a>
            </div>
        </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['user_id'])): ?>
        <div class="logout-button">
            <a href="user_logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
        <?php endif; ?>
    </main>
</body>
</html>


<script src="home.js"></script>
</body>
</html>