<?php
session_start();

if (!isset($_SESSION['name'])) {
    echo "Not logged in!";
    exit();
}

$name = $_SESSION['name'];
$userId = $_SESSION['userId'];
$userRole = $_SESSION['role'];

// Add database connection and message logic
require_once __DIR__ . "/../data_access/db.php";
require_once __DIR__ . "/../business_logic/MessageLogic.php";
require_once __DIR__ . "/../business_logic/calendarLogic.php";

$messageLogic = new MessageLogic($conn);
$inboxResult = $messageLogic->getInbox($userId, 1, 1);
$unreadCount = $inboxResult['unreadCount'];

// Get events for calendar
$calendarLogic = new CalendarLogic();
$calendarData = $calendarLogic->getCalendarViewData($userId, $userRole);
$totalEvents = $calendarData['totalEvents'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Dashboard</title>
    <link rel="stylesheet" href="../assets/css/a9.css">
    <link rel="icon" type="image/png" href="../assets/images/title.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
</head>
<body>
    <!-- sidebar navigation -->
<nav class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <img src="../assets/images/logo.png" alt="Logo" class="logo-img">
    </div>
    <div class="nav-items">

        <?php if ($userRole === 'Admin'): ?>
            <div class="nav-item">
            <a href="admin_dashboard.php" class="active">
                <i class="ri-dashboard-line"></i>
                <span>Dashboard</span>
            </a>
        </div> 
        <?php endif; ?>

        <?php if ($userRole === 'Coordinator'): ?>
            <div class="nav-item">
            <a href="coordinator_dashboard.php" class="active">
                <i class="ri-dashboard-line"></i>
                <span>Dashboard</span>
            </a>
            </div> 
        <?php endif; ?>

        <?php if ($userRole === 'Organizer'): ?>
            <div class="nav-item">
            <a href="coordinator_dashboard.php" class="active">
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

        <?php if ($userRole === 'Coordinator'): ?>
        <div class="nav-item">
            <a href="mark_attendance.php">
                <i class="ri-checkbox-circle-line"></i>
                <span>Mark Attendance</span>
            </a>
        </div>
        <?php endif; ?>

        <?php if ($userRole === 'Admin' || $userRole === 'Coordinator'): ?>
            <div class="nav-item">
                <a href="volunteer_management.php">
                    <i class="ri-user-star-line"></i>
                    <span>Volunteers Management</span>
                </a>
            </div>
        <?php endif; ?>

        <?php if ($userRole === 'Admin'): ?>
        <div class="nav-item">
                <a href="coordinator_management.php">
                    <i class="ri-group-line"></i>
                    <span>Coordinator Management</span>
                </a>
        </div> 
        <?php endif; ?>

        <?php if ($userRole === 'Admin'): ?>
            <div class="nav-item">
                <a href="organizer_requests.php">
                    <i class="ri-shield-user-line"></i>
                    <span>Organizers Management</span>
                </a>
            </div> 
        <?php endif; ?>

        <?php if ($userRole === 'Admin'): ?>
            <div class="nav-item">
                <a href="issue_certificates.php">
                    <i class="ri-user-settings-line"></i>
                    <span>Certificate issue</span>
                </a>
            </div>
        <?php endif; ?>

        <?php if ($userRole === 'Admin' || $userRole === 'Coordinator'): ?>
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
            <a href="results_management.php">
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


    <!-- main content -->
    <div class="main-content" id="mainContent">
        <!-- top header -->
        <header class="top-header">
            <div class="welcome-text">
                <button class="menu-toggle" id="menuToggle">
                    <i class="ri-menu-line"></i>
                </button>
                Welcome , <?php echo $name; ?>

              
            </div>
            <div class="header-actions">
                
                <!-- Notification Dropdown -->
                <div class="dropdown me-3">
                    <button class="btn p-0 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="ri-notification-3-line"></i>
                        <?php if ($unreadCount > 0): ?>
                            <span class="notification-badge"><?php echo $unreadCount; ?></span>
                        <?php endif; ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="inbox.php">
                                <i class="ri-mail-line me-2"></i>Messages
                                <?php if ($unreadCount > 0): ?>
                                    <span class="badge bg-danger float-end"><?php echo $unreadCount; ?> new</span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li><a class="dropdown-item" href="sent_messages.php"><i class="ri-send-plane-line me-2"></i>Sent Messages</a></li>
                        <li><a class="dropdown-item" href="send_message.php"><i class="ri-pencil-line me-2"></i>Compose</a></li>
                    </ul>
                </div>

                <!-- Profile Dropdown -->
                <div class="dropdown">
                    <button class="btn p-0 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="ri-user-3-fill header-icon"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="edit_profile.php"><i class="ri-user-line me-2"></i>Edit Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="ri-logout-box-line me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <div class="container-fluid mt-4">
            <!-- Dashboard Stats -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card dashboard-card stat-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-subtitle mb-2">Total Events</h6>
                                    <h2 class="card-title mb-0"><?php echo $totalEvents; ?></h2>
                                </div>
                                <div class="card-icon">
                                    <i class="ri-calendar-2-line text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card dashboard-card stat-card secondary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-subtitle mb-2">Unread Messages</h6>
                                    <h2 class="card-title mb-0"><?php echo $unreadCount; ?></h2>
                                </div>
                                <div class="card-icon">
                                    <i class="ri-message-3-line text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card dashboard-card stat-card success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-subtitle mb-2">Active Volunteers</h6>
                                    <h2 class="card-title mb-0">
                                        <?php 
                                        $activeSql = "SELECT COUNT(*) as active_count FROM users WHERE role = 'Volunteer'";
                                        $activeResult = $conn->query($activeSql);
                                        $activeCount = $activeResult ? $activeResult->fetch_assoc()['active_count'] : 0;
                                        echo $activeCount;
                                        ?>
                                    </h2>
                                </div>
                                <div class="card-icon">
                                    <i class="ri-user-line text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card dashboard-card stat-card warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-subtitle mb-2">Upcoming Events</h6>
                                    <h2 class="card-title mb-0">
                                        <?php 
                                        $upcomingSql = "SELECT COUNT(*) as upcoming FROM events 
                                                       WHERE status = 'Active' AND startDate >= CURDATE()";
                                        $upcomingResult = $conn->query($upcomingSql);
                                        $upcomingCount = $upcomingResult ? $upcomingResult->fetch_assoc()['upcoming'] : 0;
                                        echo $upcomingCount;
                                        ?>
                                    </h2>
                                </div>
                                <div class="card-icon">
                                    <i class="ri-calendar-check-line text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calendar Tabs -->
            <ul class="nav nav-tabs" id="dashboardTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="calendar-tab" data-bs-toggle="tab" data-bs-target="#calendar" type="button" role="tab">
                        <i class="ri-calendar-2-line me-2"></i>Calendar View
                        <?php if ($totalEvents > 0): ?>
                            <span class="badge bg-primary ms-2"><?php echo $totalEvents; ?></span>
                        <?php endif; ?>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="quick-actions-tab" data-bs-toggle="tab" data-bs-target="#quick-actions" type="button" role="tab">
                        <i class="ri-rocket-line me-2"></i>Quick Actions
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="recent-tab" data-bs-toggle="tab" data-bs-target="#recent" type="button" role="tab">
                        <i class="ri-history-line me-2"></i>Recent Activity
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="dashboardTabContent">
                <!-- Calendar Tab -->
                <div class="tab-pane fade show active" id="calendar" role="tabpanel">
                    <div class="card mt-3">
                        <div class="card-header bg-white border-bottom">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h5 class="mb-0">
                                        <i class="ri-calendar-2-line me-2"></i>
                                        Event Calendar - 
                                        <span class="badge bg-primary"><?php echo $userRole; ?></span>
                                    </h5>
                                    <p class="text-muted mb-0 small">
                                        Viewing events based on your role: 
                                        <?php 
                                        switch($userRole) {
                                            case 'Admin': echo 'All events'; break;
                                            case 'Coordinator': echo 'Your assigned events'; break;
                                            case 'Organizer': echo 'Events you organized'; break;
                                            case 'Volunteer': echo 'Events you joined'; break;
                                            default: echo 'Your events';
                                        }
                                        ?>
                                    </p>
                                </div>
                                <div class="col-md-6 text-end">
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-outline-primary" id="calendar-prev">
                                            <i class="ri-arrow-left-line"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary" id="calendar-today">Today</button>
                                        <button class="btn btn-sm btn-outline-primary" id="calendar-next">
                                            <i class="ri-arrow-right-line"></i>
                                        </button>
                                    </div>

                                    <button class="btn btn-sm btn-outline-success ms-2" id="refresh-calendar">
                                        <i class="ri-refresh-line"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div id="calendar-container"></div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions Tab -->
                <div class="tab-pane fade" id="quick-actions" role="tabpanel">
                    <div class="row mt-3">
                        <?php if ($userRole === 'Volunteer'): ?>
                            <!-- Volunteer Quick Actions -->
                            <div class="col-md-4 mb-3">
                                <a href="events_volunteer.php" class="text-decoration-none">
                                    <div class="quick-action-item">
                                        <div class="d-flex align-items-center">
                                            <div class="quick-action-icon bg-primary text-white me-3">
                                                <i class="ri-calendar-check-line"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">Join New Events</h6>
                                                <p class="text-muted mb-0 small">Browse and join upcoming events</p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <a href="my_events.php" class="text-decoration-none">
                                    <div class="quick-action-item">
                                        <div class="d-flex align-items-center">
                                            <div class="quick-action-icon bg-success text-white me-3">
                                                <i class="ri-calendar-line"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">My Events</h6>
                                                <p class="text-muted mb-0 small">View your registered events</p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            
                        <?php elseif ($userRole === 'Coordinator'): ?>
                            <!-- Coordinator Quick Actions -->
                            <div class="col-md-4 mb-3">
                                <a href="coordinator_events.php" class="text-decoration-none">
                                    <div class="quick-action-item">
                                        <div class="d-flex align-items-center">
                                            <div class="quick-action-icon bg-primary text-white me-3">
                                                <i class="ri-calendar-line"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">My Assigned Events</h6>
                                                <p class="text-muted mb-0 small">Manage events assigned to you</p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <a href="mark_attendance.php" class="text-decoration-none">
                                    <div class="quick-action-item">
                                        <div class="d-flex align-items-center">
                                            <div class="quick-action-icon bg-success text-white me-3">
                                                <i class="ri-checkbox-circle-line"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">Mark Attendance</h6>
                                                <p class="text-muted mb-0 small">Take attendance for events</p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            
                        <?php elseif ($userRole === 'Organizer'): ?>
                            <!-- Organizer Quick Actions -->
                            <div class="col-md-4 mb-3">
                                <a href="create_event.php" class="text-decoration-none">
                                    <div class="quick-action-item">
                                        <div class="d-flex align-items-center">
                                            <div class="quick-action-icon bg-primary text-white me-3">
                                                <i class="ri-add-circle-line"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">Create Event</h6>
                                                <p class="text-muted mb-0 small">Create new volunteer events</p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <a href="organizer_events.php" class="text-decoration-none">
                                    <div class="quick-action-item">
                                        <div class="d-flex align-items-center">
                                            <div class="quick-action-icon bg-success text-white me-3">
                                                <i class="ri-calendar-line"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">My Events</h6>
                                                <p class="text-muted mb-0 small">Manage events you organized</p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            
                        <?php elseif ($userRole === 'Admin'): ?>
                            <!-- Admin Quick Actions -->
                            <div class="col-md-4 mb-3">
                                <a href="admin_events.php" class="text-decoration-none">
                                    <div class="quick-action-item">
                                        <div class="d-flex align-items-center">
                                            <div class="quick-action-icon bg-primary text-white me-3">
                                                <i class="ri-calendar-line"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">All Events</h6>
                                                <p class="text-muted mb-0 small">Manage all system events</p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <a href="manage_users.php" class="text-decoration-none">
                                    <div class="quick-action-item">
                                        <div class="d-flex align-items-center">
                                            <div class="quick-action-icon bg-success text-white me-3">
                                                <i class="ri-user-settings-line"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">Manage Users</h6>
                                                <p class="text-muted mb-0 small">Manage all users and roles</p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <a href="admin_reports.php" class="text-decoration-none">
                                    <div class="quick-action-item">
                                        <div class="d-flex align-items-center">
                                            <div class="quick-action-icon bg-info text-white me-3">
                                                <i class="ri-bar-chart-line"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">Reports</h6>
                                                <p class="text-muted mb-0 small">View system reports</p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            
                        <?php endif; ?>
                        
                        <!-- Common Quick Actions for All Roles -->
                        <div class="col-md-4 mb-3">
                            <a href="send_message.php" class="text-decoration-none">
                                <div class="quick-action-item">
                                    <div class="d-flex align-items-center">
                                        <div class="quick-action-icon bg-info text-white me-3">
                                            <i class="ri-pencil-line"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Send Message</h6>
                                            <p class="text-muted mb-0 small">Contact other users</p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <a href="inbox.php" class="text-decoration-none">
                                <div class="quick-action-item">
                                    <div class="d-flex align-items-center">
                                        <div class="quick-action-icon bg-purple text-white me-3">
                                            <i class="ri-mail-line"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Inbox</h6>
                                            <p class="text-muted mb-0 small">Check your messages</p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <a href="edit_profile.php" class="text-decoration-none">
                                <div class="quick-action-item">
                                    <div class="d-flex align-items-center">
                                        <div class="quick-action-icon bg-secondary text-white me-3">
                                            <i class="ri-user-line"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Edit Profile</h6>
                                            <p class="text-muted mb-0 small">Update your information</p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Tab -->
                <div class="tab-pane fade" id="recent" role="tabpanel">
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">Recent Activity</h5>
                                </div>
                                <div class="card-body">
                                    <?php
                                    // Get recent activities based on user role
                                    $activities = [];
                                    
                                    if ($userRole === 'Volunteer') {
                                        $activitySql = "SELECT er.*, e.eventName FROM event_registrations er
                                                       JOIN events e ON er.eventId = e.eventId
                                                       WHERE er.userId = ? 
                                                       ORDER BY er.registrationDate DESC 
                                                       LIMIT 5";
                                        $stmt = $conn->prepare($activitySql);
                                        $stmt->bind_param("i", $userId);
                                        $stmt->execute();
                                        $activities = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                                    } elseif ($userRole === 'Coordinator') {
                                        $activitySql = "SELECT a.*, e.eventName FROM attendance a
                                                       JOIN events e ON a.eventId = e.eventId
                                                       JOIN event_coordinators ec ON a.eventId = ec.eventId
                                                       WHERE ec.coordinatorId = ?
                                                       ORDER BY a.attendanceDate DESC 
                                                       LIMIT 5";
                                        $stmt = $conn->prepare($activitySql);
                                        $stmt->bind_param("i", $userId);
                                        $stmt->execute();
                                        $activities = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                                    } elseif ($userRole === 'Organizer') {
                                        $activitySql = "SELECT * FROM events 
                                                       WHERE createdBy = ? 
                                                       ORDER BY createdAt DESC 
                                                       LIMIT 5";
                                        $stmt = $conn->prepare($activitySql);
                                        $stmt->bind_param("i", $userId);
                                        $stmt->execute();
                                        $activities = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                                    } elseif ($userRole === 'Admin') {
                                        $activitySql = "SELECT 'Event Created' as type, eventName as title, createdAt as date 
                                                       FROM events 
                                                       ORDER BY createdAt DESC 
                                                       LIMIT 5";
                                        $activities = $conn->query($activitySql)->fetch_all(MYSQLI_ASSOC);
                                    }
                                    
                                    if (empty($activities)): ?>
                                        <div class="text-center py-5">
                                            <i class="ri-inbox-line display-4 text-muted"></i>
                                            <p class="mt-3 text-muted">No recent activity found</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="list-group list-group-flush">
                                            <?php foreach ($activities as $activity): ?>
                                                <div class="list-group-item border-0 px-0 py-3">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <h6 class="mb-1">
                                                                <?php 
                                                                if ($userRole === 'Volunteer') {
                                                                    echo "Event Registration: " . ($activity['status'] === 'registered' ? 'Joined' : 'Cancelled') . " - " . $activity['eventName'];
                                                                } elseif ($userRole === 'Coordinator') {
                                                                    echo "Attendance Marked for: " . $activity['eventName'];
                                                                } elseif ($userRole === 'Organizer') {
                                                                    echo "Event Created: " . $activity['eventName'];
                                                                } elseif ($userRole === 'Admin') {
                                                                    echo $activity['type'] . ": " . $activity['title'];
                                                                }
                                                                ?>
                                                            </h6>
                                                            <p class="text-muted mb-0 small">
                                                                <?php 
                                                                $date = $activity['registrationDate'] ?? $activity['attendanceDate'] ?? $activity['createdAt'] ?? $activity['date'] ?? '';
                                                                echo date('F j, Y h:i A', strtotime($date));
                                                                ?>
                                                            </p>
                                                        </div>
                                                        <span class="badge bg-light text-dark">
                                                            <?php 
                                                            $date = $activity['registrationDate'] ?? $activity['attendanceDate'] ?? $activity['createdAt'] ?? $activity['date'] ?? '';
                                                            echo date('M d', strtotime($date));
                                                            ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="text-center mt-3">
                                            <a href="#" class="btn btn-sm btn-outline-primary">View All Activity</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Details Modal -->
    <div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventModalTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="eventModalBody">
                    <!-- Event details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <span id="eventStatusBadge" class="me-auto"></span>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="#" class="btn btn-primary" id="eventDetailsLink">View Details</a>
                </div>
            </div>
        </div>
    </div>

    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize FullCalendar
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Initializing calendar...');
            
            var calendarEl = document.getElementById('calendar-container');
            
            if (!calendarEl) {
                console.error('Calendar container not found!');
                return;
            }
            
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                buttonText: {
                    today: 'Today',
                    month: 'Month',
                    week: 'Week',
                    day: 'Day'
                },
                titleFormat: {
                    month: 'long',
                    year: 'numeric'
                },
                themeSystem: 'bootstrap5',
                events: function(fetchInfo, successCallback, failureCallback) {
                    console.log('Fetching events from:', 'get_calendar_events.php');
                    fetch('get_calendar_events.php?start=' + fetchInfo.startStr + '&end=' + fetchInfo.endStr)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            console.log('Events data received:', data);
                            if (data.success) {
                                successCallback(data.events);
                                updateEventCounts(data);
                            } else {
                                console.error('Failed to load events:', data.message);
                                showToast('Error', 'Failed to load calendar events', 'danger');
                                failureCallback(data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error loading events:', error);
                            showToast('Error', 'Network error loading events', 'danger');
                            failureCallback('Failed to load events');
                        });
                },
                eventClick: function(info) {
                    showEventDetails(info.event);
                },
                eventDidMount: function(info) {
                    // Add tooltip with event details
                    const event = info.event;
                    const extendedProps = event.extendedProps;
                    
                    // Create tooltip content
                    const tooltipContent = `
                        <strong>${event.title}</strong><br>
                        <strong>Date:</strong> ${event.start.toLocaleDateString()}<br>
                        <strong>Status:</strong> ${extendedProps.status ? extendedProps.status.toUpperCase() : 'ACTIVE'}
                    `;
                    
                    // Add tooltip
                    info.el.setAttribute('data-bs-toggle', 'tooltip');
                    info.el.setAttribute('data-bs-html', 'true');
                    info.el.setAttribute('title', tooltipContent);
                    
                    // Initialize tooltip
                    new bootstrap.Tooltip(info.el);
                    
                    // Add status indicator
                    if (extendedProps.status === 'cancelled') {
                        info.el.style.opacity = '0.7';
                        info.el.style.textDecoration = 'line-through';
                        info.el.style.borderLeft = '4px solid #dc3545';
                    } else if (extendedProps.status === 'over') {
                        info.el.style.opacity = '0.8';
                        info.el.style.borderLeft = '4px solid #6c757d';
                    } else {
                        // Add color-coded border based on user role
                        const userRole = '<?php echo $userRole; ?>';
                        let borderColor = '#20c997'; // default teal
                        
                        switch(userRole) {
                            case 'Admin': borderColor = '#198754'; break;
                            case 'Coordinator': borderColor = '#0d6efd'; break;
                            case 'Organizer': borderColor = '#ffc107'; break;
                            case 'Volunteer': borderColor = '#6f42c1'; break;
                        }
                        
                        info.el.style.borderLeft = '4px solid ' + borderColor;
                    }
                },
                height: 'auto',
                contentHeight: 550,
                nowIndicator: true,
                navLinks: true,
                editable: false,
                selectable: false,
                dayMaxEvents: 3,
                // Optional: Add these for better mobile experience
                dayHeaderFormat: { weekday: 'short' },
                views: {
                    dayGridMonth: {
                        dayHeaderFormat: { weekday: 'short' }
                    }
                }
            });
            
            console.log('Calendar created, rendering...');
            calendar.render();
            console.log('Calendar rendered');

            // Custom navigation buttons
            document.getElementById('calendar-prev')?.addEventListener('click', function() {
                calendar.prev();
                showToast('Calendar', 'Previous period', 'info');
            });

            document.getElementById('calendar-next')?.addEventListener('click', function() {
                calendar.next();
                showToast('Calendar', 'Next period', 'info');
            });

            document.getElementById('calendar-today')?.addEventListener('click', function() {
                calendar.today();
                showToast('Calendar', 'Back to today', 'success');
            });

            document.getElementById('refresh-calendar')?.addEventListener('click', function() {
                calendar.refetchEvents();
                showToast('Calendar', 'Refreshing events...', 'info');
            });

            // View buttons
            document.getElementById('month-view')?.addEventListener('click', function() {
                calendar.changeView('dayGridMonth');
                updateActiveViewButton('month');
                showToast('View Changed', 'Month view', 'info');
            });

            document.getElementById('week-view')?.addEventListener('click', function() {
                calendar.changeView('timeGridWeek');
                updateActiveViewButton('week');
                showToast('View Changed', 'Week view', 'info');
            });

            document.getElementById('day-view')?.addEventListener('click', function() {
                calendar.changeView('timeGridDay');
                updateActiveViewButton('day');
                showToast('View Changed', 'Day view', 'info');
            });

            // Tab switching
            document.querySelectorAll('#dashboardTabs button').forEach(tab => {
                tab.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-bs-target').substring(1);
                    console.log('Switched to tab:', tabId);
                });
            });

            // Auto-refresh calendar every 5 minutes
            setInterval(function() {
                calendar.refetchEvents();
                console.log('Calendar auto-refreshed');
            }, 300000); // 5 minutes

            // Function to update event counts
            function updateEventCounts(data) {
                const totalEvents = data.totalEvents;
                const userRole = data.userRole;
                
                console.log(`Loaded ${totalEvents} events for ${userRole}`);
            }

            // Function to show event details
            function showEventDetails(event) {
                const extendedProps = event.extendedProps;
                
                // Update modal title
                document.getElementById('eventModalTitle').textContent = event.title;
                
                // Create event details HTML
                let detailsHtml = extendedProps.description || '<p>No details available</p>';
                
                // Set modal body content
                document.getElementById('eventModalBody').innerHTML = detailsHtml;
                
                // Update status badge
                const statusBadge = document.getElementById('eventStatusBadge');
                statusBadge.innerHTML = '';
                
                let badgeClass = 'status-active';
                let badgeText = 'ACTIVE';
                
                if (extendedProps.status === 'cancelled') {
                    badgeClass = 'status-cancelled';
                    badgeText = 'CANCELLED';
                } else if (extendedProps.status === 'over') {
                    badgeClass = 'status-over';
                    badgeText = 'EVENT OVER';
                }
                
                statusBadge.innerHTML = `<span class="event-status-badge ${badgeClass}">${badgeText}</span>`;
                
                // Update details link
                const detailsLink = document.getElementById('eventDetailsLink');
                const userRole = '<?php echo $userRole; ?>';
                
                if (userRole === 'Volunteer') {
                    detailsLink.href = `event_details.php?id=${event.id}`;
                    detailsLink.textContent = 'View Event Details';
                } else {
                    detailsLink.href = `view_event.php?id=${event.id}`;
                    detailsLink.textContent = 'Manage Event';
                }
                
                // Show modal
                const eventModal = new bootstrap.Modal(document.getElementById('eventModal'));
                eventModal.show();
            }

            // Function to update active view button
            function updateActiveViewButton(activeView) {
                const buttons = ['month-view', 'week-view', 'day-view'];
                buttons.forEach(btnId => {
                    const btn = document.getElementById(btnId);
                    if (btnId === `${activeView}-view`) {
                        btn.classList.add('active');
                        btn.classList.remove('btn-outline-secondary');
                        btn.classList.add('btn-primary');
                    } else {
                        btn.classList.remove('active');
                        btn.classList.remove('btn-primary');
                        btn.classList.add('btn-outline-secondary');
                    }
                });
            }

            // Show toast notifications
            function showToast(title, message, type = 'info') {
                // Create toast container if it doesn't exist
                let toastContainer = document.querySelector('.toast-container');
                if (!toastContainer) {
                    toastContainer = document.createElement('div');
                    toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
                    document.body.appendChild(toastContainer);
                }
                
                // Create toast
                const toastId = 'toast-' + Date.now();
                const toast = document.createElement('div');
                toast.className = `toast align-items-center text-bg-${type} border-0`;
                toast.setAttribute('role', 'alert');
                toast.setAttribute('aria-live', 'assertive');
                toast.setAttribute('aria-atomic', 'true');
                toast.id = toastId;
                
                toast.innerHTML = `
                    <div class="d-flex">
                        <div class="toast-body">
                            <strong>${title}</strong>: ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                `;
                
                toastContainer.appendChild(toast);
                
                // Show toast
                const bsToast = new bootstrap.Toast(toast);
                bsToast.show();
                
                // Remove toast after it's hidden
                toast.addEventListener('hidden.bs.toast', function() {
                    toast.remove();
                });
            }

            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

        // Mobile menu toggle
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        
        if (menuToggle && sidebar) {
            menuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('mobile-visible');
            });

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                const isClickInsideSidebar = sidebar.contains(event.target);
                const isClickOnToggle = menuToggle.contains(event.target);
                
                if (!isClickInsideSidebar && !isClickOnToggle && window.innerWidth <= 991) {
                    sidebar.classList.remove('mobile-visible');
                }
            });
        }
        
        // Auto-refresh notification count every 30 seconds
        setInterval(function() {
            fetch('get_unread_count.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateNotificationBadge(data.unreadCount);
                    }
                })
                .catch(error => console.error('Error fetching unread count:', error));
        }, 30000);
        
        function updateNotificationBadge(count) {
            console.log('Updating badge count:', count);
            
            // Update TOP HEADER notification badge
            let topNotificationBadge = document.querySelector('.header-actions .dropdown:first-child .notification-badge');
            let topNotificationButton = document.querySelector('.header-actions .dropdown:first-child .btn');
            
            // Update DROPDOWN MENU badge
            let dropdownBadge = document.querySelector('.dropdown-menu .badge');
            
            // Update SIDEBAR badge
            let sidebarBadge = document.querySelector('.message-badge');
            let sidebarLink = document.querySelector('.nav-item a[href*="messages"]');
            
            if (count > 0) {
                // Update or create TOP HEADER badge
                if (topNotificationBadge) {
                    topNotificationBadge.textContent = count;
                } else if (topNotificationButton) {
                    const newBadge = document.createElement('span');
                    newBadge.className = 'notification-badge';
                    newBadge.textContent = count;
                    topNotificationButton.appendChild(newBadge);
                }
                
                // Update DROPDOWN MENU badge
                if (dropdownBadge) {
                    dropdownBadge.textContent = count + ' new';
                } else {
                    const messagesDropdownItem = document.querySelector('.dropdown-item[href="inbox.php"]');
                    if (messagesDropdownItem) {
                        const newBadge = document.createElement('span');
                        newBadge.className = 'badge bg-danger float-end';
                        newBadge.textContent = count + ' new';
                        messagesDropdownItem.appendChild(newBadge);
                    }
                }
                
                // Update SIDEBAR badge
                if (sidebarBadge) {
                    sidebarBadge.textContent = count;
                } else if (sidebarLink) {
                    const newBadge = document.createElement('span');
                    newBadge.className = 'message-badge';
                    newBadge.textContent = count;
                    sidebarLink.appendChild(newBadge);
                }
            } else {
                // Remove badges if count is 0
                if (topNotificationBadge) topNotificationBadge.remove();
                if (dropdownBadge) dropdownBadge.remove();
                if (sidebarBadge) sidebarBadge.remove();
            }
        }
        
        // Log errors for debugging
        window.addEventListener('error', function(e) {
            console.error('JavaScript error:', e.error);
        });
    </script>
</body>
</html>