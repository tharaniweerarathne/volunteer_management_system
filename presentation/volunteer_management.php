<?php
session_start();

// check if user is logged in
if (!isset($_SESSION['name'])) {
    echo "Not logged in!";
    exit();
}

$name = $_SESSION['name'];
$role = $_SESSION['role'] ?? '';

require_once __DIR__ . "/../data_access/db.php";
require_once __DIR__ . "/../business_logic/RegistrationLogic.php";

$logic = new RegistrationLogic($conn);
$message = "";
$messageType = "";

// handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $skills = $_POST['skills'] ?? [];
        $result = $logic->registerUser(
            $_POST['name'],
            $_POST['email'],
            $_POST['password'],
            $_POST['phone'],
            $_POST['location'],
            $_POST['gender'],
            $skills
        );
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'danger';
    }
    
    elseif ($action === 'edit') {
        $skills = $_POST['skills'] ?? [];
        $result = $logic->updateVolunteer(
            $_POST['userId'],
            $_POST['name'],
            $_POST['email'],
            $_POST['phone'],
            $_POST['location'],
            $_POST['gender'],
            $_POST['password'] ?? null,
            $skills
        );
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'danger';
    }
    
    elseif ($action === 'delete') {
        $result = $logic->deleteVolunteer($_POST['userId']);
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'danger';
    }
}

// get all volunteers with their skills
$volunteers = $logic->getAllVolunteers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Management</title>
    <link rel="stylesheet" href="../assets/css/a3.css">
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

        <?php if ($role === 'Admin'): ?>
            <div class="nav-item">
                <a href="coordinator_management.php">
                    <i class="ri-user-settings-line"></i>
                    <span>Coordinator Management</span>
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
    Welcome <?php echo htmlspecialchars($role); ?>, <?php echo htmlspecialchars($name); ?>
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
                        <li><a class="dropdown-item" href="#"><i class="ri-edit-line me-2"></i>Edit Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="ri-logout-box-line me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </header>

    <div class="container1">
        <h1 class="text-left page-title mb-4">
            <i class="ri-team-line"></i> Volunteer Management
        </h1>

        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- add volunteer form -->
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="ri-user-add-line"></i> Add New Volunteer</h4>
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
                                <input type="text" name="name" class="form-control with-icon" placeholder="Enter the volunteer's name" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <span class="required">*</span> Email Address
                            </label>
                            <div class="input-group">
                                <i class="ri-mail-line input-icon"></i>
                                <input type="email" name="email" id="email" class="form-control with-icon" placeholder="Enter the volunteer's email" required>
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
                    
                    <!-- Skills Section -->
                    <div class="mb-3">
                        <label class="form-label">Skills (Optional)</label>
                        <div class="skills-section">
                            <p style="margin-bottom: 0.5rem; color: #6b7280; font-size: 0.9rem;">
                                Add any skills the volunteer has
                            </p>
                            <div class="skills-grid">
                                <div class="skill-checkbox">
                                    <input type="checkbox" id="teaching" name="skills[]" value="Teaching">
                                    <label for="teaching">Teaching</label>
                                </div>
                                <div class="skill-checkbox">
                                    <input type="checkbox" id="event-organizing" name="skills[]" value="Event Organizing">
                                    <label for="event-organizing">Event Organizing</label>
                                </div>
                                <div class="skill-checkbox">
                                    <input type="checkbox" id="first-aid" name="skills[]" value="First Aid">
                                    <label for="first-aid">First Aid</label>
                                </div>
                                <div class="skill-checkbox">
                                    <input type="checkbox" id="photography" name="skills[]" value="Photography">
                                    <label for="photography">Photography</label>
                                </div>
                                <div class="skill-checkbox">
                                    <input type="checkbox" id="cooking" name="skills[]" value="Cooking">
                                    <label for="cooking">Cooking</label>
                                </div>
                                <div class="skill-checkbox">
                                    <input type="checkbox" id="environment" name="skills[]" value="Environmental Work">
                                    <label for="environment">Environmental Work</label>
                                </div>
                                <div class="skill-checkbox">
                                    <input type="checkbox" id="social-media" name="skills[]" value="Social Media">
                                    <label for="social-media">Social Media</label>
                                </div>
                                <div class="skill-checkbox">
                                    <input type="checkbox" id="graphic-design" name="skills[]" value="Graphic Design">
                                    <label for="graphic-design">Graphic Design</label>
                                </div>
                                <div class="skill-checkbox">
                                    <input type="checkbox" id="elderly-care" name="skills[]" value="Elderly Care">
                                    <label for="elderly-care">Elderly Care</label>
                                </div>
                                <div class="skill-checkbox">
                                    <input type="checkbox" id="translation" name="skills[]" value="Translation">
                                    <label for="translation">Translation</label>
                                </div>
                                <div class="skill-checkbox">
                                    <input type="checkbox" id="it-support" name="skills[]" value="IT Support">
                                    <label for="it-support">IT and Technical Support</label>
                                </div>
                                <div class="skill-checkbox">
                                    <input type="checkbox" id="public-speaking" name="skills[]" value="Public Speaking">
                                    <label for="public-speaking">Public Speaking</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-add-line"></i> Add Volunteer
                    </button>
                </form>
            </div>
        </div>

        <!-- volunteers list -->
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="ri-team-line"></i> All Volunteers</h4>
            </div>
            <div class="card-body">
                
                <div class="mb-4">
                    <div class="input-group">
                        <i class="ri-search-line input-icon"></i>
                        <input type="text" id="searchInput" class="form-control with-icon" placeholder="Search by name or email..." onkeyup="searchVolunteers()">
                        <button class="btn btn-outline-secondary" type="button" onclick="clearSearch()">
                            <i class="ri-close-line"></i> Clear
                        </button>
                    </div>
                    <small class="text-muted" id="searchResults"></small>
                </div>

                <div class="table-container">
                    <table class="table table-hover" id="volunteerTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Location</th>
                                <th>Gender</th>
                                <th>Skills</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="volunteerTableBody">
                            <?php if (empty($volunteers)): ?>
                                <tr class="no-results">
                                    <td colspan="8" class="text-center text-muted">No volunteers found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($volunteers as $vol): ?>
                                    <tr class="volunteer-row" 
                                        data-name="<?= strtolower(htmlspecialchars($vol['name'])) ?>"
                                        data-email="<?= strtolower(htmlspecialchars($vol['email'])) ?>">
                                        <td><?= $vol['userId'] ?></td>
                                        <td><?= htmlspecialchars($vol['name']) ?></td>
                                        <td><?= htmlspecialchars($vol['email']) ?></td>
                                        <td><?= htmlspecialchars($vol['telephoneNo']) ?></td>
                                        <td><?= htmlspecialchars($vol['location']) ?></td>
                                        <td><?= htmlspecialchars($vol['gender']) ?></td>
                                        <td>
                                            <?php if (!empty($vol['skills'])): ?>
                                                <small><?= htmlspecialchars(implode(', ', array_slice($vol['skills'], 0, 3))) ?><?= count($vol['skills']) > 3 ? '...' : '' ?></small>
                                            <?php else: ?>
                                                <small class="text-muted">No skills</small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="action-buttons">
                                            <button class="btn btn-sm btn-warning coordinator-edit-btn" onclick='editVolunteer(<?= json_encode($vol) ?>)'>
                                                <i class="ri-edit-line"></i> Edit
                                            </button>
                                            <button class="btn btn-sm btn-danger coordinator-delete-btn" onclick="deleteVolunteer(<?= $vol['userId'] ?>, '<?= htmlspecialchars($vol['name']) ?>')">
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
                    <h5 class="modal-title"><i class="ri-edit-line"></i> Edit Volunteer</h5>
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
                        
                        <!-- Edit Skills Section -->
                        <div class="mb-3">
                            <label class="form-label">Skills (Optional)</label>
                            <div class="skills-section">
                                <div class="skills-grid">
                                    <div class="skill-checkbox">
                                        <input type="checkbox" id="edit-teaching" name="skills[]" value="Teaching">
                                        <label for="edit-teaching">Teaching</label>
                                    </div>
                                    <div class="skill-checkbox">
                                        <input type="checkbox" id="edit-event-organizing" name="skills[]" value="Event Organizing">
                                        <label for="edit-event-organizing">Event Organizing</label>
                                    </div>
                                    <div class="skill-checkbox">
                                        <input type="checkbox" id="edit-first-aid" name="skills[]" value="First Aid">
                                        <label for="edit-first-aid">First Aid</label>
                                    </div>
                                    <div class="skill-checkbox">
                                        <input type="checkbox" id="edit-photography" name="skills[]" value="Photography">
                                        <label for="edit-photography">Photography</label>
                                    </div>
                                    <div class="skill-checkbox">
                                        <input type="checkbox" id="edit-cooking" name="skills[]" value="Cooking">
                                        <label for="edit-cooking">Cooking</label>
                                    </div>
                                    <div class="skill-checkbox">
                                        <input type="checkbox" id="edit-environment" name="skills[]" value="Environmental Work">
                                        <label for="edit-environment">Environmental Work</label>
                                    </div>
                                    <div class="skill-checkbox">
                                        <input type="checkbox" id="edit-social-media" name="skills[]" value="Social Media">
                                        <label for="edit-social-media">Social Media</label>
                                    </div>
                                    <div class="skill-checkbox">
                                        <input type="checkbox" id="edit-graphic-design" name="skills[]" value="Graphic Design">
                                        <label for="edit-graphic-design">Graphic Design</label>
                                    </div>
                                    <div class="skill-checkbox">
                                        <input type="checkbox" id="edit-elderly-care" name="skills[]" value="Elderly Care">
                                        <label for="edit-elderly-care">Elderly Care</label>
                                    </div>
                                    <div class="skill-checkbox">
                                        <input type="checkbox" id="edit-translation" name="skills[]" value="Translation">
                                        <label for="edit-translation">Translation</label>
                                    </div>
                                    <div class="skill-checkbox">
                                        <input type="checkbox" id="edit-it-support" name="skills[]" value="IT Support">
                                        <label for="edit-it-support">IT and Technical Support</label>
                                    </div>
                                    <div class="skill-checkbox">
                                        <input type="checkbox" id="edit-public-speaking" name="skills[]" value="Public Speaking">
                                        <label for="edit-public-speaking">Public Speaking</label>
                                    </div>
                                </div>
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

        function editVolunteer(vol) {
            document.getElementById('editUserId').value = vol.userId;
            document.getElementById('editName').value = vol.name;
            document.getElementById('editEmail').value = vol.email;
            document.getElementById('editPhone').value = vol.telephoneNo;
            document.getElementById('editLocation').value = vol.location;
            document.getElementById('editGender').value = vol.gender;
            document.getElementById('editPassword').value = '';
            
            // Uncheck all skills first
            document.querySelectorAll('#editModal input[type="checkbox"]').forEach(cb => cb.checked = false);
            
            // Check skills that volunteer has
            if (vol.skills && vol.skills.length > 0) {
                vol.skills.forEach(skill => {
                    const checkbox = document.querySelector(`#editModal input[value="${skill}"]`);
                    if (checkbox) checkbox.checked = true;
                });
            }
            
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }

        function deleteVolunteer(userId, name) {
            if (confirm(`Are you sure you want to delete volunteer "${name}"? This action cannot be undone.`)) {
                document.getElementById('deleteUserId').value = userId;
                document.getElementById('deleteForm').submit();
            }
        }

        function searchVolunteers() {
            const searchInput = document.getElementById('searchInput').value.toLowerCase().trim();
            const rows = document.querySelectorAll('.volunteer-row');
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

            const searchResults = document.getElementById('searchResults');
            if (searchInput === '') {
                searchResults.textContent = '';
            } else {
                searchResults.textContent = `Found ${visibleCount} volunteer(s) matching "${searchInput}"`;
            }

            updateNoResultsMessage(visibleCount);
        }

        function clearSearch() {
            document.getElementById('searchInput').value = '';
            searchVolunteers();
        }

        function updateNoResultsMessage(visibleCount) {
            const tbody = document.getElementById('volunteerTableBody');
            let noResultsRow = tbody.querySelector('.no-results-search');
            
            if (noResultsRow) {
                noResultsRow.remove();
            }

            if (visibleCount === 0 && tbody.querySelectorAll('.volunteer-row').length > 0) {
                const row = document.createElement('tr');
                row.className = 'no-results-search';
                row.innerHTML = '<td colspan="8" class="text-center text-muted">No volunteers match your search</td>';
                tbody.appendChild(row);
            }
        }

        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                bootstrap.Alert.getOrCreateInstance(alert).close();
            });
        }, 5000);
    </script>
</body>
</html>