<?php
session_start();
date_default_timezone_set('Asia/Colombo');
require_once '../business_logic/EventVolunteerLogic.php';


if (!isset($_SESSION['userId'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['userId'];
$userRole = $_SESSION['role'] ?? '';

// checking if user has permission
if (!in_array($userRole, ['Admin', 'Coordinator'])) {
    header('Location: events.php');
    exit();
}

$eventId = $_GET['eventId'] ?? 0;


$eventVolunteerLogic = new EventVolunteerLogic();

// get event details
$event = $eventVolunteerLogic->getEvent($eventId);

if (!$event) {
    header('Location: events.php');
    exit();
}

// check if user can view volunteers
if (!$eventVolunteerLogic->canViewVolunteers($eventId, $userId, $userRole)) {
    header('Location: events.php');
    exit();
}

// get volunteers
$volunteers = $eventVolunteerLogic->getFormattedVolunteers($eventId);
$stats = $eventVolunteerLogic->getStatistics($eventId);

// Handle export to CSV
// In view_volunteers.php, replace the entire export section with:
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    $volunteersData = $eventVolunteerLogic->exportCSV($eventId);
    
    if (empty($volunteersData)) {
        // If no volunteers, redirect back
        header('Location: view_volunteers.php?eventId=' . $eventId);
        exit();
    }
    
    // Format the data like your working CSV system
    $formattedData = [];
    foreach ($volunteersData as $volunteer) {
        // Format registration date with leading space for Excel
        $registrationDate = '';
        if (!empty($volunteer['registrationDate']) && 
            $volunteer['registrationDate'] !== '0000-00-00' && 
            $volunteer['registrationDate'] !== '0000-00-00 00:00:00') {
            $registrationDate = ' ' . date('Y-m-d', strtotime($volunteer['registrationDate']));
        }
        
        $formattedData[] = [
            'Volunteer Name' => $volunteer['name'] ?? '',
            'Email Address' => $volunteer['email'] ?? '',
            'Phone Number' => $volunteer['telephoneNo'] ?? 'N/A',
            'Gender' => $volunteer['gender'] ?? 'Not specified',
            'Location' => $volunteer['location'] ?? 'Not specified',
            'Registration Date' => $registrationDate, // Formatted with leading space
            'Skills' => $volunteer['skills'] ?? 'No skills'
        ];
    }
    
    // Sanitize filename
    $safeEventName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $event['eventName']);
    $filename = "volunteers_" . $safeEventName . "_" . date('Y-m-d_H-i-s') . ".csv";
    
    // Generate CSV using same pattern as your working system
    try { 
        
        header('Content-Type: text/csv; charset=utf-8'); 
        header('Content-Disposition: attachment; filename="' . $filename . '"'); 
        header('Pragma: no-cache'); 
        header('Expires: 0'); 
         
         
        $output = fopen('php://output', 'w'); 
         
         
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); 
         
         
        if (!empty($formattedData)) { 
            fputcsv($output, array_keys($formattedData[0])); 
        } 
         
        
        foreach ($formattedData as $row) { 
            fputcsv($output, $row); 
        } 
         
        fclose($output); 
        exit(); 
         
    } catch (Exception $e) { 
        error_log("CSV Export Error: " . $e->getMessage());
        echo "Error exporting CSV. Please try again.";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteers - <?php echo htmlspecialchars($event['eventName']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <style>
        .volunteer-card {
            transition: transform 0.2s;
            border: 1px solid #e0e0e0;
        }
        .volunteer-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .stat-card {
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
        }
        .profile-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #007bff;
        }
        .skill-badge {
            margin: 2px;
            font-size: 0.8rem;
        }
        .print-only {
            display: none;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            .print-only {
                display: block;
            }
            .volunteer-card {
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark no-print">
        <div class="container">
            <a class="navbar-brand" href="events.php">
                <i class="ri-arrow-left-line"></i> Back to Events
            </a>
            <div class="navbar-nav ms-auto">
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="card-title"><?php echo htmlspecialchars($event['eventName']); ?></h2>
                <p class="card-text">
                    <i class="ri-calendar-line"></i> 
                    <?php echo date('F j, Y', strtotime($event['startDate'])); ?> 
                    <?php if ($event['startTime']): ?>
                        at <?php echo date('h:i A', strtotime($event['startTime'])); ?>
                    <?php endif; ?>
                </p>
                <p class="card-text">
                    <i class="ri-map-pin-line"></i> <?php echo htmlspecialchars($event['location']); ?>
                </p>
                <?php if ($event['coordinators']): ?>
                    <p class="card-text">
                        <i class="ri-user-star-line"></i> 
                        <strong>Coordinators:</strong> <?php echo htmlspecialchars($event['coordinators']); ?>
                    </p>
                <?php endif; ?>
                <?php if ($event['requiredSkill']): ?>
                    <p class="card-text">
                        <i class="ri-tools-line"></i> 
                        <strong>Required Skill:</strong> <?php echo htmlspecialchars($event['requiredSkill']); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 col-6">
                <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h1 class="display-4"><?php echo $stats['total_volunteers'] ?? 0; ?></h1>
                    <h5>Total Volunteers</h5>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card" style="background: linear-gradient(135deg, #36d1dc 0%, #5b86e5 100%);">
                    <h1 class="display-4"><?php echo $stats['male_count'] ?? 0; ?></h1>
                    <h5>Male</h5>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <h1 class="display-4"><?php echo $stats['female_count'] ?? 0; ?></h1>
                    <h5>Female</h5>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <h1 class="display-4"><?php echo $stats['skilled_count'] ?? 0; ?></h1>
                    <h5>Skilled</h5>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="d-flex justify-content-between mb-4 no-print">
            <h3>Registered Volunteers (<?php echo count($volunteers); ?>)</h3>
            <div>
                <a href="?eventId=<?php echo $eventId; ?>&export=csv" class="btn btn-success">
                    <i class="ri-download-line"></i> Export CSV
                </a>
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="ri-printer-line"></i> Print List
                </button>
            </div>
        </div>

        <!-- Print Header (only shows when printing) -->
        <div class="print-only mb-4">
            <h1>Volunteer List - <?php echo htmlspecialchars($event['eventName']); ?></h1>
            <p>Event Date: <?php echo date('F j, Y', strtotime($event['startDate'])); ?></p>
            <p>Total Volunteers: <?php echo $stats['total_volunteers'] ?? 0; ?></p>
            <hr>
        </div>

        <!-- Volunteers List -->
        <?php if (count($volunteers) > 0): ?>
            <div class="row">
                <?php foreach ($volunteers as $volunteer): ?>
                    <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
                        <div class="card volunteer-card h-100">
                            <div class="card-body text-center">
                                <?php if (!empty($volunteer['profileImage'])): ?>
                                    <img src="../<?php echo htmlspecialchars($volunteer['profileImage']); ?>" 
                                         class="profile-img mb-3" 
                                         alt="<?php echo $volunteer['name']; ?>"
                                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0iI2NjYyIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LXNpemU9IjE0IiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSIgZmlsbD0iI2ZmZiI+VXNlcjwvdGV4dD48L3N2Zz4='">
                                <?php else: ?>
                                    <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3" 
                                         style="width: 100px; height: 100px;">
                                        <i class="ri-user-line" style="font-size: 40px; color: white;"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <h5 class="card-title"><?php echo $volunteer['name']; ?></h5>
                                <p class="card-text text-muted">
                                    <i class="ri-mail-line"></i> <?php echo $volunteer['email']; ?><br>
                                    <?php if ($volunteer['phone'] !== 'N/A'): ?>
                                        <i class="ri-phone-line"></i> <?php echo $volunteer['phone']; ?><br>
                                    <?php endif; ?>
                                    <i class="ri-user-line"></i> <?php echo $volunteer['gender']; ?><br>
                                    <i class="ri-calendar-event-line"></i> 
                                    Registered: <?php echo $volunteer['registrationDate']; ?>
                                </p>
                                
                                <?php if (!empty($volunteer['skills'])): ?>
                                    <div class="mt-3">
                                        <h6>Skills:</h6>
                                        <div class="d-flex flex-wrap gap-1 justify-content-center">
                                            <?php foreach ($volunteer['skills'] as $skill): ?>
                                                <span class="badge bg-primary skill-badge"><?php echo trim($skill); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="mt-3 no-print">
                                    <?php if (file_exists('../presentation/profile.php')): ?>
                                        <a href="profile.php?id=<?php echo $volunteer['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="ri-profile-line"></i> View Profile
                                        </a>
                                    <?php endif; ?>
                                    <a href="mailto:<?php echo $volunteer['email']; ?>" 
                                       class="btn btn-sm btn-outline-success">
                                        <i class="ri-send-plane-line"></i> Email
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Table View for printing -->
            <div class="d-none d-print-block">
                <h3>Volunteer Details</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Gender</th>
                            <th>Skills</th>
                            <th>Registered</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($volunteers as $index => $volunteer): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo $volunteer['name']; ?></td>
                                <td><?php echo $volunteer['email']; ?></td>
                                <td><?php echo $volunteer['phone']; ?></td>
                                <td><?php echo $volunteer['gender']; ?></td>
                                <td><?php echo !empty($volunteer['skills']) ? implode(', ', $volunteer['skills']) : 'No skills'; ?></td>
                                <td><?php echo $volunteer['registrationDate']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
        <?php else: ?>
            <div class="alert alert-info text-center">
                <i class="ri-information-line" style="font-size: 48px;"></i>
                <h4 class="mt-3">No volunteers have joined this event yet.</h4>
                <p class="mb-0">Share the event link to get volunteers!</p>
                <?php if ($userRole === 'Coordinator' || $userRole === 'Admin'): ?>
                    <div class="mt-3">
                        <a href="events.php?action=edit&id=<?php echo $eventId; ?>" 
                           class="btn btn-warning">
                            <i class="ri-edit-line"></i> Edit Event
                        </a>
                        <a href="view_event.php?id=<?php echo $eventId; ?>" 
                           class="btn btn-info">
                            <i class="ri-eye-line"></i> View Event Details
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // Print optimization
        window.addEventListener('beforeprint', function() {
            // Add print-specific styling
            document.body.classList.add('printing');
        });
        
        window.addEventListener('afterprint', function() {
            // Remove print-specific styling
            document.body.classList.remove('printing');
        });
    </script>
</body>
</html>