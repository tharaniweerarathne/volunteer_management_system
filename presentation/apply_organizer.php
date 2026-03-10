<?php

session_start();


if (!isset($_SESSION['name'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['userId'];
$name = $_SESSION['name'];
$role = $_SESSION['role'] ?? '';


if ($role !== 'Volunteer') {
    header("Location: dashboard.php");
    exit();
}

require_once __DIR__ . "/../data_access/db.php";
require_once __DIR__ . "/../business_logic/OrganizerLogic.php";
require_once __DIR__ . "/../business_logic/MessageLogic.php";

$organizerLogic = new OrganizerLogic($conn);
$messageLogic = new MessageLogic($conn);
$inboxResult = $messageLogic->getInbox($userId, 1, 1);
$unreadCount = $inboxResult['unreadCount'];

$message = "";
$messageType = "";

// Check if user can apply
$canApplyResult = $organizerLogic->canUserApply($userId, $role);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'apply') {
    $result = $organizerLogic->submitOrganizerApplication(
        $userId,
        $_POST['organizationName'] ?? '',
        $_POST['organizationType'] ?? '',
        $_POST['organizationDescription'] ?? '',
        $_POST['yearsOfExperience'] ?? 0,
        $_POST['previousEvents'] ?? '',
        $_POST['motivation'] ?? ''
    );
    
    $message = $result['message'];
    $messageType = $result['success'] ? 'success' : 'danger';
    
    if ($result['success']) {
        
        $canApplyResult = $organizerLogic->canUserApply($userId, $role);
    }
}


$userRequests = $organizerLogic->getUserOrganizerRequests($userId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply to Become Organizer</title>
    <link rel="stylesheet" href="../assets/css/a9.css">
    <link rel="icon" type="image/png" href="../assets/images/title.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>

    <!-- Sidebar Navigation -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <img src="../assets/images/logo.png" alt="Logo" class="logo-img">
        </div>
        <div class="nav-items">
            <div class="nav-item">
                <a href="volunteer_dashboard">
                    <i class="ri-dashboard-line"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="events_volunteer.php">
                    <i class="ri-calendar-check-line"></i>
                    <span>Join Events</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="my_events.php">
                    <i class="ri-calendar-line"></i>
                    <span>My Events</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="my_certificates.php">
                    <i class="ri-medal-line"></i>
                    <span>Download Certificates</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="apply_organizer.php" class="active">
                    <i class="ri-file-add-line"></i>
                    <span>Apply to become an Organizer</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="inbox.php" class="messages-link">
                    <i class="ri-send-plane-line"></i>
                    <span>Send Messages</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="past_event_main.php">
                    <i class="ri-history-line"></i>
                    <span>Past Events</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="logout.php">
                    <i class="ri-logout-box-line me-2"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Top Header -->
        <header class="top-header">
            <div class="welcome-text">
                <button class="menu-toggle" id="menuToggle">
                    <i class="ri-menu-line"></i>
                </button>
                Welcome Volunteer, <?php echo htmlspecialchars($name); ?>
            </div>
            <div class="header-actions">
                <div class="dropdown me-3">
                    <button class="btn p-0 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="ri-notification-3-line"></i>
                        <?php if ($unreadCount > 0): ?>
                            <span class="notification-badge"><?php echo $unreadCount; ?></span>
                        <?php endif; ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="inbox.php"><i class="ri-edit-line me-2"></i>Messages</a></li>
                        <li><a class="dropdown-item" href="send_message.php"><i class="ri-pencil-line me-2"></i>Compose</a></li>
                    </ul>
                </div>
                <div class="dropdown">
                    <button class="btn p-0 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="ri-user-3-fill header-icon"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="edit_profile.php"><i class="ri-edit-line me-2"></i>Edit Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="ri-logout-box-line me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <div class="container1">
            <h1 class="text-left page-title mb-4">
                <i class="ri-award-line"></i> Apply to Become an Organizer
            </h1>

            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Application Status Card -->
            <?php if (!$canApplyResult['canApply']): ?>
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <i class="ri-information-line" style="font-size: 48px; color: #ff6200;"></i>
                        <h4 class="mt-3"><?= htmlspecialchars($canApplyResult['message']) ?></h4>
                        <?php if (isset($canApplyResult['status']) && $canApplyResult['status'] === 'Pending'): ?>
                            <p class="text-muted">Your application is currently being reviewed by our admin team.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Application Form -->
            <?php if ($canApplyResult['canApply']): ?>
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="ri-file-text-line"></i> Organizer Application Form</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="organizerApplicationForm">
                            <input type="hidden" name="action" value="apply">
                            
                            <div class="alert alert-info">
                                <i class="ri-information-line"></i> 
                                <strong>Note:</strong> All fields marked with <span class="required">*</span> are mandatory.
                            </div>

                            <h5 class="mb-3">Organization Information (Optional)</h5>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Organization Name</label>
                                    <div class="input-group">
                                        <i class="ri-building-line input-icon"></i>
                                        <input type="text" name="organizationName" class="form-control with-icon" placeholder="Enter organization name (if any)">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Organization Type</label>
                                    <select name="organizationType" class="form-select">
                                        <option value="">Select Type</option>
                                        <option value="NGO">NGO</option>
                                        <option value="Community Group">Community Group</option>
                                        <option value="Educational Institution">Educational Institution</option>
                                        <option value="Religious Organization">Religious Organization</option>
                                        <option value="Corporate CSR">Corporate CSR</option>
                                        <option value="Independent">Independent</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Organization Description</label>
                                <textarea name="organizationDescription" class="form-control" rows="3" placeholder="Briefly describe your organization (if applicable)"></textarea>
                            </div>

                            <h5 class="mb-3 mt-4">Experience & Background</h5>

                            <div class="mb-3">
                                <label class="form-label">Years of Experience in Event Organization</label>
                                <select name="yearsOfExperience" class="form-select">
                                    <option value="0">No prior experience</option>
                                    <option value="1">Less than 1 year</option>
                                    <option value="2">1-2 years</option>
                                    <option value="3">3-5 years</option>
                                    <option value="5">5+ years</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Previous Events Organized</label>
                                <textarea name="previousEvents" class="form-control" rows="4" placeholder="List any events you have organized or helped organize (e.g., 'Beach cleanup - 50 volunteers, Food drive - collected 200kg, etc.')"></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label"><span class="required">*</span> Why do you want to become an organizer?</label>
                                <textarea name="motivation" class="form-control" rows="5" placeholder="Tell us your motivation and what you hope to achieve as an organizer..." required></textarea>
                                <small class="text-muted">Minimum 50 characters</small>
                            </div>

                            <div class="alert alert-warning">
                                <i class="ri-alert-line"></i> 
                                <strong>Important:</strong> Once submitted, your application will be reviewed by our admin team. You will be notified of the decision via email.
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="ri-send-plane-line"></i> Submit Application
                            </button>
                            <a href="volunteer_dashboard.php" class="btn btn-secondary">
                                <i class="ri-arrow-left-line"></i> Back to Dashboard
                            </a>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Previous Requests -->
            <?php if (!empty($userRequests)): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="ri-history-line"></i> Your Application History</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Request Date</th>
                                        <th>Status</th>
                                        <th>Review Date</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($userRequests as $request): ?>
                                        <tr>
                                            <td><?= date('M d, Y', strtotime($request['requestDate'])) ?></td>
                                            <td>
                                                <?php if ($request['requestStatus'] === 'Pending'): ?>
                                                    <span class="badge bg-warning">Pending</span>
                                                <?php elseif ($request['requestStatus'] === 'Approved'): ?>
                                                    <span class="badge bg-success">Approved</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Rejected</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= $request['reviewDate'] ? date('M d, Y', strtotime($request['reviewDate'])) : '-' ?></td>
                                            <td><?= htmlspecialchars($request['reviewNotes'] ?? '-') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/dashboards.js"></script>
    <script>
        // Form validation
        document.getElementById('organizerApplicationForm')?.addEventListener('submit', function(e) {
            const motivation = document.querySelector('[name="motivation"]').value;
            
            if (motivation.length < 5) {
                e.preventDefault();
                alert('Please provide at least 50 characters for your motivation.');
                return false;
            }
        });

        
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                bootstrap.Alert.getOrCreateInstance(alert).close();
            });
        }, 5000);
    </script>
</body>
</html>