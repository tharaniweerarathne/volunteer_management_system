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

// Get search parameters from GET request
$searchParams = [
    'search_name' => $_GET['search_name'] ?? '',
    'search_email' => $_GET['search_email'] ?? '',
    'search_phone' => $_GET['search_phone'] ?? '',
    'search_date_from' => $_GET['search_date_from'] ?? '',
    'search_date_to' => $_GET['search_date_to'] ?? '',
    'search_specific_date' => $_GET['search_specific_date'] ?? '',
    'search_gender' => $_GET['search_gender'] ?? ''
];

// get volunteers WITH search filters
$volunteers = $eventVolunteerLogic->getFormattedVolunteers($eventId, $searchParams);
$stats = $eventVolunteerLogic->getStatistics($eventId);

// Check if any search filters are applied
$isSearchApplied = !empty(array_filter($searchParams, function($value) {
    return !empty($value);
}));

// Handle export to CSV
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    // Pass search filters to export
    $volunteersData = $eventVolunteerLogic->searchVolunteers($eventId, $searchParams);
    
    if (empty($volunteersData)) {
        // If no volunteers, redirect back
        header('Location: view_volunteers.php?eventId=' . $eventId . '&' . http_build_query($searchParams));
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
            'Phone Number' => $volunteer['phone'] ?? 'N/A',
            'Gender' => $volunteer['gender'] ?? 'Not specified',
            'Location' => $volunteer['location'] ?? 'Not specified',
            'Registration Date' => $registrationDate,
            'Skills' => !empty($volunteer['skills']) ? implode(', ', $volunteer['skills']) : 'No skills'
        ];
    }
    
    // Add search info to filename if any search is applied
    $searchInfo = '';
    if ($isSearchApplied) {
        $searchInfo = '_filtered_' . date('Y-m-d_H-i');
    }
    
    // Sanitize filename
    $safeEventName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $event['eventName']);
    $filename = "volunteers_" . $safeEventName . $searchInfo . "_" . date('Y-m-d_H-i-s') . ".csv";
    
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
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #007bff;
        }
        .profile-img-table {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #dee2e6;
        }
        .skill-badge {
            margin: 2px;
            font-size: 0.8rem;
        }
        .print-only {
            display: none;
        }
        .search-form .form-control,
        .search-form .form-select {
            font-size: 0.9rem;
        }
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            padding: 10px 0;
        }
        .dataTables_wrapper .dataTables_paginate {
            padding: 10px 0;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }
        .table-actions {
            min-width: 150px;
        }
        .badge-skills {
            max-width: 200px;
            white-space: normal;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            .print-only {
                display: block;
            }
            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_filter,
            .dataTables_wrapper .dataTables_paginate {
                display: none !important;
            }
            table {
                font-size: 11px;
            }
            th, td {
                padding: 4px !important;
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

    <div class="container-fluid mt-4">
        
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h2 class="card-title"><?php echo htmlspecialchars($event['eventName']); ?></h2>
                        <p class="card-text mb-1">
                            <i class="ri-calendar-line"></i> 
                            <?php echo date('F j, Y', strtotime($event['startDate'])); ?> 
                            <?php if ($event['startTime']): ?>
                                at <?php echo date('h:i A', strtotime($event['startTime'])); ?>
                            <?php endif; ?>
                        </p>
                        <p class="card-text mb-1">
                            <i class="ri-map-pin-line"></i> <?php echo htmlspecialchars($event['location']); ?>
                        </p>
                        <?php if ($event['coordinators']): ?>
                            <p class="card-text mb-1">
                                <i class="ri-user-star-line"></i> 
                                <strong>Coordinators:</strong> <?php echo htmlspecialchars($event['coordinators']); ?>
                            </p>
                        <?php endif; ?>
                        <?php if ($event['requiredSkill']): ?>
                            <p class="card-text mb-0">
                                <i class="ri-tools-line"></i> 
                                <strong>Required Skill:</strong> <?php echo htmlspecialchars($event['requiredSkill']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <!-- Statistics Cards in horizontal layout -->
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="stat-card p-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                    <h4 class="mb-0"><?php echo $stats['total_volunteers'] ?? 0; ?></h4>
                                    <small>Total Volunteers</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card p-3" style="background: linear-gradient(135deg, #36d1dc 0%, #5b86e5 100%);">
                                    <h4 class="mb-0"><?php echo $stats['male_count'] ?? 0; ?></h4>
                                    <small>Male</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card p-3" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                                    <h4 class="mb-0"><?php echo $stats['female_count'] ?? 0; ?></h4>
                                    <small>Female</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card p-3" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                                    <h4 class="mb-0"><?php echo $stats['skilled_count'] ?? 0; ?></h4>
                                    <small>Skilled</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SEARCH FORM - ADD THIS SECTION -->
        <div class="card mb-4 no-print">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="ri-search-line"></i> Search Volunteers</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="" class="row g-3 search-form">
                    <input type="hidden" name="eventId" value="<?php echo $eventId; ?>">
                    
                    <div class="col-md-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="search_name" class="form-control" 
                               placeholder="Search by name" 
                               value="<?php echo htmlspecialchars($_GET['search_name'] ?? ''); ?>">
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Email</label>
                        <input type="text" name="search_email" class="form-control" 
                               placeholder="Search by email" 
                               value="<?php echo htmlspecialchars($_GET['search_email'] ?? ''); ?>">
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="search_phone" class="form-control" 
                               placeholder="Search by phone" 
                               value="<?php echo htmlspecialchars($_GET['search_phone'] ?? ''); ?>">
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Registration Date</label>
                        <input type="date" name="search_specific_date" class="form-control" 
                               value="<?php echo htmlspecialchars($_GET['search_specific_date'] ?? ''); ?>">
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Registration Date Range</label>
                        <div class="row g-2">
                            <div class="col">
                                <input type="date" name="search_date_from" class="form-control" 
                                       placeholder="From" 
                                       value="<?php echo htmlspecialchars($_GET['search_date_from'] ?? ''); ?>">
                            </div>
                            <div class="col">
                                <input type="date" name="search_date_to" class="form-control" 
                                       placeholder="To" 
                                       value="<?php echo htmlspecialchars($_GET['search_date_to'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Gender</label>
                        <select name="search_gender" class="form-select">
                            <option value="">All Genders</option>
                            <option value="Male" <?php echo ($_GET['search_gender'] ?? '') == 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo ($_GET['search_gender'] ?? '') == 'Female' ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo ($_GET['search_gender'] ?? '') == 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid gap-2 d-md-flex">
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-search-line"></i> Search
                            </button>
                            <a href="view_volunteers.php?eventId=<?php echo $eventId; ?>" class="btn btn-secondary">
                                <i class="ri-refresh-line"></i> Clear
                            </a>
                        </div>
                    </div>
                    
                    <?php if (!empty(array_filter($_GET, function($k) { 
                        return strpos($k, 'search_') === 0 && !empty($_GET[$k]); 
                    }, ARRAY_FILTER_USE_KEY))): ?>
                        <div class="col-12">
                            <div class="alert alert-info mb-0">
                                <i class="ri-information-line"></i> 
                                Search filters applied. 
                                <a href="view_volunteers.php?eventId=<?php echo $eventId; ?>" class="alert-link">Clear all filters</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Actions -->
        <div class="d-flex justify-content-between mb-4 no-print">
            <h3>Registered Volunteers (<?php echo count($volunteers); ?>)</h3>
            <div>
                <div class="btn-group" role="group">
                    <a href="?eventId=<?php echo $eventId; ?>&export=csv<?php echo $isSearchApplied ? '&' . http_build_query($searchParams) : ''; ?>" class="btn btn-success">
                        <i class="ri-download-line"></i> Export CSV
                    </a>
                    <button class="btn btn-primary" onclick="window.print()">
                        <i class="ri-printer-line"></i> Print
                    </button>
                    <button type="button" class="btn btn-secondary" id="toggleView">
                        <i class="ri-grid-line"></i> <span id="viewText">Card View</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Search Results Alert -->
        <?php if ($isSearchApplied): ?>
            <div class="alert alert-warning alert-dismissible fade show mb-3" role="alert">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="ri-filter-line"></i> 
                        <strong>Search Filters Applied:</strong>
                        <?php 
                        $filters = [];
                        if (!empty($searchParams['search_name'])) $filters[] = "Name: " . htmlspecialchars($searchParams['search_name']);
                        if (!empty($searchParams['search_email'])) $filters[] = "Email: " . htmlspecialchars($searchParams['search_email']);
                        if (!empty($searchParams['search_phone'])) $filters[] = "Phone: " . htmlspecialchars($searchParams['search_phone']);
                        if (!empty($searchParams['search_specific_date'])) $filters[] = "Date: " . htmlspecialchars($searchParams['search_specific_date']);
                        if (!empty($searchParams['search_date_from']) || !empty($searchParams['search_date_to'])) {
                            $dateRange = "";
                            if (!empty($searchParams['search_date_from'])) $dateRange .= "From: " . htmlspecialchars($searchParams['search_date_from']);
                            if (!empty($searchParams['search_date_from']) && !empty($searchParams['search_date_to'])) $dateRange .= " ";
                            if (!empty($searchParams['search_date_to'])) $dateRange .= "To: " . htmlspecialchars($searchParams['search_date_to']);
                            $filters[] = "Date Range: " . $dateRange;
                        }
                        if (!empty($searchParams['search_gender'])) $filters[] = "Gender: " . htmlspecialchars($searchParams['search_gender']);
                        echo implode(" | ", $filters);
                        ?>
                        <span class="badge bg-info ms-2"><?php echo count($volunteers); ?> result(s)</span>
                    </div>
                    <a href="view_volunteers.php?eventId=<?php echo $eventId; ?>" class="btn btn-sm btn-outline-dark">
                        <i class="ri-close-line"></i> Clear All
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Print Header (only shows when printing) -->
        <div class="print-only mb-4">
            <h1>Volunteer List - <?php echo htmlspecialchars($event['eventName']); ?></h1>
            <p>Event Date: <?php echo date('F j, Y', strtotime($event['startDate'])); ?></p>
            <p>Total Volunteers: <?php echo $stats['total_volunteers'] ?? 0; ?></p>
            <hr>
        </div>

        <!-- Table View (Default) -->
        <div id="tableView" class="mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="volunteersTable" class="table table-hover table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Photo</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Gender</th>
                                    <th>Location</th>
                                    <th>Skills</th>
                                    <th>Registered On</th>
                                    <th class="no-print">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($volunteers) > 0): ?>
                                    <?php foreach ($volunteers as $index => $volunteer): ?>
                                        <tr>
                                            <td><?php echo $index + 1; ?></td>
                                            <td>
                                                <?php if (!empty($volunteer['profileImage'])): ?>
                                                    <img src="../<?php echo htmlspecialchars($volunteer['profileImage']); ?>" 
                                                         class="profile-img-table" 
                                                         alt="<?php echo $volunteer['name']; ?>"
                                                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNTAiIGhlaWdodD0iNTAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjUwIiBoZWlnaHQ9IjUwIiBmaWxsPSIjY2NjIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtc2l6ZT0iMTIiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIiBmaWxsPSIjZmZmIj5VPC90ZXh0Pjwvc3ZnPg=='">
                                                <?php else: ?>
                                                    <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center" 
                                                         style="width: 50px; height: 50px;">
                                                        <i class="ri-user-line" style="font-size: 20px; color: white;"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?php echo $volunteer['name']; ?></strong>
                                                <?php if (isset($volunteer['age']) && $volunteer['age']): ?>
                                                    <br><small class="text-muted">Age: <?php echo $volunteer['age']; ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="mailto:<?php echo $volunteer['email']; ?>" class="text-decoration-none">
                                                    <?php echo $volunteer['email']; ?>
                                                </a>
                                            </td>
                                            <td>
                                                <?php if ($volunteer['phone'] !== 'N/A'): ?>
                                                    <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $volunteer['phone']); ?>" 
                                                       class="text-decoration-none">
                                                        <?php echo $volunteer['phone']; ?>
                                                    </a>
                                                <?php else: ?>
                                                    N/A
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge 
                                                    <?php echo strtolower($volunteer['gender']) == 'male' ? 'bg-primary' : 
                                                           (strtolower($volunteer['gender']) == 'female' ? 'bg-danger' : 'bg-secondary'); ?>">
                                                    <?php echo $volunteer['gender']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (isset($volunteer['location']) && $volunteer['location'] !== 'Not specified'): ?>
                                                    <i class="ri-map-pin-line"></i> <?php echo $volunteer['location']; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Not specified</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($volunteer['skills'])): ?>
                                                    <div class="d-flex flex-wrap gap-1">
                                                        <?php foreach ($volunteer['skills'] as $skill): ?>
                                                            <span class="badge bg-info skill-badge"><?php echo trim($skill); ?></span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">No skills</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo $volunteer['registrationDate']; ?>
                                                <?php if (isset($volunteer['registrationTime'])): ?>
                                                    <br><small class="text-muted"><?php echo $volunteer['registrationTime']; ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="table-actions no-print">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <?php if (file_exists('../presentation/profile.php')): ?>
                                                        <a href="profile.php?id=<?php echo $volunteer['id']; ?>" 
                                                           class="btn btn-outline-primary" 
                                                           title="View Profile">
                                                            <i class="ri-profile-line"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="mailto:<?php echo $volunteer['email']; ?>" 
                                                       class="btn btn-outline-success"
                                                       title="Send Email">
                                                        <i class="ri-mail-line"></i>
                                                    </a>
                                                    <?php if ($volunteer['phone'] !== 'N/A'): ?>
                                                        <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $volunteer['phone']); ?>" 
                                                           class="btn btn-outline-info"
                                                           title="Call">
                                                            <i class="ri-phone-line"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <?php if (isset($volunteer['whatsapp']) && $volunteer['whatsapp']): ?>
                                                        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $volunteer['whatsapp']); ?>" 
                                                           class="btn btn-outline-success"
                                                           target="_blank"
                                                           title="WhatsApp">
                                                            <i class="ri-whatsapp-line"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="text-center py-5">
                                            <div class="alert alert-info">
                                                <i class="ri-information-line" style="font-size: 48px;"></i>
                                                <h4 class="mt-3">
                                                    <?php echo $isSearchApplied ? 'No volunteers found matching your search criteria.' : 'No volunteers have joined this event yet.'; ?>
                                                </h4>
                                                <p class="mb-0">
                                                    <?php if ($isSearchApplied): ?>
                                                        Try different search terms or <a href="view_volunteers.php?eventId=<?php echo $eventId; ?>" class="alert-link">clear filters</a>.
                                                    <?php else: ?>
                                                        Share the event link to get volunteers!
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <?php if (count($volunteers) > 0): ?>
                                <tfoot>
                                    <tr>
                                        <td colspan="10" class="text-center text-muted">
                                            <small>
                                                Showing <?php echo count($volunteers); ?> volunteer(s)
                                                <?php if ($isSearchApplied): ?>
                                                    <span class="text-warning">(Filtered Results)</span>
                                                <?php endif; ?>
                                            </small>
                                        </td>
                                    </tr>
                                </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card View (Hidden by default) -->
        <div id="cardView" class="row mb-4" style="display: none;">
            <?php if (count($volunteers) > 0): ?>
                <?php foreach ($volunteers as $volunteer): ?>
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
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
                                <?php if (isset($volunteer['age']) && $volunteer['age']): ?>
                                    <p class="card-text text-muted mb-1">Age: <?php echo $volunteer['age']; ?></p>
                                <?php endif; ?>
                                <p class="card-text text-muted mb-1">
                                    <i class="ri-mail-line"></i> <?php echo $volunteer['email']; ?>
                                </p>
                                <?php if ($volunteer['phone'] !== 'N/A'): ?>
                                    <p class="card-text text-muted mb-1">
                                        <i class="ri-phone-line"></i> <?php echo $volunteer['phone']; ?>
                                    </p>
                                <?php endif; ?>
                                <p class="card-text text-muted mb-1">
                                    <i class="ri-user-line"></i> <?php echo $volunteer['gender']; ?>
                                    <?php if (isset($volunteer['location']) && $volunteer['location'] !== 'Not specified'): ?>
                                        <br><i class="ri-map-pin-line"></i> <?php echo $volunteer['location']; ?>
                                    <?php endif; ?>
                                </p>
                                <p class="card-text text-muted mb-3">
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
                                    <div class="btn-group btn-group-sm" role="group">
                                        <?php if (file_exists('../presentation/profile.php')): ?>
                                            <a href="profile.php?id=<?php echo $volunteer['id']; ?>" 
                                               class="btn btn-outline-primary"
                                               title="View Profile">
                                                <i class="ri-profile-line"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="mailto:<?php echo $volunteer['email']; ?>" 
                                           class="btn btn-outline-success"
                                           title="Email">
                                            <i class="ri-mail-line"></i>
                                        </a>
                                        <?php if ($volunteer['phone'] !== 'N/A'): ?>
                                            <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $volunteer['phone']); ?>" 
                                               class="btn btn-outline-info"
                                               title="Call">
                                                <i class="ri-phone-line"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Additional Information -->
        <?php if (count($volunteers) > 0): ?>
            <div class="row mt-4 no-print">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="ri-information-line"></i> Event Summary</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Total Volunteers:</span>
                                    <strong><?php echo $stats['total_volunteers'] ?? 0; ?></strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Male Volunteers:</span>
                                    <strong><?php echo $stats['male_count'] ?? 0; ?></strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Female Volunteers:</span>
                                    <strong><?php echo $stats['female_count'] ?? 0; ?></strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Volunteers with Skills:</span>
                                    <strong><?php echo $stats['skilled_count'] ?? 0; ?></strong>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="ri-file-list-3-line"></i> Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="?eventId=<?php echo $eventId; ?>&export=csv<?php echo $isSearchApplied ? '&' . http_build_query($searchParams) : ''; ?>" class="btn btn-success">
                                    <i class="ri-download-line"></i> Download Complete CSV
                                </a>
                                <button class="btn btn-primary" onclick="window.print()">
                                    <i class="ri-printer-line"></i> Print Volunteer List
                                </button>
                                <?php if (file_exists('../presentation/send_email.php')): ?>
                                    <a href="send_email.php?eventId=<?php echo $eventId; ?>" class="btn btn-warning">
                                        <i class="ri-send-plane-line"></i> Email All Volunteers
                                    </a>
                                <?php endif; ?>
                                <a href="view_event.php?id=<?php echo $eventId; ?>" class="btn btn-info">
                                    <i class="ri-eye-line"></i> View Event Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        // Initialize DataTable
        $(document).ready(function() {
            $('#volunteersTable').DataTable({
                "pageLength": 25,
                "order": [[0, 'asc']],
                "responsive": true,
                "searching": true,
                "language": {
                    "search": "Search within results:",
                    "searchPlaceholder": "Type to filter...",
                    "lengthMenu": "Show _MENU_ volunteers per page",
                    "info": "Showing _START_ to _END_ of _TOTAL_ volunteers",
                    "infoFiltered": "(filtered from _MAX_ total volunteers)",
                    "paginate": {
                        "first": "First",
                        "last": "Last",
                        "next": "Next",
                        "previous": "Previous"
                    }
                },
                <?php if ($isSearchApplied): ?>
                "initComplete": function() {
                    // Show search info if filters are applied
                    $('.dataTables_info').append(
                        '<div class="mt-2 text-warning"><small><i class="ri-filter-line"></i> Search filters are applied</small></div>'
                    );
                },
                <?php endif; ?>
                "columnDefs": [
                    {
                        "targets": 'no-print',
                        "visible": true,
                        "printable": false
                    }
                ]
            });

            // Toggle between table and card view
            $('#toggleView').click(function() {
                const tableView = $('#tableView');
                const cardView = $('#cardView');
                const viewText = $('#viewText');
                
                if (tableView.is(':visible')) {
                    tableView.hide();
                    cardView.show();
                    viewText.text('Table View');
                    $(this).find('i').removeClass('ri-grid-line').addClass('ri-table-line');
                } else {
                    tableView.show();
                    cardView.hide();
                    viewText.text('Card View');
                    $(this).find('i').removeClass('ri-table-line').addClass('ri-grid-line');
                }
            });

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
                // Hide the toggle view button
                $('#toggleView').hide();
                // Force table view for printing
                $('#tableView').show();
                $('#cardView').hide();
            });
            
            window.addEventListener('afterprint', function() {
                // Show the toggle view button again
                $('#toggleView').show();
            });
        });
    </script>
</body>
</html>