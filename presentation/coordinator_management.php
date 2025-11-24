<?php
session_start();
require_once __DIR__ . "/../data_access/db.php";
require_once __DIR__ . "/../business_logic/RegistrationLogic.php";

$logic = new RegistrationLogic($conn);
$message = "";
$messageType = "";

// Handle form submissions
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

// Get all coordinators
$coordinators = $logic->getAllCoordinators();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coordinator Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 30px;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .table-container {
            overflow-x: auto;
        }
        .action-buttons .btn {
            margin: 2px;
        }
        .required {
            color: red;
        }
        .input-group {
            position: relative;
        }
        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            z-index: 10;
        }
        .form-control.with-icon {
            padding-left: 45px;
        }
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            z-index: 10;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-white text-center mb-4">
            <i class="ri-user-settings-line"></i> Coordinator Management
        </h1>

        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Add Coordinator Form -->
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
                                <input type="text" name="name" class="form-control with-icon" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <span class="required">*</span> Email Address
                            </label>
                            <div class="input-group">
                                <i class="ri-mail-line input-icon"></i>
                                <input type="email" name="email" class="form-control with-icon" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <span class="required">*</span> Password
                            </label>
                            <div class="input-group">
                                <i class="ri-lock-line input-icon"></i>
                                <input type="password" name="password" class="form-control with-icon" id="addPassword" required>
                                <i class="ri-eye-line password-toggle" onclick="togglePassword('addPassword')"></i>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <span class="required">*</span> Telephone Number
                            </label>
                            <div class="input-group">
                                <i class="ri-phone-line input-icon"></i>
                                <input type="tel" name="phone" class="form-control with-icon" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <span class="required">*</span> Location
                            </label>
                            <div class="input-group">
                                <i class="ri-map-pin-line input-icon"></i>
                                <input type="text" name="location" class="form-control with-icon" required>
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

        <!-- Coordinators List -->
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="ri-team-line"></i> All Coordinators</h4>
            </div>
            <div class="card-body">
                <!-- Search Bar -->
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
                                            <button class="btn btn-sm btn-warning" onclick="editCoordinator(<?= htmlspecialchars(json_encode($coord)) ?>)">
                                                <i class="ri-edit-line"></i> Edit
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteCoordinator(<?= $coord['userId'] ?>, '<?= htmlspecialchars($coord['name']) ?>')">
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

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
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

    <!-- Delete Form (hidden) -->
    <form method="POST" id="deleteForm" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="userId" id="deleteUserId">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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

        // Search functionality
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

            // Update search results message
            const searchResults = document.getElementById('searchResults');
            if (searchInput === '') {
                searchResults.textContent = '';
            } else {
                searchResults.textContent = `Found ${visibleCount} coordinator(s) matching "${searchInput}"`;
            }

            // Show/hide "no results" message
            updateNoResultsMessage(visibleCount);
        }

        function clearSearch() {
            document.getElementById('searchInput').value = '';
            searchCoordinators();
        }

        function updateNoResultsMessage(visibleCount) {
            const tbody = document.getElementById('coordinatorTableBody');
            let noResultsRow = tbody.querySelector('.no-results-search');
            
            // Remove existing "no results" message if it exists
            if (noResultsRow) {
                noResultsRow.remove();
            }

            // Add "no results" message if no rows are visible
            if (visibleCount === 0 && tbody.querySelectorAll('.coordinator-row').length > 0) {
                const row = document.createElement('tr');
                row.className = 'no-results-search';
                row.innerHTML = '<td colspan="7" class="text-center text-muted">No coordinators match your search</td>';
                tbody.appendChild(row);
            }
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                bootstrap.Alert.getOrCreateInstance(alert).close();
            });
        }, 5000);
    </script>
</body>
</html>