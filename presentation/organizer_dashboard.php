<?php
session_start();

if (!isset($_SESSION['name'])) {
    echo "Not logged in!";
    exit();
}

$name = $_SESSION['name'];
$userId = $_SESSION['userId']; // Make sure you have userId in session

// Add database connection and message logic
require_once __DIR__ . "/../data_access/db.php";
require_once __DIR__ . "/../business_logic/MessageLogic.php";

$messageLogic = new MessageLogic($conn);
$inboxResult = $messageLogic->getInbox($userId, 1, 1);
$unreadCount = $inboxResult['unreadCount'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Dashboard</title>
    <link rel="stylesheet" href="../assets/css/a7.css">
    <link rel="icon" type="image/png" href="../assets/images/title.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

</head>
<body>
    <!-- sidebar navigation -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <img src="../assets/images/logo.png" alt="Logo" class="logo-img">
        </div>
        <div class="nav-items">
            <div class="nav-item">
                <a href="#" class="active">
                    <i class="ri-dashboard-line"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="events.php">
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
                    <span>Certificates</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="view_attendance.php">
                    <i class="ri-trophy-line"></i>
                    <span>View Attendance</span>
                </a>
            </div>
<div class="nav-item">
    <a href="inbox.php" class="messages-link">
        <i class="ri-message-3-line"></i>
        <span>Messages</span>
        <?php if ($unreadCount > 0): ?>
            <span class="message-badge"><?php echo $unreadCount; ?></span>
        <?php endif; ?>
    </a>
</div>
            <div class="nav-item">
                <a href="apply_organizer.php">
                    <i class="ri-feedback-line"></i>
                    <span>Organizer</span>
                </a>
            </div>

                        <div class="nav-item">
                <a href="results_management.php">
                    <i class="ri-feedback-line"></i>
                    <span>Result management</span>
                </a>
            </div>

            <div class="nav-item">
                <a href="past_event_main.php">
                    <i class="ri-trophy-line"></i>
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

    <!-- main content -->
    <div class="main-content" id="mainContent">
        <!-- top header -->
        <header class="top-header">
            <div class="welcome-text">
                <button class="menu-toggle" id="menuToggle">
                    <i class="ri-menu-line"></i>
                </button>
                Welcome Organizer, <?php echo $name; ?>
            </div>
            <div class="header-actions">
                
                <!-- Notification Dropdown - FIRST dropdown -->
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
                                <i class="ri-edit-line me-2"></i>Messages
                                <?php if ($unreadCount > 0): ?>
                                    <span class="badge bg-danger float-end"><?php echo $unreadCount; ?> new</span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li><a class="dropdown-item" href="sent_messages.php"><i class="ri-send-plane-line me-2"></i>Sent Messages</a></li>
                        <li><a class="dropdown-item" href="send_message.php"><i class="ri-pencil-line me-2"></i>Compose</a></li>
                    </ul>
                </div>

                <!-- Profile Dropdown - LAST dropdown -->
                <div class="dropdown">
                    <button class="btn p-0 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="ri-user-3-fill header-icon"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="organizer_edit_profile.php"><i class="ri-edit-line me-2"></i>Edit Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="ri-logout-box-line me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- content area -->
        <div class="content-area">
           
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // mobile menu toggle
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('mobile-visible');
        });

        // close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const isClickInsideSidebar = sidebar.contains(event.target);
            const isClickOnToggle = menuToggle.contains(event.target);
            
            if (!isClickInsideSidebar && !isClickOnToggle && window.innerWidth <= 991) {
                sidebar.classList.remove('mobile-visible');
            }
        });
        
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
        }, 30000); // 30 seconds
        
        function updateNotificationBadge(count) {
            console.log('Updating badge count:', count);
            
            // Update TOP HEADER notification badge (only first dropdown)
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
                    // Find the Messages dropdown item and add badge
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
                
                // Remove TOP HEADER badge
                if (topNotificationBadge) {
                    topNotificationBadge.remove();
                }
                
                // Remove DROPDOWN MENU badge
                if (dropdownBadge) {
                    dropdownBadge.remove();
                }
                
                // Remove SIDEBAR badge
                if (sidebarBadge) {
                    sidebarBadge.remove();
                }
            }
        }
    </script>
</body>
</html>