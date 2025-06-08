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
    $contact_info = $_POST['contact_info'];

    $stmt = $conn->prepare("INSERT INTO Suppliers (Name, ContactInfo) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $contact_info);
    $stmt->execute();
    $stmt->close();

    header("Location: suppliers.php");
    exit();
}

// Get all suppliers
$suppliers = $conn->query("SELECT * FROM Suppliers ORDER BY Name");
?>

<!DOCTYPE html>
<html>
<head>
  <title>Manage Suppliers</title>
  <style>
    /* Your existing CSS styles */
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f2f6fc;
    }
    .header {
      background-color: #50b47e;
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
    input {
      padding: 12px;
      border-radius: 8px;
      border: 1px solid #ccc;
      width: 100%;
      font-size: 15px;
    }
    button {
      grid-column: span 2;
      padding: 14px;
      background-color: #50b47e;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
      transition: background 0.3s;
    }
    button:hover {
      background-color: #3b9767;
    }
    .supplier-table {
      margin-top: 40px;
      width: 100%;
      border-collapse: collapse;
    }
    .supplier-table th, .supplier-table td {
      border: 1px solid #ddd;
      padding: 12px;
      text-align: left;
    }
    .supplier-table th {
      background-color: #f0f0f0;
      color: #333;
    }
  </style>
</head>
<body>
  <div class="header">
    <h2>Manage Suppliers</h2>
    <div class="back">
      <a href="dashboard.php">← Back to Dashboard</a>
    </div>
  </div>
  <div class="container">
    <h3>Add New Supplier</h3>
    <form method="POST">
      <input type="text" name="name" placeholder="Supplier Name" required>
      <input type="text" name="contact_info" placeholder="Contact Info" required>
      <button type="submit">Add Supplier</button>
    </form>

    <table class="supplier-table">
      <thead>
        <tr>
          <th>Supplier ID</th>
          <th>Name</th>
          <th>Contact Info</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($supplier = $suppliers->fetch_assoc()): ?>
          <tr>
            <td><?= $supplier['SupplierID'] ?></td>
            <td><?= htmlspecialchars($supplier['Name']) ?></td>
            <td><?= htmlspecialchars($supplier['ContactInfo']) ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</body>
</html>