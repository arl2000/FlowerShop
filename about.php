<!DOCTYPE html>
<html>
<head>
    <title>About Us - Heavenly Bloom</title>
    <link rel="stylesheet" type="text/css" href="about.css">
    <style>
    body {
        margin: 0;
        font-family: 'Roboto', sans-serif;
        background-color: #f8f0e3; /* Light Cream */
        color: #333; /* Dark Gray */
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    body::before {
        content: "";
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('images/floral-bg.png'); /* optional soft floral watermark */
        background-repeat: repeat;
        opacity: 0.05; /* Slightly more visible watermark */
        z-index: -1;
    }

    .hero-banner {
        background-image: url('images/about-hero.jpg'); /* floral image banner */
        background-size: cover;
        background-position: center;
        color: #fff; /* White text on the banner */
        text-align: center;
        padding: 150px 20px; /* Increased padding for better visibility */
        border-bottom: 5px solid #e91e63; /* Vibrant Pink */
        width: 100%;
        box-sizing: border-box;
    }

    .hero-banner h1 {
        font-family: 'Playfair Display', serif;
        font-size: 4.0em; /* Slightly larger heading */
        margin-bottom: 20px;
        color: #ffc107; /* Bright Yellow */
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3); /* Add a subtle shadow */
    }

    .hero-banner p {
        font-size: 1.6em; /* Slightly larger paragraph */
        font-weight: 400;
        margin: 0;
        color: #fdd835; /* Another shade of Yellow */
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2); /* Add a subtle shadow */
    }

    .container {
        max-width: 1200px; /* Slightly wider container */
        margin: 50px auto;
        padding: 30px;
        width: 95%; /* Adjust width as needed */
        box-sizing: border-box;
        background-color: #fff; /* White container background */
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .about-section {
        display: flex;
        flex-wrap: wrap;
        gap: 40px;
        align-items: flex-start;
    }

    .about-content {
        flex: 1 1 50%;
    }

    .about-content h2 {
        font-size: 2.5em;
        color: #9c27b0; /* Vibrant Purple */
        font-family: 'Playfair Display', serif;
        margin-bottom: 20px;
    }

    .about-content p {
        font-size: 1.1em;
        line-height: 1.8;
        margin-bottom: 25px;
        color: #555; /* Slightly darker gray */
    }

    .about-image {
        flex: 1 1 45%;
        text-align: center;
    }

    .about-image img {
        max-width: 100%;
        border-radius: 15px;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
    }

    .section-divider {
        border: none;
        height: 2px;
        background-color: #ff4081; /* Bright Pink Accent */
        margin: 60px auto;
        width: 70%;
        opacity: 0.7;
    }

    .quote-section {
        text-align: center;
        margin-top: 40px;
    }

    .quote-section h3 {
        font-family: 'Playfair Display', serif;
        font-size: 2.0em;
        color: #4caf50; /* Fresh Green */
        font-style: italic;
        margin: 0;
        line-height: 1.6;
    }

</style>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=Roboto&display=swap" rel="stylesheet">
</head>
<body>
<?php include 'navi.php'; ?>

<div class="hero-banner">
    <h1>Welcome to Heavenly Bloom</h1>
    <p>Crafting moments of love and elegance through flowers</p>
</div>

<div class="container">
    <div class="about-section">
        <div class="about-content">
            <h2>About Us</h2>
            <p>Heavenly Bloom is more than just a flower shop; it's a place where emotions come to life through the artistry of blooms. Established with a passion for creating memorable moments, we specialize in crafting exquisite floral arrangements for every occasion.</p>
            <p>Our journey began with a simple belief: that flowers have the power to convey emotions when words fall short. Whether it's a joyful celebration, a heartfelt apology, or a moment of remembrance, our floral creations speak volumes.</p>
            <p>At Heavenly Bloom, we source the finest and freshest flowers to ensure that every arrangement is a masterpiece. Our team of skilled florists combines creativity with precision to design arrangements that reflect your sentiments with elegance and style.</p>
            <p>Customer satisfaction is at the heart of everything we do. We take pride in providing personalized service, timely delivery, and floral designs that exceed expectations. From weddings to birthdays, anniversaries to corporate events, we're here to add a touch of floral magic to your special moments.</p>
        </div>
        <div class="about-image">
    <img src="uploads/gif.gif" alt="Heavenly Bloom Story">
</div>

</div>

    </div>

    <hr class="section-divider">

    <div class="quote-section">
        <h3>"Every petal tells a story – let us tell yours."</h3>
    </div>
</div>

</body>
</html>