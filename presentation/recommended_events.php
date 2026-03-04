<?php

require_once '../business_logic/eventLogic.php';
require_once '../data_access/UserData.php';
require_once '../data_access/SkillData.php';
require_once '../data_access/db.php'; 

// Now check if user is logged in (session was started by eventLogic.php)
if (!isset($_SESSION['userId']) || $_SESSION['role'] !== 'Volunteer') {
    header("Location: sign_in.php");
    exit();
}

$userId = $_SESSION['userId'];
$eventLogic = new EventLogic();

// Use the global connection
global $conn;

// Pass connection to data classes
$userData = new UserData($conn);
$skillData = new SkillData($conn);

// Get recommended events
$result = $eventLogic->getRecommendedEvents($userId);
$user = $userData->getUserById($userId);

// Process user skills
$userSkills = [];
$userSkillIds = [];
if (!empty($user['skillNames'])) {
    $userSkills = explode(', ', $user['skillNames']);
}
if (!empty($user['skillIds'])) {
    $userSkillIds = explode(',', $user['skillIds']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Recommendations · Unity Volunteers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/recommendation.css">
    <link rel="icon" type="image/png" href="../assets/images/title.png">

</head>
<body>

<!-- Navigation -->
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
                    <a class="nav-link active" href="volunteer_dashboard.php">Dashboard</a>
            </div>
        </div>
    </div>
</nav>

<div class="container-custom">

  
    <div class="profile-card">
        <div class="profile-avatar">
            <i class="ri-user-heart-line"></i>
        </div>
        <div style="flex: 1;">
            <h2>Hello, <?php echo htmlspecialchars($user['name']); ?>! 👋</h2>
            <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <span class="skill-badge">
                    <i class="ri-tools-line"></i>
                    Your Skills: 
                    <strong>
                        <?php 
                        if (!empty($userSkills)) {
                            if (count($userSkills) > 2) {
                                echo implode(', ', array_slice($userSkills, 0, 2)) . ' +' . (count($userSkills) - 2);
                            } else {
                                echo implode(', ', $userSkills);
                            }
                        } else {
                            echo 'Not specified';
                        }
                        ?>
                    </strong>
                </span>
                
                <?php if (!empty($userSkills)): ?>
                <span class="skill-count-badge">
                    <i class="ri-star-fill"></i> <?php echo count($userSkills); ?> skill(s)
                </span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recommendations Section -->
    <div class="section-title">
        <h2>
            <i class="ri-star-smile-line"></i>
            Smart Recommendations For You
        </h2>
        <span class="ai-badge">
            <i class="ri-robot-2-line"></i> AI-Powered
        </span>
    </div>

    <?php if (!$result['success']): ?>
        
        <!-- ERROR DISPLAY -->
        <div class="error-alert">
            <h3>
                <i class="ri-error-warning-line"></i>
                Recommendation System Error
            </h3>
            <p><?php echo htmlspecialchars($result['message']); ?></p>
            
<?php if (isset($result['error_type'])): ?>
<div class="error-details">
    <p class="mt-2">An error occurred while processing the prediction request. Please try again later.</p>
</div>
<?php endif; ?>
            
            <a href="recommended_events.php" class="retry-btn">
                <i class="ri-refresh-line"></i> Retry
            </a>
        </div>

    <?php elseif (empty($result['events'])): ?>
        
        <div class="no-events">
            <i class="ri-heart-line"></i>
            <h3>No Recommendations Yet</h3>
            <p>We're still learning about your preferences. Check back soon!</p>
            <a href="events_volunteer.php" class="btn btn-primary mt-3 rounded-pill">
                Browse All Events
            </a>
        </div>

    <?php else: ?>
        
        <div class="events-grid">
            <?php foreach ($result['events'] as $event): 
                $matchPercentage = $event['match_percentage'] ?? 50;
                $matchColor = $matchPercentage >= 80 ? '#10b981' : ($matchPercentage >= 60 ? '#f59e0b' : '#94a3b8');
                $userHasSkill = false;
                if (!empty($event['requiredSkillId']) && !empty($userSkillIds)) {
                    $userHasSkill = in_array($event['requiredSkillId'], $userSkillIds);
                }
            ?>
                <div class="event-card">
                    <?php if ($matchPercentage >= 70): ?>
                    <div class="match-badge">
                        <i class="ri-star-fill"></i> <?php echo $matchPercentage; ?>% Match
                    </div>
                    <?php endif; ?>
                    
                    <div class="event-image" style="background-image: url('../<?php echo $event['eventImage'] ?? 'assets/images/default-event.jpg'; ?>')"></div>
                    
                    <div class="event-content">
                        <span class="event-category">
                            <i class="ri-price-tag-3-line"></i> <?php echo $event['category'] ?? 'General'; ?>
                        </span>
                        
                        <h3 class="event-title"><?php echo htmlspecialchars($event['eventName']); ?></h3>
                        
                        <div class="event-details">
                            <div class="detail-item">
                                <i class="ri-calendar-line"></i>
                                <?php echo date('M d, Y', strtotime($event['startDate'])); ?>
                            </div>
                            <div class="detail-item">
                                <i class="ri-time-line"></i>
                                <?php echo date('h:i A', strtotime($event['startTime'])); ?> - <?php echo date('h:i A', strtotime($event['endTime'])); ?>
                            </div>
                            <div class="detail-item">
                                <i class="ri-map-pin-line"></i>
                                <?php echo htmlspecialchars($event['location']); ?>
                            </div>
                        </div>

                        <?php if (!empty($event['skillName'])): ?>
                        <div class="skill-required">
                            <i class="ri-tools-line"></i>
                            Required: <strong><?php echo $event['skillName']; ?></strong>
                            <?php if ($userHasSkill): ?>
                                <span class="skill-match-badge">
                                    <i class="ri-check-line"></i> You have this skill!
                                </span>
                            <?php else: ?>
                                <span style="margin-left: auto; color: #f59e0b; font-size: 0.8rem;">
                                    <i class="ri-error-warning-line"></i> Skill needed
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Match Progress -->
                        <div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span style="color: #64748b; font-size: 0.9rem;">Match Score</span>
                                <span style="color: <?php echo $matchColor; ?>; font-weight: 700;"><?php echo $matchPercentage; ?>%</span>
                            </div>
                            <div class="progress-bar-container">
                                <div class="progress-bar-fill" style="width: <?php echo $matchPercentage; ?>%; background: <?php echo $matchColor; ?>"></div>
                            </div>
                        </div>

                        <div class="event-footer">
                            <a href="event_details.php?eventId=<?php echo $event['eventId']; ?>" class="btn-join">
                             Join Event
                            </a>
                            <span class="spots-left">
                                <i class="ri-team-line"></i> 
                                <?php 
                                $spots = $event['maxVolunteers'] - ($event['joinedCount'] ?? 0);
                                echo $spots > 0 ? "$spots spots left" : "Full";
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

    

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>