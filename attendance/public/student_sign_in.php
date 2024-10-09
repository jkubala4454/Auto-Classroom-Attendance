<?php
// student_sign_in.php - Backend logic for processing sign-in

// Error Debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Always return JSON content
header('Content-Type: application/json');

// Start output buffering to capture any errors
ob_start();

include('../includes/config.php');  // Include the config file first
include(ROOT_PATH . '/includes/db.php'); // Include the database connection
include(ROOT_PATH . '/includes/functions.php');  // Include the helper functions

$response = [];  // Prepare an array for the response

try {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
        
        if (empty($student_id)) {
            throw new Exception('Student ID is required.');
        }

        $current_time = date('H:i:s');
        $current_date = date('Y-m-d');
        $current_day_type = getCurrentDayType();  // 'A Day', 'B Day', 'Weekend', or 'Holiday'

        // Check if it's a non-school day
        if ($current_day_type == 'Weekend' || $current_day_type == 'Holiday') {
            throw new Exception("No school today. Attendance cannot be marked.");
        }

        // Define the early sign-in window
        $early_sign_in_window_seconds = 15 * 60;  // 15-minute sign-in window before class begins
        $sign_in_window_seconds = 2700; // 45 minutes after class starts for valid sign-in

        // Query for class schedule matching the current day type
        $query = "SELECT * FROM class_schedule 
                  WHERE ((start_time - INTERVAL ? SECOND <= ? AND end_time >= ?) OR 
                         (start_time > ? AND TIMESTAMPDIFF(SECOND, ?, start_time) <= ?))
                  AND day_type = ?";

        $stmt = $conn->prepare($query);
        if ($stmt === false) {
            $response = [
                'status' => 'error',
                'message' => 'Failed to prepare class schedule query: ' . $conn->error
            ];
            echo json_encode($response);
            exit;
        }

        // Bind parameters: early sign-in window, current time, current time, current time, current time, sign-in window, day type
        $stmt->bind_param("issssss", $early_sign_in_window_seconds, $current_time, $current_time, $current_time, $current_time, $sign_in_window_seconds, $current_day_type);

        if (!$stmt->execute()) {
            $response = [
                'status' => 'error',
                'message' => 'Failed to execute class schedule query: ' . $stmt->error
            ];
            echo json_encode($response);
            exit;
        }

        $result = $stmt->get_result();

        // Check if there is a scheduled class
        if ($result->num_rows > 0) {
            $class = $result->fetch_assoc();
            $class_period = $class['class_period'];
            $class_start_time = $class['start_time'];

            // Query to check if the student is enrolled in this class period for the current day type
            $query_student_class = "
                SELECT sc.*
                FROM student_classes sc
                JOIN school_calendar cal ON cal.date = ?
                WHERE sc.student_id = ? AND sc.class_period = ? AND cal.day_type = ?
            ";
            
            $stmt_student_class = $conn->prepare($query_student_class);
            if (!$stmt_student_class) {
                throw new Exception('Failed to prepare student class query: ' . $conn->error);
            }

            // Bind parameters and execute the query
            $stmt_student_class->bind_param("ssss", $current_date, $student_id, $class_period, $current_day_type);
            if (!$stmt_student_class->execute()) {
                throw new Exception('Failed to execute student class query: ' . $stmt_student_class->error);
            }

            $result_student_class = $stmt_student_class->get_result();

            if ($result_student_class->num_rows > 0) {
                $time_diff = strtotime($current_time) - strtotime($class_start_time);
                $status = getAttendanceStatus($time_diff);

                // Insert attendance record
                $query_insert = "INSERT INTO attendance (student_id, class_period, status, check_in_time, date) VALUES (?, ?, ?, ?, ?)";
                $stmt_insert = $conn->prepare($query_insert);
                if (!$stmt_insert) {
                    throw new Exception('Failed to prepare attendance insert query: ' . $conn->error);
                }

                $stmt_insert->bind_param("sssss", $student_id, $class_period, $status, $current_time, $current_date);
                if ($stmt_insert->execute()) {
                    $response = [
                        'status' => 'success',
                        'message' => "Attendance marked as $status for class period $class_period."
                    ];
                } else {
                    throw new Exception('Failed to execute attendance insert query: ' . $stmt_insert->error);
                }
            } else {
                throw new Exception('Student not registered for this class period today.');
            }
        } else {
            throw new Exception('No class scheduled or sign-in too early.');
        }

        echo json_encode($response);
    }
} catch (Exception $e) {
    // Capture any thrown exceptions and send a JSON response
    $response = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
    echo json_encode($response);
}

// Check for any buffered output (HTML or otherwise)
$buffered_output = ob_get_clean();
if (!empty($buffered_output)) {
    // Log or handle the buffered output for debugging (this might include errors or warnings)
    file_put_contents('error_log.txt', $buffered_output);
}

// Ensure clean output by explicitly ending the buffer and returning JSON only
echo json_encode($response);
exit;
?>
