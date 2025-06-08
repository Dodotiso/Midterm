<?php
// Database credentials
$servername = "localhost";  // This is the default host for MySQL on XAMPP
$username = "root";         // Default username for XAMPP MySQL
$password = "12345";             // Default password is empty for XAMPP
$dbname = "nevermore";      // Changed to your new database name

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    // If connection fails, display error
    die("Connection failed: " . $conn->connect_error);
} else {
    //Connection successful (optional)
     //echo "Connected successfully";
}
?>