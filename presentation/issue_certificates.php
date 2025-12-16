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
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$eligibleVolunteers = [];
$eventDetails = null;
$searchInfo = ['hasSearch' => false, 'resultCount' => 0, 'totalCount' => 0];

if ($selectedEventId) {
    // Pass search term to the logic
    $volunteersResult = $certificateLogic->getEligibleVolunteers($selectedEventId, $searchTerm);
    
    if ($volunteersResult['success']) {
        $eligibleVolunteers = $volunteersResult['volunteers'];
        $eventDetails = $volunteersResult['event'];
        $searchInfo = [
            'hasSearch' => !empty($searchTerm),
            'resultCount' => $volunteersResult['count'],
            'totalCount' => $volunteersResult['totalCount']
        ];
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
        
        // Refresh volunteers list with current search
        $volunteersResult = $certificateLogic->getEligibleVolunteers($selectedEventId, $searchTerm);
        if ($volunteersResult['success']) {
            $eligibleVolunteers = $volunteersResult['volunteers'];
            $searchInfo = [
                'hasSearch' => !empty($searchTerm),
                'resultCount' => $volunteersResult['count'],
                'totalCount' => $volunteersResult['totalCount']
            ];
        }
    } else {
        $message = $result['message'];
    }
}

// Handle bulk certificate issuance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['issue_bulk'])) {
    $eventId = $_POST['eventId'];
    
    // If there's a search, only issue to filtered volunteers
    if (!empty($searchTerm)) {
        $results = [
            'total' => count($eligibleVolunteers),
            'success' => 0,
            'failed' => 0,
            'certificates' => []
        ];
        
        foreach ($eligibleVolunteers as $volunteer) {
            $result = $certificateLogic->issueCertificate($eventId, $volunteer['userId']);
            
            if ($result['success']) {
                $results['success']++;
                $results['certificates'][] = [
                    'volunteerName' => $volunteer['name'],
                    'certificateNumber' => $result['certificateNumber']
                ];
            } else {
                $results['failed']++;
            }
        }
        
        $message = "Issued {$results['success']} certificates to filtered volunteers, {$results['failed']} failed";
        $success = $results['success'] > 0;
        $certificatesIssued = $results['certificates'];
    } else {
        // Issue to all eligible volunteers
        $result = $certificateLogic->bulkIssueCertificates($eventId);
        
        if ($result['success']) {
            $message = $result['message'];
            $success = true;
            $certificatesIssued = $result['results']['certificates'];
        } else {
            $message = $result['message'];
        }
    }
    
    // Refresh volunteers list
    $volunteersResult = $certificateLogic->getEligibleVolunteers($selectedEventId, $searchTerm);
    if ($volunteersResult['success']) {
        $eligibleVolunteers = $volunteersResult['volunteers'];
        $searchInfo = [
            'hasSearch' => !empty($searchTerm),
            'resultCount' => $volunteersResult['count'],
            'totalCount' => $volunteersResult['totalCount']
        ];
    }
}

// Function to highlight search text
function highlightSearchText($text, $search) {
    if (empty($search) || empty($text)) {
        return htmlspecialchars($text);
    }
    
    $search = preg_quote($search, '/');
    $pattern = "/($search)/i";
    
    if (preg_match($pattern, $text)) {
        return preg_replace($pattern, '<span class="search-highlight">$1</span>', htmlspecialchars($text));
    }
    
    return htmlspecialchars($text);
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
        .search-highlight {
            background-color: #fff3cd;
            font-weight: bold;
            padding: 0 2px;
            border-radius: 2px;
        }
        .search-tips {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }
        .volunteer-count {
            font-size: 0.9rem;
            color: #6c757d;
        }
        .contact-info {
            font-size: 0.85rem;
        }
        .bulk-action-alert {
            border-left: 5px solid #ffc107;
        }
        .event-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .event-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .event-card.selected {
            border: 2px solid #0d6efd;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="../assets/images/logo.png" alt="Logo" style="height: 40px;">
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="admin_dashboard.php">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                        <i class="bi bi-<?php echo $success ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
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
                            <div class="text-center mt-3">
                                <a href="?eventId=<?php echo $selectedEventId; ?>" class="btn btn-outline-success">
                                    <i class="bi bi-arrow-counterclockwise"></i> Continue Issuing Certificates
                                </a>
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
                                        <div class="card h-100 event-card <?php echo $selectedEventId == $event['eventId'] ? 'selected' : ''; ?>">
                                            <div class="card-body">
                                                <h5 class="card-title"><?php echo htmlspecialchars($event['eventName']); ?></h5>
                                                <p class="card-text">
                                                    <i class="bi bi-calendar"></i> 
                                                    <?php echo date('F j, Y', strtotime($event['startDate'])); ?><br>
                                                    <span class="badge eligible-badge">
                                                        <i class="bi bi-people"></i> 
                                                        <?php echo $event['eligibleCount']; ?> eligible volunteer(s)
                                                    </span>
                                                </p>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <a href="?eventId=<?php echo $event['eventId']; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>" 
                                                       class="btn btn-sm btn-<?php echo $selectedEventId == $event['eventId'] ? 'secondary' : 'primary'; ?>">
                                                        <i class="bi bi-arrow-right"></i> 
                                                        <?php echo $selectedEventId == $event['eventId'] ? 'Viewing' : 'Select'; ?>
                                                    </a>
                                                    <small class="text-muted">
                                                        ID: <?php echo $event['eventId']; ?>
                                                    </small>
                                                </div>
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
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-people-fill"></i> Eligible Volunteers for Certificates
                            </h5>
                            <?php if (!empty($eligibleVolunteers) || $searchInfo['hasSearch']): ?>
                                <span class="badge bg-light text-dark">
                                    <i class="bi bi-person-check"></i>
                                    <?php echo count($eligibleVolunteers); ?> 
                                    <?php if ($searchInfo['hasSearch']): ?>
                                        of <?php echo $searchInfo['totalCount']; ?> total
                                    <?php endif; ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Event Information -->
                        <div class="alert alert-info mb-4">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6>Event Information:</h6>
                                    <strong><?php echo htmlspecialchars($eventDetails['eventName']); ?></strong><br>
                                    Category: <?php echo htmlspecialchars($eventDetails['category']); ?> | 
                                    Skill: <?php echo htmlspecialchars($eventDetails['skillName'] ?? 'Not specified'); ?><br>
                                    Date: <?php echo date('F j, Y', strtotime($eventDetails['startDate'])); ?>
                                </div>
                                <a href="?eventId=<?php echo $selectedEventId; ?>" class="btn btn-sm btn-outline-info">
                                    <i class="bi bi-arrow-clockwise"></i> Refresh
                                </a>
                            </div>
                        </div>
                        
                        <!-- SEARCH BAR -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <form method="GET" class="row g-3">
                                    <input type="hidden" name="eventId" value="<?php echo $selectedEventId; ?>">
                                    
                                    <div class="col-md-9">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                                            <input type="text" class="form-control" 
                                                   name="search" 
                                                   placeholder="Search volunteers by name, email, or phone..." 
                                                   value="<?php echo htmlspecialchars($searchTerm); ?>"
                                                   id="searchInput">
                                        </div>
                                        <div class="search-tips">
                                            <small>
                                                <i class="bi bi-lightbulb"></i> 
                                                Search tips: Type name, email, or phone number to filter volunteers
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-search"></i> Search
                                            </button>
                                            <?php if (!empty($searchTerm)): ?>
                                                <a href="?eventId=<?php echo $selectedEventId; ?>" 
                                                   class="btn btn-outline-secondary">
                                                    <i class="bi bi-x-circle"></i> Clear Search
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </form>
                                
                                <?php if ($searchInfo['hasSearch']): ?>
                                    <div class="alert alert-info mt-3 py-2 mb-0">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="bi bi-info-circle"></i> 
                                                Searching for: "<strong><?php echo htmlspecialchars($searchTerm); ?></strong>"
                                                <span class="ms-2 badge bg-info">
                                                    <?php echo $searchInfo['resultCount']; ?> of <?php echo $searchInfo['totalCount']; ?> volunteer(s)
                                                </span>
                                            </div>
                                            <?php if ($searchInfo['resultCount'] == 0): ?>
                                                <a href="?eventId=<?php echo $selectedEventId; ?>" class="btn btn-sm btn-outline-info">
                                                    Show all volunteers
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if (empty($eligibleVolunteers)): ?>
                            <div class="alert alert-<?php echo $searchInfo['hasSearch'] ? 'warning' : 'success'; ?>">
                                <i class="bi bi-<?php echo $searchInfo['hasSearch'] ? 'exclamation-triangle' : 'check-circle'; ?>"></i> 
                                <?php if ($searchInfo['hasSearch']): ?>
                                    <strong>No volunteers found!</strong><br>
                                    No volunteers match your search for "<strong><?php echo htmlspecialchars($searchTerm); ?></strong>".<br>
                                    Try a different search term or <a href="?eventId=<?php echo $selectedEventId; ?>" class="alert-link">clear the search</a> to see all volunteers.
                                <?php else: ?>
                                    <strong>All certificates issued!</strong><br>
                                    All eligible volunteers for this event have already received certificates.
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <!-- Bulk Issue Button with search awareness -->
                            <div class="alert alert-warning bulk-action-alert mb-4">
                                <form method="POST" class="mb-0">
                                    <input type="hidden" name="eventId" value="<?php echo $selectedEventId; ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="bi bi-info-circle"></i> 
                                            <?php if ($searchInfo['hasSearch']): ?>
                                                <strong>Bulk action will affect <?php echo $searchInfo['resultCount']; ?> filtered volunteer(s) only</strong><br>
                                                <small>Only volunteers matching your search will receive certificates</small>
                                            <?php else: ?>
                                                <strong>Ready to issue certificates to <?php echo $searchInfo['totalCount']; ?> eligible volunteer(s)</strong>
                                            <?php endif; ?>
                                        </div>
                                        <button type="submit" name="issue_bulk" class="btn btn-success btn-lg" 
                                                onclick="return confirm('<?php echo $searchInfo['hasSearch'] ? 'Issue certificates to ' . $searchInfo['resultCount'] . ' FILTERED volunteers?' : 'Issue certificates to ALL ' . $searchInfo['totalCount'] . ' eligible volunteers?'; ?>')">
                                            <i class="bi bi-award"></i> 
                                            <?php echo $searchInfo['hasSearch'] ? 'Issue to Filtered (' . $searchInfo['resultCount'] . ')' : 'Issue to All (' . $searchInfo['totalCount'] . ')'; ?>
                                        </button>
                                    </div>
                                    <small class="text-muted d-block mt-2">
                                        <i class="bi bi-envelope"></i> Will send email notifications to all selected volunteers
                                    </small>
                                </form>
                            </div>
                            
                            <!-- Volunteers List with Search Highlighting -->
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="5%">#</th>
                                            <th>Volunteer Details</th>
                                            <th>Email</th>
                                            <th>Attendance Date</th>
                                            <th width="15%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($eligibleVolunteers as $index => $volunteer): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td>
                                                    <strong><?php echo highlightSearchText($volunteer['name'], $searchTerm); ?></strong>
                                                    <?php if (!empty($volunteer['telephoneNo'])): ?>
                                                        <br>
                                                        <small class="text-muted contact-info">
                                                            <i class="bi bi-telephone"></i> 
                                                            <?php echo highlightSearchText($volunteer['telephoneNo'], $searchTerm); ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="contact-info">
                                                        <?php echo highlightSearchText($volunteer['email'], $searchTerm); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        <?php echo date('F j, Y', strtotime($volunteer['attendanceDate'])); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="eventId" value="<?php echo $selectedEventId; ?>">
                                                            <input type="hidden" name="userId" value="<?php echo $volunteer['userId']; ?>">
                                                            <button type="submit" name="issue_single" class="btn btn-success"
                                                                    onclick="return confirm('Issue certificate to <?php echo htmlspecialchars($volunteer['name']); ?>?')">
                                                                <i class="bi bi-award"></i> Issue
                                                            </button>
                                                        </form>
                                                        <a href="#" class="btn btn-outline-info" 
                                                           data-bs-toggle="tooltip" 
                                                           title="Volunteer ID: <?php echo $volunteer['userId']; ?>">
                                                            <i class="bi bi-info-circle"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Summary Footer -->
                            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                <div class="volunteer-count">
                                    <i class="bi bi-people"></i> 
                                    Showing <?php echo count($eligibleVolunteers); ?> volunteer(s)
                                    <?php if ($searchInfo['hasSearch']): ?>
                                        (filtered by search)
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <?php if (count($eligibleVolunteers) > 10): ?>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.scrollTo({top: 0, behavior: 'smooth'})">
                                            <i class="bi bi-arrow-up"></i> Back to Top
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Back Button -->
                <div class="mt-4 mb-5">
                    <a href="admin_dashboard.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Dashboard
                    </a>
                    <?php if ($selectedEventId): ?>
                        <a href="?eventId=<?php echo $selectedEventId; ?>" class="btn btn-outline-primary ms-2">
                            <i class="bi bi-arrow-clockwise"></i> Refresh Page
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-focus search input
        <?php if ($selectedEventId): ?>
            document.getElementById('searchInput').focus();
        <?php endif; ?>
        
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl+F to focus search
            if (e.ctrlKey && e.key === 'f') {
                e.preventDefault();
                const searchInput = document.getElementById('searchInput');
                if (searchInput) {
                    searchInput.focus();
                    searchInput.select();
                }
            }
            
            // ESC to clear search if search input has value
            if (e.key === 'Escape') {
                const searchInput = document.getElementById('searchInput');
                if (searchInput && searchInput.value) {
                    window.location.href = '?eventId=<?php echo $selectedEventId; ?>';
                }
            }
            
            // Alt+S to focus search (alternative shortcut)
            if (e.altKey && e.key === 's') {
                e.preventDefault();
                const searchInput = document.getElementById('searchInput');
                if (searchInput) {
                    searchInput.focus();
                    searchInput.select();
                }
            }
        });
        
        // Auto-submit search on Enter in search field
        document.getElementById('searchInput')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.form.submit();
            }
        });
        
        // Confirm before leaving page if there are unsaved changes
        window.addEventListener('beforeunload', function (e) {
            // You can add logic here to check if there are unsaved changes
            // For now, we'll just return null (no warning)
            return null;
        });
        
        // Smooth scroll to volunteers section when clicking on event card
        document.querySelectorAll('.event-card a[href*="eventId"]').forEach(link => {
            link.addEventListener('click', function(e) {
                if (this.getAttribute('href').includes('eventId')) {
                    // Allow default navigation
                }
            });
        });
    </script>
</body>
</html>