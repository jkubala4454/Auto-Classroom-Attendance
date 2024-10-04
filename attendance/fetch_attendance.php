<?php
include('db.php'); // Include database connection

// Get the selected date and class period from the POST request
$date_selected = isset($_POST['date']) ? $_POST['date'] : date('Y-m-d');
$class_period_selected = isset($_POST['class_period']) ? $_POST['class_period'] : '';

// Check if a class period is selected
$class_period_condition = '';
if (!empty($class_period_selected)) {
    $class_period_condition = "AND attendance.class_period = ?";
}

// Prepare the query with the additional class period condition
$query = "SELECT students.first_name, students.last_name, attendance.class_period, 
                 IFNULL(attendance.status, 'Absent') AS status, 
                 IFNULL(attendance.check_in_time, '-') AS check_in_time
          FROM students
          LEFT JOIN attendance ON students.student_id = attendance.student_id
          AND attendance.date = ?
          WHERE 1=1 $class_period_condition
          ORDER BY attendance.class_period ASC, students.last_name ASC, students.first_name ASC";

// Prepare and bind parameters
$stmt = $conn->prepare($query);
if (!empty($class_period_selected)) {
    $stmt->bind_param("ss", $date_selected, $class_period_selected); // Bind date and class period
} else {
    $stmt->bind_param("s", $date_selected); // Bind only date if no class period is selected
}

$stmt->execute();
$result = $stmt->get_result();

// Output the table as in the previous examples
echo '<table border="1">';
echo '<tr><th>First Name</th><th>Last Name</th><th>Class Period</th><th>Status</th><th>Check-in Time</th></tr>';

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $status_color = '';
        if ($row['status'] == 'Absent') {
            $status_color = 'style="color: red;"';
        } elseif ($row['status'] == 'Tardy') {
            $status_color = 'style="color: orange;"';
        } elseif ($row['status'] == 'Present') {
            $status_color = 'style="color: green;"';
        }

        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['first_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['last_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['class_period']) . "</td>";
        echo "<td $status_color>" . htmlspecialchars($row['status']) . "</td>";
        echo "<td>" . htmlspecialchars($row['check_in_time']) . "</td>";
        echo "</tr>";
    }
} else {
    echo '<tr><td colspan="5">No attendance records found for this class period and date.</td></tr>';
}

echo '</table>';

$stmt->close();
$conn->close();
?>
