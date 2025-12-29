<?php
// organizer_requests.php --> presentation folder
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['name']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['userId'];
$name = $_SESSION['name'];
$role = $_SESSION['role'];

require_once __DIR__ . "/../data_access/db.php";
require_once __DIR__ . "/../business_logic/OrganizerLogic.php";
require_once __DIR__ . "/../business_logic/MessageLogic.php";

$organizerLogic = new OrganizerLogic($conn);
$messageLogic = new MessageLogic($conn);
$inboxResult = $messageLogic->getInbox($userId, 1, 1);
$unreadCount = $inboxResult['unreadCount'];

$message = "";
$messageType = "";

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'approve') {
        $result = $organizerLogic->approveOrganizerRequest(
            $_POST['requestId'],
            $userId,
            $_POST['reviewNotes'] ?? ''
        );
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'danger';
    }
    
    elseif ($action === 'reject') {
        $result = $organizerLogic->rejectOrganizerRequest(
            $_POST['requestId'],
            $userId,
            $_POST['reviewNotes']
        );
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'danger';
    }
    
    elseif ($action === 'delete') {
        $result = $organizerLogic->deleteOrganizerRequest($_POST['requestId']);
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'danger';
    }
}

// Get filter from URL
$filterStatus = $_GET['status'] ?? null;

// Get all requests
$requests = $organizerLogic->getAllOrganizerRequests($filterStatus);
$stats = $organizerLogic->getRequestStatistics();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organizer Requests Management</title>
    <link rel="stylesheet" href="../assets/css/a7.css">
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
                <a href="admin_dashboard.php">
                    <i class="ri-dashboard-line"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="coordinator_management.php">
                    <i class="ri-user-settings-line"></i>
                    <span>Coordinator Management</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="organizer_requests.php" class="active">
                    <i class="ri-award-line"></i>
                    <span>Organizer Requests</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="volunteer_management.php">
                    <i class="ri-add-circle-line"></i>
                    <span>Volunteers</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="view_messages.php">
                    <i class="ri-message-3-line"></i>
                    <span>Messages</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="logout.php">
                    <i class="ri-logout-box-line"></i>
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
                        <li><a class="dropdown-item" href="inbox.php"><i class="ri-edit-line me-2"></i>Messages</a></li>
                        <li><a class="dropdown-item" href="send_message.php"><i class="ri-pencil-line me-2"></i>Compose</a></li>
                    </ul>
                </div>
                <div class="dropdown">
                    <button class="btn p-0 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="ri-user-3-fill header-icon"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="edit_profile_admin.php"><i class="ri-edit-line me-2"></i>Edit Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="ri-logout-box-line me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <div class="container1">
            <h1 class="text-left page-title mb-4">
                <i class="ri-award-line"></i> Organizer Requests Management
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
                            <h3 class="text-primary"><?= $stats['total'] ?></h3>
                            <p class="mb-0">Total Requests</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-warning"><?= $stats['pending'] ?></h3>
                            <p class="mb-0">Pending</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-success"><?= $stats['approved'] ?></h3>
                            <p class="mb-0">Approved</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-danger"><?= $stats['rejected'] ?></h3>
                            <p class="mb-0">Rejected</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="btn-group" role="group">
                                <a href="organizer_requests.php" class="btn <?= !$filterStatus ? 'btn-primary' : 'btn-outline-primary' ?>">
                                    <i class="ri-file-list-line"></i> All
                                </a>
                                <a href="organizer_requests.php?status=Pending" class="btn <?= $filterStatus === 'Pending' ? 'btn-warning' : 'btn-outline-warning' ?>">
                                    <i class="ri-time-line"></i> Pending
                                </a>
                                <a href="organizer_requests.php?status=Approved" class="btn <?= $filterStatus === 'Approved' ? 'btn-success' : 'btn-outline-success' ?>">
                                    <i class="ri-checkbox-circle-line"></i> Approved
                                </a>
                                <a href="organizer_requests.php?status=Rejected" class="btn <?= $filterStatus === 'Rejected' ? 'btn-danger' : 'btn-outline-danger' ?>">
                                    <i class="ri-close-circle-line"></i> Rejected
                                </a>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <input type="text" id="searchInput" class="form-control" placeholder="Search by name or email..." onkeyup="searchRequests()">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Requests List -->
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="ri-list-check"></i> Organizer Requests</h4>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="table table-hover" id="requestsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Applicant</th>
                                    <th>Organization</th>
                                    <th>Experience</th>
                                    <th>Request Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="requestsTableBody">
                                <?php if (empty($requests)): ?>
                                    <tr class="no-results">
                                        <td colspan="7" class="text-center text-muted">No requests found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($requests as $request): ?>
                                        <tr class="request-row" 
                                            data-name="<?= strtolower(htmlspecialchars($request['userName'])) ?>"
                                            data-email="<?= strtolower(htmlspecialchars($request['userEmail'])) ?>">
                                            <td><?= $request['requestId'] ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($request['userName']) ?></strong><br>
                                                <small class="text-muted"><?= htmlspecialchars($request['userEmail']) ?></small>
                                            </td>
                                            <td>
                                                <?php if ($request['organizationName']): ?>
                                                    <strong><?= htmlspecialchars($request['organizationName']) ?></strong><br>
                                                    <small class="text-muted"><?= htmlspecialchars($request['organizationType']) ?></small>
                                                <?php else: ?>
                                                    <span class="text-muted">Individual</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= $request['yearsOfExperience'] ?> years</td>
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
                                            <td class="action-buttons">
                                                <button class="btn btn-sm btn-info" onclick="viewRequest(<?= htmlspecialchars(json_encode($request)) ?>)">
                                                    <i class="ri-eye-line"></i> View
                                                </button>
                                                <?php if ($request['requestStatus'] === 'Pending'): ?>
                                                    <button class="btn btn-sm btn-success" onclick="approveRequest(<?= $request['requestId'] ?>, '<?= htmlspecialchars($request['userName']) ?>')">
                                                        <i class="ri-check-line"></i> Approve
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="rejectRequest(<?= $request['requestId'] ?>, '<?= htmlspecialchars($request['userName']) ?>')">
                                                        <i class="ri-close-line"></i> Reject
                                                    </button>
                                                <?php endif; ?>
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

    <!-- View Request Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #ff6200 0%, #994524 100%); color: white;">
                    <h5 class="modal-title"><i class="ri-file-text-line"></i> Request Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="viewModalContent">
                    <!-- Content will be loaded dynamically -->
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
                        <h5 class="modal-title"><i class="ri-check-line"></i> Approve Request</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="approve">
                        <input type="hidden" name="requestId" id="approveRequestId">
                        <p>Are you sure you want to approve <strong id="approveUserName"></strong> as an organizer?</p>
                        <div class="mb-3">
                            <label class="form-label">Notes (Optional)</label>
                            <textarea name="reviewNotes" class="form-control" rows="3" placeholder="Add any notes or comments..."></textarea>
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
                        <h5 class="modal-title"><i class="ri-close-line"></i> Reject Request</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="reject">
                        <input type="hidden" name="requestId" id="rejectRequestId">
                        <p>Are you sure you want to reject <strong id="rejectUserName"></strong>'s request?</p>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/dashboards.js"></script>
    <script>
        function viewRequest(request) {
            const content = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Applicant Information</h6>
                        <p><strong>Name:</strong> ${request.userName}</p>
                        <p><strong>Email:</strong> ${request.userEmail}</p>
                        <p><strong>Phone:</strong> ${request.telephoneNo || 'N/A'}</p>
                        <p><strong>Location:</strong> ${request.location || 'N/A'}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Organization Information</h6>
                        <p><strong>Organization:</strong> ${request.organizationName || 'Individual'}</p>
                        <p><strong>Type:</strong> ${request.organizationType || 'N/A'}</p>
                        <p><strong>Experience:</strong> ${request.yearsOfExperience} years</p>
                    </div>
                </div>
                <hr>
                <div class="mb-3">
                    <h6>Organization Description</h6>
                    <p>${request.organizationDescription || 'N/A'}</p>
                </div>
                <div class="mb-3">
                    <h6>Previous Events</h6>
                    <p>${request.previousEvents || 'No previous events listed'}</p>
                </div>
                <div class="mb-3">
                    <h6>Motivation</h6>
                    <p>${request.motivation}</p>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Request Date:</strong> ${new Date(request.requestDate).toLocaleDateString()}</p>
                        <p><strong>Status:</strong> <span class="badge bg-${request.requestStatus === 'Pending' ? 'warning' : request.requestStatus === 'Approved' ? 'success' : 'danger'}">${request.requestStatus}</span></p>
                    </div>
                    ${request.reviewDate ? `
                    <div class="col-md-6">
                        <p><strong>Reviewed By:</strong> ${request.reviewerName || 'N/A'}</p>
                        <p><strong>Review Date:</strong> ${new Date(request.reviewDate).toLocaleDateString()}</p>
                        <p><strong>Review Notes:</strong> ${request.reviewNotes || 'N/A'}</p>
                    </div>
                    ` : ''}
                </div>
            `;
            
            document.getElementById('viewModalContent').innerHTML = content;
            new bootstrap.Modal(document.getElementById('viewModal')).show();
        }

        function approveRequest(requestId, userName) {
            document.getElementById('approveRequestId').value = requestId;
            document.getElementById('approveUserName').textContent = userName;
            new bootstrap.Modal(document.getElementById('approveModal')).show();
        }

        function rejectRequest(requestId, userName) {
            document.getElementById('rejectRequestId').value = requestId;
            document.getElementById('rejectUserName').textContent = userName;
            new bootstrap.Modal(document.getElementById('rejectModal')).show();
        }

        function searchRequests() {
            const searchInput = document.getElementById('searchInput').value.toLowerCase().trim();
            const rows = document.querySelectorAll('.request-row');
            let visibleCount = 0;

            rows.forEach(row => {
                const name = row.getAttribute('data-name');
                const email = row.getAttribute('data-email');
                
                if (name.includes(searchInput) || email.includes(searchInput)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
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