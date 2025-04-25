<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in (adjust this condition as needed)
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - Heavenly Bloom</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        nav.admin-navbar {
            background-color: #fff;
            padding: 1rem 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
            border-bottom: 1px solid #f0ece7;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        nav .admin-logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            color: #e91e63;
        }

        nav .admin-links {
            display: flex;
            gap: 2rem;
        }

        nav .admin-links a {
            text-decoration: none;
            color: #5a3e36;
            font-size: 1rem;
            display: flex;
            align-items: center;
            transition: color 0.3s ease;
        }

        nav .admin-links a i {
            margin-right: 0.5rem;
        }

        nav .admin-links a:hover {
            color: #e91e63;
        }

        @media (max-width: 768px) {
            nav.admin-navbar {
                flex-direction: column;
                align-items: flex-start;
                padding: 1rem;
            }

            nav .admin-links {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
                width: 100%;
            }
        }
    </style>
</head>
<body>

<nav class="admin-navbar">
    <div class="admin-logo">Admin Panel</div>
    <div class="admin-links">
        <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="manage_products.php"><i class="fas fa-boxes"></i> Products</a>
        <a href="manage_categories.php"><i class="fas fa-tags"></i> Categories</a>
        <a href="orders.php"><i class="fas fa-receipt"></i> Orders</a>
        <a href="users.php"><i class="fas fa-users"></i> Users</a>
        <a href="user_logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</nav>

</body>
</html>
