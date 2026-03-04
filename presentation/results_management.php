<?php
// results_management.php ---> presentation folder

// Check if session is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['userId'])) {
    header("Location: sign_in.php");
    exit();
}

$userId = $_SESSION['userId'];
$name = $_SESSION['name'];
$role = $_SESSION['role'];

// Check if user has permission (Admin, Organizer, or Coordinator)
if (!in_array($role, ['Admin', 'Organizer', 'Coordinator'])) {
    header("Location: index.php");
    exit();
}

require_once __DIR__ . "/../data_access/db.php";
require_once __DIR__ . "/../business_logic/resultsLogic.php";
require_once __DIR__ . "/../business_logic/MessageLogic.php";
require_once __DIR__ . "/../data_access/eventData.php"; // For skills/categories

$resultsLogic = new ResultsLogic();
$messageLogic = new MessageLogic($conn);
$eventData = new EventData();

$inboxResult = $messageLogic->getInbox($userId, 1, 1);
$unreadCount = $inboxResult['unreadCount'];

$message = "";
$messageType = "";

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $result = $resultsLogic->handleCreateResult();
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'danger';
    }
    
    elseif ($action === 'update') {
        $resultId = $_POST['resultId'] ?? 0;
        $result = $resultsLogic->handleUpdateResult($resultId);
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'danger';
    }
    
    elseif ($action === 'approve') {
        $resultId = $_POST['resultId'] ?? 0;
        $notes = $_POST['reviewNotes'] ?? '';
        $result = $resultsLogic->handleApproveResult($resultId, $notes);
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'danger';
    }
    
    elseif ($action === 'reject') {
        $resultId = $_POST['resultId'] ?? 0;
        $notes = $_POST['reviewNotes'] ?? '';
        $result = $resultsLogic->handleRejectResult($resultId, $notes);
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'danger';
    }
    
    elseif ($action === 'delete') {
        $resultId = $_POST['resultId'] ?? 0;
        $result = $resultsLogic->handleDeleteResult($resultId);
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'danger';
    }
}

// Get filters from URL
$filters = [];
if (!empty($_GET['search'])) $filters['search'] = $_GET['search'];
if (!empty($_GET['eventId'])) $filters['eventId'] = $_GET['eventId'];
if (!empty($_GET['skillId'])) $filters['skillId'] = $_GET['skillId'];
if (!empty($_GET['category'])) $filters['category'] = $_GET['category'];
if (!empty($_GET['organizerId'])) $filters['organizerId'] = $_GET['organizerId'];
if (!empty($_GET['fromDate'])) $filters['fromDate'] = $_GET['fromDate'];
if (!empty($_GET['toDate'])) $filters['toDate'] = $_GET['toDate'];
if (!empty($_GET['status'])) $filters['approvalStatus'] = $_GET['status'];

// Get data
$resultsData = $resultsLogic->getResults($filters);
$results = $resultsData['results'];
$stats = $resultsLogic->getStatistics();
$events = $resultsLogic->getEventsForDropdown();
$skills = $eventData->getAllSkills();
$categories = $eventData->getCategories();

// Get organizers for dropdown (only for admin)
$organizers = [];
if ($role === 'Admin') {
    $organizersResult = $resultsLogic->getAllOrganizers();
    $organizers = $organizersResult['success'] ? $organizersResult['organizers'] : [];
}

// Get events for dropdown (for all users)
$eventsForDropdown = $events['events'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Results Management</title>
    <link rel="stylesheet" href="../assets/css/a9.css">
    <link rel="stylesheet" href="../assets/css/result_management.css">
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

        <?php if ($role === 'Admin'): ?>
            <div class="nav-item">
            <a href="admin_dashboard.php">
                <i class="ri-dashboard-line"></i>
                <span>Dashboard</span>
            </a>
        </div> 
        <?php endif; ?>

        <?php if ($role === 'Coordinator'): ?>
            <div class="nav-item">
            <a href="coordinator_dashboard.php">
                <i class="ri-dashboard-line"></i>
                <span>Dashboard</span>
            </a>
            </div> 
        <?php endif; ?>

        <?php if ($role === 'Organizer'): ?>
            <div class="nav-item">
            <a href="coordinator_dashboard.php">
                <i class="ri-dashboard-line"></i>
                <span>Dashboard</span>
            </a>
            </div> 
        <?php endif; ?>

        <div class="nav-item">
            <a href="events.php">
                <i class="ri-calendar-event-line"></i>
                <span>Manage Events</span>
            </a>
        </div>

        <?php if ($role === 'Coordinator'): ?>
        <div class="nav-item">
            <a href="mark_attendance.php">
                <i class="ri-checkbox-circle-line"></i>
                <span>Mark Attendance</span>
            </a>
        </div>
        <?php endif; ?>

        <?php if ($role === 'Admin' || $role === 'Coordinator'): ?>
            <div class="nav-item">
                <a href="volunteer_management.php">
                    <i class="ri-user-star-line"></i>
                    <span>Volunteers Management</span>
                </a>
            </div>
        <?php endif; ?>

        <?php if ($role === 'Admin'): ?>
        <div class="nav-item">
                <a href="coordinator_management.php">
                    <i class="ri-group-line"></i>
                    <span>Coordinator Management</span>
                </a>
        </div> 
        <?php endif; ?>

        <?php if ($role === 'Admin'): ?>
            <div class="nav-item">
                <a href="organizer_requests.php">
                    <i class="ri-shield-user-line"></i>
                    <span>Organizers Management</span>
                </a>
            </div> 
        <?php endif; ?>

        <?php if ($role === 'Admin'): ?>
            <div class="nav-item">
                <a href="issue_certificates.php">
                    <i class="ri-user-settings-line"></i>
                    <span>Certificate issue</span>
                </a>
            </div>
        <?php endif; ?>

        <?php if ($role === 'Admin' || $role === 'Coordinator'): ?>
         <div class="nav-item">
            <a href="view_messages.php"> 
                <i class="ri-chat-3-line"></i>
                <span>Support Messages</span>
            </a>
        </div>
        <?php endif; ?>

        <div class="nav-item">
            <a href="send_message.php">
                <i class="ri-send-plane-line"></i>
                <span>Send Messages</span>
            </a>
        </div>

        <div class="nav-item">
            <a href="results_management.php" class="active">
                <i class="ri-history-line"></i>
                <span>Results Management</span>
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
                Welcome <?php echo htmlspecialchars($role); ?>, <?php echo htmlspecialchars($name); ?>
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
                        <li><a class="dropdown-item" href="view_messages.php"><i class="ri-inbox-line me-2"></i>Inbox</a></li>
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
                <i class="ri-file-chart-line"></i> Event Results Management
            </h1>

            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-primary"><?= $stats['stats']['total'] ?? 0 ?></h3>
                            <p class="mb-0">Total Results</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-warning"><?= $stats['stats']['pending'] ?? 0 ?></h3>
                            <p class="mb-0">Pending</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-success"><?= $stats['stats']['approved'] ?? 0 ?></h3>
                            <p class="mb-0">Approved</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-danger"><?= $stats['stats']['rejected'] ?? 0 ?></h3>
                            <p class="mb-0">Rejected</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add New Result Button -->
            <div class="mb-4">
                <button class="btn btn-primary" onclick="showAddModal()">
                    <i class="ri-add-line"></i> Add New Result
                </button>
            </div>

            <!-- Filters -->
            <div class="filter-section">
                <form method="GET" class="row g-3">
                    <div class="col-md-2">
                        <input type="text" name="search" class="form-control" placeholder="Search title..." 
                               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="eventId" class="form-control">
                            <option value="">All Events</option>
                            <?php foreach ($eventsForDropdown as $event): ?>
                                <option value="<?= $event['eventId'] ?>" 
                                    <?= ($_GET['eventId'] ?? '') == $event['eventId'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($event['eventName']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="skillId" class="form-control">
                            <option value="">All Skills</option>
                            <?php foreach ($skills as $skill): ?>
                                <option value="<?= $skill['skillId'] ?>" 
                                    <?= ($_GET['skillId'] ?? '') == $skill['skillId'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($skill['skillName']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="category" class="form-control">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['category'] ?>" 
                                    <?= ($_GET['category'] ?? '') == $cat['category'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['category']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if ($role === 'Admin'): ?>
                    <div class="col-md-2">
                        <select name="organizerId" class="form-control">
                            <option value="">All Organizers</option>
                            <?php foreach ($organizers as $org): ?>
                                <option value="<?= $org['userId'] ?>" 
                                    <?= ($_GET['organizerId'] ?? '') == $org['userId'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($org['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div class="col-md-2">
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="Pending" <?= ($_GET['status'] ?? '') == 'Pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="Approved" <?= ($_GET['status'] ?? '') == 'Approved' ? 'selected' : '' ?>>Approved</option>
                            <option value="Rejected" <?= ($_GET['status'] ?? '') == 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="date" name="fromDate" class="form-control" placeholder="From Date"
                                   value="<?= htmlspecialchars($_GET['fromDate'] ?? '') ?>">
                            <span class="input-group-text">to</span>
                            <input type="date" name="toDate" class="form-control" placeholder="To Date"
                                   value="<?= htmlspecialchars($_GET['toDate'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ri-search-line"></i> Filter
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="results_management.php" class="btn btn-secondary w-100">
                            <i class="ri-refresh-line"></i> Clear
                        </a>
                    </div>
                </form>
            </div>

            <!-- Results List -->
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="ri-list-check"></i> Event Results (<?= count($results) ?>)</h4>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Images</th>
                                    <th>Title</th>
                                    <th>Event</th>
                                    <th>Organizer</th>
                                    <th>Date</th>
                                    <th>Category</th>
                                    <th>Skill</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($results)): ?>
                                    <tr>
                                        <td colspan="10" class="text-center text-muted">No results found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($results as $result): ?>
                                        <tr>
                                            <td><?= $result['resultId'] ?></td>
                                            <td>
                                                <?php 
                                                $imageCount = 0;
                                                for ($i = 1; $i <= 5; $i++) {
                                                    $fieldName = $i == 1 ? 'resultImage' : 'resultImage' . $i;
                                                    if (!empty($result[$fieldName])) {
                                                        $imageCount++;
                                                    }
                                                }
                                                if ($imageCount > 0): ?>
                                                    <div class="d-flex">
                                                        <?php 
                                                        $mainImage = $result['resultImage'];
                                                        if ($mainImage): ?>
                                                            <img src="../<?= htmlspecialchars($mainImage) ?>" 
                                                                 alt="Result" class="result-image me-1">
                                                        <?php endif; ?>
                                                        <?php if ($imageCount > 1): ?>
                                                            <div class="ms-1">
                                                                <span class="badge bg-info">+<?= $imageCount - 1 ?></span>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">No images</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($result['resultTitle']) ?></strong><br>
                                                <small class="text-muted"><?= htmlspecialchars(substr($result['description'] ?? '', 0, 50)) ?>...</small>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($result['eventName'] ?? 'N/A') ?><br>
                                                <small class="text-muted"><?= date('M d, Y', strtotime($result['eventStartDate'] ?? '')) ?></small>
                                            </td>
                                            <td>
                                                <?php if (!empty($result['organizerName'])): ?>
                                                    <span class="organizer-badge">
                                                        <i class="ri-user-star-line"></i> <?= htmlspecialchars($result['organizerName']) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= date('M d, Y', strtotime($result['resultDate'])) ?></td>
                                            <td>
                                                <?php if (!empty($result['category'])): ?>
                                                    <span class="badge bg-info"><?= htmlspecialchars($result['category']) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($result['skillName'])): ?>
                                                    <span class="badge bg-secondary"><?= htmlspecialchars($result['skillName']) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($result['approvalStatus'] === 'Pending'): ?>
                                                    <span class="status-badge status-pending">Pending</span>
                                                <?php elseif ($result['approvalStatus'] === 'Approved'): ?>
                                                    <span class="status-badge status-approved">Approved</span>
                                                <?php else: ?>
                                                    <span class="status-badge status-rejected">Rejected</span>
                                                <?php endif; ?>
                                                <br>
                                                <small class="text-muted">by <?= htmlspecialchars($result['addedByName'] ?? '') ?></small>
                                            </td>
                                            <td class="action-buttons">
                                                <button class="btn btn-sm btn-info" onclick="viewResult(<?= $result['resultId'] ?>)">
                                                    <i class="ri-eye-line"></i>
                                                </button>
                                                <?php if ($result['approvalStatus'] === 'Pending'): ?>
                                                    <button class="btn btn-sm btn-warning" onclick="editResult(<?= $result['resultId'] ?>)">
                                                        <i class="ri-edit-line"></i>
                                                    </button>
                                                    <?php if ($role === 'Admin'): ?>
                                                        <button class="btn btn-sm btn-success" onclick="approveResult(<?= $result['resultId'] ?>)">
                                                            <i class="ri-check-line"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-danger" onclick="rejectResult(<?= $result['resultId'] ?>)">
                                                            <i class="ri-close-line"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                <button class="btn btn-sm btn-dark" onclick="deleteResult(<?= $result['resultId'] ?>)">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Result Modal -->
    <div class="modal fade" id="resultModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data" id="resultForm">
                    <div class="modal-header" style="background: linear-gradient(135deg, #ff6200 0%, #994524 100%); color: white;">
                        <h5 class="modal-title"><i class="ri-file-chart-line"></i> <span id="modalTitle">Add Result</span></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" id="formAction" value="create">
                        <input type="hidden" name="resultId" id="resultId">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><span class="required">*</span> Result Title</label>
                                    <input type="text" name="resultTitle" class="form-control" required id="resultTitle">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><span class="required">*</span> Event</label>
                                    <select name="eventId" class="form-control" required id="eventSelect" onchange="updateOrganizer()">
                                        <option value="">Select Event</option>
                                        <?php foreach ($eventsForDropdown as $event): ?>
                                            <option value="<?= $event['eventId'] ?>" data-organizer="<?= $event['createdBy'] ?? '' ?>">
                                                <?= htmlspecialchars($event['eventName']) ?> (<?= date('M d, Y', strtotime($event['startDate'])) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><span class="required">*</span> Result Date</label>
                                    <input type="date" name="resultDate" class="form-control" max="<?= date('Y-m-d') ?>" required id="resultDate">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Primary Skill</label>
                                    <select name="skillId" class="form-control" id="skillSelect">
                                        <option value="">Select Skill (Optional)</option>
                                        <?php foreach ($skills as $skill): ?>
                                            <option value="<?= $skill['skillId'] ?>"><?= htmlspecialchars($skill['skillName']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Organizer Field -->
                        <div class="mb-3">
                            <label class="form-label">Organizer</label>
                            <?php if ($role === 'Admin'): ?>
                                <select name="organizerId" class="form-control" id="organizerSelect">
                                    <option value="">Select Organizer</option>
                                    <?php foreach ($organizers as $org): ?>
                                        <option value="<?= $org['userId'] ?>">
                                            <?= htmlspecialchars($org['name']) ?> (<?= htmlspecialchars($org['email']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Select the organizer responsible for this result</small>
                            <?php else: ?>
                                <input type="hidden" name="organizerId" id="organizerId" value="<?= $userId ?>">
                                <div class="form-control" readonly>
                                    <i class="ri-user-star-line"></i> <?= htmlspecialchars($name) ?> (You)
                                </div>
                                <small class="text-muted">You are listed as the organizer for this result</small>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label"><span class="required">*</span> Description</label>
                            <textarea name="description" class="form-control" rows="4" required id="description"
                                      placeholder="Describe the event results, outcomes, achievements..."></textarea>
                        </div>
                        
                        <!-- Main Image Upload -->
                        <div class="mb-3">
                            <label class="form-label">Main Result Image (Required)</label>
                            <input type="file" name="resultImage" class="form-control" accept="image/*" id="resultImage">
                            <small class="text-muted">Main image for the result. Max 5MB. JPG, PNG, GIF, WebP allowed.</small>
                            <div id="mainImagePreview" class="image-preview-container mt-2"></div>
                        </div>
                        
                        <!-- Additional Images Upload -->
                        <div class="additional-images-section">
                            <h6><i class="ri-image-line"></i> Additional Images (Optional)</h6>
                            <p class="text-muted">You can upload up to 4 additional images.</p>
                            
                            <div class="row">
                                <?php for ($i = 2; $i <= 5; $i++): ?>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Additional Image <?= $i-1 ?></label>
                                        <input type="file" name="resultImage<?= $i ?>" class="form-control" accept="image/*" 
                                               id="resultImage<?= $i ?>">
                                        <div id="imagePreview<?= $i ?>" class="image-preview-container mt-2"></div>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                        
                        <!-- Existing Images Section (for edit mode) -->
                        <div id="existingImagesSection" style="display: none;">
                            <div class="additional-images-section">
                                <h6><i class="ri-image-line"></i> Existing Images</h6>
                                <p class="text-muted">Check images you want to keep.</p>
                                <div id="existingImagesPreview" class="image-preview-container"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitButton">Save Result</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Result Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #ff6200 0%, #994524 100%); color: white;">
                    <h5 class="modal-title"><i class="ri-eye-line"></i> Result Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="viewModalContent">
                    <!-- Content loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="ri-check-line"></i> Approve Result</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="approve">
                        <input type="hidden" name="resultId" id="approveResultId">
                        <p>Are you sure you want to approve this result?</p>
                        <div class="mb-3">
                            <label class="form-label">Notes (Optional)</label>
                            <textarea name="reviewNotes" class="form-control" rows="3" placeholder="Add approval notes..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Approve</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="ri-close-line"></i> Reject Result</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="reject">
                        <input type="hidden" name="resultId" id="rejectResultId">
                        <p>Are you sure you want to reject this result?</p>
                        <div class="mb-3">
                            <label class="form-label"><span class="required">*</span> Reason for Rejection</label>
                            <textarea name="reviewNotes" class="form-control" rows="3" placeholder="Provide a reason for rejection..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="ri-delete-bin-line"></i> Delete Result</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="resultId" id="deleteResultId">
                        <p>Are you sure you want to delete this result?</p>
                        <p class="text-danger"><i class="ri-alert-line"></i> This action cannot be undone!</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/dashboards.js"></script>
    <script>
        // Function to update organizer based on selected event
        function updateOrganizer() {
            const eventSelect = document.getElementById('eventSelect');
            const selectedOption = eventSelect.options[eventSelect.selectedIndex];
            const organizerId = selectedOption.getAttribute('data-organizer');
            
            // Only update if organizer select exists (admin only)
            const organizerSelect = document.getElementById('organizerSelect');
            if (organizerSelect && organizerId) {
                // Try to select the event's organizer
                for (let i = 0; i < organizerSelect.options.length; i++) {
                    if (organizerSelect.options[i].value == organizerId) {
                        organizerSelect.selectedIndex = i;
                        break;
                    }
                }
            }
        }
        
        // Image preview functions
        function setupImagePreview(inputId, previewId) {
            const input = document.getElementById(inputId);
            const preview = document.getElementById(previewId);
            
            if (input && preview) {
                input.addEventListener('change', function(e) {
                    preview.innerHTML = '';
                    
                    if (this.files && this.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const div = document.createElement('div');
                            div.className = 'image-preview';
                            
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.alt = 'Preview';
                            
                            div.appendChild(img);
                            preview.appendChild(div);
                        }
                        reader.readAsDataURL(this.files[0]);
                    }
                });
            }
        }
        
        // Initialize image previews
        setupImagePreview('resultImage', 'mainImagePreview');
        for (let i = 2; i <= 5; i++) {
            setupImagePreview('resultImage' + i, 'imagePreview' + i);
        }
        
        function showAddModal() {
            document.getElementById('modalTitle').textContent = 'Add Result';
            document.getElementById('formAction').value = 'create';
            document.getElementById('resultForm').reset();
            document.getElementById('mainImagePreview').innerHTML = '';
            for (let i = 2; i <= 5; i++) {
                document.getElementById('imagePreview' + i).innerHTML = '';
            }
            document.getElementById('existingImagesSection').style.display = 'none';
            document.getElementById('resultDate').value = '<?= date("Y-m-d") ?>';
            
            // Set default organizer for non-admin users
            <?php if ($role !== 'Admin'): ?>
                document.getElementById('organizerId').value = '<?= $userId ?>';
            <?php endif; ?>
            
            new bootstrap.Modal(document.getElementById('resultModal')).show();
        }

        function editResult(resultId) {
            fetch('get_result.php?id=' + resultId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const result = data.result;
                        document.getElementById('modalTitle').textContent = 'Edit Result';
                        document.getElementById('formAction').value = 'update';
                        document.getElementById('resultId').value = resultId;
                        
                        // Fill form fields
                        document.getElementById('resultTitle').value = result.resultTitle;
                        document.getElementById('eventSelect').value = result.eventId;
                        document.getElementById('resultDate').value = result.resultDate;
                        document.getElementById('skillSelect').value = result.skillId || '';
                        document.getElementById('description').value = result.description;
                        
                        // Set organizer (for admin only)
                        <?php if ($role === 'Admin'): ?>
                            const organizerSelect = document.getElementById('organizerSelect');
                            if (organizerSelect) {
                                organizerSelect.value = result.organizerId || '';
                            }
                        <?php else: ?>
                            document.getElementById('organizerId').value = result.organizerId || '<?= $userId ?>';
                        <?php endif; ?>
                        
                        // Clear existing previews
                        document.getElementById('mainImagePreview').innerHTML = '';
                        for (let i = 2; i <= 5; i++) {
                            document.getElementById('imagePreview' + i).innerHTML = '';
                        }
                        
                        // Show existing images section
                        const existingImagesSection = document.getElementById('existingImagesSection');
                        const existingImagesPreview = document.getElementById('existingImagesPreview');
                        existingImagesPreview.innerHTML = '';
                        
                        if (result.allImages && result.allImages.length > 0) {
                            existingImagesSection.style.display = 'block';
                            
                            // Display existing images with keep checkboxes
                            result.allImages.forEach((imagePath, index) => {
                                const div = document.createElement('div');
                                div.className = 'image-preview';
                                
                                const img = document.createElement('img');
                                img.src = '../' + imagePath;
                                img.alt = 'Existing Image';
                                
                                const checkboxDiv = document.createElement('div');
                                checkboxDiv.className = 'keep-checkbox';
                                
                                const checkbox = document.createElement('input');
                                checkbox.type = 'checkbox';
                                checkbox.name = 'keep_' + (index === 0 ? 'resultImage' : 'resultImage' + (index + 1));
                                checkbox.value = '1';
                                checkbox.checked = true;
                                checkbox.id = 'keepImage' + (index + 1);
                                
                                const label = document.createElement('label');
                                label.htmlFor = 'keepImage' + (index + 1);
                                label.innerHTML = '<i class="ri-check-line"></i>';
                                label.style.marginLeft = '5px';
                                label.style.cursor = 'pointer';
                                
                                checkboxDiv.appendChild(checkbox);
                                checkboxDiv.appendChild(label);
                                div.appendChild(checkboxDiv);
                                div.appendChild(img);
                                
                                existingImagesPreview.appendChild(div);
                            });
                        } else {
                            existingImagesSection.style.display = 'none';
                        }
                        
                        new bootstrap.Modal(document.getElementById('resultModal')).show();
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function viewResult(resultId) {
            fetch('get_result.php?id=' + resultId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const result = data.result;
                        let imagesHTML = '';
                        
                        if (result.allImages && result.allImages.length > 0) {
                            imagesHTML = `
                                <div class="mb-3">
                                    <h6>Images (${result.allImages.length})</h6>
                                    <div class="row">
                            `;
                            
                            result.allImages.forEach((imagePath, index) => {
                                imagesHTML += `
                                    <div class="col-md-4 mb-3">
                                        <img src="../${imagePath}" class="img-fluid img-thumbnail" 
                                             alt="Result Image ${index + 1}">
                                        <p class="text-center small mt-1">Image ${index + 1}</p>
                                    </div>
                                `;
                            });
                            
                            imagesHTML += `
                                    </div>
                                </div>
                            `;
                        }
                        
                        const content = `
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Result Information</h6>
                                    <p><strong>Title:</strong> ${result.resultTitle}</p>
                                    <p><strong>Event:</strong> ${result.eventName}</p>
                                    <p><strong>Organizer:</strong> ${result.organizerName || 'Not specified'}</p>
                                    <p><strong>Date:</strong> ${new Date(result.resultDate).toLocaleDateString()}</p>
                                    <p><strong>Skill:</strong> ${result.skillName || 'N/A'}</p>
                                    <p><strong>Category:</strong> ${result.category || 'N/A'}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Submission Details</h6>
                                    <p><strong>Added by:</strong> ${result.addedByName} (${result.addedByRole})</p>
                                    <p><strong>Status:</strong> <span class="badge bg-${result.approvalStatus === 'Pending' ? 'warning' : result.approvalStatus === 'Approved' ? 'success' : 'danger'}">${result.approvalStatus}</span></p>
                                    ${result.approvedByName ? `<p><strong>Approved by:</strong> ${result.approvedByName}</p>` : ''}
                                    ${result.approvalNotes ? `<p><strong>Notes:</strong> ${result.approvalNotes}</p>` : ''}
                                </div>
                            </div>
                            <hr>
                            <div class="mb-3">
                                <h6>Description</h6>
                                <p>${result.description.replace(/\n/g, '<br>')}</p>
                            </div>
                            ${imagesHTML}
                        `;
                        
                        document.getElementById('viewModalContent').innerHTML = content;
                        new bootstrap.Modal(document.getElementById('viewModal')).show();
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function approveResult(resultId) {
            document.getElementById('approveResultId').value = resultId;
            new bootstrap.Modal(document.getElementById('approveModal')).show();
        }

        function rejectResult(resultId) {
            document.getElementById('rejectResultId').value = resultId;
            new bootstrap.Modal(document.getElementById('rejectModal')).show();
        }

        function deleteResult(resultId) {
            document.getElementById('deleteResultId').value = resultId;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }

        // Auto-hide alerts
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                bootstrap.Alert.getOrCreateInstance(alert).close();
            });
        }, 5000);
    </script>
</body>
</html>