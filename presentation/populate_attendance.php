<?php
$conn = new mysqli("localhost","root","","volunteer_management");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Event ID
$eventId = 128;
$attendanceDate = '2026-03-02'; // Event date
$markedBy = 45; // Admin/Coordinator ID who marks attendance
$markedAt = "2026-03-02 15:45:05";

// Get all registered volunteers for this event
$result = $conn->query("SELECT userId FROM event_registrations WHERE eventId=$eventId AND status='registered'");

while($row = $result->fetch_assoc()){
    $userId = $row['userId'];

    // Randomly decide Present or Absent (90% present)
    $status = (rand(1,10) > 1) ? 'Present' : 'Absent';
    $remarks = ($status == 'Absent') ? 'Did not attend' : '';

    $conn->query("INSERT INTO attendance (eventId, userId, attendanceDate, status, markedBy, markedAt, remarks)
    VALUES ($eventId, $userId, '$attendanceDate', '$status', $markedBy, '$markedAt', '$remarks')");
}

echo "Attendance marked for all registered volunteers for Event ID $eventId!";
?>