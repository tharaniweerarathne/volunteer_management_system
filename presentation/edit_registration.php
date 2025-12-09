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

$registrationData = new EventRegistrationData();
$eventLogic = new EventLogic();

// Get registration ID
$registrationId = $_GET['id'] ?? ($_POST['registrationId'] ?? 0);

if (!$registrationId) {
    header('Location: my_events.php');
    exit();
}

// Get current registration details
$registration = $registrationData->getRegistrationById($registrationId);

if (!$registration || $registration['userId'] != $_SESSION['userId']) {
    header('Location: my_events.php');
    exit();
}

// Get current event details
$currentEvent = $registrationData->getEventDetails($registration['eventId']);

// Get available events for editing
$availableEvents = $registrationData->getAvailableEventsForEdit($_SESSION['userId'], $registration['eventId']);

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_event'])) {
    $newEventId = $_POST['new_event_id'] ?? 0;
    
    if (!$newEventId) {
        $message = '<div class="alert alert-danger">Please select an event</div>';
    } else {
        // Check if user already joined new event
        if ($registrationData->isAlreadyJoined($newEventId, $_SESSION['userId'])) {
            $message = '<div class="alert alert-danger">You are already registered for the selected event</div>';
        } else {
            // Check time conflicts for new event
            $conflicts = $registrationData->checkTimeConflict($_SESSION['userId'], $newEventId);
            
            if (!empty($conflicts)) {
                $eventLogic->sendConflictEmail($_SESSION['userId'], $conflicts, 'Selected Event');
                $message = '<div class="alert alert-danger">Time conflict detected with selected event</div>';
            } else {
                // Update registration
                $result = $registrationData->updateRegistration($registrationId, $newEventId, $_SESSION['userId']);
                
                if ($result['success']) {
                    // Send confirmation email
                    $eventLogic->sendEditConfirmationEmail($_SESSION['userId'], $registration['eventId'], $newEventId);
                    
                    $message = '<div class="alert alert-success">
                                <i class="bi bi-check-circle"></i> 
                                Event registration changed successfully! A confirmation email has been sent.
                                </div>';
                    
                    // Get updated registration
                    $registration = $registrationData->getRegistrationById($result['newRegistrationId']);
                    $currentEvent = $registrationData->getEventDetails($registration['eventId']);
                    
                    // Refresh available events
                    $availableEvents = $registrationData->getAvailableEventsForEdit($_SESSION['userId'], $registration['eventId']);
                } else {
                    $message = '<div class="alert alert-danger">
                                <i class="bi bi-x-circle"></i> 
                                Failed to change event: ' . htmlspecialchars($result['message']) . '
                                </div>';
                }
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .event-card { border: 1px solid #ddd; border-radius: 5px; padding: 15px; margin-bottom: 15px; }
        .current-event { background-color: #e7f3ff; border-left: 4px solid #0d6efd; }
        .available-event { background-color: #f8f9fa; }
        .available-event:hover { background-color: #e9ecef; cursor: pointer; }
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
    <a href="my_events.php" class="btn btn-outline-secondary mb-3">
        <i class="bi bi-arrow-left"></i> Back to My Events
    </a>
    
    <h1 class="mb-4">Change Event Registration</h1>
    
    <?php echo $message; ?>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card event-card current-event">
                <div class="card-body">
                    <h5 class="card-title">Current Event</h5>
                    <h6><?php echo htmlspecialchars($currentEvent['eventName']); ?></h6>
                    <p class="mb-1"><i class="bi bi-calendar"></i> 
                        <?php echo date('F j, Y', strtotime($currentEvent['startDate'])); ?>
                    </p>
                    <p class="mb-1"><i class="bi bi-clock"></i> 
                        <?php echo date('h:i A', strtotime($currentEvent['startTime'])); ?>
                    </p>
                    <p class="mb-1"><i class="bi bi-geo-alt"></i> 
                        <?php echo htmlspecialchars($currentEvent['location']); ?>
                    </p>
                    <p class="mb-0"><i class="bi bi-people"></i> 
                        <?php echo $currentEvent['availableSlots']; ?> slots available
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Select New Event</h5>
                    
                    <?php if (empty($availableEvents)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            No other events available to switch to.
                        </div>
                    <?php else: ?>
                        <form method="POST">
                            <input type="hidden" name="registrationId" value="<?php echo $registrationId; ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">Choose a new event:</label>
                                <select name="new_event_id" class="form-control" required>
                                    <option value="">-- Select Event --</option>
                                    <?php foreach ($availableEvents as $event): ?>
                                        <option value="<?php echo $event['eventId']; ?>">
                                            <?php echo htmlspecialchars($event['eventName']); ?> 
                                            (<?php echo date('M j, Y', strtotime($event['startDate'])); ?>)
                                            - <?php echo $event['availableSlots']; ?> slots left
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i>
                                <strong>Note:</strong> Changing events will cancel your current registration 
                                and register you for the new event.
                            </div>
                            
                            <button type="submit" name="change_event" class="btn btn-primary">
                                <i class="bi bi-arrow-repeat"></i> Change to Selected Event
                            </button>
                            <a href="my_events.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (!empty($availableEvents)): ?>
    <div class="mt-4">
        <h5>Available Events</h5>
        <div class="row">
            <?php foreach ($availableEvents as $event): ?>
            <div class="col-md-4 mb-3">
                <div class="card available-event" onclick="document.querySelector('select[name=\"new_event_id\"]').value='<?php echo $event['eventId']; ?>'">
                    <div class="card-body">
                        <h6><?php echo htmlspecialchars($event['eventName']); ?></h6>
                        <p class="mb-1 small">
                            <i class="bi bi-calendar"></i> 
                            <?php echo date('M j, Y', strtotime($event['startDate'])); ?>
                        </p>
                        <p class="mb-1 small">
                            <i class="bi bi-clock"></i> 
                            <?php echo date('h:i A', strtotime($event['startTime'])); ?>
                        </p>
                        <p class="mb-1 small">
                            <i class="bi bi-geo-alt"></i> 
                            <?php echo htmlspecialchars($event['location']); ?>
                        </p>
                        <p class="mb-0 small text-success">
                            <i class="bi bi-people"></i> 
                            <?php echo $event['availableSlots']; ?> slots available
                        </p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Auto-select event when clicking on event card
    document.querySelectorAll('.available-event').forEach(card => {
        card.addEventListener('click', function() {
            const select = document.querySelector('select[name="new_event_id"]');
            const eventId = this.getAttribute('onclick').match(/'(\d+)'/)[1];
            select.value = eventId;
            
            // Highlight selected card
            document.querySelectorAll('.available-event').forEach(c => {
                c.classList.remove('border-primary');
            });
            this.classList.add('border-primary');
        });
    });
</script>
</body>
</html>