<?php
session_start();
require_once('db_connect.php');

if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $quantity_sold = $_POST['quantity_sold'];
    $sale_date = $_POST['sale_date'];
    
    // Get product price
    $stmt = $conn->prepare("SELECT Price FROM Products WHERE ProductID = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();
    
    $total_amount = $product['Price'] * $quantity_sold;
    
    // Record sale
    $stmt = $conn->prepare("INSERT INTO Sales (ProductID, QuantitySold, SaleDate, TotalAmount) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iisd", $product_id, $quantity_sold, $sale_date, $total_amount);
    $stmt->execute();
    $stmt->close();
    
    header("Location: sales.php");
    exit();
}

// Get all sales with product info
$sales = $conn->query("
    SELECT s.SaleID, p.Name AS ProductName, s.QuantitySold, 
           s.SaleDate, s.TotalAmount
    FROM Sales s
    JOIN Products p ON s.ProductID = p.ProductID
    ORDER BY s.SaleDate DESC
");

// Get products for dropdown
$products = $conn->query("SELECT * FROM Products ORDER BY Name");
?>

<!DOCTYPE html>
<html>
<head>
  <title>Manage Sales</title>
  <style>
    /* Your existing CSS styles */
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f2f6fc;
    }
    .header {
      background-color: #2980b9;
      color: white;
      padding: 15px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .header h2 {
      margin: 0;
    }
    .back {
      font-size: 14px;
    }
    .back a {
      color: white;
      text-decoration: underline;
    }
    .container {
      max-width: 900px;
      margin: 40px auto;
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    }
    h3 {
      text-align: center;
      margin-bottom: 30px;
      color: #333;
    }
    form {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }
    input, select {
      padding: 12px;
      border-radius: 8px;
      border: 1px solid #ccc;
      width: 100%;
      font-size: 15px;
    }
    button {
      grid-column: span 2;
      padding: 14px;
      background-color: #2980b9;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
      transition: background 0.3s;
    }
    button:hover {
      background-color: #1c669d;
    }
    .sales-table {
      margin-top: 40px;
      width: 100%;
      border-collapse: collapse;
    }
    .sales-table th, .sales-table td {
      border: 1px solid #ddd;
      padding: 12px;
      text-align: left;
    }
    .sales-table th {
      background-color: #f0f0f0;
      color: #333;
    }
  </style>
</head>
<body>
  <div class="header">
    <h2>Manage Sales</h2>
    <div class="back">
      <a href="dashboard.php">← Back to Dashboard</a>
    </div>
  </div>
  <div class="container">
    <h3>Record New Sale</h3>
    <form method="POST">
      <select name="product_id" required>
        <option value="">Select Product</option>
        <?php while ($product = $products->fetch_assoc()): ?>
          <option value="<?= $product['ProductID'] ?>"><?= htmlspecialchars($product['Name']) ?></option>
        <?php endwhile; ?>
      </select>
      <input type="number" name="quantity_sold" placeholder="Quantity Sold" required>
      <input type="date" name="sale_date" required>
      <button type="submit">Record Sale</button>
    </form>

    <table class="sales-table">
      <thead>
        <tr>
          <th>Sale ID</th>
          <th>Product</th>
          <th>Quantity Sold</th>
          <th>Sale Date</th>
          <th>Total Amount</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($sale = $sales->fetch_assoc()): ?>
          <tr>
            <td><?= $sale['SaleID'] ?></td>
            <td><?= htmlspecialchars($sale['ProductName']) ?></td>
            <td><?= $sale['QuantitySold'] ?></td>
            <td><?= $sale['SaleDate'] ?></td>
            <td>$<?= number_format($sale['TotalAmount'], 2) ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</body>
</html>