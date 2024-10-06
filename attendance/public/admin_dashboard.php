<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Attendance Records</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .status-present { color: green; font-weight: bold; }
        .status-tardy { color: orange; font-weight: bold; }
        .status-absent { color: red; font-weight: bold; }
        .table-hover tbody tr:hover { background-color: #f1f1f1; }
    </style>
    <script>
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
                    document.getElementById('attendance-table').innerHTML = response;
                }
            });
        }

        setInterval(fetchAttendance, 10000);
        window.onload = fetchAttendance;
    </script>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <span class="navbar-brand mb-0 h1">Attendance Dashboard</span>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-6">
                <h3>Attendance Records</h3>
            </div>
            <div class="col-md-6 text-right">
                <form class="form-inline" onsubmit="fetchAttendance(); return false;">
                    <label class="mr-2" for="date">Select Date:</label>
                    <input type="date" id="date" class="form-control mr-3" value="<?php echo htmlspecialchars($date_selected); ?>" onchange="fetchAttendance();">
                    
                    <label class="mr-2" for="class_period">Select Class Period:</label>
                    <select id="class_period" class="form-control mr-3" onchange="fetchAttendance();">
                        <option value="">All Periods</option>
                        <?php
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
                    <button type="submit" class="btn btn-primary">Filter</button>
                </form>
            </div>
        </div>

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
                    <!-- Attendance data populated here -->
                </tbody>
            </table>
        </div>
    </div>

    <footer class="footer bg-dark text-white text-center py-3 mt-5">
        <p>Attendance System &copy; 2024</p>
    </footer>
</body>
</html>
