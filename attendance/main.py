import mysql.connector
import datetime

class Student:
    def __init__(self, student_id, first_name, last_name):
        self.student_id = student_id
        self.first_name = first_name
        self.last_name = last_name
        self.attendance = 0
        self.tardies = 0
        self.hall_passes = 0

    # ... (other methods remain the same)

def add_student_to_database(db_cursor, first_name, last_name):
    query = "INSERT INTO Students (first_name, last_name) VALUES (%s, %s)"
    values = (first_name, last_name)
    db_cursor.execute(query, values)
    db_connection.commit()
    return db_cursor.lastrowid

def get_student_by_id(db_cursor, student_id):
    query = "SELECT first_name, last_name FROM Students WHERE student_id = %s"
    db_cursor.execute(query, (student_id,))
    result = db_cursor.fetchone()
    if result:
        return result[0], result[1]
    return None, None

def count_active_hall_passes(db_cursor):
    query = "SELECT COUNT(*) FROM HallPasses WHERE time_in IS NULL"
    db_cursor.execute(query)
    result = db_cursor.fetchone()
    if result:
        return result[0]
    return 0

def mark_attendance(db_cursor, student_id):
    current_time = datetime.datetime.now().time()
    start_time_1st_period = datetime.time(8, 30)
    late_time_1st_period = datetime.time(8, 50)
    end_time_1st_period = datetime.time(10, 8)
    start_time_2nd_period = datetime.time(10, 13)
    end_time_2nd_period = datetime.time(12, 5)
    start_time_3rd_period = datetime.time(12, 43)
    end_time_3rd_period = datetime.time(13, 50)
    start_time_4th_period = datetime.time(14, 22)
    end_time_4th_period = datetime.time(16, 0)
    
    if current_time <= start_time_1st_period:
        status = "Present"
    elif start_time_1st_period < current_time <= late_time_1st_period:
        status = "Tardy"
    elif late_time_1st_period < current_time <= end_time_1st_period:
        status = "Partial Absence"
    elif current_time <= start_time_2nd_period:
        status = "Present"
    elif start_time_2nd_period < current_time <= end_time_2nd_period:
        status = "Tardy"
    elif current_time <= start_time_3rd_period:
        status = "Present"
    elif start_time_3rd_period < current_time <= end_time_3rd_period:
        status = "Tardy"
    elif current_time <= start_time_4th_period:
        status = "Present"
    elif start_time_4th_period < current_time <= end_time_4th_period:
        status = "Tardy"
    else:
        status = "Partial Absence"
    
    # Check if hall pass issuance is allowed
    if (current_time.minute >= 30 and
        (current_time.minute <= 59 or current_time.hour < 9) and
        (current_time.minute <= 59 or current_time.hour >= 16)):
        query = "INSERT INTO AttendanceRecords (student_id, date, status) VALUES (%s, CURDATE(), %s)"
        values = (student_id, status)
        db_cursor.execute(query, values)
        db_connection.commit()
    else:
        print("Hall passes cannot be issued during the first 30 minutes or the last 30 minutes of class.\n")

def issue_hall_pass(db_cursor, student_id, used):
    current_time = datetime.datetime.now().time()
    active_passes = count_active_hall_passes(db_cursor)
    
    if active_passes < 2 and (
        (current_time.minute >= 30 and
        (current_time.minute <= 59 or current_time.hour < 9) and
        (current_time.minute <= 59 or current_time.hour >= 16))):
        
        query = "INSERT INTO HallPasses (student_id, date_issued, time_out, used) VALUES (%s, CURDATE(), %s, %s)"
        values = (student_id, current_time, used)
        db_cursor.execute(query, values)
        db_connection.commit()
        print("Hall pass issued.\n")
    elif active_passes >= 2:
        print("Maximum number of hall passes issued. Please wait until a pass is returned.\n")
    else:
        print("Hall passes cannot be issued during the first 30 minutes or the last 30 minutes of class.\n")

def return_from_hall_pass(db_cursor, student_id):
    current_time = datetime.datetime.now().time()
    query = "UPDATE HallPasses SET time_in = %s WHERE student_id = %s AND time_in IS NULL"
    values = (current_time, student_id)
    db_cursor.execute(query, values)
    db_connection.commit()
    print("Returned from hall pass.\n")

def generate_report(db_cursor, student_id):
    query = "SELECT status, time_out, time_in FROM AttendanceRecords LEFT JOIN HallPasses ON AttendanceRecords.student_id = HallPasses.student_id WHERE AttendanceRecords.student_id = %s"
    db_cursor.execute(query, (student_id,))
    records = db_cursor.fetchall()
    
    total_absences = 0
    total_tardies = 0
    total_minutes_missed = 0
    
    for record in records:
        if record[0] == "Absent":
            total_absences += 1
        elif record[0] == "Tardy":
            total_tardies += 1
        
        if record[0] == "Partial Absence" and record[2]:
            time_out = datetime.datetime.combine(datetime.date.today(), record[1])
            time_in = datetime.datetime.combine(datetime.date.today(), record[2])
            time_difference = time_in - time_out
            total_minutes_missed += time_difference.total_seconds() / 60
    
    return total_absences, total_tardies, total_minutes_missed

def main():
    db_connection = mysql.connector.connect(
        host="your_db_host",
        user="your_db_user",
        password="your_db_password",
        database="your_db_name"
    )
    db_cursor = db_connection.cursor()

    students = []

    while True:
        print("1. Add Student")
        print("2. Mark Attendance")
        print("3. Issue Hall Pass")
        print("4. Return from Hall Pass")
        print("5. Generate Report")
        # ... (other menu options remain the same)

        choice = int(input("Enter your choice: "))

        if choice == 1:
            first_name = input("Enter first name: ")
            last_name = input("Enter last name: ")
            student_id = add_student_to_database(db_cursor, first_name, last_name)
            print(f"Student added with ID: {student_id}\n")

        elif choice == 2:
            student_id = int(input("Enter your student ID: "))
            first_name, last_name = get_student_by_id(db_cursor, student_id)
            if first_name and last_name:
                mark_attendance(db_cursor, student_id)
                print(f"Attendance marked for {first_name} {last_name}.\n")
            else:
                print("Student ID not found. Please try again.\n")

        elif choice == 3:
            student_id = int(input("Enter your student ID: "))
            first_name, last_name = get_student_by_id(db_cursor, student_id)
            if first_name and last_name:
                issue_hall_pass(db_cursor, student_id, False)

        elif choice == 4:
            student_id = int(input("Enter your student ID: "))
            first_name, last_name = get_student_by_id(db_cursor, student_id)
            if first_name and last_name:
                return_from_hall_pass(db_cursor, student_id)

        elif choice == 5:
            student_id = int(input("Enter your student ID: "))
            first_name, last_name = get_student_by_id(db_cursor, student_id)
            if first_name and last_name:
                total_absences, total_tardies, total_minutes_missed = generate_report(db_cursor, student_id)
                print(f"Report for {first_name} {last_name}:")
                print(f"Total Absences: {total_absences}")
                print(f"Total Tardies: {total_tardies}")
                print(f"Total Minutes of Class Missed: {total_minutes_missed:.2f} minutes\n")

        # ... (other menu options remain the same)

    db_cursor.close()
    db_connection.close()

if __name__ == "__main__":
    main()
