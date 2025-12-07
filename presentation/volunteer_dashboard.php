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
                <a href="events_volunteer.php">
                    <i class="ri-calendar-check-line"></i>
                    <span>Join Events</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="#">
                    <i class="ri-calendar-line"></i>
                    <span>Calendar</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="#">
                    <i class="ri-medal-line"></i>
                    <span>Certificates</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="#">
                    <i class="ri-trophy-line"></i>
                    <span>Leaderboard</span>
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
                <a href="#">
                    <i class="ri-feedback-line"></i>
                    <span>Feedback</span>
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
                Welcome Volunteer, <?php echo $name; ?>
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
                        <li><a class="dropdown-item" href="edit_profile.php"><i class="ri-edit-line me-2"></i>Edit Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="ri-logout-box-line me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- content area -->
        <div class="content-area">
            <!-- stats cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="ri-calendar-check-fill"></i>
                    </div>
                    <div class="stat-info">
                        <h3>12</h3>
                        <p>Events Joined</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="ri-medal-fill"></i>
                    </div>
                    <div class="stat-info">
                        <h3>8</h3>
                        <p>Certificates</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="ri-trophy-fill"></i>
                    </div>
                    <div class="stat-info">
                        <h3>5</h3>
                        <p>Badges Earned</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple">
                        <i class="ri-time-fill"></i>
                    </div>
                    <div class="stat-info">
                        <h3>42</h3>
                        <p>Hours Volunteered</p>
                    </div>
                </div>
            </div>

            <!-- main grid -->
            <div class="row">
                <!-- upcoming events -->
                <div class="col-lg-8 mb-4">
                    <div class="section-card">
                        <div class="section-title">
                            <i class="ri-calendar-event-line"></i>
                            Upcoming Events
                        </div>
                        <div class="event-item">
                            <h5>Community Food Drive</h5>
                            <p class="event-date"><i class="ri-calendar-line me-1"></i>December 5, 2024 | 9:00 AM - 3:00 PM</p>
                            <p>Help distribute food to families in need at the local community center.</p>
                        </div>
                        <div class="event-item">
                            <h5>Beach Cleanup Initiative</h5>
                            <p class="event-date"><i class="ri-calendar-line me-1"></i>December 8, 2024 | 7:00 AM - 12:00 PM</p>
                            <p>Join us for a morning of environmental conservation at Sunset Beach.</p>
                        </div>
                        <div class="event-item">
                            <h5>Senior Center Visit</h5>
                            <p class="event-date"><i class="ri-calendar-line me-1"></i>December 12, 2024 | 2:00 PM - 5:00 PM</p>
                            <p>Spend quality time with seniors, play games, and share stories.</p>
                        </div>
                    </div>

                    <!-- suggested events -->
                    <div class="section-card">
                        <div class="section-title">
                            <i class="ri-lightbulb-line"></i>
                            Suggested Events
                        </div>
                        <div class="event-item">
                            <h5>Animal Shelter Assistance</h5>
                            <p class="event-date"><i class="ri-calendar-line me-1"></i>Every Saturday | 10:00 AM - 2:00 PM</p>
                            <p>Help care for animals at the local shelter. No experience required!</p>
                            <button class="btn btn-primary-custom btn-sm mt-2">Join Event</button>
                        </div>
                        <div class="event-item">
                            <h5>Youth Mentoring Program</h5>
                            <p class="event-date"><i class="ri-calendar-line me-1"></i>Flexible Schedule</p>
                            <p>Mentor local youth and help them reach their full potential.</p>
                            <button class="btn btn-primary-custom btn-sm mt-2">Join Event</button>
                        </div>
                    </div>
                </div>

                <!-- sidebar -->
                <div class="col-lg-4 mb-4">
                    <!-- calendar widget -->
                    <div class="section-card">
                        <div class="mini-calendar">
                            <div class="calendar-header">December 2024</div>
                            <div class="calendar-grid">
                                <div class="calendar-day header">S</div>
                                <div class="calendar-day header">M</div>
                                <div class="calendar-day header">T</div>
                                <div class="calendar-day header">W</div>
                                <div class="calendar-day header">T</div>
                                <div class="calendar-day header">F</div>
                                <div class="calendar-day header">S</div>
                                <div class="calendar-day">1</div>
                                <div class="calendar-day">2</div>
                                <div class="calendar-day">3</div>
                                <div class="calendar-day">4</div>
                                <div class="calendar-day active">5</div>
                                <div class="calendar-day">6</div>
                                <div class="calendar-day">7</div>
                                <div class="calendar-day active">8</div>
                                <div class="calendar-day">9</div>
                                <div class="calendar-day">10</div>
                                <div class="calendar-day">11</div>
                                <div class="calendar-day active">12</div>
                                <div class="calendar-day">13</div>
                                <div class="calendar-day">14</div>
                                <div class="calendar-day">15</div>
                                <div class="calendar-day">16</div>
                                <div class="calendar-day">17</div>
                                <div class="calendar-day">18</div>
                                <div class="calendar-day">19</div>
                                <div class="calendar-day">20</div>
                                <div class="calendar-day">21</div>
                                <div class="calendar-day today">22</div>
                                <div class="calendar-day">23</div>
                                <div class="calendar-day">24</div>
                                <div class="calendar-day">25</div>
                                <div class="calendar-day">26</div>
                                <div class="calendar-day">27</div>
                                <div class="calendar-day">28</div>
                                <div class="calendar-day">29</div>
                                <div class="calendar-day">30</div>
                                <div class="calendar-day">31</div>
                            </div>
                        </div>
                    </div>

                    <!-- leaderboard preview -->
                    <div class="section-card">
                        <div class="section-title">
                            <i class="ri-trophy-line"></i>
                            Top Volunteers
                        </div>
                        <div class="leaderboard-item">
                            <div class="rank">1</div>
                            <div class="leaderboard-info">
                                <h6>Michael Chen</h6>
                                <p>128 hours volunteered</p>
                            </div>
                        </div>
                        <div class="leaderboard-item">
                            <div class="rank">2</div>
                            <div class="leaderboard-info">
                                <h6>Emily Rodriguez</h6>
                                <p>115 hours volunteered</p>
                            </div>
                        </div>
                        <div class="leaderboard-item">
                            <div class="rank">3</div>
                            <div class="leaderboard-info">
                                <h6>David Thompson</h6>
                                <p>98 hours volunteered</p>
                            </div>
                        </div>
                        <div class="leaderboard-item">
                            <div class="rank">4</div>
                            <div class="leaderboard-info">
                                <h6>Sarah Johnson</h6>
                                <p>42 hours volunteered</p>
                            </div>
                        </div>
                        <button class="btn btn-primary-custom w-100 mt-3">View Full Leaderboard</button>
                    </div>
                </div>
            </div>
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