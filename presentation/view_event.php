<?php
require_once '../business_logic/eventLogic.php';

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check login
if (!isset($_SESSION['userId'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['userId'];

// Create instances of EventData and EventLogic
$eventData = new EventData();
$eventLogic = new EventLogic();

// Get user role
$userRole = $eventLogic->getUserRoleSafe();

// Get event ID
$eventId = $_GET['id'] ?? 0;
if (!$eventId) {
    header('Location: events.php');
    exit();
}

// Get event data using OOP
$event = $eventData->getEventById($eventId);
if (!$event) {
    header('Location: events.php');
    exit();
}

// Get assigned coordinators using OOP
$coordinators = $eventData->getAllCoordinators();
$assignedCoordinators = [];
if (!empty($event['coordinatorIds'])) {
    $coordinatorIds = explode(',', $event['coordinatorIds']);
    $assignedCoordinators = array_filter($coordinators, function($coord) use ($coordinatorIds) {
        return in_array($coord['userId'], $coordinatorIds);
    });
}

// Get skill name using OOP
$skills = $eventData->getAllSkills();
$skillName = 'No specific skill required';
foreach ($skills as $skill) {
    if ($skill['skillId'] == $event['requiredSkillId']) {
        $skillName = $skill['skillName'];
        break;
    }
}

// Check if event is over
$eventDateTime = $event['endDate'] . ' ' . $event['endTime'];
$isEventOver = strtotime($eventDateTime) < time();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($event['eventName']); ?> - Event Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .event-header {
            background: linear-gradient(135deg, #ff6200 0%, #994524 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 20px 20px;
        }
        .event-image {
            height: 400px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .detail-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s;
        }
        .detail-card:hover {
            transform: translateY(-5px);
        }
        .info-icon {
            font-size: 1.5rem;
            color: #667eea;
            margin-right: 10px;
        }
        .coordinator-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #667eea;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
        }
        .action-btn {
            border-radius: 25px;
            padding: 10px 25px;
            font-weight: 500;
        }
        .status-badge {
            font-size: 1rem;
            padding: 8px 20px;
            border-radius: 25px;
        }

        .logo-img { 
            height: 60px;
            width: auto; 
            margin-right: 10px; 
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="../assets/images/logo.png" alt="Logo" class="logo-img">
            </a>
            <div class="navbar-nav ms-auto">
                <a href="events.php" class="nav-link">
                    <i class="bi bi-arrow-left"></i> Back to Events
                </a>
            </div>
        </div>
    </nav>

    <!-- Event Header -->
    <div class="event-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-5 fw-bold"><?php echo htmlspecialchars($event['eventName']); ?></h1>
                    <div class="d-flex align-items-center mt-3">
                        <?php if ($isEventOver): ?>
                            <span class="status-badge bg-secondary">
                                <i class="bi bi-check-circle"></i> Event Completed
                            </span>
                        <?php else: ?>
                            <span class="status-badge bg-success">
                                <i class="bi bi-clock"></i> Upcoming Event
                            </span>
                        <?php endif; ?>
                        <span class="ms-3 status-badge bg-info">
                            <i class="bi bi-tag"></i> <?php echo htmlspecialchars($event['category'] ?? 'General'); ?>
                        </span>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="btn-group">
                        <?php if ($eventData->canUserEditEvent($eventId, $userId)): ?>
                            <a href="events.php?action=edit&id=<?php echo $eventId; ?>" 
                               class="btn btn-warning action-btn">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                        <?php endif; ?>
                        <?php if ($userRole === 'Admin'): ?>
                            <a href="events.php?action=assign&id=<?php echo $eventId; ?>" 
                               class="btn btn-primary action-btn">
                                <i class="bi bi-person-plus"></i> Assign
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <!-- Left Column: Event Image & Details -->
            <div class="col-lg-8">
                <?php if ($event['eventImage']): ?>
                    <div class="mb-4">
                        <img src="../<?php echo htmlspecialchars($event['eventImage']); ?>" 
                             class="event-image w-100" 
                             alt="<?php echo htmlspecialchars($event['eventName']); ?>">
                    </div>
                <?php endif; ?>

                <!-- Event Description -->
                <div class="card detail-card mb-4">
                    <div class="card-body">
                        <h4 class="card-title">
                            <i class="bi bi-card-text info-icon"></i> Description
                        </h4>
                        <p class="card-text fs-5">
                            <?php echo nl2br(htmlspecialchars($event['eventDescription'] ?: 'No description provided.')); ?>
                        </p>
                    </div>
                </div>

                <!-- Event Details Cards -->
                <div class="row">
                    <!-- Date & Time -->
                    <div class="col-md-6 mb-4">
                        <div class="card detail-card h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="bi bi-calendar-date info-icon"></i> Date & Time
                                </h5>
                                <div class="mt-3">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-primary text-white rounded-circle p-3 me-3">
                                            <i class="bi bi-play-circle"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted">Starts</small>
                                            <div class="fw-bold">
                                                <?php echo date('l, F j, Y', strtotime($event['startDate'])); ?>
                                            </div>
                                            <div>
                                                <i class="bi bi-clock"></i> 
                                                <?php echo date('h:i A', strtotime($event['startTime'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex align-items-center">
                                        <div class="bg-success text-white rounded-circle p-3 me-3">
                                            <i class="bi bi-check-circle"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted">Ends</small>
                                            <div class="fw-bold">
                                                <?php echo date('l, F j, Y', strtotime($event['endDate'])); ?>
                                            </div>
                                            <div>
                                                <i class="bi bi-clock"></i> 
                                                <?php echo date('h:i A', strtotime($event['endTime'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="col-md-6 mb-4">
                        <div class="card detail-card h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="bi bi-geo-alt info-icon"></i> Location
                                </h5>
                                <div class="mt-3">
                                    <div class="d-flex align-items-start">
                                        <div class="bg-info text-white rounded-circle p-3 me-3">
                                            <i class="bi bi-pin-map"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold"><?php echo htmlspecialchars($event['location']); ?></h6>
                                            <?php if ($event['googleMapLink']): ?>
                                                <a href="<?php echo htmlspecialchars($event['googleMapLink']); ?>" 
                                                   target="_blank" 
                                                   class="btn btn-outline-primary btn-sm mt-2">
                                                    <i class="bi bi-google"></i> View on Google Maps
                                                </a>
                                            <?php else: ?>
                                                <p class="text-muted mt-2">No map link provided</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Info -->
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card detail-card h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="bi bi-tools info-icon"></i> Required Skill
                                </h5>
                                <div class="mt-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-warning text-white rounded-circle p-3 me-3">
                                            <i class="bi bi-star"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold"><?php echo htmlspecialchars($skillName); ?></h6>
                                            <p class="text-muted mb-0">Skill needed for this event</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="card detail-card h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="bi bi-people info-icon"></i> Volunteers
                                </h5>
                                <div class="mt-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-danger text-white rounded-circle p-3 me-3">
                                            <i class="bi bi-person-badge"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold">
                                                <?php echo $event['maxVolunteers'] > 0 ? $event['maxVolunteers'] : 'Unlimited'; ?>
                                            </h6>
                                            <p class="text-muted mb-0">Maximum volunteers allowed</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Coordinators & Actions -->
            <div class="col-lg-4">
                <!-- Assigned Coordinators -->
                <div class="card detail-card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-person-badge"></i> Assigned Coordinators
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($assignedCoordinators)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($assignedCoordinators as $coordinator): ?>
                                    <div class="list-group-item border-0 px-0">
                                        <div class="d-flex align-items-center">
                                            <div class="coordinator-avatar">
                                                <?php echo strtoupper(substr($coordinator['name'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($coordinator['name']); ?></h6>
                                                <small class="text-muted">
                                                    <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($coordinator['email']); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <div class="text-muted mb-3">
                                    <i class="bi bi-person-x" style="font-size: 3rem;"></i>
                                </div>
                                <h6>No coordinators assigned yet</h6>
                                <p class="text-muted">Assign coordinators to manage this event</p>
                                <?php if ($userRole === 'Admin'): ?>
                                    <a href="events.php?action=assign&id=<?php echo $eventId; ?>" 
                                       class="btn btn-primary btn-sm">
                                        <i class="bi bi-person-plus"></i> Assign Now
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card detail-card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-lightning"></i> Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-3">
                            <?php if ($eventData->canUserEditEvent($eventId, $userId)): ?>
                                <a href="events.php?action=edit&id=<?php echo $eventId; ?>" 
                                   class="btn btn-warning action-btn">
                                    <i class="bi bi-pencil-square"></i> Edit Event Details
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($userRole === 'Admin'): ?>
                                <a href="events.php?action=assign&id=<?php echo $eventId; ?>" 
                                   class="btn btn-primary action-btn">
                                    <i class="bi bi-people"></i> Manage Coordinators
                                </a>
                            <?php endif; ?>
                            
                            <a href="events.php" class="btn btn-outline-secondary action-btn">
                                <i class="bi bi-arrow-left-circle"></i> Back to Events List
                            </a>
                            
                            <?php if ($eventData->canUserEditEvent($eventId, $userId)): ?>
                                <form method="POST" action="events.php" class="d-grid">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="eventId" value="<?php echo $eventId; ?>">
                                    <button type="submit" 
                                            class="btn btn-danger action-btn"
                                            onclick="return confirm('Are you sure? This will permanently delete this event.')">
                                        <i class="bi bi-trash"></i> Delete Event
                                    </button>
                                </form>
                            <?php endif; ?>
                            
                            <button onclick="window.print()" class="btn btn-outline-info action-btn">
                                <i class="bi bi-printer"></i> Print Event Details
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Event Info -->
                <div class="card detail-card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-info-circle"></i> Event Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li class="mb-3">
                                <i class="bi bi-calendar-plus text-primary"></i>
                                <strong>Created:</strong> 
                                <?php echo date('F j, Y', strtotime($event['createdAt'])); ?>
                            </li>
                            <li class="mb-3">
                                <i class="bi bi-person text-success"></i>
                                <strong>Created By:</strong> 
                                <?php 
                                    $creator = $eventData->getUserById($event['createdBy']);
                                    echo $creator ? htmlspecialchars($creator['name']) : 'Unknown';
                                ?>
                            </li>
                            <li class="mb-3">
                                <i class="bi bi-clock-history text-warning"></i>
                                <strong>Duration:</strong> 
                                <?php 
                                    $start = strtotime($event['startDate'] . ' ' . $event['startTime']);
                                    $end = strtotime($event['endDate'] . ' ' . $event['endTime']);
                                    $hours = round(($end - $start) / 3600, 1);
                                    echo $hours . ' hours';
                                ?>
                            </li>
                            <li>
                                <i class="bi bi-shield-check text-danger"></i>
                                <strong>Access:</strong> 
                                <?php 
                                    if ($eventData->canUserEditEvent($eventId, $userId)) {
                                        echo 'You can edit this event';
                                    } else {
                                        echo 'View only';
                                    }
                                ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container text-center">
            <p class="mb-0">
                <i class="bi bi-calendar-event"></i>
                &copy; <?php echo date('Y'); ?> Unity Volunteers Trust. All rights reserved. 
            </p>
            <small>Event ID: <?php echo $eventId; ?></small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>