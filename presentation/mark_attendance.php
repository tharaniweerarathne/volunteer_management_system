<?php
session_start();
require_once '../business_logic/attendanceLogic.php';

// checking if user is logged in and is a coordinator
if (!isset($_SESSION['userId']) || $_SESSION['role'] !== 'Coordinator') {
    header('Location: login.php');
    exit();
}

$attendanceLogic = new AttendanceLogic();
$message = '';
$success = false;

// handle date selection
$selectedDate = $_GET['date'] ?? date('Y-m-d');

// get events for the selected date
$eventsResult = $attendanceLogic->getCoordinatorEvents($selectedDate);
$events = $eventsResult['success'] ? $eventsResult['events'] : [];

// handle event selection
$selectedEventId = $_GET['eventId'] ?? null;
$volunteers = [];
$eventDetails = null;
$searchTerm = $_GET['search'] ?? ''; // Get search term

if ($selectedEventId) {
    $volunteersResult = $attendanceLogic->getEventVolunteers($selectedEventId, $searchTerm);
    if ($volunteersResult['success']) {
        $volunteers = $volunteersResult['volunteers'];
        $eventDetails = $volunteersResult['event'] ?? null;
    }
}

// handle attendance submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_attendance'])) {
    $eventId = $_POST['eventId'];
    $attendances = [];
    
    foreach ($_POST['attendance'] as $userId => $status) {
        $attendances[$userId] = [
            'status' => $status,
            'remarks' => $_POST['remarks'][$userId] ?? ''
        ];
    }
    
    $result = $attendanceLogic->markAttendance($eventId, $attendances, $selectedDate);
    
    if ($result['success']) {
        $message = $result['message'];
        $success = true;
        
        // Refresh volunteers list
        $volunteersResult = $attendanceLogic->getEventVolunteers($selectedEventId, $searchTerm);
        if ($volunteersResult['success']) {
            $volunteers = $volunteersResult['volunteers'];
        }
    } else {
        $message = $result['message'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance - Unity Volunteers Trust</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .status-completed { color: #6c757d; }
        .status-ongoing { color: #198754; font-weight: bold; }
        .status-upcoming { color: #0d6efd; }
        .attendance-table th { background-color: #f8f9fa; }
        .present-checkbox:checked { background-color: #198754; border-color: #198754; }
        .header-bar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 0;
            margin-bottom: 30px;
        }
        .search-highlight {
            background-color: #fff3cd;
            font-weight: bold;
        }
        .volunteer-count {
            font-size: 0.9rem;
            color: #6c757d;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="#">
            <img src="../assets/images/logo.png" alt="Logo" style="height: 40px;">
        </a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="coordinator_dashboard.php">Back</a>
        </div>
    </div>
</nav>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- date selection card -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-calendar"></i> Select Date</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-6">
                                <label for="date" class="form-label">Date</label>
                                <input type="date" class="form-control" id="date" name="date" 
                                       value="<?php echo htmlspecialchars($selectedDate); ?>"
                                       max="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i> View Events
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- events list card -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-calendar-event"></i> Events on <?php echo date('F j, Y', strtotime($selectedDate)); ?></h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($events)): ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i> No events found for the selected date.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Event Name</th>
                                            <th>Organizer</th>
                                            <th>Time</th>
                                            <th>Location</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($events as $event): ?>
                                            <tr class="<?php echo $selectedEventId == $event['eventId'] ? 'table-active' : ''; ?>">
                                                <td>
                                                    <strong><?php echo htmlspecialchars($event['eventName']); ?></strong><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($event['category']); ?></small>
                                                </td>
                                                <td>
    <?php 
        $organizer = $attendanceLogic->getUserById($event['createdBy']);
        if ($organizer):
    ?>
        <small class="text-muted">
            Organizer: <?php echo htmlspecialchars($organizer['name']); ?> 
            (<?php echo htmlspecialchars($organizer['email']); ?>)
        </small>
    <?php else: ?>
        <small class="text-muted">Organizer: Unknown</small>
    <?php endif; ?>
</td>
                                                <td>
                                                    <?php echo date('h:i A', strtotime($event['startTime'])); ?> - 
                                                    <?php echo date('h:i A', strtotime($event['endTime'])); ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($event['location']); ?></td>
                                                <td>
                                                    <span class="status-<?php echo strtolower($event['status']); ?>">
                                                        <?php echo $event['status']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="?date=<?php echo $selectedDate; ?>&eventId=<?php echo $event['eventId']; ?>#volunteers"
                                                       class="btn btn-sm btn-<?php echo $selectedEventId == $event['eventId'] ? 'secondary' : 'primary'; ?>">
                                                        <i class="bi bi-people"></i> 
                                                        <?php echo $selectedEventId == $event['eventId'] ? 'Viewing' : 'Select'; ?>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($selectedEventId): ?>
                <!-- volunteers attendance card -->
                <div class="card mb-4" id="volunteers">
                    <div class="card-header bg-success text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-people-fill"></i> Mark Attendance for Volunteers</h5>
                            <?php if (!empty($volunteers)): ?>
                                <span class="badge bg-light text-dark">
                                    <?php echo count($volunteers); ?> volunteer(s)
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if ($eventDetails): ?>
                            <div class="alert alert-info mb-3">
                                <h6>Event Details:</h6>
                                <strong><?php echo htmlspecialchars($eventDetails['eventName']); ?></strong><br>
                                <?php echo date('F j, Y', strtotime($eventDetails['startDate'])); ?> | 
                                <?php echo date('h:i A', strtotime($eventDetails['startTime'])) . ' - ' . date('h:i A', strtotime($eventDetails['endTime'])); ?><br>
                                Location: <?php echo htmlspecialchars($eventDetails['location']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Search Bar -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <form method="GET" class="row g-3">
                                    <input type="hidden" name="date" value="<?php echo htmlspecialchars($selectedDate); ?>">
                                    <input type="hidden" name="eventId" value="<?php echo $selectedEventId; ?>">
                                    
                                    <div class="col-md-10">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                                            <input type="text" class="form-control" 
                                                   name="search" 
                                                   placeholder="Search volunteers by name, email, or phone number..." 
                                                   value="<?php echo htmlspecialchars($searchTerm); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="bi bi-search"></i> Search
                                        </button>
                                    </div>
                                    <?php if (!empty($searchTerm)): ?>
                                        <div class="col-12">
                                            <div class="alert alert-info py-2 mb-0">
                                                <i class="bi bi-info-circle"></i> 
                                                Searching for: "<strong><?php echo htmlspecialchars($searchTerm); ?></strong>"
                                                <a href="?date=<?php echo $selectedDate; ?>&eventId=<?php echo $selectedEventId; ?>" 
                                                   class="btn btn-sm btn-outline-info ms-3">
                                                    <i class="bi bi-x-circle"></i> Clear Search
                                                </a>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                        
                        <?php if (empty($volunteers)): ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i> 
                                <?php if (!empty($searchTerm)): ?>
                                    No volunteers found matching your search criteria.
                                <?php else: ?>
                                    No registered volunteers found for this event.
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <form method="POST" id="attendanceForm">
                                <input type="hidden" name="eventId" value="<?php echo $selectedEventId; ?>">
                                
                                <div class="table-responsive">
                                    <table class="table table-bordered attendance-table">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="5%">#</th>
                                                <th>Volunteer Details</th>
                                                <th width="15%">Attendance</th>
                                                <th width="20%">Remarks</th>
                                                <th width="15%">Current Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($volunteers as $index => $volunteer): ?>
                                                <tr>
                                                    <td><?php echo $index + 1; ?></td>
                                                    <td>
                                                        <strong>
                                                            <?php 
                                                            // Highlight search term in name
                                                            if (!empty($searchTerm)) {
                                                                echo highlightText($volunteer['name'], $searchTerm);
                                                            } else {
                                                                echo htmlspecialchars($volunteer['name']);
                                                            }
                                                            ?>
                                                        </strong><br>
                                                        <small class="text-muted">
                                                            <i class="bi bi-envelope"></i> 
                                                            <?php 
                                                            if (!empty($searchTerm)) {
                                                                echo highlightText($volunteer['email'], $searchTerm);
                                                            } else {
                                                                echo htmlspecialchars($volunteer['email']);
                                                            }
                                                            ?>
                                                        </small><br>
                                                        <?php if (!empty($volunteer['telephoneNo'])): ?>
                                                            <small class="text-muted">
                                                                <i class="bi bi-telephone"></i> 
                                                                <?php 
                                                                if (!empty($searchTerm)) {
                                                                    echo highlightText($volunteer['telephoneNo'], $searchTerm);
                                                                } else {
                                                                    echo htmlspecialchars($volunteer['telephoneNo']);
                                                                }
                                                                ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input present-checkbox" type="radio" 
                                                                   name="attendance[<?php echo $volunteer['userId']; ?>]" 
                                                                   id="present_<?php echo $volunteer['userId']; ?>" 
                                                                   value="Present" 
                                                                   <?php echo $volunteer['attendanceStatus'] == 'Present' ? 'checked' : ''; ?>>
                                                            <label class="form-check-label text-success" for="present_<?php echo $volunteer['userId']; ?>">
                                                                <i class="bi bi-check-circle"></i> Present
                                                            </label>
                                                        </div>
                                                        <div class="form-check form-check-inline mt-1">
                                                            <input class="form-check-input" type="radio" 
                                                                   name="attendance[<?php echo $volunteer['userId']; ?>]" 
                                                                   id="absent_<?php echo $volunteer['userId']; ?>" 
                                                                   value="Absent"
                                                                   <?php echo $volunteer['attendanceStatus'] == 'Absent' ? 'checked' : ''; ?>>
                                                            <label class="form-check-label text-danger" for="absent_<?php echo $volunteer['userId']; ?>">
                                                                <i class="bi bi-x-circle"></i> Absent
                                                            </label>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control form-control-sm" 
                                                               name="remarks[<?php echo $volunteer['userId']; ?>]"
                                                               placeholder="Optional remarks...">
                                                    </td>
                                                    <td>
                                                        <?php if ($volunteer['attendanceStatus'] == 'Present'): ?>
                                                            <span class="badge bg-success">Present</span>
                                                        <?php elseif ($volunteer['attendanceStatus'] == 'Absent'): ?>
                                                            <span class="badge bg-danger">Absent</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">Not Marked</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                                    <div class="me-auto">
                                        <span class="volunteer-count">
                                            <i class="bi bi-people"></i> 
                                            Showing <?php echo count($volunteers); ?> volunteer(s)
                                        </span>
                                    </div>
                                    <button type="button" class="btn btn-secondary me-md-2" onclick="markAllPresent()">
                                        <i class="bi bi-check-all"></i> Mark All Present
                                    </button>
                                    <button type="submit" name="submit_attendance" class="btn btn-success">
                                        <i class="bi bi-save"></i> Save Attendance
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="mt-4">
                    <a href="coordinator_dashboard.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function markAllPresent() {
            document.querySelectorAll('.present-checkbox').forEach(checkbox => {
                checkbox.checked = true;
            });
        }
        
        document.getElementById('date').addEventListener('change', function() {
            this.form.submit();
        });
        
        // Auto-focus search input when showing volunteers
        <?php if ($selectedEventId && !empty($searchTerm)): ?>
            document.querySelector('input[name="search"]').focus();
        <?php endif; ?>
    </script>
</body>
</html>

<?php
// Function to highlight search text
function highlightText($text, $search) {
    if (empty($search) || empty($text)) {
        return htmlspecialchars($text);
    }
    
    $search = preg_quote($search, '/');
    return preg_replace(
        "/($search)/i", 
        '<span class="search-highlight">$1</span>', 
        htmlspecialchars($text)
    );
}
?>