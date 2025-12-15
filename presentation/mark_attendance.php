<?php
session_start();
require_once '../business_logic/attendanceLogic.php';

// Check if user is logged in and is a coordinator
if (!isset($_SESSION['userId']) || $_SESSION['role'] !== 'Coordinator') {
    header('Location: login.php');
    exit();
}

$attendanceLogic = new AttendanceLogic();
$message = '';
$success = false;

// Handle date selection
$selectedDate = $_GET['date'] ?? date('Y-m-d');

// Get events for the selected date
$eventsResult = $attendanceLogic->getCoordinatorEvents($selectedDate);
$events = $eventsResult['success'] ? $eventsResult['events'] : [];

// Handle event selection
$selectedEventId = $_GET['eventId'] ?? null;
$volunteers = [];
$eventDetails = null;

if ($selectedEventId) {
    $volunteersResult = $attendanceLogic->getEventVolunteers($selectedEventId);
    if ($volunteersResult['success']) {
        $volunteers = $volunteersResult['volunteers'];
        $eventDetails = $volunteersResult['event'] ?? null;
    }
}

// Handle attendance submission
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
        $volunteersResult = $attendanceLogic->getEventVolunteers($selectedEventId);
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
    </style>
</head>
<body>
    <!-- Simple Header Bar instead of navbar -->
    <div class="header-bar">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="bi bi-clipboard-check"></i> Mark Attendance
                </h4>
                <div class="text-end">
                    <span class="me-3"><?php echo htmlspecialchars($_SESSION['name']); ?> (Coordinator)</span>
                    <a href="../../index.php" class="btn btn-light btn-sm">
                        <i class="bi bi-house"></i> Home
                    </a>
                    <a href="../auth/logout.php" class="btn btn-outline-light btn-sm ms-2">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Date Selection Card -->
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
                
                <!-- Events List Card -->
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
                
                <?php if ($selectedEventId && !empty($volunteers)): ?>
                <!-- Volunteers Attendance Card -->
                <div class="card mb-4" id="volunteers">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-people-fill"></i> Mark Attendance for Volunteers</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($eventDetails): ?>
                            <div class="alert alert-info mb-3">
                                <h6>Event Details:</h6>
                                <strong><?php echo htmlspecialchars($eventDetails['eventName']); ?></strong><br>
                                <?php echo date('F j, Y', strtotime($eventDetails['startDate'])); ?> | 
                                <?php echo date('h:i A', strtotime($eventDetails['startTime'])); ?> - 
                                <?php echo date('h:i A', strtotime($eventDetails['endTime'])); ?><br>
                                Location: <?php echo htmlspecialchars($eventDetails['location']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" id="attendanceForm">
                            <input type="hidden" name="eventId" value="<?php echo $selectedEventId; ?>">
                            
                            <div class="table-responsive">
                                <table class="table table-bordered attendance-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="5%">#</th>
                                            <th>Volunteer Name</th>
                                            <th width="15%">Attendance</th>
                                            <th width="25%">Remarks</th>
                                            <th width="15%">Current Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($volunteers as $index => $volunteer): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($volunteer['name']); ?></strong><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($volunteer['email']); ?></small>
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
                                                           placeholder="Optional remarks">
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
                                <button type="button" class="btn btn-secondary me-md-2" onclick="markAllPresent()">
                                    <i class="bi bi-check-all"></i> Mark All Present
                                </button>
                                <button type="submit" name="submit_attendance" class="btn btn-success">
                                    <i class="bi bi-save"></i> Save Attendance
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php elseif ($selectedEventId && empty($volunteers)): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> No registered volunteers found for this event.
                    </div>
                <?php endif; ?>
                
                <!-- Back Button -->
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
        
        // Auto-submit date change
        document.getElementById('date').addEventListener('change', function() {
            this.form.submit();
        });
    </script>
</body>
</html>