<?php
// Database configuration
$servername = "localhost";  // Usually 'localhost' if you're running locally
$username = "root";         // MySQL username
$password = "password";     // MySQL password (leave empty if no password)
$dbname = "attendance_db";  // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
