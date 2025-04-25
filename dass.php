<?php include 'navbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - Heavenly Bloom</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #fff6f2;
      margin: 0;
      padding: 0;
    }

    .dashboard {
      max-width: 1200px;
      margin: 40px auto;
      padding: 0 40px;
    }

    .card-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 30px;
    }

    .card {
      background-color: #ffe8ec;
      border-radius: 16px;
      padding: 30px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      display: flex;
      flex-direction: column;
      align-items: center;
      transition: transform 0.3s ease;
    }

    .card:hover {
      transform: scale(1.05);
    }

    .card i {
      font-size: 40px;
      color: #d15e97;
      margin-bottom: 15px;
    }

    .card h3 {
      font-size: 24px;
      margin-bottom: 8px;
      color: #3b2a45;
    }

    .card p {
      font-size: 20px;
      color: #5c5c5c;
    }
  </style>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
</head>
<body>

<div class="dashboard">

  <div class="card-container">
    <div class="card">
      <i class="fas fa-seedling"></i>
      <h3>Total Products</h3>
      <p></p>
    </div>
    <div class="card">
      <i class="fas fa-box"></i>
      <h3>Total Orders</h3>
      <p></p>
    </div>
    <div class="card">
      <i class="fas fa-chart-line"></i>
      <h3>Total Sales</h3>
      <p></p>
    </div>
    <div class="card">
      <i class="fas fa-users"></i>
      <h3>Customers</h3>
      <p></p>
    </div>
  </div>
</div>

</body>
</html>
