<?php
$conn = new mysqli("localhost","root","","volunteer_management");

// Event ID 26
$eventId = 75;
$registrationDate = "2026-02-27 17:35:05";

// List of 10 volunteer user IDs for first event
$volunteers = [
    55,56.57
];

foreach($volunteers as $userId){
    // Randomly decide if cancelled or registered
    $status = (rand(1,10) > 1) ? 'registered' : 'cancelled';
    $cancellationReason = ($status == 'cancelled') ? "Unable to attend" : null;

    $conn->query("INSERT INTO event_registrations (eventId,userId,registrationDate,status,cancellationReason)
    VALUES ($eventId,$userId,'$registrationDate','$status','$cancellationReason')");
}

// Update event_stats table
$result = $conn->query("SELECT COUNT(*) as count FROM event_registrations WHERE eventId=$eventId AND status='registered'");
$row = $result->fetch_assoc();
$joinedCount = $row['count'];

$conn->query("UPDATE event_stats SET joinedCount=$joinedCount WHERE eventId=$eventId");

echo "Registered 10 volunteers to Event ID $eventId and updated stats!";
?>