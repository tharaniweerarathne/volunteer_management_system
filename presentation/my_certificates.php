<?php
session_start();


if (!isset($_SESSION['userId'])) {
    header('Location: login.php');
    exit();
}


if ($_SESSION['role'] !== 'Volunteer') {
    header('Location: login.php');
    exit();
}

require_once '../data_access/certificateData.php';
require_once '../business_logic/certificateLogic.php'; 
$certificateLogic = new CertificateLogic();
$certificateData = new CertificateData();


$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get all certificates 
$allCertificates = $certificateData->getCertificatesByVolunteer($_SESSION['userId']);

// Apply search filter 
$certificates = [];
if (!empty($searchTerm)) {
    $searchLower = strtolower($searchTerm);
    foreach ($allCertificates as $cert) {
        $eventNameLower = strtolower($cert['eventName']);
        $certNumber = $cert['certificateNumber'];
        $category = strtolower($cert['category']);
        $skill = isset($cert['skillName']) ? strtolower($cert['skillName']) : '';
        
        // Check if search term exists in any field
        if (strpos($eventNameLower, $searchLower) !== false || 
            strpos($certNumber, $searchTerm) !== false || 
            strpos($category, $searchLower) !== false || 
            strpos($skill, $searchLower) !== false) {
            $certificates[] = $cert;
        }
    }
} else {
    $certificates = $allCertificates;
}

// handle certificate download
if (isset($_GET['download']) && isset($_GET['id'])) {
    $certificateId = $_GET['id'];
    
    // verify certificate belongs to this volunteer
    $certificate = $certificateData->getCertificate($certificateId);
    
    if ($certificate && $certificate['userId'] == $_SESSION['userId']) {
        $filePath = '../' . $certificate['filePath'];
        
        if (file_exists($filePath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="certificate_' . $certificate['certificateNumber'] . '.pdf"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        } else {
            $error = "Certificate file not found.";
        }
    } else {
        $error = "Certificate not found or access denied.";
    }
}

// Function to highlight search text
function highlightSearchText($text, $search) {
    if (empty($search) || empty($text)) {
        return htmlspecialchars($text);
    }
    
    $search = preg_quote($search, '/');
    $pattern = "/($search)/i";
    
    if (preg_match($pattern, $text)) {
        return preg_replace($pattern, '<span class="search-highlight">$1</span>', htmlspecialchars($text));
    }
    
    return htmlspecialchars($text);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Certificates - Unity Volunteers Trust</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .certificate-card {
            border-left: 5px solid #198754;
            transition: all 0.3s ease;
        }
        .certificate-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .header-bar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 0;
            margin-bottom: 30px;
        }
        .certificate-count {
            font-size: 1.2rem;
            font-weight: bold;
        }
        .search-highlight {
            background-color: #fff3cd;
            font-weight: bold;
            padding: 0 2px;
            border-radius: 2px;
        }
        .search-tips {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }
        .no-certificates {
            min-height: 300px;
        }
        .stats-card {
            transition: all 0.2s ease;
        }
        .stats-card:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="#">
            <img src="../assets/images/logo.png" alt="Logo" style="height: 40px;">
        </a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="Volunteer_dashboard.php">Back</a>
        </div>
    </div>
</nav>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Search Bar -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-award"></i> My Certificates</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <form method="GET" class="mb-0">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                                        <input type="text" class="form-control" 
                                               name="search" 
                                               placeholder="Search certificates by event name, certificate number, category, or skill..." 
                                               value="<?php echo htmlspecialchars($searchTerm); ?>"
                                               id="searchInput">
                                        <?php if (!empty($searchTerm)): ?>
                                            <a href="my_certificates.php" class="btn btn-outline-secondary">
                                                <i class="bi bi-x-circle"></i> Clear
                                            </a>
                                        <?php endif; ?>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-search"></i> Search
                                        </button>
                                    </div>
                                    <div class="search-tips">
                                        <small>
                                            <i class="bi bi-lightbulb"></i> 
                                            Search by: Event name, Certificate number (#CERT-...), Category, or Skill
                                        </small>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center h-100 d-flex flex-column justify-content-center">
                                    <span class="certificate-count badge bg-success p-3">
                                        <i class="bi bi-award fs-4"></i> 
                                        <span class="fs-5"><?php echo count($certificates); ?> Certificate(s)</span>
                                        <?php if (!empty($searchTerm)): ?>
                                            <br><small>of <?php echo count($allCertificates); ?> total</small>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!empty($searchTerm)): ?>
                            <div class="alert alert-info mt-3 py-2 mb-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-info-circle"></i> 
                                        Searching for: "<strong><?php echo htmlspecialchars($searchTerm); ?></strong>"
                                        <span class="ms-2 badge bg-info">
                                            <?php echo count($certificates); ?> result(s)
                                        </span>
                                    </div>
                                    <?php if (count($certificates) == 0): ?>
                                        <a href="my_certificates.php" class="btn btn-sm btn-outline-info">
                                            Show all certificates
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if (empty($certificates)): ?>
                    <div class="card no-certificates">
                        <div class="card-body text-center py-5">
                            <?php if (!empty($searchTerm)): ?>
                                <i class="bi bi-search display-1 text-muted mb-3"></i>
                                <h4>No Certificates Found</h4>
                                <p class="text-muted">No certificates match your search for "<strong><?php echo htmlspecialchars($searchTerm); ?></strong>".</p>
                                <p class="text-muted">Try a different search term or <a href="my_certificates.php" class="alert-link">view all certificates</a>.</p>
                            <?php else: ?>
                                <i class="bi bi-award display-1 text-muted mb-3"></i>
                                <h4>No Certificates Yet</h4>
                                <p class="text-muted">You haven't received any certificates yet.</p>
                                <p class="text-muted">Complete events and maintain good attendance to earn certificates.</p>
                                <a href="events_volunteer.php" class="btn btn-primary mt-3">
                                    <i class="bi bi-calendar-event"></i> Browse Events
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- certificate stats -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white stats-card">
                                <div class="card-body">
                                    <h5 class="card-title">Total</h5>
                                    <h2 class="display-4"><?php echo count($certificates); ?></h2>
                                    <small>Certificate<?php echo count($certificates) != 1 ? 's' : ''; ?></small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white stats-card">
                                <div class="card-body">
                                    <h5 class="card-title">Events</h5>
                                    <?php
                                    $uniqueEvents = array_unique(array_column($certificates, 'eventId'));
                                    ?>
                                    <h2 class="display-4"><?php echo count($uniqueEvents); ?></h2>
                                    <small>Different Event<?php echo count($uniqueEvents) != 1 ? 's' : ''; ?></small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white stats-card">
                                <div class="card-body">
                                    <h5 class="card-title">Latest</h5>
                                    <?php
                                    $latest = $certificates[0];
                                    $issueDate = date('M j, Y', strtotime($latest['issueDate']));
                                    ?>
                                    <h5><?php echo $issueDate; ?></h5>
                                    <p class="mb-0 small"><?php echo htmlspecialchars($latest['eventName']); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-dark stats-card">
                                <div class="card-body">
                                    <h5 class="card-title">Earliest</h5>
                                    <?php
                                    $earliest = end($certificates);
                                    $earliestDate = date('M j, Y', strtotime($earliest['issueDate']));
                                    ?>
                                    <h5><?php echo $earliestDate; ?></h5>
                                    <p class="mb-0 small"><?php echo htmlspecialchars($earliest['eventName']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- certificates list -->
                    <div class="row row-cols-1 row-cols-md-2 g-4">
                        <?php foreach ($certificates as $certificate): ?>
                            <div class="col">
                                <div class="card h-100 certificate-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h5 class="card-title mb-1">
                                                    <?php echo highlightSearchText($certificate['eventName'], $searchTerm); ?>
                                                </h5>
                                                <small class="text-muted">
                                                    <i class="bi bi-calendar"></i> 
                                                    <?php echo date('F j, Y', strtotime($certificate['startDate'])); ?>
                                                </small>
                                            </div>
                                            <span class="badge bg-primary">
                                                #<?php echo highlightSearchText($certificate['certificateNumber'], $searchTerm); ?>
                                            </span>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <p class="card-text mb-1">
                                                <strong>Category:</strong> 
                                                <?php echo highlightSearchText($certificate['category'], $searchTerm); ?>
                                            </p>

<p class="card-text mb-1">
    <strong>Organizer:</strong> 
    <?php echo htmlspecialchars($certificate['organizerName'] ?? 'Unknown Organizer'); ?>
</p>


                                            <p class="card-text mb-1">
                                                <strong>Skill:</strong> 
                                                <?php 
                                                $skillText = $certificate['skillName'] ?? 'Not specified';
                                                echo highlightSearchText($skillText, $searchTerm); 
                                                ?>
                                            </p>
                                            <p class="card-text mb-1">
                                                <strong>Issued By:</strong> <?php echo htmlspecialchars($certificate['issuedByName']); ?>
                                            </p>
                                            <p class="card-text">
                                                <strong>Issue Date:</strong> <?php echo date('F j, Y', strtotime($certificate['issueDate'])); ?>
                                            </p>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle"></i> Verified
                                                </span>
                                            </div>
                                            <div class="btn-group btn-group-sm">
                                                <a href="?download=1&id=<?php echo $certificate['certificateId']; ?>" 
                                                   class="btn btn-primary" 
                                                   title="Download Certificate">
                                                    <i class="bi bi-download"></i> PDF
                                                </a>
                                                <a href="view_certificate.php?id=<?php echo $certificate['certificateId']; ?>" 
                                                   class="btn btn-outline-secondary" 
                                                   title="View Certificate">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                                <a href="#" class="btn btn-outline-info" 
                                                   data-bs-toggle="tooltip" 
                                                   title="Certificate ID: <?php echo $certificate['certificateId']; ?>">
                                                    <i class="bi bi-info-circle"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- certificate verification -->
                    <div class="card mt-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="bi bi-shield-check"></i> Certificate Verification</h5>
                        </div>
                        <div class="card-body">
                            <p>All certificates issued by Unity Volunteers Trust include:</p>
                            <ul>
                                <li><strong>Unique Certificate Number</strong> (e.g., #CERT-20240115-ABC123)</li>
                                <li><strong>Official Signature</strong> from Unity Volunteers Trust</li>
                                <li><strong>Issue Date</strong> when certificate was issued</li>
                                <li><strong>Event Details</strong> including date, category, and skill</li>
                                <li><strong>Volunteer Name</strong> exactly as registered</li>
                            </ul>
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle"></i> 
                                <strong>To verify a certificate:</strong> Contact the administrator with the certificate number.
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Back Button -->
                <div class="mt-4">
                    <a href="volunteer_dashboard.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Dashboard
                    </a>
                    <?php if (!empty($certificates)): ?>
                        <button onclick="window.print()" class="btn btn-outline-primary ms-2">
                            <i class="bi bi-printer"></i> Print Certificates List
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-focus search input
        document.getElementById('searchInput')?.focus();
        
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl+F to focus search
            if (e.ctrlKey && e.key === 'f') {
                e.preventDefault();
                const searchInput = document.getElementById('searchInput');
                if (searchInput) {
                    searchInput.focus();
                    searchInput.select();
                }
            }
            
            // ESC to clear search if search input has value
            if (e.key === 'Escape') {
                const searchInput = document.getElementById('searchInput');
                if (searchInput && searchInput.value) {
                    window.location.href = 'my_certificates.php';
                }
            }
        });
        
        // Auto-submit search on Enter
        document.getElementById('searchInput')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.form.submit();
            }
        });
        
        
        document.querySelectorAll('.certificate-card').forEach(card => {
            card.addEventListener('click', function(e) {
                if (!e.target.closest('a') && !e.target.closest('button')) {
                    window.scrollTo({top: 0, behavior: 'smooth'});
                }
            });
        });
    </script>
</body>
</html>