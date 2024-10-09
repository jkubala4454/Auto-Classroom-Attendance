<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('../includes/config.php');  // Include the config file first
include(ROOT_PATH . '/includes/db.php'); // Include database connection

// Open the CSV file
$file = fopen('students_with_classes.csv', 'r');
if (!$file) {
    die('Error opening the file.');
}

// Prepare queries
$query_student = "INSERT INTO students (student_id, first_name, last_name) 
                  VALUES (?, ?, ?)
                  ON DUPLICATE KEY UPDATE first_name = VALUES(first_name), last_name = VALUES(last_name)";
$query_class = "INSERT INTO student_classes (student_id, class_period) VALUES (?, ?)";
$stmt_student = $conn->prepare($query_student);
$stmt_class = $conn->prepare($query_class);

if (!$stmt_student || !$stmt_class) {
    die('Error preparing queries: ' . $conn->error);
}

while (($data = fgetcsv($file)) !== FALSE) {
    // Read data from the CSV
    $student_id = $data[0];
    $first_name = $data[1];
    $last_name = $data[2];
    $class_periods = explode(',', $data[3]);  // Split class periods by comma

    // Insert or update student
    $stmt_student->bind_param("sss", $student_id, $first_name, $last_name);
    if (!$stmt_student->execute()) {
        echo "Error inserting/updating student " . $student_id . ": " . $stmt_student->error . "<br>";
        continue;
    }

    // Insert class enrollments
    foreach ($class_periods as $class_period) {
        $stmt_class->bind_param("ss", $student_id, $class_period);
        if (!$stmt_class->execute()) {
            echo "Error inserting class period for student " . $student_id . ": " . $stmt_class->error . "<br>";
        }
    }
}

echo "Students and classes successfully added/updated!";
fclose($file);
$stmt_student->close();
$stmt_class->close();
$conn->close();
?>
