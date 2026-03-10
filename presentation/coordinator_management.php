<?php
session_start();


if (!isset($_SESSION['name'])) {
    echo "Not logged in!";
    exit();
}

$userId = $_SESSION['userId'];
$name = $_SESSION['name'];
$role = $_SESSION['role'] ?? '';

require_once __DIR__ . "/../data_access/db.php";
require_once __DIR__ . "/../business_logic/RegistrationLogic.php";
require_once __DIR__ . "/../business_logic/MessageLogic.php";


$messageLogic = new MessageLogic($conn);
$inboxResult = $messageLogic->getInbox($userId, 1, 1);
$unreadCount = $inboxResult['unreadCount'];


$logic = new RegistrationLogic($conn);
$message = "";
$messageType = "";

// handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $result = $logic->registerCoordinator(
            $_POST['name'],
            $_POST['email'],
            $_POST['password'],
            $_POST['phone'],
            $_POST['location'],
            $_POST['gender']
        );
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'danger';
    }
    
    elseif ($action === 'edit') {
        $result = $logic->updateCoordinator(
            $_POST['userId'],
            $_POST['name'],
            $_POST['email'],
            $_POST['phone'],
            $_POST['location'],
            $_POST['gender'],
            $_POST['password'] ?? null
        );
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'danger';
    }
    
    elseif ($action === 'delete') {
        $result = $logic->deleteCoordinator($_POST['userId']);
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'danger';
    }
}

// get all coordinators
$coordinators = $logic->getAllCoordinators();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coordinator Management</title>
    <link rel="stylesheet" href="../assets/css/a9.css">
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
                <a href="coordinator_management.php" class="active">
                    <i class="ri-group-line" ></i>
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
            <i class="ri-user-settings-line"></i> Coordinator Management
        </h1>

        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- add coordinator form -->
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="ri-user-add-line"></i> Add New Coordinator</h4>
            </div>
            <div class="card-body">
                <form method="POST" id="addForm">
                    <input type="hidden" name="action" value="add">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <span class="required">*</span> Full Name
                            </label>
                            <div class="input-group">
                                <i class="ri-user-line input-icon"></i>
                                <input type="text" name="name" class="form-control with-icon" placeholder="Enter the coordinator's name" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <span class="required">*</span> Email Address
                            </label>
                            <div class="input-group">
                                <i class="ri-mail-line input-icon"></i>
                                <input type="email" name="email" id="email" class="form-control with-icon" placeholder="Enter the coordinator's email" required>
                            </div>
                            <div class="email-hints" id="emailHints"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <span class="required">*</span> Password
                            </label>
                            <div class="input-group">
                                <i class="ri-lock-line input-icon"></i>
                                <input type="password" name="password" class="form-control with-icon" placeholder="Enter the password" id="addPassword" required>
                                <i class="ri-eye-line password-toggle" onclick="togglePassword('addPassword')"></i>
                            </div>
                            <div class="email-hints" id="passwordHints"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <span class="required">*</span> Telephone Number
                            </label>
                            <div class="input-group">
                                <i class="ri-phone-line input-icon"></i>
                                <input type="tel" name="phone" id="phone" class="form-control with-icon" placeholder="Enter the telephone number" required>
                            </div>
                             <div class="email-hints" id="mobileHints"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <span class="required">*</span> Location
                            </label>
                            <div class="input-group">
                                <i class="ri-map-pin-line input-icon"></i>
                                <input type="text" name="location" class="form-control with-icon" placeholder="Enter the location" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <span class="required">*</span> Gender
                            </label>
                            <select name="gender" class="form-select" required>
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                                <option value="Prefer not to say">Prefer not to say</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-add-line"></i> Add Coordinator
                    </button>
                </form>
            </div>
        </div>


        <!--csv file generating-->
        <div class="csv-generating">
    <div class="section-title"><i class="ri-file-chart-line"></i> Export Data to CSV</div>
    <div class="button-group">

        <a href="export_csv.php?type=coordinators" class="csv-button btn-coordinators" onclick="showCsvLoading(this)">
            <i class="ri-download-cloud-line"></i>
            Coordinators
        </a>

        <a href="export_csv.php?type=all_users" class="csv-button btn-all-users" onclick="showCsvLoading(this)">
            <i class="ri-download-cloud-line"></i>
            All Users
        </a>
    </div>
</div>

        <!-- coordinators list -->
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="ri-team-line"></i> All Coordinators</h4>
            </div>
            <div class="card-body">
                
                <div class="mb-4">
                    <div class="input-group">
                        <i class="ri-search-line input-icon"></i>
                        <input type="text" id="searchInput" class="form-control with-icon" placeholder="Search by name or email..." onkeyup="searchCoordinators()">
                        <button class="btn btn-outline-secondary" type="button" onclick="clearSearch()">
                            <i class="ri-close-line"></i> Clear
                        </button>
                    </div>
                    <small class="text-muted" id="searchResults"></small>
                </div>

                <div class="table-container">
                    <table class="table table-hover" id="coordinatorTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Location</th>
                                <th>Gender</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="coordinatorTableBody">
                            <?php if (empty($coordinators)): ?>
                                <tr class="no-results">
                                    <td colspan="7" class="text-center text-muted">No coordinators found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($coordinators as $coord): ?>
                                    <tr class="coordinator-row" 
                                        data-name="<?= strtolower(htmlspecialchars($coord['name'])) ?>"
                                        data-email="<?= strtolower(htmlspecialchars($coord['email'])) ?>">
                                        <td><?= $coord['userId'] ?></td>
                                        <td><?= htmlspecialchars($coord['name']) ?></td>
                                        <td><?= htmlspecialchars($coord['email']) ?></td>
                                        <td><?= htmlspecialchars($coord['telephoneNo']) ?></td>
                                        <td><?= htmlspecialchars($coord['location']) ?></td>
                                        <td><?= htmlspecialchars($coord['gender']) ?></td>
                                        <td class="action-buttons">
                                            <button class="btn btn-sm btn-warning coordinator-edit-btn" onclick="editCoordinator(<?= htmlspecialchars(json_encode($coord)) ?>)">
                                                <i class="ri-edit-line"></i> Edit
                                            </button>
                                            <button class="btn btn-sm btn-danger coordinator-delete-btn" onclick="deleteCoordinator(<?= $coord['userId'] ?>, '<?= htmlspecialchars($coord['name']) ?>')">
                                                <i class="ri-delete-bin-line"></i> Delete
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

    <!-- edit modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #ff6200 0%, #994524 100%); color: white;">
                    <h5 class="modal-title"><i class="ri-edit-line"></i> Edit Coordinator</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="userId" id="editUserId">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><span class="required">*</span> Full Name</label>
                                <div class="input-group">
                                    <i class="ri-user-line input-icon"></i>
                                    <input type="text" name="name" id="editName" class="form-control with-icon" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><span class="required">*</span> Email</label>
                                <div class="input-group">
                                    <i class="ri-mail-line input-icon"></i>
                                    <input type="email" name="email" id="editEmail" class="form-control with-icon" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">New Password (leave blank to keep current)</label>
                                <div class="input-group">
                                    <i class="ri-lock-line input-icon"></i>
                                    <input type="password" name="password" id="editPassword" class="form-control with-icon">
                                    <i class="ri-eye-line password-toggle" onclick="togglePassword('editPassword')"></i>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><span class="required">*</span> Phone</label>
                                <div class="input-group">
                                    <i class="ri-phone-line input-icon"></i>
                                    <input type="tel" name="phone" id="editPhone" class="form-control with-icon" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><span class="required">*</span> Location</label>
                                <div class="input-group">
                                    <i class="ri-map-pin-line input-icon"></i>
                                    <input type="text" name="location" id="editLocation" class="form-control with-icon" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><span class="required">*</span> Gender</label>
                                <select name="gender" id="editGender" class="form-select" required>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                    <option value="Prefer not to say">Prefer not to say</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- delete form -->
    <form method="POST" id="deleteForm" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="userId" id="deleteUserId">
    </form>

    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/dashboards.js"></script>
    <script src="../assets/js/form_validation1.js"></script>
    

    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling;
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('ri-eye-line');
                icon.classList.add('ri-eye-off-line');
            } else {
                input.type = 'password';
                icon.classList.remove('ri-eye-off-line');
                icon.classList.add('ri-eye-line');
            }
        }

        function editCoordinator(coord) {
            document.getElementById('editUserId').value = coord.userId;
            document.getElementById('editName').value = coord.name;
            document.getElementById('editEmail').value = coord.email;
            document.getElementById('editPhone').value = coord.telephoneNo;
            document.getElementById('editLocation').value = coord.location;
            document.getElementById('editGender').value = coord.gender;
            document.getElementById('editPassword').value = '';
            
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }

        function deleteCoordinator(userId, name) {
            if (confirm(`Are you sure you want to delete coordinator "${name}"? This action cannot be undone.`)) {
                document.getElementById('deleteUserId').value = userId;
                document.getElementById('deleteForm').submit();
            }
        }

        // search functionality
        function searchCoordinators() {
            const searchInput = document.getElementById('searchInput').value.toLowerCase().trim();
            const rows = document.querySelectorAll('.coordinator-row');
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

            // updating search results message
            const searchResults = document.getElementById('searchResults');
            if (searchInput === '') {
                searchResults.textContent = '';
            } else {
                searchResults.textContent = `Found ${visibleCount} coordinator(s) matching "${searchInput}"`;
            }

            // show/hide "no results" message
            updateNoResultsMessage(visibleCount);
        }

        function clearSearch() {
            document.getElementById('searchInput').value = '';
            searchCoordinators();
        }

        function updateNoResultsMessage(visibleCount) {
            const tbody = document.getElementById('coordinatorTableBody');
            let noResultsRow = tbody.querySelector('.no-results-search');
            
            // remove existing "no results" message if it exists
            if (noResultsRow) {
                noResultsRow.remove();
            }

            // adding "no results" message if no rows are visible
            if (visibleCount === 0 && tbody.querySelectorAll('.coordinator-row').length > 0) {
                const row = document.createElement('tr');
                row.className = 'no-results-search';
                row.innerHTML = '<td colspan="7" class="text-center text-muted">No coordinators match your search</td>';
                tbody.appendChild(row);
            }
        }

        // auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                bootstrap.Alert.getOrCreateInstance(alert).close();
            });
        }, 5000);



         function showCsvLoading(button) {
        button.classList.add('loading');
        setTimeout(() => {
            button.classList.remove('loading');
        }, 2000);
    }



    // Auto-refresh notification
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
            
            
            let topNotificationBadge = document.querySelector('.header-actions .dropdown:first-child .notification-badge');
            let topNotificationButton = document.querySelector('.header-actions .dropdown:first-child .btn');
            
            
            let dropdownBadge = document.querySelector('.dropdown-menu .badge');
            
            
            let sidebarBadge = document.querySelector('.message-badge');
            let sidebarLink = document.querySelector('.nav-item a[href*="messages"]');
            
            if (count > 0) {
                
                if (topNotificationBadge) {
                    topNotificationBadge.textContent = count;
                } else if (topNotificationButton) {
                    const newBadge = document.createElement('span');
                    newBadge.className = 'notification-badge';
                    newBadge.textContent = count;
                    topNotificationButton.appendChild(newBadge);
                }
                
                
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
                
                
                if (sidebarBadge) {
                    sidebarBadge.textContent = count;
                } else if (sidebarLink) {
                    const newBadge = document.createElement('span');
                    newBadge.className = 'message-badge';
                    newBadge.textContent = count;
                    sidebarLink.appendChild(newBadge);
                }
            } else {

                if (topNotificationBadge) {
                    topNotificationBadge.remove();
                }
                
               
                if (dropdownBadge) {
                    dropdownBadge.remove();
                }
                
          
                if (sidebarBadge) {
                    sidebarBadge.remove();
                }
            }
        }
    </script>
</body>
</html>