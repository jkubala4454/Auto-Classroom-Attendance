// functions.php
function getCurrentDayType() {
    include('db.php'); // Include the database connection
    
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
