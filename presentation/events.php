<?php
date_default_timezone_set('Asia/Colombo');
require_once '../business_logic/eventLogic.php';

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
$userRole = $_SESSION['role'] ?? $eventData->getUserRole($userId);

// Get data using OOP methods
$skills = $eventData->getAllSkills();
$coordinators = $eventData->getAllCoordinators();
$categories = $eventData->getCategories();

// Handle actions
$action = $_GET['action'] ?? '';
$eventId = $_GET['id'] ?? 0;
$message = '';
$error = '';

// Process POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'create') {
        $result = $eventLogic->handleCreateEvent();
        if ($result['success']) {
            $message = 'Event created successfully!';
            if (isset($result['warning'])) {
                $error = $result['warning'];
            }
        } else {
            $error = $result['message'];
        }
    }
    elseif ($_POST['action'] === 'update') {
        $result = $eventLogic->handleUpdateEvent($_POST['eventId']);
        if ($result['success']) {
            $message = 'Event updated successfully!';
            if (isset($result['warning'])) {
                $error = $result['warning'];
            }
        } else {
            $error = $result['message'];
        }
    }
    elseif ($_POST['action'] === 'delete') {
        $result = $eventLogic->handleDeleteEvent($_POST['eventId']);
        if ($result['success']) {
            $message = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
elseif ($_POST['action'] === 'assign') {
        if ($userRole !== 'Admin') {
            $error = 'Only admins can assign coordinators';
        } else {
            $coordinatorIds = isset($_POST['coordinators']) ? array_map('intval', $_POST['coordinators']) : [];
            $result = $eventData->assignCoordinators($_POST['eventId'], $coordinatorIds);
            
            if ($result['success']) {
                // Get event details for the message
                $eventDetails = $eventData->getEventById($_POST['eventId']);
                
                if ($eventDetails && !empty($coordinatorIds)) {
                    // Send notification messages to coordinators
                    require_once '../business_logic/MessageLogic.php';
                    $messageLogic = new MessageLogic($conn);
                    
                    $assignmentResult = $messageLogic->sendEventAssignmentMessage(
                        $userId, // admin ID
                        $coordinatorIds,
                        $eventDetails
                    );
                    
                    if ($assignmentResult['success']) {
                        $message = 'Coordinators assigned successfully! ' . $assignmentResult['message'];
                    } else {
                        $message = 'Coordinators assigned but failed to send notifications: ' . $assignmentResult['message'];
                    }
                } else {
                    $message = 'Coordinators assigned successfully!';
                }
            } else {
                $conflictMsg = '';
                foreach ($result['conflicts'] as $conflict) {
                    $conflictMsg .= $conflict['coordinatorName'] . " has conflict with '{$conflict['eventName']}' ";
                }
                $error = $conflictMsg;
            }
        }
    }
}

// Get events with filters
$filters = [
    'search' => $_GET['search'] ?? '',
    'skillId' => $_GET['skillId'] ?? '',
    'category' => $_GET['category'] ?? ''
];

$events = $eventData->getAllEvents($filters);

// Get event for editing
$editEvent = null;
if ($action === 'edit' && $eventId) {
    $editEvent = $eventData->getEventById($eventId);
    if (!$editEvent || !$eventData->canUserEditEvent($eventId, $userId)) {
        header('Location: events.php');
        exit();
    }
}

// Get event for assigning
$assignEvent = null;
if ($action === 'assign' && $eventId) {
    $assignEvent = $eventData->getEventById($eventId);
    if (!$assignEvent || $userRole !== 'Admin') {
        header('Location: events.php');
        exit();
    }
}

// Get coordinator events for dashboard
$coordinatorEvents = [];
if ($userRole === 'Coordinator') {
    $coordinatorEvents = $eventData->getEventsByCoordinator($userId);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management</title>
    <link rel="icon" type="image/png" href="../assets/images/title.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">

    <style>
        .card-img-top { height: 200px; object-fit: cover; }
        .required:after { content: " *"; color: red; }
        .logo-img { height: 60px; width: auto; margin-right: 10px; vertical-align: middle;}
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="#">
            <img src="../assets/images/logo.png" alt="Logo" class="logo-img">
        </a>
        <!-- Toggle button for mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
            aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Collapsible content -->
        <div class="collapse navbar-collapse" id="navbarContent">
            <div class="navbar-nav ms-auto">
                <?php if ($userRole === 'Admin'): ?>
                    <a class="nav-link active" href="admin_dashboard.php">Back</a>
                <?php endif; ?>
                <?php if ($userRole === 'Coordinator'): ?>
                    <a class="nav-link active" href="coordinator_dashboard.php">Back</a>
                <?php endif; ?>
                <a class="nav-link" href="events.php">Events</a>
                <?php if ($userRole === 'Coordinator'): ?>
                    <a class="nav-link" href="events.php?dashboard=1">My Dashboard</a>
                <?php endif; ?>
                <?php if (in_array($userRole, ['Admin', 'Coordinator'])): ?>
                    <a class="nav-link" href="events.php?action=create">Create Event</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

    <div class="container mt-4">
        <!-- error messages -->
        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Dashboard View for Coordinators -->
        <?php if (isset($_GET['dashboard']) && $userRole === 'Coordinator'): ?>
            <h2>My Assigned Events</h2>
            <div class="row">
                <?php foreach ($coordinatorEvents as $event): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <?php if ($event['eventImage']): ?>
                                <img src="../<?php echo htmlspecialchars($event['eventImage']); ?>" class="card-img-top">
                            <?php endif; ?>
                            <div class="card-body">
                                <?php if ($eventLogic->isEventOver($event)): ?>
                                    <span class="badge bg-secondary">Over</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Upcoming</span>
                                <?php endif; ?>
                                <h5 class="card-title"><?php echo htmlspecialchars($event['eventName']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars(substr($event['eventDescription'], 0, 100)); ?>...</p>
                                <p><strong>Date:</strong> <?php echo date('M d, Y', strtotime($event['startDate'])); ?></p>
                                <p><strong>Time:</strong> <?php echo date('h:i A', strtotime($event['startTime'])); ?></p>
                                <p><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
                                <a href="view_event.php?id=<?php echo $event['eventId']; ?>" class="btn btn-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <!-- Event Creation/Editing Form -->
        <?php elseif (in_array($action, ['create', 'edit'])): ?>
            <h2><?php echo $action === 'create' ? 'Create New Event' : 'Edit Event'; ?></h2>
            <form method="POST" enctype="multipart/form-data" class="row g-3">
                <input type="hidden" name="action" value="<?php echo $action === 'edit' ? 'update' : $action; ?>">
                <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="eventId" value="<?php echo $eventId; ?>">
                <?php endif; ?>
                
                <div class="col-md-6">
                    <label class="form-label required">Event Name</label>
                    <input type="text" name="eventName" class="form-control" placeholder="Enter the event name" required 
                           value="<?php echo $editEvent['eventName'] ?? ''; ?>">
                </div>
                
<div class="col-md-6">
    <label class="form-label">Category</label>
    <select name="category" class="form-select" id="categorySelect">
        <option value="">Select Category</option>
        <?php 
        // Get categories from database
        $categories = $eventData->getCategories();
        
        // Always show these default categories
        $defaultCats = ['Charity', 'Education', 'Environment', 'Health', 'Community', 
                       'Sports', 'Arts & Culture', 'Disaster Relief', 'Animal Welfare', 'Technology'];
        
        // Merge database categories with defaults
        $allCategories = [];
        foreach ($categories as $cat) {
            $allCategories[] = $cat['category'];
        }
        
        // Add defaults that aren't already in the list
        foreach ($defaultCats as $defaultCat) {
            if (!in_array($defaultCat, $allCategories)) {
                $allCategories[] = $defaultCat;
            }
        }
        
        // Sort alphabetically
        sort($allCategories);
        
        // Display all categories
        foreach ($allCategories as $cat):
        ?>
            <option value="<?php echo htmlspecialchars($cat); ?>"
                <?php echo (isset($editEvent['category']) && $editEvent['category'] == $cat) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($cat); ?>
            </option>
        <?php endforeach; ?>
        <option value="new_category">+ Add New Category</option>
    </select>
    
    <!-- New category input (hidden by default) -->
    <div id="newCategoryDiv" class="mt-2" style="display: none;">
        <input type="text" name="new_category" class="form-control" placeholder="Enter new category name">
    </div>
</div>
                
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="eventDescription" placeholder="Description" class="form-control" rows="3"><?php echo $editEvent['eventDescription'] ?? ''; ?></textarea>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label required">Location</label>
                    <input type="text" name="location" class="form-control" placeholder="Enter the location " required 
                           value="<?php echo $editEvent['location'] ?? ''; ?>">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Google Maps Link</label>
                    <input type="url" name="googleMapLink" class="form-control" 
                           value="<?php echo $editEvent['googleMapLink'] ?? ''; ?>" 
                           placeholder="https://maps.google.com/...">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label required">Start Date</label>
                    <input type="date" name="startDate" class="form-control" required 
                           value="<?php echo $editEvent['startDate'] ?? ''; ?>">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Start Time</label>
                    <input type="time" name="startTime" class="form-control" 
                           value="<?php echo substr($editEvent['startTime'] ?? '00:00', 0, 5); ?>">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label required">End Date</label>
                    <input type="date" name="endDate" class="form-control" required 
                           value="<?php echo $editEvent['endDate'] ?? ''; ?>">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">End Time</label>
                    <input type="time" name="endTime" class="form-control" 
                           value="<?php echo substr($editEvent['endTime'] ?? '23:59', 0, 5); ?>">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Max Volunteers</label>
                    <input type="number" name="maxVolunteers" class="form-control" min="0" 
                           value="<?php echo $editEvent['maxVolunteers'] ?? 0; ?>">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Required Skill</label>
                    <select name="requiredSkillId" class="form-select">
                        <option value="">No specific skill required</option>
                        <?php foreach ($skills as $skill): ?>
                            <option value="<?php echo $skill['skillId']; ?>"
                                <?php echo ($editEvent['requiredSkillId'] ?? '') == $skill['skillId'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($skill['skillName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Event Image</label>
                    <input type="file" name="eventImage" class="form-control" accept="image/*">
                    <?php if ($editEvent && $editEvent['eventImage']): ?>
                        <small>Current: <?php echo basename($editEvent['eventImage']); ?></small>
                    <?php endif; ?>
                </div>
                
                <?php if ($userRole === 'Admin'): ?>
                <div class="col-12">
                    <label class="form-label">Assign Coordinators</label>
                    <select name="coordinators[]" class="form-select" multiple size="4">
                        <?php foreach ($coordinators as $coordinator): 
                            $selected = false;
                            if ($editEvent && !empty($editEvent['coordinatorIds'])) {
                                $selected = in_array($coordinator['userId'], explode(',', $editEvent['coordinatorIds']));
                            }
                        ?>
                            <option value="<?php echo $coordinator['userId']; ?>" <?php echo $selected ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($coordinator['name'] . ' (' . $coordinator['email'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Hold Ctrl to select multiple</small>
                </div>
                <?php endif; ?>
                
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Save Event</button>
                    <a href="events.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>

        <!-- Assign Coordinators Form -->
        <?php elseif ($action === 'assign' && $assignEvent): ?>
            <h2>Assign Coordinators to: <?php echo htmlspecialchars($assignEvent['eventName']); ?></h2>
            
            <div class="card mb-4">
                <div class="card-body">
                    <p><strong>Date:</strong> <?php echo date('M d, Y', strtotime($assignEvent['startDate'])); ?> 
                       to <?php echo date('M d, Y', strtotime($assignEvent['endDate'])); ?></p>
                    <p><strong>Time:</strong> <?php echo date('h:i A', strtotime($assignEvent['startTime'])); ?> 
                       to <?php echo date('h:i A', strtotime($assignEvent['endTime'])); ?></p>
                </div>
            </div>
            
            <form method="POST">
                <input type="hidden" name="action" value="assign">
                <input type="hidden" name="eventId" value="<?php echo $eventId; ?>">
                
                <div class="mb-3">
                    <label class="form-label">Select Coordinators</label>
                    <select name="coordinators[]" class="form-select" multiple size="6">
                        <?php foreach ($coordinators as $coordinator): 
                            $selected = !empty($assignEvent['coordinatorIds']) && 
                                       in_array($coordinator['userId'], explode(',', $assignEvent['coordinatorIds']));
                        ?>
                            <option value="<?php echo $coordinator['userId']; ?>" <?php echo $selected ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($coordinator['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">Save Assignments</button>
                <a href="events.php" class="btn btn-secondary">Back</a>
            </form>

        <!-- main events list -->
        <?php else: ?>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Events Management</h2>
                <?php if (in_array($userRole, ['Admin', 'Coordinator'])): ?>
                    <a href="events.php?action=create" class="btn btn-success">+ Create Event</a>
                <?php endif; ?>
                <?php if (in_array($userRole, ['Admin', 'Coordinator'])): ?>
                    <a href="export_csv.php?type=events_filtered" class="btn btn-primary">Export Data as CSV</a>
                <?php endif; ?>
                <?php if ($userRole === 'Coordinator'): ?>
                    <a href="events.php?dashboard=1" class="btn btn-info">My Dashboard</a>
                <?php endif; ?>
            </div>

            <!-- search form -->
            <form method="GET" class="row g-3 mb-4 p-3 border rounded bg-light">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search events..." 
                           value="<?php echo htmlspecialchars($filters['search']); ?>">
                </div>
                
                <div class="col-md-3">
                    <select name="skillId" class="form-select">
                        <option value="">All Skills</option>
                        <?php foreach ($skills as $skill): ?>
                            <option value="<?php echo $skill['skillId']; ?>" 
                                <?php echo $filters['skillId'] == $skill['skillId'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($skill['skillName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['category']); ?>" 
                                <?php echo $filters['category'] == $cat['category'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['category']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary"><i class="ri-search-line search-icon"></i> Search</button>
                    <a href="events.php" class="btn btn-secondary"><i class="ri-refresh-line"></i> Clear</a>
                </div>
            </form>

            <!-- events Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Event Name</th>
                            <th>Category</th>
                            <th>Location</th>
                            <th>Date</th>
                            <th>Skill</th>
                            <th>Coordinators</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($event['eventName']); ?></strong>
                                <?php if ($event['eventImage']): ?>
                                    <br><small class="text-muted">📷 Has Image</small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($event['category'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($event['location']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($event['startDate'])); ?></td>
                            <td><?php echo htmlspecialchars($event['skillName'] ?? 'Any'); ?></td>
                            <td>
                                <?php if (!empty($event['coordinators'])): ?>
                                    <?php echo htmlspecialchars($event['coordinators']); ?>
                                <?php else: ?>
                                    <span class="text-danger">Not Assigned</span>
                                <?php endif; ?>
                            </td>
<td>
    <?php if ($eventLogic->isEventOver($event)): ?>
        <span class="badge bg-secondary">Over</span>
    <?php else: ?>
        <span class="badge bg-success">Upcoming</span>
    <?php endif; ?>
</td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="view_event.php?id=<?php echo $event['eventId']; ?>" class="btn btn-info"><i class="ri-information-line"></i> View Details</a>

                                            <a href="view_volunteers.php?eventId=<?php echo $event['eventId']; ?>" class="btn btn-outline-danger"><i class="ri-user-heart-line"></i> View Volunteers</a>
                                    
                                    <?php if ($eventData->canUserEditEvent($event['eventId'], $userId)): ?>
                                        <a href="events.php?action=edit&id=<?php echo $event['eventId']; ?>" 
                                           class="btn btn-success"><i class="ri-pencil-line"></i> Edit</a>
                                        
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="eventId" value="<?php echo $event['eventId']; ?>">
                                            <button type="submit" class="btn btn-danger" 
                                                    onclick="return confirm('Delete this event?')"><i class="ri-delete-bin-6-line"></i> Delete</button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <?php if ($userRole === 'Admin'): ?>
                                        <a href="events.php?action=assign&id=<?php echo $event['eventId']; ?>" 
                                           class="btn btn-primary"><i class="ri-user-add-line"></i> Assign</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Handle new category selection
    document.getElementById('categorySelect').addEventListener('change', function() {
        const newCategoryDiv = document.getElementById('newCategoryDiv');
        const newCategoryInput = document.querySelector('input[name="new_category"]');
        
        if (this.value === 'new_category') {
            newCategoryDiv.style.display = 'block';
            newCategoryInput.required = true;
            // Clear the select so the new category name will be used
            this.name = 'category_old';
            newCategoryInput.name = 'category';
        } else {
            newCategoryDiv.style.display = 'none';
            newCategoryInput.required = false;
            this.name = 'category';
            newCategoryInput.name = 'new_category';
        }
    });
</script>
</body>
</html>