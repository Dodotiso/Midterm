<?php
session_start();
require_once('db_connect.php');

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

// Get sales analytics
$total_sales_result = $conn->query("SELECT COUNT(*) AS count FROM Sales");
$total_sales = $total_sales_result->fetch_assoc()['count'];
$total_sales_result->close();

$total_revenue_result = $conn->query("SELECT SUM(TotalAmount) AS total FROM Sales");
$total_revenue = $total_revenue_result->fetch_assoc()['total'];
$total_revenue_result->close();

$top_product_result = $conn->query("
    SELECT p.Name, SUM(s.QuantitySold) AS total_sold
    FROM Sales s
    JOIN Products p ON s.ProductID = p.ProductID
    GROUP BY p.ProductID
    ORDER BY total_sold DESC
    LIMIT 1
");
$top_product = $top_product_result->fetch_assoc();
$top_product_result->close();

// Get sales by product for bar chart
$sales_by_product_result = $conn->query("
    SELECT p.Name, SUM(s.QuantitySold) AS total_sold, SUM(s.TotalAmount) AS total_revenue
    FROM Sales s
    JOIN Products p ON s.ProductID = p.ProductID
    GROUP BY p.ProductID
    ORDER BY total_sold DESC
");

// Prepare data for charts
$product_names = [];
$sales_data = [];
$revenue_data = [];

if ($sales_by_product_result) {
    while ($row = $sales_by_product_result->fetch_assoc()) {
        $product_names[] = $row['Name'];
        $sales_data[] = $row['total_sold'];
        $revenue_data[] = $row['total_revenue'];
    }
    $sales_by_product_result->close();
}

// Get monthly sales for line chart
$monthly_sales_result = $conn->query("
    SELECT 
        DATE_FORMAT(SaleDate, '%Y-%m') AS month,
        SUM(TotalAmount) AS monthly_revenue,
        SUM(QuantitySold) AS monthly_sales
    FROM Sales
    GROUP BY DATE_FORMAT(SaleDate, '%Y-%m')
    ORDER BY month
");

$months = [];
$monthly_revenue = [];
$monthly_sales = [];

if ($monthly_sales_result) {
    while ($row = $monthly_sales_result->fetch_assoc()) {
        $months[] = $row['month'];
        $monthly_revenue[] = $row['monthly_revenue'];
        $monthly_sales[] = $row['monthly_sales'];
    }
    $monthly_sales_result->close();
}

// Get sales report
$sales_report_result = $conn->query("
    SELECT s.SaleID, p.Name AS ProductName, s.QuantitySold, 
           s.SaleDate, s.TotalAmount
    FROM Sales s
    JOIN Products p ON s.ProductID = p.ProductID
    ORDER BY s.SaleDate DESC
    LIMIT 10
");
?>

<!DOCTYPE html>
<html>
<head>
  <title>Analytics and Reports</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f2f6fc;
    }
    .header {
      background-color: #8e44ad;
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
      max-width: 1200px;
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
    .analytics-card {
      display: flex;
      justify-content: space-between;
      gap: 20px;
      margin-bottom: 40px;
    }
    .card {
      flex: 1;
      background-color: #f1c40f;
      padding: 20px;
      border-radius: 8px;
      text-align: center;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .card h4 {
      margin: 10px 0;
      color: #333;
    }
    .card p {
      font-size: 18px;
      margin: 5px 0;
      font-weight: bold;
      color: #2c3e50;
    }
    .chart-container {
      margin: 40px 0;
      padding: 20px;
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .chart-row {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      margin-bottom: 30px;
    }
    .chart-box {
      flex: 1;
      min-width: 300px;
      background: white;
      padding: 15px;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .reports-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 30px;
    }
    .reports-table th, .reports-table td {
      border: 1px solid #ddd;
      padding: 12px;
      text-align: left;
    }
    .reports-table th {
      background-color: #f0f0f0;
      color: #333;
    }
    .reports-table tr:nth-child(even) {
      background-color: #f9f9f9;
    }
  </style>
  <!-- Include Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <div class="header">
    <h2>Analytics and Reports</h2>
    <div class="back">
      <a href="dashboard.php">← Back to Dashboard</a>
    </div>
  </div>
  <div class="container">
    <h3>Sales Overview</h3>
    <div class="analytics-card">
      <div class="card">
        <h4>Total Sales</h4>
        <p><?= $total_sales ?></p>
      </div>
      <div class="card">
        <h4>Total Revenue</h4>
        <p>$<?= number_format($total_revenue, 2) ?></p>
      </div>
      <div class="card">
        <h4>Top Selling Product</h4>
        <p><?= isset($top_product['Name']) ? htmlspecialchars($top_product['Name']) : 'N/A' ?></p>
        <p>(<?= isset($top_product['total_sold']) ? $top_product['total_sold'] : '0' ?> sold)</p>
      </div>
    </div>

    <?php if (!empty($product_names)): ?>
    <div class="chart-row">
      <div class="chart-box">
        <h3>Sales by Product</h3>
        <canvas id="salesByProductChart"></canvas>
      </div>
      <div class="chart-box">
        <h3>Revenue by Product</h3>
        <canvas id="revenueByProductChart"></canvas>
      </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($months)): ?>
    <div class="chart-row">
      <div class="chart-box" style="flex: 2;">
        <h3>Monthly Sales Trend</h3>
        <canvas id="monthlySalesChart"></canvas>
      </div>
    </div>
    <?php endif; ?>

    <h3>Recent Sales</h3>
    <table class="reports-table">
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
        <?php if ($sales_report_result): ?>
          <?php while ($sale = $sales_report_result->fetch_assoc()): ?>
            <tr>
              <td><?= $sale['SaleID'] ?></td>
              <td><?= htmlspecialchars($sale['ProductName']) ?></td>
              <td><?= $sale['QuantitySold'] ?></td>
              <td><?= $sale['SaleDate'] ?></td>
              <td>$<?= number_format($sale['TotalAmount'], 2) ?></td>
            </tr>
          <?php endwhile; ?>
          <?php $sales_report_result->close(); ?>
        <?php else: ?>
          <tr>
            <td colspan="5">No sales data available</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php if (!empty($product_names)): ?>
  <script>
    // Sales by Product Bar Chart
    const salesByProductCtx = document.getElementById('salesByProductChart').getContext('2d');
    const salesByProductChart = new Chart(salesByProductCtx, {
      type: 'bar',
      data: {
        labels: <?= json_encode($product_names) ?>,
        datasets: [{
          label: 'Units Sold',
          data: <?= json_encode($sales_data) ?>,
          backgroundColor: 'rgba(54, 162, 235, 0.7)',
          borderColor: 'rgba(54, 162, 235, 1)',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'top',
          },
          title: {
            display: true,
            text: 'Product Sales Volume'
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: 'Units Sold'
            }
          }
        }
      }
    });

    // Revenue by Product Bar Chart
    const revenueByProductCtx = document.getElementById('revenueByProductChart').getContext('2d');
    const revenueByProductChart = new Chart(revenueByProductCtx, {
      type: 'bar',
      data: {
        labels: <?= json_encode($product_names) ?>,
        datasets: [{
          label: 'Revenue ($)',
          data: <?= json_encode($revenue_data) ?>,
          backgroundColor: 'rgba(75, 192, 192, 0.7)',
          borderColor: 'rgba(75, 192, 192, 1)',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'top',
          },
          title: {
            display: true,
            text: 'Product Revenue'
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: 'Revenue ($)'
            }
          }
        }
      }
    });
  </script>
  <?php endif; ?>

  <?php if (!empty($months)): ?>
  <script>
    // Monthly Sales Trend Line Chart
    const monthlySalesCtx = document.getElementById('monthlySalesChart').getContext('2d');
    const monthlySalesChart = new Chart(monthlySalesCtx, {
      type: 'line',
      data: {
        labels: <?= json_encode($months) ?>,
        datasets: [
          {
            label: 'Revenue ($)',
            data: <?= json_encode($monthly_revenue) ?>,
            borderColor: 'rgba(153, 102, 255, 1)',
            backgroundColor: 'rgba(153, 102, 255, 0.2)',
            borderWidth: 2,
            yAxisID: 'y'
          },
          {
            label: 'Units Sold',
            data: <?= json_encode($monthly_sales) ?>,
            borderColor: 'rgba(255, 159, 64, 1)',
            backgroundColor: 'rgba(255, 159, 64, 0.2)',
            borderWidth: 2,
            yAxisID: 'y1'
          }
        ]
      },
      options: {
        responsive: true,
        interaction: {
          mode: 'index',
          intersect: false,
        },
        plugins: {
          title: {
            display: true,
            text: 'Monthly Sales Trend'
          }
        },
        scales: {
          y: {
            type: 'linear',
            display: true,
            position: 'left',
            title: {
              display: true,
              text: 'Revenue ($)'
            }
          },
          y1: {
            type: 'linear',
            display: true,
            position: 'right',
            title: {
              display: true,
              text: 'Units Sold'
            },
            grid: {
              drawOnChartArea: false
            }
          }
        }
      }
    });
  </script>
  <?php endif; ?>
</body>
</html>