<?php
session_start();
require_once('db_connect.php');

if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $supplier_id = $_POST['supplier_id'];

    // Insert product
    $stmt = $conn->prepare("INSERT INTO Products (Name, Category, Price) VALUES (?, ?, ?)");
    $stmt->bind_param("ssd", $name, $category, $price);
    $stmt->execute();
    $product_id = $stmt->insert_id;
    $stmt->close();



    // Link product to supplier if selected
    if ($supplier_id > 0) {
        $stmt = $conn->prepare("INSERT INTO SupplierProducts (SupplierID, ProductID) VALUES (?, ?)");
        $stmt->bind_param("ii", $supplier_id, $product_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: products.php");
    exit();
}

// Get all products with their suppliers
$products = $conn->query("
    SELECT p.*, GROUP_CONCAT(s.Name SEPARATOR ', ') AS SupplierNames
    FROM Products p
    LEFT JOIN SupplierProducts sp ON p.ProductID = sp.ProductID
    LEFT JOIN Suppliers s ON sp.SupplierID = s.SupplierID
    GROUP BY p.ProductID
");

// Get all suppliers for dropdown
$suppliers = $conn->query("SELECT * FROM Suppliers");
?>

<!DOCTYPE html>
<html>
<head>
  <title>Manage Products</title>
  <style>
    /* Your existing CSS styles */
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f2f6fc;
    }
    .header {
      background-color: #4a90e2;
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
      max-width: 800px;
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
      background-color: #4a90e2;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
      transition: background 0.3s;
    }
    button:hover {
      background-color: #357ABD;
    }
    .product-table {
      margin-top: 40px;
      width: 100%;
      border-collapse: collapse;
    }
    .product-table th, .product-table td {
      border: 1px solid #ddd;
      padding: 12px;
      text-align: left;
    }
    .product-table th {
      background-color: #f0f0f0;
      color: #333;
    }
  </style>
</head>
<body>
  <div class="header">
    <h2>Manage Products</h2>
    <div class="back">
      <a href="dashboard.php">← Back to Dashboard</a>
    </div>
  </div>
  <div class="container">
    <h3>Add New Product</h3>
    <form method="POST">
      <input type="text" name="name" placeholder="Product Name" required>
      <input type="text" name="category" placeholder="Category" required>
      <input type="number" step="0.01" name="price" placeholder="Price" required>
      <select name="supplier_id">
        <option value="0">Select Supplier (optional)</option>
        <?php while ($supplier = $suppliers->fetch_assoc()): ?>
          <option value="<?= $supplier['SupplierID'] ?>"><?= $supplier['Name'] ?></option>
        <?php endwhile; ?>
      </select>
      <button type="submit">Add Product</button>
    </form>

    <table class="product-table">
      <thead>
        <tr>
          <th>User</th>
          <th>Product ID</th>
          <th>Name</th>
          <th>Category</th>
          <th>Price</th>
          <th>Supplier(s)</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($product = $products->fetch_assoc()): ?>
          <tr>
            <td>Admin</td>
            <td><?= $product['ProductID'] ?></td>
            <td><?= htmlspecialchars($product['Name']) ?></td>
            <td><?= htmlspecialchars($product['Category']) ?></td>
            <td>$<?= number_format($product['Price'], 2) ?></td>
            <td><?= $product['SupplierNames'] ? htmlspecialchars($product['SupplierNames']) : 'None' ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</body>
</html>