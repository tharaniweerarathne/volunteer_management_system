<?php
// presentation/edit_registration.php

error_reporting(E_ALL & ~E_NOTICE);
require_once '../business_logic/eventLogic.php';
error_reporting(E_ALL);

if (!isset($_SESSION['userId'])) {
    header('Location: sign_in.php');
    exit();
}

require_once '../data_access/eventRegistrationData.php';

$registrationId = $_GET['id'] ?? 0;
$registrationData = new EventRegistrationData();
$eventLogic = new EventLogic();

// Get registration details
$registration = $registrationData->getRegistrationById($registrationId);
if (!$registration || $registration['userId'] != $_SESSION['userId']) {
    header('Location: my_events.php');
    exit();
}

// Check if registration is active
if ($registration['status'] !== 'registered') {
    header('Location: my_events.php');
    exit();
}

// Get available events for editing
$availableEvents = $registrationData->getAvailableEventsForEdit(
    $_SESSION['userId'], 
    $registration['eventId']
);

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_event'])) {
    $newEventId = $_POST['new_event_id'];
    
    // Validate new event
    if ($newEventId == $registration['eventId']) {
        $message = '<div class="alert alert-warning">Please select a different event.</div>';
    } else {
        // Check if new event has available slots
        $newEvent = $registrationData->getEventDetails($newEventId);
        if ($newEvent['availableSlots'] <= 0) {
            $message = '<div class="alert alert-warning">Selected event is full.</div>';
        } else {
            // Update registration
            $result = $registrationData->updateRegistration(
                $registrationId, 
                $newEventId, 
                $_SESSION['userId']
            );
            
            if ($result['success']) {
                // Send email to volunteer
                $eventLogic->sendEditConfirmationEmail(
                    $_SESSION['userId'],
                    $registration['eventId'],
                    $newEventId
                );
                
                // ✅ SEND INTERNAL MESSAGE FOR EVENT CHANGE
                $messageSent = $eventLogic->notifyVolunteerEventChange(
                    $_SESSION['userId'],
                    $registration['eventId'],
                    $newEventId
                );
                
                $successMsg = 'Event registration changed successfully! ';
                $successMsg .= 'A confirmation email has been sent.';
                
                if ($messageSent) {
                    $successMsg .= ' Check your messages for details.';
                }
                
                $_SESSION['success'] = $successMsg;
                header('Location: my_events.php');
                exit();
            } else {
                $message = '<div class="alert alert-danger">Failed to change event: ' . 
                          htmlspecialchars($result['message'] ?? 'Unknown error') . '</div>';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Event Registration</title>
    <link rel="icon" type="image/png" href="../assets/images/title.png">
    <link rel="stylesheet" href="../assets/css/edit_registration.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">

</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="#">
            <img src="../assets/images/logo.png" alt="Logo" style="height: 40px;">
        </a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="volunteer_dashboard.php">Dashboard</a>
            <a class="nav-link" href="events_volunteer.php">Browse Events</a>
            <a class="nav-link active" href="my_events.php">My Events</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <a href="my_events.php" class="btn btn-outline-secondary mb-3">
        <i class="bi bi-arrow-left"></i> Back to My Events
    </a>
    
    <?php echo $message; ?>
    
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">Change Event Registration</h4>
        </div>
        <div class="card-body">
            <!-- Current Event -->
            <div class="current-event mb-4">
                <h5>Current Event</h5>
                <p><strong><?php echo htmlspecialchars($registration['eventName']); ?></strong></p>
                <p><i class="bi bi-calendar"></i> 
                   <?php echo date('F j, Y', strtotime($registration['startDate'])); ?> at 
                   <?php echo date('h:i A', strtotime($registration['startTime'])); ?></p>
                <p><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($registration['location']); ?></p>
            </div>
            
            <!-- Available Events Form -->
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Select New Event:</label>
                    
                    <?php if (empty($availableEvents)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            No available events to switch to. All other events are either full, 
                            have time conflicts, or have already passed.
                        </div>
                    <?php else: ?>
                        <?php foreach ($availableEvents as $event): ?>
                            <div class="event-option">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" 
                                           name="new_event_id" 
                                           value="<?php echo $event['eventId']; ?>"
                                           id="event<?php echo $event['eventId']; ?>"
                                           required>
                                    <label class="form-check-label" for="event<?php echo $event['eventId']; ?>">
                                        <strong><?php echo htmlspecialchars($event['eventName']); ?></strong>
                                        <?php if ($event['category']): ?>
                                            <span class="badge bg-primary"><?php echo htmlspecialchars($event['category']); ?></span>
                                        <?php endif; ?>
                                        <br>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar"></i> 
                                            <?php echo date('F j, Y', strtotime($event['startDate'])); ?> at 
                                            <?php echo date('h:i A', strtotime($event['startTime'])); ?>
                                            <br>
                                            <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($event['location']); ?>
                                            <br>
                                            <i class="bi bi-people"></i> 
                                            Available slots: <?php echo $event['availableSlots']; ?> / <?php echo $event['maxVolunteers']; ?>
                                        </small>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Note:</strong> Changing events will cancel your current registration 
                    and register you for the new event. You will receive confirmation messages.
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" name="change_event" class="btn btn-primary" 
                            <?php echo empty($availableEvents) ? 'disabled' : ''; ?>>
                        <i class="bi bi-check-circle"></i> Change Event
                    </button>
                    <a href="my_events.php" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>