<?php
// student_sign_in.php - Backend logic for processing sign-in
include('../includes/config.php');  // Include the config file first
include(ROOT_PATH . '/includes/db.php'); // Include the database connection
include(ROOT_PATH . '/includes/functions.php');  // Include the helper functions

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $current_time = date('H:i:s');
    $current_date = date('Y-m-d');
    $current_day_type = getCurrentDayType();  // 'A Day', 'B Day', 'Weekend', or 'Holiday'

    if ($current_day_type == 'Weekend' || $current_day_type == 'Holiday') {
        echo json_encode(['status' => 'error', 'message' => "No school today. Attendance cannot be marked."]);
        exit;
    }

    $sign_in_window_seconds = 15 * 60;  // 15 minutes sign-in window before class begins

    $query = "SELECT * FROM class_schedule 
              WHERE ((start_time <= ? AND end_time >= ?) OR 
                     (start_time > ? AND TIMESTAMPDIFF(SECOND, ?, start_time) <= ?))
              AND day_type = ?";

    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        echo json_encode(['status' => 'error', 'message' => 'Query preparation failed.']);
        exit;
    }

    $stmt->bind_param("ssssss", $current_time, $current_time, $current_time, $current_time, $sign_in_window_seconds, $current_day_type);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $class = $result->fetch_assoc();
        $class_period = $class['class_period'];
        $class_start_time = $class['start_time'];

        $query_student_class = "SELECT * FROM student_classes WHERE student_id = ? AND class_period = ?";
        $stmt_student_class = $conn->prepare($query_student_class);
        if ($stmt_student_class === false) {
            echo json_encode(['status' => 'error', 'message' => 'Student class query failed.']);
            exit;
        }

        $stmt_student_class->bind_param("ss", $student_id, $class_period);
        $stmt_student_class->execute();
        $result_student_class = $stmt_student_class->get_result();

        if ($result_student_class->num_rows > 0) {
            $time_diff = strtotime($current_time) - strtotime($class_start_time);
            $status = getAttendanceStatus($time_diff);

            $query_insert = "INSERT INTO attendance (student_id, class_period, status, check_in_time, date) VALUES (?, ?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($query_insert);
            if ($stmt_insert === false) {
                echo json_encode(['status' => 'error', 'message' => 'Attendance insert query failed.']);
                exit;
            }

            $stmt_insert->bind_param("sssss", $student_id, $class_period, $status, $current_time, $current_date);
            if ($stmt_insert->execute()) {
                echo json_encode(['status' => 'success', 'message' => "Attendance marked as $status for class period $class_period."]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to mark attendance.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Student not registered for this class.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No class scheduled or sign-in too early.']);
    }

    $stmt->close();
    $conn->close();
}

function getAttendanceStatus($time_diff) {
    if ($time_diff <= 0) {  // On time or within 15 minutes of start time mark Present
        return 'Present';
    } elseif ($time_diff < 600) {   // Between 1 minute and 10 minutes late mark Tardy
        return 'Tardy';
    } elseif ($time_diff < 2700) { // Between 11 minutes and 45 minutes late mark Late or Partial Absence
        return 'L';
    } else { // More than 45 minutes late mark Absent
        return 'Absent';
    }
}
?>
