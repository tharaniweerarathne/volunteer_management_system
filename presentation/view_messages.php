<?php
session_start();
require_once __DIR__ . '/../data_access/db.php';
require_once __DIR__ . '/../business_logic/ContactMessageLogic.php';

// checking if user is logged in and is admin or coordinator
if (!isset($_SESSION['userId']) || !in_array($_SESSION['role'], ['Admin', 'Coordinator'])) {
    header('Location: login.php');
    exit();
}


if (!isset($_SESSION['name'])) {
    echo "Not logged in!";
    exit();
}

$name = $_SESSION['name'];
$role = $_SESSION['role'] ?? '';

$contactLogic = new ContactMessageLogic($conn);
$messages = $contactLogic->getAllMessages();
$pendingCount = $contactLogic->getPendingCount();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message (sent) </title>
    <link rel="stylesheet" href="../assets/css/a6.css">
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

        <?php if ($role === 'Admin'): ?>
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
        <?php endif; ?>

        <?php if ($role === 'Coordinator'): ?>
            <div class="nav-item">
            <a href="coordinator_dashboard.php">
                <i class="ri-dashboard-line"></i>
                <span>Dashboard</span>
            </a>
            </div> 
        <?php endif; ?>

        <?php if ($role === 'Admin' || $role === 'Coordinator'): ?>
            <div class="nav-item">
                <a href="volunteer_management.php">
                    <i class="ri-add-circle-line"></i>
                    <span>Volunteers</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="#">
                    <i class="ri-calendar-line"></i>
                    <span>Calendar</span>
                </a>
            </div>
        <?php endif; ?>

        <div class="nav-item">
            <a href="view_messages.php" class="active"> 
                <i class="ri-medal-line" ></i>
                <span>Message</span>
            </a>
        </div>

        <div class="nav-item">
                <a href="volunteer_management.php">
                    <i class="ri-add-circle-line"></i>
                    <span>Volunteers</span>
                </a>
            </div>
        <div class="nav-item">
            <a href="#">
                <i class="ri-trophy-line"></i>
                <span>Leaderboard</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="#">
                <i class="ri-message-3-line"></i>
                <span>Messages</span>
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
                Welcome Coordinator, <?php echo $name; ?>
            </div>
            <div class="header-actions">
                

                <div class="dropdown">
                    <button class="btn p-0 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="ri-notification-3-line"></i><span class="notification-badge">3</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="ri-edit-line me-2"></i>Edit Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#"><i class="ri-logout-box-line me-2"></i>Logout</a></li>
                    </ul>
                </div>




                <div class="dropdown">
                    <button class="btn p-0 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="ri-user-3-fill header-icon"></i>
                    </button>
<ul class="dropdown-menu dropdown-menu-end">
    <li>
        <a class="dropdown-item" href="<?php 
            if ($_SESSION['role'] === 'Admin') {
                echo 'edit_profile_admin.php';
            } elseif ($_SESSION['role'] === 'Coordinator') {
                echo 'edit_profile_coordinator.php';
            } else {
                echo '#'; // fallback or for other roles
            }
        ?>">
            <i class="ri-edit-line me-2"></i>Edit Profile
        </a>
    </li>
    <li><hr class="dropdown-divider"></li>
    <li><a class="dropdown-item" href="logout.php"><i class="ri-logout-box-line me-2"></i>Logout</a></li>
</ul>

                </div>
            </div>
        </header>


          <!-- content area -->
    <div class="container1">
        <h1 class="text-left page-title mb-4">
                <i class="ri-send-plane-line"></i>
                Contact Messages
            </h2>

            <?php if (isset($_SESSION['reply_result'])): ?>
                <div class="alert alert-<?php echo $_SESSION['reply_result']['success'] ? 'success' : 'danger'; ?> alert-dismissible fade show">
                    <?php echo htmlspecialchars($_SESSION['reply_result']['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['reply_result']); ?>
            <?php endif; ?>

            <!-- statistics cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-primary"><?php echo count($messages); ?></h3>
                            <p class="text-muted mb-0">Total Messages</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 style="color: #ff6200;"><?php echo $pendingCount; ?></h3>
                            <p class="text-muted mb-0">Pending Replies</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-success"><?php echo count($messages) - $pendingCount; ?></h3>
                            <p class="text-muted mb-0">Replied</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- messages list -->
            <div class="row">
                <?php if (empty($messages)): ?>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                                <h5 class="text-muted">No messages yet</h5>
                                <p class="text-muted">Messages from the contact form will appear here</p>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($messages as $msg): ?>
                        <div class="col-12 mb-3">
                            <div class="card message-card <?php echo $msg['status']; ?>">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="mb-1">
                                                <i class="fas fa-user-circle text-primary"></i>
                                                <?php echo htmlspecialchars($msg['senderName']); ?>
                                            </h5>
                                            <p class="text-muted mb-0">
                                                <i class="fas fa-envelope"></i>
                                                <?php echo htmlspecialchars($msg['senderEmail']); ?>
                                            </p>
                                            <small class="text-muted">
                                                <i class="fas fa-clock"></i>
                                                <?php echo date('F j, Y \a\t g:i A', strtotime($msg['createdAt'])); ?>
                                            </small>
                                        </div>
                                        <span class="status-badge status-<?php echo $msg['status']; ?>">
                                            <?php echo ucfirst($msg['status']); ?>
                                        </span>
                                    </div>

                                    <div class="mb-3">
                                        <strong class="d-block mb-2">Message:</strong>
                                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                                    </div>

                                    <?php if ($msg['status'] === 'replied'): ?>
                                        <div class="reply-section">
                                            <strong class="d-block mb-2">
                                                <i class="fas fa-reply"></i> Reply:
                                            </strong>
                                            <p class="mb-2"><?php echo nl2br(htmlspecialchars($msg['replyMessage'])); ?></p>
                                            <small class="text-muted">
                                                <i class="fas fa-user"></i> Replied by: <?php echo htmlspecialchars($msg['repliedByName']); ?> (<?php echo htmlspecialchars($msg['repliedByRole']); ?>)
                                                <br>
                                                <i class="fas fa-clock"></i> <?php echo date('F j, Y \a\t g:i A', strtotime($msg['repliedAt'])); ?>
                                            </small>
                                        </div>
                                    <?php else: ?>
                                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#replyModal<?php echo $msg['messageId']; ?>">
                                            <i class="fas fa-reply"></i> Send Reply
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- reply modal -->
                        <?php if ($msg['status'] === 'pending'): ?>
                            <div class="modal fade" id="replyModal<?php echo $msg['messageId']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                <i class="fas fa-reply"></i> Reply to <?php echo htmlspecialchars($msg['senderName']); ?>
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="send_reply.php" method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="messageId" value="<?php echo $msg['messageId']; ?>">
                                                
                                                <div class="mb-3">
                                                    <label class="form-label"><strong>Original Message:</strong></label>
                                                    <div class="p-3 bg-light rounded">
                                                        <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="replyMessage<?php echo $msg['messageId']; ?>" class="form-label">
                                                        <strong>Your Reply: <span class="required">*</span></strong>
                                                    </label>
                                                    <textarea class="form-control" id="replyMessage<?php echo $msg['messageId']; ?>" name="replyMessage" rows="8" required placeholder="Type your reply here..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-paper-plane"></i> Send Reply
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
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
    </script>
</body>
</html>