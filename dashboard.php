<?php
session_start();
if (!isset($_SESSION['username'])) {
  header('Location: index.php');
  exit();
}
$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html>
<head>
  <title>Dashboard</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f4f7f9;
      color: #333;
    }
    .header {
      background-color: #4a90e2;
      color: white;
      padding: 15px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .header h1 {
      margin: 0;
      font-size: 24px;
    }
    .logout {
      font-size: 14px;
    }
    .logout a {
      color: white;
      text-decoration: underline;
    }
    .menu {
      margin: 30px;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
    }
    .menu a {
      background-color: white;
      padding: 20px;
      text-align: center;
      text-decoration: none;
      color: #4a90e2;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      font-size: 18px;
      transition: transform 0.2s ease-in-out;
    }
    .menu a:hover {
      transform: translateY(-5px);
    }
  </style>
</head>
<body>
  <div class="header">
    <h1>Inventory Dashboard</h1>
    <div class="logout">
      Logged in as: <?php echo $_SESSION['username']; ?> (<?php echo $role; ?>) |
      <a href="logout.php">Logout</a>
    </div>
  </div>
  <div class="menu">
    <a href="products.php">📦 Manage Products</a>
    <a href="Suppliers.php">🚚 Manage Suppliers</a>
    <a href="stock.php">📥 Manage Stock</a>
    <a href="sales.php">💰 Manage Sales</a>
    <?php if ($role === 'admin') echo '<a href="reports.php">📊 Analytics & Reports</a>'; ?>
  </div>
</body>
</html>
