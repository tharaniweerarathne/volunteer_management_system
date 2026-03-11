<?php
session_start();


if (!isset($_SESSION['userId'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: login.php');
    exit();
}

require_once '../data_access/certificateData.php';
$certificateData = new CertificateData();
$certificateId = $_GET['id'];
$certificate = $certificateData->getCertificate($certificateId);


if (!$certificate || $certificate['userId'] != $_SESSION['userId']) {
    header('Location: my_certificates.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate #<?php echo $certificate['certificateNumber']; ?> - Unity Volunteers Trust</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        .certificate-container {
            background: white;
            border: 20px solid #2c3e50;
            padding: 50px;
            max-width: 1000px;
            margin: 30px auto;
            position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .certificate-header {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 40px;
        }
        .certificate-title {
            font-size: 48px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .certificate-subtitle {
            color: #7f8c8d;
            font-size: 24px;
            margin-bottom: 40px;
        }
        .volunteer-name {
            font-size: 42px;
            color: #2c3e50;
            margin: 40px 0;
            padding: 20px;
            border-bottom: 3px solid #3498db;
            text-align: center;
        }
        .certificate-details {
            font-size: 20px;
            color: #555;
            margin: 20px 0;
            line-height: 1.8;
        }
        .certificate-footer {
            margin-top: 60px;
            color: #7f8c8d;
            font-size: 16px;
            text-align: center;
        }
        .signature {
            margin-top: 40px;
            border-top: 2px solid #000;
            display: inline-block;
            padding-top: 10px;
            width: 300px;
        }
        .cert-number {
            position: absolute;
            bottom: 20px;
            right: 20px;
            font-size: 12px;
            color: #95a5a6;
        }
        .action-buttons {
            margin-top: 30px;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../../index.php">
                <i class="bi bi-people-fill"></i> Unity Volunteers Trust
            </a>
            <div class="navbar-text text-white">
                Certificate #<?php echo $certificate['certificateNumber']; ?>
            </div>
        </div>
    </nav>
    
    <div class="container">
        
        <div class="action-buttons">
            <a href="my_certificates.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Certificates
            </a>
            <a href="my_certificates.php?download=1&id=<?php echo $certificateId; ?>" 
               class="btn btn-primary ms-2">
                <i class="bi bi-download"></i> Download PDF
            </a>
            <button onclick="window.print()" class="btn btn-success ms-2">
                <i class="bi bi-printer"></i> Print Certificate
            </button>
        </div>
        
        <!-- Certificate Display -->
        <div class="certificate-container">
            <div class="certificate-header">
                <h1 class="certificate-title">Unity Volunteers Trust</h1>
                <div class="certificate-subtitle">Certificate of Appreciation</div>
            </div>
            
            <div class="text-center">
                <div class="certificate-subtitle">This Certificate is Proudly Presented to</div>
                <div class="volunteer-name"><?php echo htmlspecialchars($certificate['volunteerName']); ?></div>
            </div>
            
<div class="certificate-details text-center">
    In recognition of valuable contribution as a volunteer for<br>
    <strong><?php echo htmlspecialchars($certificate['eventName']); ?></strong><br>
    <em>
        <?php 
            $skillText = $certificate['skillName'] ?? 'Not specified';
            echo htmlspecialchars($skillText . ' - ' . $certificate['category']); 
        ?>
    </em><br>
    Organizer: <strong><?php echo htmlspecialchars($certificate['organizerName'] ?? 'Unknown Organizer'); ?></strong><br>
    Held on <?php echo date('F j, Y', strtotime($certificate['startDate'])); ?><br>
    In appreciation of dedicated service and commitment
</div>

            
            <div class="certificate-footer">
                <div class="signature">
                    <strong>Unity Volunteers Trust</strong><br>
                    Director
                </div>
                <div style="margin-top: 20px;">
                    Date Issued: <?php echo date('F j, Y', strtotime($certificate['issueDate'])); ?>
                </div>
            </div>
            
            <div class="cert-number">
                Certificate No: <?php echo $certificate['certificateNumber']; ?>
            </div>
        </div>
        
        <!-- Certificate Details Card -->
        <div class="card mt-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Certificate Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Certificate Number:</strong> <?php echo $certificate['certificateNumber']; ?></p>
                        <p><strong>Volunteer Name:</strong> <?php echo htmlspecialchars($certificate['volunteerName']); ?></p>
                        <p><strong>Event Name:</strong> <?php echo htmlspecialchars($certificate['eventName']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Event Date:</strong> <?php echo date('F j, Y', strtotime($certificate['startDate'])); ?></p>
                        <p><strong>Issue Date:</strong> <?php echo date('F j, Y', strtotime($certificate['issueDate'])); ?></p>
                        <p><strong>Issued By:</strong> <?php echo htmlspecialchars($certificate['issuedByName']); ?></p>
                    </div>
                </div>
                <div class="alert alert-info mt-3">
                    <i class="bi bi-shield-check"></i> 
                    <strong>Verification:</strong> This is an officially issued certificate. 
                    To verify, contact the administrator with certificate number.
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('print') === '1') {
            window.print();
        }
    </script>
</body>
</html>