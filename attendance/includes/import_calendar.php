<?php
// This file is used to import school_calendar.csv to populate the database
// with the dates and day types. ex. Holiday, Weekend, A Day, B Day

include('config.php');  // Include the config file first
include(ROOT_PATH . '/includes/db.php'); // Include the database connection

// Open the CSV file
$file = fopen('school_calendar.csv', 'r');

// Check if the file opened successfully
if (!$file) {
    die("Error opening CSV file: " . error_get_last()['message']);
}

// Prepare SQL query for inserting data into school_calendar
$query = "INSERT INTO school_calendar (date, day_type) VALUES (?, ?)";
$stmt = $conn->prepare($query);

// Check if query preparation succeeded
if (!$stmt) {
    die('Query preparation failed: ' . $conn->error);
}

// Loop through the CSV rows
while (($data = fgetcsv($file)) !== FALSE) {
    // Skip the header row
    if ($data[0] === 'date') continue;

    // Validate the data
    if (count($data) !== 2) {
        echo "Error: Invalid data format in row: " . implode(', ', $data) . "<br>";
        continue;
    }

    // Read date and day_type from the CSV
    $date = $data[0];
    $day_type = $data[1];

    // Validate date format
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        echo "Error: Invalid date format: $date<br>";
        continue;
    }

    // Bind parameters and execute the query
    $stmt->bind_param("ss", $date, $day_type);

    try {
        $stmt->execute();
    } catch (Exception $e) {
        echo "Error inserting calendar data: " . $e->getMessage() . "<br>";
        continue;
    }
}

echo "Calendar successfully imported!";
fclose($file);
$stmt->close();
$conn->close();
