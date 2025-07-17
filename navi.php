<?php
include 'db_connection.php';

// Fetch categories for the dropdown
$categoryQuery = "SELECT * FROM categories";
$categoryResult = $conn->query($categoryQuery);

// Assuming you have a user ID stored in the session
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// Count the total number of items in the cart from the database
$cartCount = 0;
if ($userId > 0) {
    // Query to count the total quantity of items in the user's cart
    $cartQuery = "SELECT SUM(quantity) AS total_quantity FROM cart WHERE user_id = $userId";
    $cartResult = $conn->query($cartQuery);

    if ($cartResult && $row = $cartResult->fetch_assoc()) {
        $cartCount = $row['total_quantity']; // Set the cart count based on the database result
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Heavenly Bloom</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
        /* Updated Navigation CSS for Romantic Modern Flower Shop - Centered Links, Logo Left with Search */
        nav {
            background-color: #fff; /* Clean white background */
            padding: 1rem 20px; /* Adjusted overall padding */
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: flex-start; /* Align items to the start to accommodate left section */
            flex-wrap: wrap;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08); /* Softer shadow */
            border-bottom: 1px solid #f0ece7; /* Subtle bottom border */
            position: sticky; /* Make the navbar sticky */
            top: 0;
            z-index: 100; /* Ensure it stays on top */
        }

        /* Left Section: Logo and Search */
        nav .left-section {
            display: flex;
            align-items: center;
            margin-right: auto; /* Push the rest of the elements to the right */
        }

        nav .logo {
            font-family: 'Playfair Display', serif; /* Elegant, romantic font */
            font-size: 1.8rem;
            color: #e91e63; /* Romantic pink for the logo */
            margin-right: 1rem; /* Space between logo and search */
        }

        /* Search Container in Left Section */
        nav .left-section .search-container {
            display: flex;
            align-items: center;
            padding: 0.6rem 1rem; /* Adjusted padding */
            background-color: #fcf6f2; /* Very light background */
            border-radius: 25px; /* More rounded */
            border: 1px solid #d4c4b9; /* Softer border */
        }

        nav .left-section .search-container input {
            padding: 0.4rem 0.8rem; /* Adjusted padding */
            border: none;
            outline: none;
            width: 150px; /* Slightly smaller */
            background-color: transparent;
            font-size: 0.85rem;
            color: #5a3e36; /* Soft brown text */
        }

        nav .left-section .search-container button {
            background-color: #f9c5ce; /* Soft pink */
            border: none;
            color: white;
            padding: 0.4rem 0.6rem; /* Adjusted padding */
            border-radius: 50%;
            margin-left: 0.3rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        nav .left-section .search-container button:hover {
            background-color: #f4b6be; /* Slightly darker pink */
        }

        nav .left-section .search-container i {
            font-size: 0.85rem;
        }

        /* Center Navigation Links Container */
        nav .center-nav {
            display: flex;
            justify-content: center; /* Center the content */
            flex-grow: 1; /* Allow it to take up available space */
        }

        /* Navigation Links */
        nav .nav-links {
            display: flex;
            align-items: center;
            gap: 1.5rem; /* Adjusted gap */
            margin: 0.5rem 0; /* Add some vertical margin */
        }

        nav .nav-links a {
            text-decoration: none;
            color: #5a3e36; /* Soft brown text */
            display: flex;
            align-items: center;
            font-size: 0.95rem; /* Slightly larger font */
            transition: color 0.3s ease;
        }

        nav .nav-links a:hover {
            color: #e91e63; /* Romantic pink on hover */
        }

        nav .nav-links a i {
            margin-right: 0.5rem;
            font-size: 1rem;
        }

        /* Highlight the customizer link */
        nav .nav-links a.customizer-link {
            color: #e91e63;
            font-weight: 600;
        }

        nav .nav-links a.customizer-link:hover {
            color: #c2185b;
        }

        /* Occasions Dropdown */
        nav .dropdown {
            position: relative;
        }

        nav .dropdown-toggle {
            text-decoration: none;
            color: #5a3e36;
            display: flex;
            align-items: center;
            font-size: 0.95rem;
            cursor: pointer;
        }

        nav .dropdown-toggle i {
            margin-left: 0.3rem;
        }

        nav .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background-color: #fff;
            min-width: 180px;
            border: 1px solid #f0ece7; /* Light border */
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            z-index: 999;
            white-space: nowrap;
            padding: 0.5rem 0;
            margin-top: 0.2rem;
        }

        nav .dropdown-menu a {
            display: flex;
            align-items: center;
            padding: 0.8rem 1.2rem;
            text-decoration: none;
            color: #5a3e36;
            transition: background-color 0.3s ease, color 0.3s ease;
            font-size: 0.9rem;
        }

        nav .dropdown-menu a:hover {
            background-color: #f9d5d3; /* Soft pink hover */
            color: #e91e63;
        }

        nav .dropdown-menu a i {
            margin-right: 0.8rem;
            font-size: 1rem;
        }

        /* Style for the cart count span */
        nav .cart-link span {
            font-size: 0.7em; /* Make the number smaller */
            color: red; /* Make the color red */
            vertical-align: super; /* Position it slightly above */
            margin-left: 0.2em; /* Add a little space */
        }

        /* Hide the duplicate cart count within the parentheses */
        nav .cart-link span:first-child {
            display: none;
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            nav {
                padding: 1rem;
                flex-direction: column;
                align-items: stretch;
            }

            nav .left-section {
                flex-direction: column;
                align-items: center;
                margin-bottom: 0.8rem;
                margin-right: 0; /* Reset margin */
            }

            nav .logo {
                margin-right: 0; /* Reset margin for smaller screens */
                margin-bottom: 0.5rem; /* Add space below logo */
            }

            nav .center-nav {
                justify-content: center; /* Center links on smaller screens */
                margin-bottom: 0.8rem;
            }

            nav .nav-links {
                flex-direction: row;
                justify-content: center; /* Center links on smaller screens */
                gap: 1rem;
                margin-bottom: 0.8rem;
            }

            nav .nav-links a {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 768px) {
            nav .left-section {
                align-items: flex-start;
            }

            nav .center-nav {
                justify-content: flex-start;
            }

            nav .nav-links {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.8rem;
            }

            nav .nav-links a {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <nav>
        <div class="left-section">
            <div class="logo">Heavenly Bloom</div>
            <div class="search-container">
                <form action="search_results.php" method="GET" style="display: flex; align-items: center;">
                    <input
                        type="text"
                        name="search_query"
                        placeholder="Search..."
                        style="padding: 4px 8px; border-radius: 20px; border: 1px solid #ccc; outline: none; width: 150px; font-size: 0.85rem; background-color: #f8f8f8; color: #555;"
                    >
                    <button type="submit" style="background-color: #f9c5ce; border-radius: 50%; padding: 6px 10px; border: none; cursor: pointer;">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        </div>
        <div class="center-nav">
            <div class="nav-links">
                <a href="homepage.php">
                    <i class="fas fa-home"></i> Home
                </a>
                <div class="dropdown">
                    <a href="#" class="dropdown-toggle" onclick="toggleDropdown(event)">
                        <i class="fas fa-gift"></i> Occasions
                        <i class="fas fa-caret-down"></i>
                    </a>
                    <div id="occasionDropdown" class="dropdown-menu">
                        <?php while ($row = $categoryResult->fetch_assoc()): ?>
                            <?php
                            // Dynamically assign icons based on category name (or use a default)
                            $categoryIcon = 'fas fa-gift'; // Default icon
                            if (strpos(strtolower($row['name']), 'birthday') !== false) {
                                $categoryIcon = 'fas fa-birthday-cake'; // Birthday icon
                            } elseif (strpos(strtolower($row['name']), 'anniversary') !== false) {
                                $categoryIcon = 'fas fa-heart'; // Anniversary icon
                            } elseif (strpos(strtolower($row['name']), 'corporate') !== false) {
                                $categoryIcon = 'fas fa-briefcase'; // Corporate icon
                            }
                            ?>
                            <a href="occasions.php?category_id=<?php echo $row['id']; ?>">
                                <i class="<?php echo $categoryIcon; ?>"></i>
                                <?php echo htmlspecialchars($row['name']); ?>
                            </a>
                        <?php endwhile; ?>
                    </div>
                </div>
                <a href="bouquet_customizer.php" class="customizer-link">
                    <i class="fas fa-magic"></i> Design Bouquet
                </a>
                <a href="cart.php" class="cart-link">
                    <i class="fas fa-shopping-cart"></i> Cart <span><?= $cartCount ?></span>
                </a>
                <a href="trackorders.php">
                    <i class="fas fa-shipping-fast"></i> Track Orders
                </a>
                <a href="about.php">
                    <i class="fas fa-info-circle"></i> About Us
                </a>
                <a href="profile.php">
                    <i class="fas fa-user"></i> Profile
                </a>
            </div>
        </div>
    </nav>
</body>
</html>
