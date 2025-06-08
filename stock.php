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
    $supplier_id = $_POST['supplier_id'];
    $quantity = $_POST['quantity'];
    $date_added = $_POST['date_added'];

    $stmt = $conn->prepare("INSERT INTO Stock (ProductID, SupplierID, QuantityAdded, DateAdded) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $product_id, $supplier_id, $quantity, $date_added);
    $stmt->execute();
    $stmt->close();

    header("Location: stock.php");
    exit();
}

// Get all stock entries with product and supplier info
$stock = $conn->query("
    SELECT s.StockID, p.Name AS ProductName, sup.Name AS SupplierName, 
           s.QuantityAdded, s.DateAdded
    FROM Stock s
    JOIN Products p ON s.ProductID = p.ProductID
    JOIN Suppliers sup ON s.SupplierID = sup.SupplierID
    ORDER BY s.DateAdded DESC
");

// Get products and suppliers for dropdowns
$products = $conn->query("SELECT * FROM Products ORDER BY Name");
$suppliers = $conn->query("SELECT * FROM Suppliers ORDER BY Name");
?>

<!DOCTYPE html>
<html>
<head>
  <title>Manage Stock</title>
  <style>
    /* Your existing CSS styles */
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f2f6fc;
    }
    .header {
      background-color: #f39c12;
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
      background-color: #f39c12;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
      transition: background 0.3s;
    }
    button:hover {
      background-color: #d68910;
    }
    .stock-table {
      margin-top: 40px;
      width: 100%;
      border-collapse: collapse;
    }
    .stock-table th, .stock-table td {
      border: 1px solid #ddd;
      padding: 12px;
      text-align: left;
    }
    .stock-table th {
      background-color: #f0f0f0;
      color: #333;
    }
  </style>
</head>
<body>
  <div class="header">
    <h2>Manage Stock</h2>
    <div class="back">
      <a href="dashboard.php">← Back to Dashboard</a>
    </div>
  </div>
  <div class="container">
    <h3>Add Stock Entry</h3>
    <form method="POST">
      <select name="product_id" required>
        <option value="">Select Product</option>
        <?php while ($product = $products->fetch_assoc()): ?>
          <option value="<?= $product['ProductID'] ?>"><?= htmlspecialchars($product['Name']) ?></option>
        <?php endwhile; ?>
      </select>
      <select name="supplier_id" required>
        <option value="">Select Supplier</option>
        <?php while ($supplier = $suppliers->fetch_assoc()): ?>
          <option value="<?= $supplier['SupplierID'] ?>"><?= htmlspecialchars($supplier['Name']) ?></option>
        <?php endwhile; ?>
      </select>
      <input type="number" name="quantity" placeholder="Quantity Added" required>
      <input type="date" name="date_added" required>
      <button type="submit">Add Stock</button>
    </form>

    <table class="stock-table">
      <thead>
        <tr>
          <th>Stock ID</th>
          <th>Product</th>
          <th>Supplier</th>
          <th>Quantity</th>
          <th>Date Added</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($entry = $stock->fetch_assoc()): ?>
          <tr>
            <td><?= $entry['StockID'] ?></td>
            <td><?= htmlspecialchars($entry['ProductName']) ?></td>
            <td><?= htmlspecialchars($entry['SupplierName']) ?></td>
            <td><?= $entry['QuantityAdded'] ?></td>
            <td><?= $entry['DateAdded'] ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</body>
</html>