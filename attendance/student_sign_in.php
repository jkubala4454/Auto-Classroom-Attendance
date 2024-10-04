<?php
include('db.php');  // Include database connection

// Assuming student_id is sent via POST when student submits ID
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_POST['student_id'];  // The student ID entered by the student

    // Sanitize input
    $student_id = mysqli_real_escape_string($conn, $student_id);

    // Get the current time and date
    $current_time = date('H:i:s');
    $current_date = date('Y-m-d');

    // Echo the current time for debugging
    echo "Current time: $current_time<br>";

    // Fetch the class schedule for the current time
    $query = "SELECT * FROM class_schedule WHERE start_time <= ? AND end_time >= ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die('Query preparation failed: ' . $conn->error);  // Error handling
    }
    $stmt->bind_param("ss", $current_time, $current_time);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // There is an active class at the current time
        $class = $result->fetch_assoc();
        $class_period = $class['class_period'];
        $class_start_time = $class['start_time'];

        // Check if the student is registered for the class period
        $query_student_class = "SELECT * FROM student_classes WHERE student_id = ? AND class_period = ?";
        $stmt_student_class = $conn->prepare($query_student_class);
        if (!$stmt_student_class) {
            die('Query preparation failed: ' . $conn->error);
        }
        $stmt_student_class->bind_param("ss", $student_id, $class_period);
        $stmt_student_class->execute();
        $result_student_class = $stmt_student_class->get_result();

        if ($result_student_class->num_rows > 0) {
            // Calculate the time difference
            $time_diff = strtotime($current_time) - strtotime($class_start_time);

            // Determine attendance status
            if ($time_diff <= 0) {
                $status = 'Present';  // On time
            } elseif ($time_diff < 600) {
                $status = 'Tardy';  // Less than 10 minutes late
            } elseif ($time_diff < 2700) {
                $status = 'L';  // Between 10 and 45 minutes late
            } else {
                $status = 'Absent';  // More than 45 minutes late
            }

            // Insert attendance record
            $query_insert = "INSERT INTO attendance (student_id, class_period, status, check_in_time, date) VALUES (?, ?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($query_insert);
            if (!$stmt_insert) {
                die('Query preparation failed: ' . $conn->error);
            }
            $stmt_insert->bind_param("sssss", $student_id, $class_period, $status, $current_time, $current_date);
            if ($stmt_insert->execute()) {
                echo "Attendance marked as $status for class period $class_period.";
            } else {
                die("SQL Error: " . $stmt_insert->error);  // Added detailed error output
            }
        } else {
            echo "Student not registered for this class.";
        }

        $stmt_student_class->close();
    } else {
        echo "No class scheduled at this time.";
    }

    $stmt->close();
    $stmt_insert->close();
    $conn->close();
}
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Sign-In</title>
    <script type="text/javascript">
        // Function to update the clock every second
        function updateClock() {
            var now = new Date();
            var hours = now.getHours();
            var minutes = now.getMinutes();
            var seconds = now.getSeconds();
            var ampm = hours >= 12 ? 'PM' : 'AM';

        // Convert the hours to 12-hour format
            hours = hours % 12;
            hours = hours ? hours : 12;  // If the hour is 0, set it to 12 (midnight or noon)

        // Add leading zeros to minutes and seconds
            minutes = minutes < 10 ? "0" + minutes : minutes;
            seconds = seconds < 10 ? "0" + seconds : seconds;

        // Display the current time in 12-hour format
            var timeString = hours + ":" + minutes + ":" + seconds + " " + ampm;
            document.getElementById('clock').textContent = timeString;
        }


        // Function to auto-focus on the student_id field when the page loads
        function focusStudentID() {
            document.getElementById('student_id').focus();
        }

        // Update the clock every second
        setInterval(updateClock, 1000);

        // Focus on the student ID field when the page is loaded
        window.onload = function() {
            updateClock();  // Start the clock immediately
            focusStudentID();  // Focus on the input field
        };
    </script>
</head>
<body>
    <h2>Student Sign-In</h2>
    
    <!-- Display the current time -->
    <div>
        <strong>Current Time:</strong> <span id="clock"></span>
    </div>

    <!-- Sign-In Form -->
    <form method="POST" action="student_sign_in.php">
        <label for="student_id">Enter Student ID:</label>
        <input type="text" id="student_id" name="student_id" required>
        <button type="submit">Sign In</button>
    </form>
</body>
</html>
