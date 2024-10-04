<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Attendance Records</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript">
        // Function to fetch and update attendance data periodically
        function fetchAttendance() {
            var dateSelected = document.getElementById('date').value;
            var classPeriodSelected = document.getElementById('class_period').value;

            $.ajax({
                url: 'fetch_attendance.php',
                method: 'POST',
                data: { 
                    date: dateSelected,
                    class_period: classPeriodSelected 
                },
                success: function(response) {
                    // Update the attendance table with the new data
                    document.getElementById('attendance-table').innerHTML = response;
                }
            });
        }

        // Automatically fetch attendance data every 10 seconds
        setInterval(fetchAttendance, 10000);
        
        // Fetch attendance data immediately when the page loads
        window.onload = fetchAttendance;
    </script>
</head>
<body>
    <h1>Attendance Records for <span id="date-display"><?php echo htmlspecialchars($date_selected); ?></span></h1>

    <!-- Date and Class Period selection form -->
    <form method="post" action="admin_dashboard.php" onsubmit="fetchAttendance(); return false;">
        <label for="date">Select Date:</label>
        <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($date_selected); ?>" onchange="fetchAttendance();">

        <label for="class_period">Select Class Period:</label>
        <select id="class_period" name="class_period" onchange="fetchAttendance();">
            <option value="">All Periods</option>
            <?php
            // Fetch available class periods from the database
            include('db.php');
            $class_periods_query = "SELECT DISTINCT class_period FROM class_schedule ORDER BY class_period ASC";
            $class_periods_result = $conn->query($class_periods_query);

            if ($class_periods_result->num_rows > 0) {
                while ($row = $class_periods_result->fetch_assoc()) {
                    echo "<option value='" . $row['class_period'] . "'>" . htmlspecialchars($row['class_period']) . "</option>";
                }
            }
            ?>
        </select>

        <input type="submit" value="Filter">
    </form>

    <!-- Attendance Table (Updated by AJAX) -->
    <div id="attendance-table">
        <!-- The attendance table will be populated here by the AJAX call -->
    </div>
</body>
</html>
