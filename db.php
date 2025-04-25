<?php
// db.php - Database connection

$host = 'localhost';        // Your database host
$dbname = 'capstone';  // Your database name
$username = 'root';  // Your database username
$password = '';  // Your database password


// Create a connection to the database
$conn = new mysqli($host, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

