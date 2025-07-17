<?php
session_start();
include 'db_connection.php';

// Count the total number of items in the cart
$cartCount = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cartCount += $item['quantity']; // Count the quantity of each product
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>About Us - Heavenly Bloom</title>
    
    <link href="https://fonts.googleapis.com/css?family=Raleway:100,300,400,500,700,900" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- Additional CSS Files -->
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/font-awesome.css">
    <link rel="stylesheet" href="assets/css/templatemo-softy-pinko.css">
    <link rel="stylesheet" href="home.css">
    
    <style>
        body {
            font-family: 'Raleway', sans-serif;
            background-color: #fff;
            color: #636e72;
            margin: 0;
            padding: 0;
        }
        
        .hero-section {
            position: relative;
            background: rgba(255, 138, 195, 0.8);
            background-size: cover;
            background-position: center;
            padding: 120px 0;
            text-align: center;
            color: #fff;
        }
        
        .hero-section h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .hero-section p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        }
        
        .about-section {
            padding: 80px 0;
            background-color: #fff;
        }
        
        .about-container {
            max-width: 1140px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        .section-heading {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .section-heading h2 {
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            color: #333;
            margin-bottom: 15px;
            font-weight: 600;
            position: relative;
            padding-bottom: 15px;
        }
        
        .section-heading h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(45deg, #ff4483, #ff6b6b);
            border-radius: 5px;
        }
        
        .section-heading p {
            color: #777;
            font-size: 16px;
            max-width: 700px;
            margin: 0 auto;
        }
        
        .about-content {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: 50px;
        }
        
        .about-text {
            flex: 1;
            padding: 0 20px;
        }
        
        .about-text p {
            font-size: 16px;
            line-height: 1.8;
            margin-bottom: 20px;
            color: #636e72;
        }
        
        .about-image {
            flex: 1;
            padding: 0 20px;
            text-align: center;
        }
        
        .about-image img {
            max-width: 100%;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        
        .about-image img:hover {
            transform: translateY(-10px);
        }
        
        .quote-section {
            background-color: #fdf6f8;
            padding: 60px 0;
            text-align: center;
            margin-top: 30px;
        }
        
        .quote-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        .quote-text {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            font-style: italic;
            color: #ff4483;
            line-height: 1.6;
            position: relative;
            padding: 0 40px;
        }
        
        .quote-text::before,
        .quote-text::after {
            content: '"';
            font-size: 60px;
            font-family: Georgia, serif;
            position: absolute;
            color: rgba(255, 68, 131, 0.2);
            line-height: 1;
        }
        
        .quote-text::before {
            left: 0;
            top: -10px;
        }
        
        .quote-text::after {
            right: 0;
            bottom: -40px;
        }
        
        @media (max-width: 768px) {
            .hero-section {
                padding: 80px 0;
            }
            
            .hero-section h1 {
                font-size: 2.5rem;
            }
            
            .about-content {
                flex-direction: column;
            }
            
            .about-text, .about-image {
                flex: none;
                width: 100%;
                padding: 0;
                margin-bottom: 30px;
            }
            
            .quote-text {
                font-size: 20px;
                padding: 0 20px;
            }
        }
    </style>
</head>
<body>
    <?php include 'navi.php'; ?>
    
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1>Welcome to Heavenly Bloom</h1>
            <p>Crafting moments of love and elegance through flowers</p>
        </div>
    </section>
    
    <!-- About Section -->
    <section class="about-section">
        <div class="about-container">
            <div class="section-heading">
                <h2>Our Story</h2>
                <p>Learn more about our passion for flowers and dedication to excellence</p>
            </div>
            
            <div class="about-content">
                <div class="about-text">
                    <p>Heavenly Bloom is more than just a flower shop; it's a place where emotions come to life through the artistry of blooms. Established with a passion for creating memorable moments, we specialize in crafting exquisite floral arrangements for every occasion.</p>
                    <p>Our journey began with a simple belief: that flowers have the power to convey emotions when words fall short. Whether it's a joyful celebration, a heartfelt apology, or a moment of remembrance, our floral creations speak volumes.</p>
                    <p>At Heavenly Bloom, we source the finest and freshest flowers to ensure that every arrangement is a masterpiece. Our team of skilled florists combines creativity with precision to design arrangements that reflect your sentiments with elegance and style.</p>
                </div>
                <div class="about-image">
                    <img src="uploads/gif.gif" alt="Heavenly Bloom Story">
                </div>
            </div>
            
            <div class="about-content">
                <div class="about-image">
                    <img src="uploads/about-image.jpg" alt="Our Commitment" onerror="this.src='uploads/gif.gif'">
                </div>
                <div class="about-text">
                    <p>Customer satisfaction is at the heart of everything we do. We take pride in providing personalized service, timely delivery, and floral designs that exceed expectations. From weddings to birthdays, anniversaries to corporate events, we're here to add a touch of floral magic to your special moments.</p>
                    <p>Each arrangement is crafted with attention to detail and a commitment to quality. We believe that every occasion deserves the perfect floral touch, which is why we offer customized solutions to meet your specific needs and preferences.</p>
                    <p>Thank you for choosing Heavenly Bloom. We look forward to being a part of your special moments and helping you express your emotions through the language of flowers.</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Quote Section -->
    <section class="quote-section">
        <div class="quote-container">
            <h3 class="quote-text">"Every petal tells a story – let us tell yours."</h3>
        </div>
    </section>
    
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
</body>
</html>