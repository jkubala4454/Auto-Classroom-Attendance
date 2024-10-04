<?php
// Database connection (assuming `db.php` handles the connection)
include('db.php');

// Admin username and password
$username = 'admin';
$password = '***REMOVED***';  // Plain text password

// Hash the password
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

// Prepare SQL statement to insert username and hashed password
$query = "INSERT INTO admins (username, password) VALUES (?, ?)";
$stmt = $conn->prepare($query);

// Bind parameters and execute the statement
$stmt->bind_param("ss", $username, $hashed_password);
$stmt->execute();

// Check if the insertion was successful
if ($stmt->affected_rows > 0) {
    echo "Admin user created successfully.";
} else {
    echo "Error: " . $stmt->error;
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>
