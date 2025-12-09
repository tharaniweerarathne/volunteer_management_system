<?php
// presentation/my_events.php

// Suppress the session notice temporarily
error_reporting(E_ALL & ~E_NOTICE);

// Include eventLogic.php FIRST (it has session_start())
require_once '../business_logic/eventLogic.php';

// Turn error reporting back if needed
error_reporting(E_ALL);

// Check if user is logged in
if (!isset($_SESSION['userId'])) {
    header('Location: sign_in.php');
    exit();
}

// Include other files
require_once '../data_access/eventRegistrationData.php';

$registrationData = new EventRegistrationData();
$eventLogic = new EventLogic();
// Get volunteer's events
$events = $registrationData->getVolunteerEvents($_SESSION['userId']);

// Handle cancellation
$successMsg = '';
$errorMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_registration'])) {
    $registrationId = $_POST['registrationId'];
    $reason = $_POST['cancellation_reason'] ?? null;
    
    // Get event details before cancellation
    $registration = $registrationData->getRegistrationById($registrationId);
    
    if ($registration && $registration['userId'] == $_SESSION['userId']) {
        $success = $registrationData->cancelRegistration($registrationId, $reason);
        
        if ($success) {
            // Send cancellation email
            $eventLogic->sendCancellationEmail($_SESSION['userId'], $registration['eventId'], $reason);
            
            $successMsg = 'Registration cancelled successfully! A confirmation email has been sent.';
            
            // Refresh events
            $events = $registrationData->getVolunteerEvents($_SESSION['userId']);
        } else {
            $errorMsg = 'Failed to cancel registration.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .event-card { transition: transform 0.2s; }
        .event-card:hover { transform: translateY(-5px); }
        .badge-status { font-size: 0.8em; }
        .cancelled { opacity: 0.7; }
        .alert { margin-top: 20px; }
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
            <a class="nav-link active" href="my_events.php">My Events</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h1 class="mb-4">My Events</h1>
    
    <?php if ($successMsg): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle"></i> <?php echo $successMsg; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($errorMsg): ?>
        <div class="alert alert-danger">
            <i class="bi bi-x-circle"></i> <?php echo $errorMsg; ?>
        </div>
    <?php endif; ?>
    
    <?php if (empty($events)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> 
            You haven't joined any events yet. 
            <a href="events_volunteer.php" class="alert-link">Browse events</a> to get started!
        </div>
    <?php else: ?>
        <div class="row">
            <?php 
            $upcomingCount = 0;
            $completedCount = 0;
            $cancelledCount = 0;
            
            foreach ($events as $event): 
                $eventEnd = $event['endDate'] . ' ' . $event['endTime'];
                $isUpcoming = strtotime($eventEnd) > time();
            ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 event-card <?php echo ($event['status'] === 'cancelled') ? 'cancelled' : ''; ?>">
                        <?php if ($event['eventImage']): ?>
                            <img src="../<?php echo htmlspecialchars($event['eventImage']); ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($event['eventName']); ?>"
                                 style="height: 200px; object-fit: cover;">
                        <?php endif; ?>
                        
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($event['eventName']); ?></h5>
                            
                            <?php if ($event['status'] === 'cancelled'): ?>
                                <span class="badge bg-danger badge-status">Cancelled</span>
                                <?php $cancelledCount++; ?>
                            <?php elseif ($isUpcoming): ?>
                                <span class="badge bg-success badge-status">Upcoming</span>
                                <?php $upcomingCount++; ?>
                            <?php else: ?>
                                <span class="badge bg-secondary badge-status">Completed</span>
                                <?php $completedCount++; ?>
                            <?php endif; ?>
                            
                            <?php if ($event['category']): ?>
                                <span class="badge bg-primary badge-status"><?php echo htmlspecialchars($event['category']); ?></span>
                            <?php endif; ?>
                            
                            <p class="card-text mt-3">
                                <small>
                                    <i class="bi bi-calendar"></i> 
                                    <?php echo date('M j, Y', strtotime($event['startDate'])); ?><br>
                                    <i class="bi bi-clock"></i> 
                                    <?php echo date('h:i A', strtotime($event['startTime'])); ?><br>
                                    <i class="bi bi-geo-alt"></i> 
                                    <?php echo htmlspecialchars($event['location']); ?><br>
                                    <i class="bi bi-person-check"></i> 
                                    Registered: <?php echo date('M j, Y', strtotime($event['registrationDate'])); ?>
                                </small>
                            </p>
                        </div>
                        
<div class="card-footer bg-transparent border-top-0">
    <?php if ($event['status'] === 'cancelled'): ?>
        <?php 
        // Check if event is still available for joining
        $eventDetails = $registrationData->getEventDetails($event['eventId']);
        $canRejoin = $eventDetails['availableSlots'] > 0;
        ?>
        <?php if ($canRejoin): ?>
            <a href="event_details.php?id=<?php echo $event['eventId']; ?>" 
               class="btn btn-success btn-sm">
                <i class="bi bi-arrow-clockwise"></i> Join Again
            </a>
        <?php else: ?>
            <button class="btn btn-secondary btn-sm" disabled title="Event is full">
                <i class="bi bi-arrow-clockwise"></i> Join Again
            </button>
        <?php endif; ?>
    <?php elseif ($event['status'] === 'registered' && $isUpcoming): ?>
        <a href="edit_registration.php?id=<?php echo $event['registrationId']; ?>" 
           class="btn btn-warning btn-sm">
            <i class="bi bi-arrow-repeat"></i> Change Event
        </a>
        <button type="button" class="btn btn-danger btn-sm" 
                data-bs-toggle="modal" 
                data-bs-target="#cancelModal<?php echo $event['registrationId']; ?>">
            <i class="bi bi-x-circle"></i> Cancel
        </button>
    <?php endif; ?>
    <a href="event_details.php?id=<?php echo $event['eventId']; ?>" 
       class="btn btn-info btn-sm">
        <i class="bi bi-eye"></i> View
    </a>
</div>
                    </div>
                </div>
                
                <!-- Cancel Modal -->
                <?php if ($event['status'] === 'registered' && $isUpcoming): ?>
                <div class="modal fade" id="cancelModal<?php echo $event['registrationId']; ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST">
                                <div class="modal-header">
                                    <h5 class="modal-title">Cancel Registration</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Are you sure you want to cancel your registration for:</p>
                                    <p><strong><?php echo htmlspecialchars($event['eventName']); ?></strong></p>
                                    <div class="mb-3">
                                        <label class="form-label">Reason (Optional):</label>
                                        <textarea name="cancellation_reason" class="form-control" rows="3" 
                                                  placeholder="Why are you cancelling? (Optional)"></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <input type="hidden" name="registrationId" value="<?php echo $event['registrationId']; ?>">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, Keep</button>
                                    <button type="submit" name="cancel_registration" class="btn btn-danger">Yes, Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        
        <!-- Summary Stats -->
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">Event Summary</h5>
                <div class="row text-center">
                    <div class="col-md-4">
                        <h3><?php echo $upcomingCount; ?></h3>
                        <p class="text-muted">Upcoming Events</p>
                    </div>
                    <div class="col-md-4">
                        <h3><?php echo $completedCount; ?></h3>
                        <p class="text-muted">Completed Events</p>
                    </div>
                    <div class="col-md-4">
                        <h3><?php echo $cancelledCount; ?></h3>
                        <p class="text-muted">Cancelled Events</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>