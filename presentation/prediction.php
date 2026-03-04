<?php
require_once "../business_logic/eventLogic.php";

$prediction = $predictionResult ?? null;
$eventId = $_GET['id'] ?? 0;

// Get event details
$eventData = new EventData();
$event = $eventData->getEventById($eventId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Prediction · Unity Volunteers</title>
    <link rel="stylesheet" href="../assets/css/prediction.css">
    <link rel="icon" type="image/png" href="../assets/images/title.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">

</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="#">
            <img src="../assets/images/logo.png" alt="Logo" class="logo-img" style="height: 60px; width: auto;">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <div class="navbar-nav ms-auto">
                <?php if ($userRole === 'Admin'): ?>
                    <a class="nav-link active" href="admin_dashboard.php">Dashboard</a>
                <?php endif; ?>
                <?php if ($userRole === 'Coordinator'): ?>
                    <a class="nav-link active" href="coordinator_dashboard.php">Dashboard</a>
                <?php endif; ?>
                <?php if ($userRole === 'Organizer'): ?>
                    <a class="nav-link active" href="organizer_dashboard.php">Dashboard</a>
                <?php endif; ?>
                <a class="nav-link" href="events.php">Events</a>
                <?php if ($userRole === 'Coordinator'): ?>
                    <a class="nav-link" href="events.php?dashboard=1">My Events</a>
                <?php endif; ?>
                <?php if (in_array($userRole, ['Admin', 'Organizer'])): ?>
                    <a class="nav-link" href="events.php?action=create">Create Event</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<div class="prediction-container">
    <div class="modern-card">
        
        <?php if ($event): ?>
        <!-- Event Header -->
        <div class="event-header">
            <span class="event-category">
                <i class="ri-price-tag-3-line"></i> 
                <?php echo htmlspecialchars($event['category'] ?? 'General'); ?>
            </span>
            
            <h1 class="event-title"><?php echo htmlspecialchars($event['eventName']); ?></h1>
            
            <?php if (!empty($event['eventDescription'])): ?>
            <p style="color: #cbd5e1; max-width: 600px; margin-bottom: 2rem;">
                <?php echo htmlspecialchars(substr($event['eventDescription'], 0, 150)) . '...'; ?>
            </p>
            <?php endif; ?>
            
            
            <div class="event-meta-grid">
                <div class="meta-item">
                    <i class="ri-calendar-line meta-icon"></i>
                    <div class="meta-label">Date</div>
                    <div class="meta-value"><?php echo date('M d, Y', strtotime($event['startDate'])); ?></div>
                </div>
                
                <div class="meta-item">
                    <i class="ri-time-line meta-icon"></i>
                    <div class="meta-label">Time</div>
                    <div class="meta-value">
                        <?php echo date('h:i A', strtotime($event['startTime'])); ?> - 
                        <?php echo date('h:i A', strtotime($event['endTime'])); ?>
                    </div>
                </div>
                
                <div class="meta-item">
                    <i class="ri-map-pin-line meta-icon"></i>
                    <div class="meta-label">Location</div>
                    <div class="meta-value"><?php echo htmlspecialchars($event['location']); ?></div>
                </div>
                
                <div class="meta-item">
                    <i class="ri-group-line meta-icon"></i>
                    <div class="meta-label">Capacity</div>
                    <div class="meta-value"><?php echo $event['maxVolunteers'] ?? 'Unlimited'; ?> volunteers</div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Prediction Section -->
        <div class="prediction-section">
            <div class="section-title">
                <i class="ri-bar-chart-2-line"></i>
                <span>AI-Powered Participation Forecast</span>
            </div>
            
            <?php if ($prediction && $prediction['success']): ?>
                
               
                <div class="prediction-number-card">
                    <div class="number-label">PREDICTED VOLUNTEERS</div>
                    <div class="number-value"><?php echo $prediction['prediction'] ?? '0'; ?></div>
                    <div class="number-unit">volunteers expected to attend</div>
                </div>
                
                
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $event['maxVolunteers'] ?? '∞'; ?></div>
                        <div class="stat-label">Maximum Capacity</div>
                    </div>
                    
                    <div class="stat-item">
                        <?php 
                        $percentage = $event['maxVolunteers'] > 0 
                            ? round(($prediction['prediction'] / $event['maxVolunteers']) * 100) 
                            : ($prediction['prediction'] == 1 ? 80 : 30);
                        ?>
                        <div class="stat-value"><?php echo $percentage; ?>%</div>
                        <div class="stat-label">of capacity</div>
                    </div>
                    
                </div>
                
               
                <div style="text-align: center; color: #94a3b8; font-size: 0.875rem; padding: 1rem; background: #f8fafc; border-radius: 15px;">
                    <i class="ri-magic-line" style="margin-right: 5px;"></i>
                    ML Model v2.0 · Based on historical event data · Updated in real-time
                </div>
                
            <?php else: ?>
                
             
                <div class="error-state">
                    <div class="error-icon">
                        <i class="ri-cloud-off-line"></i>
                    </div>
                    <h3 class="error-title">Prediction Unavailable</h3>
                    <p class="error-message">
                        <?php echo $prediction['message'] ?? 'Unable to generate prediction at this moment.'; ?>
                    </p>
                    <div style="background: #f1f5f9; padding: 1rem; border-radius: 15px; display: inline-block;">
                        <i class="ri-time-line" style="color: #64748b;"></i>
                        <span style="color: #64748b; margin-left: 5px;">Please try again later</span>
                    </div>
                </div>
                
            <?php endif; ?>
            
           
            <div class="action-buttons">
                <a href="view_event.php?id=<?php echo $eventId; ?>" class="btn-modern btn-primary-modern">
                    <i class="ri-eye-line"></i>
                    View Full Event Details
                </a>
                <a href="events.php" class="btn-modern btn-secondary-modern">
                    <i class="ri-arrow-left-line"></i>
                    Back to Events
                </a>
            </div>
            
            
            <div style="text-align: center; margin-top: 2rem; color: #94a3b8; font-size: 0.8rem;">
                <i class="ri-time-line"></i>
                Generated on <?php echo date('F j, Y \a\t g:i A'); ?>
            </div>
        </div>
    </div>
    
    
    <div style="text-align: center; margin-top: 2rem;">
        <span style="background: #1e293b; color: white; padding: 0.5rem 1.5rem; border-radius: 100px; font-size: 0.875rem;">
            <i class="ri-shield-check-line"></i>
            Powered by Machine Learning · Unity Volunteers Trust
        </span>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>