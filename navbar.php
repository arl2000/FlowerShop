<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Floral Navbar</title>

  <!-- Font Awesome for Icons -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
    }

    .navbar {
      background-color: #f8e1d4;
      padding: 20px 0;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .navbar-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .logo {
      font-size: 32px;
      font-weight: bold;
      color: #d15e97;
      text-decoration: none;
    }

    .nav-right {
      display: flex;
      align-items: center;
      gap: 25px;
    }

    .nav-links {
      list-style: none;
      display: flex;
      gap: 25px;
    }

    .nav-links li a {
      text-decoration: none;
      color: #3b2a45;
      font-size: 20px;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 10px;
      transition: color 0.3s ease, transform 0.3s ease;
    }

    .nav-links li a:hover {
      color: #f36f6f;
      transform: scale(1.1);
    }

    .nav-links i {
      font-size: 22px;
    }

    .logout-btn {
      background-color: #d15e97;
      color: white;
      padding: 10px 18px;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      font-weight: bold;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    .logout-btn:hover {
      background-color: #c14a87;
    }

    @media (max-width: 768px) {
      .navbar-container {
        flex-direction: column;
        align-items: flex-start;
      }

      .nav-right {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
        margin-top: 10px;
      }

      .nav-links {
        flex-direction: column;
        gap: 15px;
      }

      .logout-btn {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <nav class="navbar">
  
    <div class="navbar-container">
      <a href="dass.php" class="logo">Heavenly Bloom</a>

      <div class="nav-right">
        <ul class="nav-links">
		
          <li><a href="admin_dashboard.php"><i class="fas fa-home"></i>Home</a></li>
          <li><a href="product.php"><i class="fas fa-seedling"></i>Products</a></li>
          <li><a href="orders.php"><i class="fas fa-box"></i>Orders</a></li>
     
          <li><a href="sales.php"><i class="fas fa-chart-line"></i>Sales</a></li>
        </ul>
        <form action="logout.php" method="post">
          <button type="submit" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
        </form>
      </div>
    </div>
  </nav>
</body>
</html>
