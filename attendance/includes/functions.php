<?php
function getCurrentDayType() {
    include('config.php');  // Include the config file first
    include(ROOT_PATH . '/includes/db.php'); // Include the database connection
    
    // Get the current date
    $current_date = date("Y-m-d");

    // Prepare the SQL query to get the day type for the current date
    $query = "SELECT day_type FROM school_calendar WHERE date = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Query preparation failed: " . $conn->error);
    }
    $stmt->bind_param("s", $current_date);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch the day type if a matching date is found
        $row = $result->fetch_assoc();
        return $row['day_type']; // Return the day type (e.g., 'A Day', 'B Day', 'Weekend', 'Holiday')
    } else {
        return "Unknown"; // Handle case when the current date is not found in the calendar
    }

    $stmt->close();
}
// Function to determine attendance status based on time difference
function getAttendanceStatus($time_diff) {
    if ($time_diff <= 0) {  // On time or early, mark Present
        return 'Present';
    } elseif ($time_diff > 0 && $time_diff < 600) {  // Between 1 minute and 10 minutes late, mark Tardy
        return 'Tardy';
    } elseif ($time_diff >= 600 && $time_diff < 2700) {  // Between 11 minutes and 45 minutes late, mark L (Late or Partial Absence)
        return 'L';
    } else {  // More than 45 minutes late, mark Absent
        return 'Absent';
    }
}


?>