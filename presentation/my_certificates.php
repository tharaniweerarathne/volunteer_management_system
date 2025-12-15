<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['userId'])) {
    header('Location: login.php');
    exit();
}

// Only volunteers can access this page
if ($_SESSION['role'] !== 'Volunteer') {
    header('Location: login.php');
    exit();
}

require_once '../data_access/certificateData.php';
$certificateData = new CertificateData();
$certificates = $certificateData->getCertificatesByVolunteer($_SESSION['userId']);

// Handle certificate download
if (isset($_GET['download']) && isset($_GET['id'])) {
    $certificateId = $_GET['id'];
    
    // Verify certificate belongs to this volunteer
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
    </style>
</head>
<body>
    <!-- Simple Header Bar -->
    <div class="header-bar">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="bi bi-award"></i> My Certificates
                </h4>
                <div class="text-end">
                    <span class="me-3"><?php echo htmlspecialchars($_SESSION['name']); ?> (Volunteer)</span>
                    <a href="../../index.php" class="btn btn-light btn-sm">
                        <i class="bi bi-house"></i> Home
                    </a>
                    <a href="../auth/logout.php" class="btn btn-outline-light btn-sm ms-2">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3>My Certificates of Appreciation</h3>
                    <span class="certificate-count badge bg-success">
                        <i class="bi bi-award"></i> <?php echo count($certificates); ?> Certificate(s)
                    </span>
                </div>
                
                <?php if (empty($certificates)): ?>
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-award display-1 text-muted mb-3"></i>
                            <h4>No Certificates Yet</h4>
                            <p class="text-muted">You haven't received any certificates yet.</p>
                            <p class="text-muted">Complete events and maintain good attendance to earn certificates.</p>
                            <a href="events.php" class="btn btn-primary mt-3">
                                <i class="bi bi-calendar-event"></i> Browse Events
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Certificate Stats -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Certificates</h5>
                                    <h2 class="display-4"><?php echo count($certificates); ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Different Events</h5>
                                    <?php
                                    $uniqueEvents = array_unique(array_column($certificates, 'eventId'));
                                    ?>
                                    <h2 class="display-4"><?php echo count($uniqueEvents); ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Latest Certificate</h5>
                                    <?php
                                    $latest = $certificates[0];
                                    $issueDate = date('M j, Y', strtotime($latest['issueDate']));
                                    ?>
                                    <h5><?php echo $issueDate; ?></h5>
                                    <p class="mb-0"><?php echo htmlspecialchars($latest['eventName']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Certificates List -->
                    <div class="row row-cols-1 row-cols-md-2 g-4">
                        <?php foreach ($certificates as $certificate): ?>
                            <div class="col">
                                <div class="card h-100 certificate-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h5 class="card-title mb-1"><?php echo htmlspecialchars($certificate['eventName']); ?></h5>
                                                <small class="text-muted">
                                                    <i class="bi bi-calendar"></i> 
                                                    <?php echo date('F j, Y', strtotime($certificate['startDate'])); ?>
                                                </small>
                                            </div>
                                            <span class="badge bg-primary">
                                                #<?php echo $certificate['certificateNumber']; ?>
                                            </span>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <p class="card-text mb-1">
                                                <strong>Category:</strong> <?php echo htmlspecialchars($certificate['category']); ?>
                                            </p>
                                            <p class="card-text mb-1">
                                                <strong>Skill:</strong> <?php echo htmlspecialchars($certificate['skillName'] ?? 'Not specified'); ?>
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
                                            <div>
                                                <a href="?download=1&id=<?php echo $certificate['certificateId']; ?>" 
                                                   class="btn btn-sm btn-primary" 
                                                   title="Download Certificate">
                                                    <i class="bi bi-download"></i> Download PDF
                                                </a>
                                                <a href="view_certificate.php?id=<?php echo $certificate['certificateId']; ?>" 
                                                   class="btn btn-sm btn-outline-secondary" 
                                                   title="View Certificate">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Certificate Verification -->
                    <div class="card mt-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="bi bi-shield-check"></i> Certificate Verification</h5>
                        </div>
                        <div class="card-body">
                            <p>All certificates issued by Unity Volunteers Trust include:</p>
                            <ul>
                                <li>Unique Certificate Number</li>
                                <li>Official Signature</li>
                                <li>Issue Date</li>
                                <li>Event Details</li>
                                <li>Volunteer Name</li>
                            </ul>
                            <p class="mb-0">To verify a certificate, contact the administrator with the certificate number.</p>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Back Button -->
                <div class="mt-4">
                    <a href="volunteer_dashboard.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>