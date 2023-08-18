-- Create the database
CREATE DATABASE StudentManagementSystem;

-- Use the newly created database
USE StudentManagementSystem;

-- Create the Students table
CREATE TABLE Students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL
);

-- Create the AttendanceRecords table
CREATE TABLE AttendanceRecords (
    record_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    date DATE,
    status VARCHAR(20),
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES Students(student_id)
);

-- Create the HallPasses table
CREATE TABLE HallPasses (
    pass_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    date_issued DATE,
    time_out TIME,
    time_in TIME,
    used BOOLEAN,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES Students(student_id)
);
