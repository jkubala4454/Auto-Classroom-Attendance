confirmDay = "n"
#TODO Setup MySQL Database

# Start of Day
while (confirmDay.casefold() != "y"):
    day = "z"
    sched = "z"

    # Determine A Day or B Day
    while (day.casefold() != "a") and (day.casefold() != "b"):
        day = input("Is today an A day or B day? ")

    print("Today is a(n) " + str(day.upper()) + " day.")
    # Determine Normal Schedule, Pep Rally Schedule, or Half-Day Schedule
    while (sched.casefold() != "n") and (sched.casefold() != "p") and (sched.casefold() != "h"):
        sched = input("What schedule is running today? (N)ormal (P)ep Rally (H)alf Day ")

    print ("You said that today is a(n) " + str(day.upper()) + " day running on " + str(sched.upper()) + " schedule.")
    confirmDay = input("Enter Y to confirm or any other key to try again. ")

#TODO Main Loop
# All students marked as Absent at beginning of class
# Show list of students enrolled in current class on screen and color coded

#TODO
# Ask for studentID
# FUNCTION Verify student is in database
# FUNCTION Compare studentID to Class based on Schedule to verify student is in class
# FUNCTION compare Timestamp to schedule and determine attendance status (AttendStat):
# ie Present On-Time, Present Tardy, Present Late, Absent
# Store Timestamp, studentID, AttendStat in DB
