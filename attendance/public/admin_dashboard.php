<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Attendance Records</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Custom CSS -->
    <style>
        .status-present { color: green; font-weight: bold; }
        .status-tardy { color: orange; font-weight: bold; }
        .status-absent { color: red; font-weight: bold; }
        .table-hover tbody tr:hover { background-color: #f1f1f1; }
    </style>

    <script>
        // Function to fetch attendance records via AJAX
        function fetchAttendance() {
            let dateSelected = $('#date').val();
            let classPeriodSelected = $('#class_period').val();

            $.ajax({
                url: '../includes/fetch_attendance.php',
                method: 'POST',
                data: {
                    date: dateSelected,
                    class_period: classPeriodSelected
                },
                success: function(response) {
                    $('#attendance-table').html(response);
                },
                error: function() {
                    $('#attendance-table').html('<tr><td colspan="4" class="text-center text-danger">Error fetching data</td></tr>');
                }
            });
        }

        // Auto-refresh attendance data every 10 seconds
        setInterval(fetchAttendance, 10000);

        // Fetch attendance data when the page loads
        $(document).ready(function() {
            fetchAttendance();
        });
    </script>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-dark bg-dark">
        <span class="navbar-brand mb-0 h1">Attendance Dashboard</span>
    </nav>

    <!-- Main Container -->
    <div class="container mt-4">
        <!-- Header Row -->
        <div class="row">
            <div class="col-md-6">
                <h3>Attendance Records</h3>
            </div>
            <div class="col-md-6 text-right">
                <form class="form-inline" onsubmit="fetchAttendance(); return false;">
                    <!-- Date Filter -->
                    <label for="date" class="mr-2">Select Date:</label>
                    <input type="date" id="date" class="form-control mr-3" value="<?php echo htmlspecialchars($date_selected); ?>" onchange="fetchAttendance();">
                    
                    <!-- Class Period Filter -->
                    <label for="class_period" class="mr-2">Select Class Period:</label>
                    <select id="class_period" class="form-control mr-3" onchange="fetchAttendance();">
                        <option value="">All Periods</option>
                        <?php
                        // Fetch distinct class periods
                        include('../includes/config.php');  // Include the config file first
                        include(ROOT_PATH . '/includes/db.php'); // Include the database connection
                        $query = "SELECT DISTINCT class_period FROM class_schedule ORDER BY class_period ASC";
                        $result = $conn->query($query);
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='" . htmlspecialchars($row['class_period']) . "'>" . htmlspecialchars($row['class_period']) . "</option>";
                            }
                        }
                        ?>
                    </select>
                    <button type="submit" class="btn btn-primary">Filter</button>
                </form>
            </div>
        </div>

        <!-- Attendance Table -->
        <div class="mt-4">
            <table class="table table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>Student Name</th>
                        <th>Class Period</th>
                        <th>Status</th>
                        <th>Check-in Time</th>
                    </tr>
                </thead>
                <tbody id="attendance-table">
                    <!-- Attendance data will be populated here -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer bg-dark text-white text-center py-3 mt-5">
        <p>Auto Classroom Attendance System</p>
    </footer>
</body>
</html>
