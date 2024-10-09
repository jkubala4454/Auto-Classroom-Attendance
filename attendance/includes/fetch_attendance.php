<?php
include('config.php');  // Include the config file first
include(ROOT_PATH . '/includes/db.php'); // Include the database connection
include(ROOT_PATH . '/includes/functions.php');  // This brings in all functions defined in functions.php

// Call the function getCurrentDayType, store and display the result
$current_day_type = getCurrentDayType();
echo "<h1>Today's Schedule: $current_day_type</h1>"; // Display the result

// Fetch the date and class period selected
$date = $_POST['date'];
$class_period = $_POST['class_period'];

// Default date to today's date if not selected
if (empty($date)) {
    $date = date('Y-m-d');
}

// Prepare the SQL query to fetch all students in the selected class period
$query = "
    SELECT s.student_id, s.first_name, s.last_name, COALESCE(a.status, 'Absent') as status, a.check_in_time
    FROM students s
    LEFT JOIN student_classes sc ON s.student_id = sc.student_id
    LEFT JOIN attendance a ON s.student_id = a.student_id AND a.class_period = sc.class_period AND a.date = ?
    WHERE sc.class_period = ?
    ORDER BY s.last_name, s.first_name
";

// Prepare and execute the statement
$stmt = $conn->prepare($query);
$stmt->bind_param('si', $date, $class_period);
$stmt->execute();
$result = $stmt->get_result();

// Build the table
echo "<table border='1'>
        <tr>
            <th>Student ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Status</th>
            <th>Check-in Time</th>
        </tr>";

// Populate the table with the fetched results
while ($row = $result->fetch_assoc()) {
    $status = $row['status'];
    $statusColor = ($status === 'Absent') ? 'darkred' : (($status === 'Tardy') ? 'darkorange' : 'darkgreen');

    echo "<tr>
            <td>{$row['student_id']}</td>
            <td>{$row['first_name']}</td>
            <td>{$row['last_name']}</td>
            <td style='color: $statusColor;'>{$status}</td>
            <td>{$row['check_in_time']}</td>
          </tr>";
}
echo "</table>";

// Close the statement
$stmt->close();
$conn->close();
?>
