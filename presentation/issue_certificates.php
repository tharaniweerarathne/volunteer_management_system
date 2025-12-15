<?php
session_start();
require_once '../business_logic/certificateLogic.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['userId']) || $_SESSION['role'] !== 'Admin') {
    header('Location: login.php');
    exit();
}

$certificateLogic = new CertificateLogic();
$message = '';
$success = false;
$certificatesIssued = [];

// Handle event selection
$selectedEventId = $_GET['eventId'] ?? null;
$eligibleVolunteers = [];
$eventDetails = null;

if ($selectedEventId) {
    $volunteersResult = $certificateLogic->getEligibleVolunteers($selectedEventId);
    if ($volunteersResult['success']) {
        $eligibleVolunteers = $volunteersResult['volunteers'];
        $eventDetails = $volunteersResult['event'];
    }
}

// Handle single certificate issuance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['issue_single'])) {
    $eventId = $_POST['eventId'];
    $userId = $_POST['userId'];
    
    $result = $certificateLogic->issueCertificate($eventId, $userId);
    
    if ($result['success']) {
        $message = "Certificate issued successfully for volunteer. Certificate #: " . $result['certificateNumber'];
        $success = true;
        
        // Refresh volunteers list
        $volunteersResult = $certificateLogic->getEligibleVolunteers($selectedEventId);
        if ($volunteersResult['success']) {
            $eligibleVolunteers = $volunteersResult['volunteers'];
        }
    } else {
        $message = $result['message'];
    }
}

// Handle bulk certificate issuance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['issue_bulk'])) {
    $eventId = $_POST['eventId'];
    
    $result = $certificateLogic->bulkIssueCertificates($eventId);
    
    if ($result['success']) {
        $message = $result['message'];
        $success = true;
        $certificatesIssued = $result['results']['certificates'];
        
        // Refresh volunteers list
        $volunteersResult = $certificateLogic->getEligibleVolunteers($selectedEventId);
        if ($volunteersResult['success']) {
            $eligibleVolunteers = $volunteersResult['volunteers'];
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
    <title>Issue Certificates - Unity Volunteers Trust</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .certificate-card { border-left: 5px solid #198754; }
        .eligible-badge { background-color: #198754; }
        .issued-badge { background-color: #6c757d; }
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
                    <i class="bi bi-award"></i> Issue Certificates
                </h4>
                <div class="text-end">
                    <span class="me-3"><?php echo htmlspecialchars($_SESSION['name']); ?> (Admin)</span>
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
                
                <?php if (!empty($certificatesIssued) && $success): ?>
                    <div class="card mb-4 border-success">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-check-circle"></i> Certificates Issued Successfully</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Volunteer Name</th>
                                            <th>Certificate Number</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($certificatesIssued as $cert): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($cert['volunteerName']); ?></td>
                                                <td><code><?php echo $cert['certificateNumber']; ?></code></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Event Selection Card -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-calendar-event"></i> Select Event</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        // Get events with eligible volunteers
                        require_once '../data_access/certificateData.php';
                        $certificateData = new CertificateData();
                        $events = $certificateData->getEventsWithEligibleVolunteers();
                        ?>
                        
                        <?php if (empty($events)): ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> No events have eligible volunteers for certificates at this time.
                            </div>
                        <?php else: ?>
                            <div class="row row-cols-1 row-cols-md-2 g-4">
                                <?php foreach ($events as $event): ?>
                                    <div class="col">
                                        <div class="card h-100 certificate-card <?php echo $selectedEventId == $event['eventId'] ? 'border-primary' : ''; ?>">
                                            <div class="card-body">
                                                <h5 class="card-title"><?php echo htmlspecialchars($event['eventName']); ?></h5>
                                                <p class="card-text">
                                                    <i class="bi bi-calendar"></i> 
                                                    <?php echo date('F j, Y', strtotime($event['startDate'])); ?><br>
                                                    <span class="badge eligible-badge">
                                                        <?php echo $event['eligibleCount']; ?> eligible volunteer(s)
                                                    </span>
                                                </p>
                                                <a href="?eventId=<?php echo $event['eventId']; ?>" 
                                                   class="btn btn-sm btn-<?php echo $selectedEventId == $event['eventId'] ? 'secondary' : 'primary'; ?>">
                                                    <i class="bi bi-arrow-right"></i> 
                                                    <?php echo $selectedEventId == $event['eventId'] ? 'Viewing' : 'Select'; ?>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($selectedEventId && $eventDetails): ?>
                <!-- Eligible Volunteers Card -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-people-fill"></i> Eligible Volunteers for Certificates
                            <span class="badge bg-light text-dark ms-2"><?php echo count($eligibleVolunteers); ?> volunteers</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Event Information -->
                        <div class="alert alert-info mb-4">
                            <h6>Event Information:</h6>
                            <strong><?php echo htmlspecialchars($eventDetails['eventName']); ?></strong><br>
                            Category: <?php echo htmlspecialchars($eventDetails['category']); ?> | 
                            Skill: <?php echo htmlspecialchars($eventDetails['skillName'] ?? 'Not specified'); ?><br>
                            Date: <?php echo date('F j, Y', strtotime($eventDetails['startDate'])); ?>
                        </div>
                        
                        <?php if (empty($eligibleVolunteers)): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle"></i> All eligible volunteers for this event have already received certificates.
                            </div>
                        <?php else: ?>
                            <!-- Bulk Issue Button -->
                            <form method="POST" class="mb-4">
                                <input type="hidden" name="eventId" value="<?php echo $selectedEventId; ?>">
                                <button type="submit" name="issue_bulk" class="btn btn-success btn-lg" 
                                        onclick="return confirm('Issue certificates to ALL <?php echo count($eligibleVolunteers); ?> eligible volunteers?')">
                                    <i class="bi bi-award"></i> Issue Certificates to All Eligible Volunteers
                                </button>
                                <small class="text-muted ms-2">(Will send email notifications to all volunteers)</small>
                            </form>
                            
                            <!-- Volunteers List -->
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Volunteer Name</th>
                                            <th>Email</th>
                                            <th>Attendance Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($eligibleVolunteers as $index => $volunteer): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($volunteer['name']); ?></strong>
                                                </td>
                                                <td><?php echo htmlspecialchars($volunteer['email']); ?></td>
                                                <td>
                                                    <?php echo date('F j, Y', strtotime($volunteer['attendanceDate'])); ?>
                                                </td>
                                                <td>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="eventId" value="<?php echo $selectedEventId; ?>">
                                                        <input type="hidden" name="userId" value="<?php echo $volunteer['userId']; ?>">
                                                        <button type="submit" name="issue_single" class="btn btn-sm btn-success"
                                                                onclick="return confirm('Issue certificate to <?php echo htmlspecialchars($volunteer['name']); ?>?')">
                                                            <i class="bi bi-award"></i> Issue Certificate
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Back Button -->
                <div class="mt-4">
                    <a href="admin_dashboard.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>