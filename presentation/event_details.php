<?php
error_reporting(E_ALL & ~E_NOTICE);

// Include eventLogic.php FIRST
require_once '../business_logic/eventLogic.php';

// Turn error reporting back
error_reporting(E_ALL);

// Check if user is logged in
if (!isset($_SESSION['userId'])) {
    header('Location: sign_in.php');
    exit();
}

// Include other files
require_once '../data_access/eventRegistrationData.php';

$eventId = $_GET['id'] ?? 0;
$registrationData = new EventRegistrationData();
$eventLogic = new EventLogic();

// Get event details
$event = $registrationData->getEventDetails($eventId);
if (!$event) {
    header('Location: events_volunteer.php');
    exit();
}

// Check if user already joined
$alreadyJoined = $registrationData->isAlreadyJoined($eventId, $_SESSION['userId']);

// Check if user has CANCELLED this event before
global $conn;
$cancelledSql = "SELECT registrationId FROM event_registrations 
                 WHERE eventId = ? AND userId = ? AND status = 'cancelled'";
$cancelledStmt = $conn->prepare($cancelledSql);
$cancelledStmt->bind_param("ii", $eventId, $_SESSION['userId']);
$cancelledStmt->execute();
$wasCancelled = $cancelledStmt->get_result()->num_rows > 0;

// Check for time conflicts
$conflicts = $registrationData->checkTimeConflict($_SESSION['userId'], $eventId);

// Handle registration
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['join_event'])) {
    
    // Double check if already joined
    if ($alreadyJoined) {
        $message = '<div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> 
                    You are already registered for this event.
                    </div>';
    }
    // Check if event has available slots
    elseif ($event['availableSlots'] <= 0) {
        $message = '<div class="alert alert-warning">
                    <i class="bi bi-info-circle"></i> 
                    This event is currently full.
                    </div>';
    }
    // Check for time conflicts
    elseif (!empty($conflicts)) {
        // Send conflict email
        $eventLogic->sendConflictEmail($_SESSION['userId'], $conflicts, $event['eventName']);
        $message = '<div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> 
                    Time conflict detected! Cannot join this event.
                    An email has been sent with details.
                    </div>';
    }
else {
    // Try to register
    $result = $registrationData->insertRegistration($eventId, $_SESSION['userId']);
    
    if ($result['success']) {
        
        // Send appropriate email based on whether it's a re-join or new registration
        if ($wasCancelled) {
            // Send re-join email
            $emailSent = $eventLogic->sendRejoinEmail($_SESSION['userId'], $eventId);
            $message = '<div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> 
                        Successfully re-joined the event!';
        } else {
            // Send regular registration email
            $emailSent = $eventLogic->sendRegistrationEmail($_SESSION['userId'], $eventId);
            $message = '<div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> 
                        Successfully joined the event!';
        }
        
        if ($emailSent) {
            $message .= ' A confirmation email has been sent.';
        }
        
        $message .= '</div>';
        $alreadyJoined = true;
        $wasCancelled = false; // Reset since they joined again
    } else {
        $message = '<div class="alert alert-danger">
                    <i class="bi bi-x-circle"></i> 
                    Failed to join event. You may already be registered.
                    </div>';
    }
}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .event-image { max-height: 400px; object-fit: cover; }
        .slot-available { color: green; }
        .slot-full { color: red; }
        .conflict-alert { background-color: #fff3cd; border-color: #ffc107; }
        .btn-join-again { background-color: #ffc107; border-color: #ffc107; }
        .btn-join-again:hover { background-color: #e0a800; border-color: #d39e00; }
    </style>
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
            <a class="nav-link" href="my_events.php">My Events</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <a href="events_volunteer.php" class="btn btn-outline-secondary mb-3">
        <i class="bi bi-arrow-left"></i> Back to Events
    </a>
    
    <?php echo $message; ?>
    
    <?php if (!empty($conflicts) && !$alreadyJoined): ?>
    <div class="alert alert-warning conflict-alert">
        <h5><i class="bi bi-exclamation-triangle"></i> Warning: Time Conflict</h5>
        <p>This event conflicts with:</p>
        <ul>
            <?php foreach ($conflicts as $conflict): ?>
            <li><?php echo htmlspecialchars($conflict['eventName']); ?> 
                (<?php echo date('M j, Y', strtotime($conflict['startDate'])); ?>)</li>
            <?php endforeach; ?>
        </ul>
        <p>You cannot join events that overlap in time.</p>
    </div>
    <?php endif; ?>
    
    <div class="card">
        <?php if ($event['eventImage']): ?>
            <img src="../<?php echo htmlspecialchars($event['eventImage']); ?>" 
                 class="card-img-top event-image" 
                 alt="<?php echo htmlspecialchars($event['eventName']); ?>">
        <?php endif; ?>
        
        <div class="card-body">
            <h1 class="card-title"><?php echo htmlspecialchars($event['eventName']); ?></h1>
            
            <?php if ($event['category']): ?>
                <span class="badge bg-primary mb-2"><?php echo htmlspecialchars($event['category']); ?></span>
            <?php endif; ?>
            
            <?php if ($event['skillName']): ?>
                <span class="badge bg-info mb-2"><?php echo htmlspecialchars($event['skillName']); ?></span>
            <?php endif; ?>
            
            <p class="card-text mt-3"><?php echo nl2br(htmlspecialchars($event['eventDescription'])); ?></p>
            
            <div class="row mt-4">
                <div class="col-md-6">
                    <h5>Event Details</h5>
                    <ul class="list-unstyled">
                        <li><strong>Start Date:</strong> <?php echo date('F j, Y', strtotime($event['startDate'])); ?></li>
                        <li><strong>End Date:</strong> <?php echo date('F j, Y', strtotime($event['endDate'])); ?></li>
                        <li><strong>Time:</strong> <?php echo date('h:i A', strtotime($event['startTime'])); ?> 
                            to <?php echo date('h:i A', strtotime($event['endTime'])); ?></li>
                        <li><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></li>
                        <?php if ($event['googleMapLink']): ?>
                            <li><strong>Map:</strong> <a href="<?php echo htmlspecialchars($event['googleMapLink']); ?>" target="_blank">View on Google Maps</a></li>
                        <?php endif; ?>
                        <li><strong>Required Skill:</strong> <?php echo htmlspecialchars($event['skillName'] ?? 'None'); ?></li>
                        <li><strong>Coordinators:</strong> <?php echo htmlspecialchars($event['coordinators'] ?? 'Not assigned'); ?></li>
                        <li><strong>Available Slots:</strong> 
                            <span class="<?php echo ($event['availableSlots'] > 0) ? 'slot-available' : 'slot-full'; ?>">
                                <?php echo $event['availableSlots']; ?> available
                            </span>
                        </li>
                        <li><strong>Total Volunteers:</strong> <?php echo $event['joinedCount'] ?? 0; ?> / <?php echo $event['maxVolunteers']; ?></li>
                    </ul>
                </div>
            </div>
            
            <div class="mt-4">
                <?php if ($alreadyJoined): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> You are registered for this event.
                    </div>
                    <a href="my_events.php" class="btn btn-primary">
                        <i class="bi bi-calendar-event"></i> View My Events
                    </a>
                <?php elseif ($wasCancelled): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> 
                        You previously cancelled this event. You can join again!
                    </div>
                    <?php if ($event['availableSlots'] > 0 && empty($conflicts)): ?>
                        <form method="POST">
                            <button type="submit" name="join_event" class="btn btn-warning btn-lg btn-join-again">
                                <i class="bi bi-arrow-clockwise"></i> Join Again
                            </button>
                            <p class="text-muted mt-2">
                                <i class="bi bi-info-circle"></i> 
                                You can re-join this event since you cancelled previously.
                            </p>
                        </form>
                    <?php elseif (!empty($conflicts)): ?>
                        <button class="btn btn-secondary btn-lg" disabled>
                            <i class="bi bi-x-circle"></i> Cannot Join (Time Conflict)
                        </button>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-info-circle"></i> This event is currently full.
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <?php if ($event['availableSlots'] > 0 && empty($conflicts)): ?>
                        <form method="POST">
                            <button type="submit" name="join_event" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle"></i> Join This Event
                            </button>
                            <p class="text-muted mt-2">
                                <i class="bi bi-info-circle"></i> 
                                You will receive a confirmation email after joining.
                            </p>
                        </form>
                    <?php elseif (!empty($conflicts)): ?>
                        <button class="btn btn-secondary btn-lg" disabled>
                            <i class="bi bi-x-circle"></i> Cannot Join (Time Conflict)
                        </button>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-info-circle"></i> This event is currently full.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>