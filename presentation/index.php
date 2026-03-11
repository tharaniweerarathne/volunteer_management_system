<?php
require_once '../business_logic/eventLogic.php';
require_once '../business_logic/resultsLogic.php';
require_once '../business_logic/LeaderboardFacade.php';
require_once '../business_logic/StatisticsFacade.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$eventLogic = new EventLogic();
$resultsLogic = new ResultsLogic();

// Get filters from query parameters
$filters = [
    'search' => $_GET['search'] ?? '',
    'skillId' => $_GET['skillId'] ?? '',
    'category' => $_GET['category'] ?? '',
    'location' => $_GET['location'] ?? '',
    'date' => $_GET['date'] ?? ''
];

// Get upcoming events
$eventsData = $eventLogic->getUpcomingEvents($filters);
if ($eventsData['success']) {
    $events = $eventsData['events'];
} else {
    $events = [];
    $eventsError = $eventsData['message'] ?? 'Error loading events';
}

// Get approved events
$resultsData = $resultsLogic->getPublicResults(6, $filters);
if ($resultsData['success']) {
    $results = $resultsData['results'];
} else {
    $results = [];
    $resultsError = $resultsData['message'] ?? 'Error loading event results';
}

// Get categories and skills for filter dropdowns
$eventData = new EventData();
$categories = $eventData->getCategories();
$skills = $eventData->getAllSkills();

$leaderboardFacade = new LeaderboardFacade();
$podiumVolunteers = $leaderboardFacade->getPodiumLeaderboard();

$statisticsFacade = new StatisticsFacade();
$stats = $statisticsFacade->getDashboardStats();
$simpleStats = $stats['simple'];
$detailedStats = $stats['detailed'];
$allStats = $stats['all'];
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unity Volunteers Trust</title>
    <link rel="stylesheet" href="../assets/css/index4.css">
    <link rel="icon" type="image/png" href="../assets/images/title.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.css" rel="stylesheet">
 <style>

 </style>
</head>
<body>
    <section id ="home" class="hero-section">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="about.html">
                    <img src="../assets/images/logo.png" alt="Logo" class="logo-img">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" style="background: rgba(255,255,255,0.3);">
                    <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="#home">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#about">About</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#events">Events</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#leadership_board">Top Volunteers</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#past_events">Past Events</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#contact">Contact Us</a>
                        </li>
                        <li class="nav-item">
                            <button class="nav-button" onclick="window.location.href='sign_in.php'">Join Now</button>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- hero section -->
        <div class="hero-content">
            <div class="hero-text">
                <h1>Connecting Hearts, Organizing Hope</h1>
                <p>Together we organize impactful events that bring communities closer.</p>
                <button class="cta-button" onclick="window.location.href='sign_in.php'">Get Started</button>
            </div>
        </div>
    </section>


 <!-- about us section -->
<section id ="about" class="about_us animate-on-scroll">
     <div class="container-fluid px-0">
            <div class="row g-0 align-items-center">
                <div class="col-lg-5">
                    <div class="image-section">
                        <img src="../assets/images/about.png" alt="Volunteers helping community">
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="content-section">
                        <div class="welcome-text">Welcome to Unity Volunteers Trust</div>
                        <h1 class="main-heading">Our Goal Is to Make a Difference Together</h1>
                        <p class="description">
                            Unity Volunteers Trust is a community-based volunteer management system launched in 2026. This system enables organizers to create and manage volunteer events such as beach cleanups, blood donation drives, and awareness campaigns. Organizers can easily post new events, update event details, and monitor volunteer registrations. Volunteers can register, view upcoming events, and join activities based on their interests. The platform also stores past event results and images to showcase community impact. The mission is to connect passionate volunteers with meaningful causes and empower community action through organized volunteering.
                        </p>
                        <a href="about.html" class="cta-button">Learn More</a>
                    </div>
                </div>
            </div>
    </div>
</section>


<!-- help section -->
<section class="help-section animate-on-scroll">
        <div class="container">
            <h1 class="help-title">Powerful Features for Everyone</h1>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="help-card help-card-orange"  onclick="location.href='sign_in.php';" style="cursor:pointer;">
                        <i class="ri-user-fill help-icon"></i>
                        <p class="help-text">Easy Registration</p>
                        <p class="help-text1">Join in seconds with a simple sign-up process.
                             No complicated forms or lengthy approvals required.</p>
                        
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="help-card help-card-orange"  onclick="location.href='sign_in.php';" style="cursor:pointer;">
                        <i class="ri-calendar-2-line help-icon"></i>
                        <p class="help-text">Organize Event</p>
                        <p class="help-text1">Create and manage your own volunteer events with our
                             intuitive event management tools.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="help-card help-card-orange"  onclick="location.href='sign_in.php';" style="cursor:pointer;">
                        <i class="ri-megaphone-fill help-icon"></i>
                        <p class="help-text">Promote Your Cause</p>
                        <p class="help-text1">Share your events across social media and attract volunteers to support 
                            your initiative.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>



<!-- statistics section -->
<section class="statistics">
    <div class="stats-container">
        <?php foreach ($simpleStats as $stat): ?>
        <div class="stat-item">
            <span class="stat-number"><?php echo $stat['number']; ?></span>
            <span class="stat-label"><?php echo $stat['label']; ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</section>


<!-- join events section -->
    <section id="events" class="joinEventsSection">
        <div class="container">
            <h1 class="joinEventsTitle">Be Part of Our Latest Events</h1>
            <p class="joinEventsSubtitle">Find an event that inspires you to make a change.</p>

<div class="joinEventsSearchContainer">
            <input type="text" 
                   class="joinEventsSearchInput" 
                   placeholder="Search events..."
                   id="searchInput"
                   value="<?php echo htmlspecialchars($filters['search']); ?>">
            <button class="joinEventsSearchBtn" onclick="searchEvents()">
                <i class="ri-search-line"></i>
            </button>
        </div>

            
<div class="filter_details">
            <form method="GET" id="filterForm">
                <div class="search-row">
                    <input type="date" 
                           id="date" 
                           name="date" 
                           placeholder="Date"
                           value="<?php echo htmlspecialchars($filters['date']); ?>">
                    
                    <select id="category" name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category['category']); ?>"
                                <?php echo ($filters['category'] == $category['category']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['category']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <select id="skill" name="skillId">
                        <option value="">All Skills</option>
                        <?php foreach ($skills as $skill): ?>
                            <option value="<?php echo $skill['skillId']; ?>"
                                <?php echo ($filters['skillId'] == $skill['skillId']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($skill['skillName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <input type="text" 
                           id="location" 
                           name="location" 
                           placeholder="Location"
                           value="<?php echo htmlspecialchars($filters['location']); ?>">
                    
                    <button type="submit" class="search-btn">
                        <i class="ri-search-line"></i> Search
                    </button>
                </div>
            </form>
        </div>


<!-- Events Grid -->
<div class="row">
    <?php if (empty($events)): ?>
        <div class="col-12 text-center py-5">
            <p class="lead">No events found. Try different search criteria.</p>
        </div>
    <?php else: ?>
        <?php foreach ($events as $event): ?>
            <div class="col-md-4">
                <div class="joinEventsCard">
                    <div class="joinEventsCardImage">
                        <?php if ($event['eventImage']): ?>
                            <img src="../<?php echo htmlspecialchars($event['eventImage']); ?>" 
                                 alt="<?php echo htmlspecialchars($event['eventName']); ?>">
                        <?php else: ?>
                            <img src="../assets/images/default-event.jpg" 
                                 alt="Event Image">
                        <?php endif; ?>
                        
                        <div class="joinEventsDateBadge">
                            <?php 
                            $startDate = new DateTime($event['startDate']);
                            ?>
                            <span class="joinEventsDateDay"><?php echo $startDate->format('d'); ?></span>
                            <span class="joinEventsDateMonth"><?php echo $startDate->format('M'); ?></span>
                        </div>
                        
                        <!-- Organizer Badge -->
                        <?php if (!empty($event['organizerName'])): ?>
                            <div class="joinEventsOrganizerBadge">
                                <i class="ri-user-star-line"></i>
                                <span><?php echo htmlspecialchars($event['organizerName']); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="joinEventsCardBody">
                        <div class="joinEventsCardMeta">
                            <?php if ($event['category']): ?>
                                <span class="joinEventsCategory"><?php echo htmlspecialchars($event['category']); ?></span>
                            <?php endif; ?>
                            
                            <?php if ($event['skillName']): ?>
                                <span class="joinEventsSkill"><?php echo htmlspecialchars($event['skillName']); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <h3 class="joinEventsCardTitle"><?php echo htmlspecialchars($event['eventName']); ?></h3>
                        
                        <p class="joinEventsCardText">
                            <?php 
                            $description = $event['eventDescription'];
                            echo htmlspecialchars(substr($description, 0, 100));
                            if (strlen($description) > 100) echo '...';
                            ?>
                        </p>
                        
                        
                        <div class="joinEventsOrganizerInfo">
                            <div class="joinEventsCardDetail">
                                <i class="ri-user-3-line"></i>
                                <span>
                                    <?php 
                                    if (!empty($event['organizerName'])) {
                                        echo 'Organizer: ' . htmlspecialchars($event['organizerName']);
                                    } else {
                                        echo 'Organizer: Not specified';
                                    }
                                    ?>
                                </span>
                            </div>
                            
                            <?php if (!empty($event['coordinators'])): ?>
                                <div class="joinEventsCardDetail">
                                    <i class="ri-team-line"></i>
                                    <span>
                                        Coordinators: <?php 
                                        $coordNames = explode(', ', $event['coordinators']);
                                        if (count($coordNames) > 2) {
                                            echo htmlspecialchars($coordNames[0]) . ', ' . htmlspecialchars($coordNames[1]) . ' +' . (count($coordNames) - 2) . ' more';
                                        } else {
                                            echo htmlspecialchars($event['coordinators']);
                                        }
                                        ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="joinEventsCardDetails">
                            <div class="joinEventsCardDetail">
                                <i class="ri-calendar-event-line"></i>
                                <span><?php echo date('d/m/Y', strtotime($event['startDate'])); ?></span>
                            </div>
                            
                            <div class="joinEventsCardDetail">
                                <i class="ri-time-line"></i>
                                <span>
                                    <?php 
                                    echo date('h:i A', strtotime($event['startTime']));
                                    if ($event['endTime']) {
                                        echo ' to ' . date('h:i A', strtotime($event['endTime']));
                                    }
                                    ?>
                                </span>
                            </div>
                            
                            <div class="joinEventsCardDetail">
                                <i class="ri-map-pin-line"></i>
                                <?php if ($event['googleMapLink']): ?>
                                    <a href="<?php echo htmlspecialchars($event['googleMapLink']); ?>" 
                                       target="_blank" 
                                       class="joinEventsLocationLink">
                                        <?php echo htmlspecialchars($event['location']); ?>
                                    </a>
                                <?php else: ?>
                                    <span><?php echo htmlspecialchars($event['location']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php
                        
                        $joinUrl = isset($_SESSION['userId']) ? 'event_details.php?id=' . $event['eventId'] : 'sign_in.php';
                        ?>
                        
                        <button class="joinEventsJoinBtn" 
                                onclick="window.location.href='<?php echo $joinUrl; ?>'">
                            <?php echo isset($_SESSION['userId']) ? 'View Details' : 'Join Now'; ?>
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>


            
            </div>
           <button class="joinEventsMoreBtn" onclick="window.open('join_events_main.php', '_blank')">More Events</button>
        </div>
    </section>


    <!--certficate section-->
    <section class="certificates-section">
  <div class="container">
    <div class="content-wrapper">
      
      <div class="text-content">
        <h2>AI-Powered Volunteer Matching and Certification System</h2>
        <p>Our <span>AI-powered </span>system intelligently analyzes your skills and interests to match you with the most suitable volunteer
             events. Participate, make a real impact, and earn <span>official certificates</span>recognizing your valuable contributions!</p>
        <div class="button2"><a href="sign_in.php" class="cta-button1">Join Us</a></div>
      </div>
    </div>
  </div>
</section>




    <!-- leadership section -->
<section id="leadership_board" class="leaderboard-section animate-on-scroll">
    <div class="container">
        <div class="section-header">
            <h1 class="section-title">
                <i class="ri-trophy-line"></i> Top Volunteers
            </h1>
            <p class="section-subtitle">Celebrating our most dedicated community heroes based on attendance</p>
        </div>

        <div class="podium-container">
            <?php foreach ($podiumVolunteers as $volunteer): ?>
                <div class="podium-item <?php echo $volunteer['class']; ?>">
                    <div class="rank-badge">
                        <i class="<?php echo $volunteer['icon']; ?>" style="color: <?php echo $volunteer['icon_color']; ?>;"></i>
                    </div>
                    
                    <div class="volunteer-avatar" style="background: linear-gradient(135deg, <?php echo $volunteer['level_color']; ?>20, #fc5d0e);">
                        <?php echo htmlspecialchars($volunteer['initials']); ?>
                    </div>
                    
                    <h3 class="volunteer-name"><?php echo htmlspecialchars($volunteer['name']); ?></h3>
                    
                    <p class="volunteer-level">
                        <i class="ri-star-fill" style="color: <?php echo $volunteer['level_color']; ?>;"></i> 
                        <?php echo $volunteer['level']; ?>
                    </p>
                    
<div class="volunteer-stats">
    <div class="stat-item">
        <span class="stat-value"><?php echo $volunteer['total_hours']; ?></span>
        <span class="stat-label">Hours</span>
    </div>
    <div class="stat-item">
        <span class="stat-value"><?php echo $volunteer['unique_events']; ?></span>
        <span class="stat-label">Events</span>
    </div>
</div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
</section>
        <!--past events-->
<section id="past_events" class="past_events py-5">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="display-5 fw-bold">Highlights from Our Recent Activities</h2>
            <p class="lead mb-4">See what our volunteers have accomplished and get inspired to join the next one!</p>
        </div>

        <?php if (isset($resultsError)): ?>
            <div class="alert alert-danger text-center">
                <?php echo htmlspecialchars($resultsError); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($results)): ?>
            <div class="text-center py-5">
                <i class="ri-file-search-line" style="font-size: 4rem; color: #ccc;"></i>
                <h3 class="mt-3">No event results found</h3>
                <p class="text-muted">Check back later for more event highlights</p>
            </div>
        <?php else: ?>
            <!-- Carousel for Results -->
            <div id="resultsCarousel" class="carousel slide position-relative" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php 
                    $first = true;
                    $chunkedResults = array_chunk($results, 1);
                    
                    foreach ($chunkedResults as $index => $chunk): 
                    ?>
                        <div class="carousel-item <?php echo $first ? 'active' : ''; ?>">
                            <div class="row justify-content-center">
                                <?php foreach ($chunk as $result): ?>
                                    <div class="col-md-8">
                                        <div class="card shadow-lg" style="border-radius: 30px; border: 3px solid #333;">
                                            <div class="card-body p-4">
                                                <div class="row align-items-center">
                                                    <!-- Result Image -->
                                                    <div class="col-md-5">
                                                        <?php if (!empty($result['resultImage'])): ?>
                                                            <?php 
                                                            $resultImage = $result['resultImage'];
                                                            
                                                            if (!str_starts_with($resultImage, '../') && !str_starts_with($resultImage, 'http')) {
                                                                $resultImage = '../' . $resultImage;
                                                            }
                                                            ?>
                                                            <img src="<?php echo htmlspecialchars($resultImage); ?>" 
                                                                 alt="<?php echo htmlspecialchars($result['resultTitle']); ?>" 
                                                                 class="img-fluid" 
                                                                 style="border-radius: 20px; object-fit: cover; height: 300px; width: 100%;"
                                                                 onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1559027615-cd4628902d4a?w=400&h=400&fit=crop';">
                                                        <?php else: ?>
                                                            <div class="d-flex align-items-center justify-content-center bg-light" 
                                                                 style="border-radius: 20px; height: 300px; width: 100%;">
                                                                <i class="ri-image-line" style="font-size: 3rem; color: #ccc;"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <!-- Result Details -->
                                                    <div class="col-md-7">
                                                        <h3 class="fw-bold mb-2"><?php echo htmlspecialchars($result['resultTitle']); ?></h3>
                                                        
                                                        <!-- Event Name -->
                                                        <p class="text-muted mb-2">
                                                            <i class="ri-calendar-event-line me-1"></i>
                                                            Event: <?php echo htmlspecialchars($result['eventName'] ?? 'N/A'); ?>
                                                        </p>
                                                        
                                                        
                                                        <!-- Category and Skill -->
                                                        <div class="joinEventsCardMeta mb-3">
                                                            <?php if (!empty($result['category'])): ?>
                                                                <span class="joinEventsCategory">
                                                                    <?php echo htmlspecialchars($result['category']); ?>
                                                                </span>
                                                            <?php endif; ?>
                                                            
                                                            <?php if (!empty($result['skillName'])): ?>
                                                                <span class="joinEventsSkill">
                                                                    <?php echo htmlspecialchars($result['skillName']); ?>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                        
                                                        
                                                        
                                                        <!-- Result Date -->
                                                        <div class="d-flex align-items-center mb-3">
                                                            <i class="ri-calendar-line" style="color: #ff6b35; font-size: 24px;"></i>
                                                            <span class="ms-2 fw-semibold">
                                                                <?php echo date('d M, Y', strtotime($result['resultDate'])); ?>
                                                            </span>
                                                        </div>

                                                        <p class="mb-2">
                                                                <i class="ri-user-star-line me-1" style="color: #ff6b35;"></i>
                                                                <span class="fw-semibold">Organized by:</span> 
                                                                <?php echo htmlspecialchars($result['organizerName']); ?>
                                                            </p>
                                                        
                                                        <!-- Description -->
                                                        <p class="text-muted mb-4">
                                                            <?php 
                                                            $description = htmlspecialchars($result['description'] ?? '');
                                                            if (strlen($description) > 200) {
                                                                echo substr($description, 0, 200) . '...';
                                                            } else {
                                                                echo $description;
                                                            }
                                                            ?>
                                                        </p>
                                                        

                                                        <!-- View Details Button -->
                                                        <a href="view_result.php?resultId=<?php echo $result['resultId']; ?>"  
                                                           class="btn btn-link text-decoration-none fw-bold p-0" 
                                                           style="color: #ff6b35; font-size: 1.1rem;">
                                                            View Full Details <i class="ri-arrow-right-line"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php $first = false; ?>
                    <?php endforeach; ?>
                </div>

                <!-- Carousel Controls -->
                <?php if (count($chunkedResults) > 1): ?>
                    <button class="carousel-control-prev" type="button" data-bs-target="#resultsCarousel" data-bs-slide="prev">
                        <i class="ri-arrow-left-s-fill" style="font-size: 60px; color: black;"></i>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#resultsCarousel" data-bs-slide="next">
                        <i class="ri-arrow-right-s-fill" style="font-size: 60px; color: black;"></i>
                        <span class="visually-hidden">Next</span>
                    </button>
                <?php endif; ?>
            </div>
            
            
            <?php if (count($chunkedResults) > 1): ?>
                <div class="carousel-indicators position-static mt-4">
                    <?php for ($i = 0; $i < count($chunkedResults); $i++): ?>
                        <button type="button" data-bs-target="#resultsCarousel" data-bs-slide-to="<?php echo $i; ?>" 
                                class="<?php echo $i === 0 ? 'active' : ''; ?>">
                        </button>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
            
           
            <div class="text-center mt-5">
                <button class="joinEventsMoreBtn" onclick="window.open('past_events.php', '_blank')">
                    View All Past Events
                </button>
            </div>
        <?php endif; ?>
    </div>
</section>

<!--contact us section-->
<section class="contact-section" id="contact">
    <div class="contact-container">
        <div class="contact-header">
            <h2>Get In Touch</h2>
            <p>Have a question or want to work together? Drop us a message!</p>
        </div>
        
            
            <?php
            if (isset($_SESSION['contact_result'])):
            ?>
                <div class="alert alert-<?php echo $_SESSION['contact_result']['success'] ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                    <i class="fas fa-<?php echo $_SESSION['contact_result']['success'] ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo htmlspecialchars($_SESSION['contact_result']['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php
                unset($_SESSION['contact_result']);
            endif;
            ?>
        
        <form id="contactForm" action="process_contact.php" method="POST" novalidate>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name" class="form-label">Full Name</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="name" 
                            name="name" 
                            placeholder="Please enter your name."
                            required
                        >
                        <div class="invalid-feedback">Please enter your name.</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input 
                            type="email" 
                            class="form-control" 
                            id="email" 
                            name="email" 
                            placeholder="example@gmail.com"
                            required
                        >
                        <div class="email-hints" id="emailHints"></div>
                        <div class="invalid-feedback">Please enter a valid email address.</div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="message" class="form-label">Message</label>
                <textarea 
                    class="form-control" 
                    id="message" 
                    name="message" 
                    rows="6"
                    placeholder="Please enter your message..."
                    required
                ></textarea>
                <div class="invalid-feedback">Please enter your message.</div>
            </div>

            <button type="submit" class="submit-btn">
                <span class="btn-text">Send Message</span>
                <span class="btn-loading" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i> Sending...
                </span>
            </button>
        </form>
    </div>
</section>



<!--footer section-->
 <footer>
        <div class="container">
            <div class="row">
              
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="footer-logo">
                        <img src="../assets/images/logo.png" alt="Logo" class="logo-img">
                    </div>
                    <p class="footer-description">
                        Empowering communities through seamless volunteer coordination. We connect people, 
                        manage events, and inspire positive change with smart, efficient solutions.
                    <div class="social-links mt-4">
                        <a href="#"><i class="ri-facebook-fill"></i></a>
                        <a href="#"><i class="ri-instagram-line"></i></a>
                        <a href="#"><i class="ri-linkedin-fill"></i></a>
                    </div>
                </div>

               
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5 class="footer-title">Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="#home"><i class="ri-arrow-right-s-line"></i> Home</a></li>
                        <li><a href="#about"><i class="ri-arrow-right-s-line"></i> About Us</a></li>
                        <li><a href="#events"><i class="ri-arrow-right-s-line"></i> Events</a></li>
                        <li><a href="#leadership_board"><i class="ri-arrow-right-s-line"></i> Top Volunteers</a></li>
                        <li><a href="#past_events"><i class="ri-arrow-right-s-line"></i> Past Events</a></li>
                        <li><a href="#contact"><i class="ri-arrow-right-s-line"></i> Contact Us</a></li>
                    </ul>
                </div>


                
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="footer-title">Contact Us</h5>
                    <ul class="footer-links">
                        <li>
                            <i class="ri-map-pin-line"></i> 
                            Liyanagemulla Road<br>
                            <span style="padding-left: 24px">Seeduwa, Sri Lanka</span>
                        </li>
                        <li><i class="ri-phone-line"></i>077 235 3565</li>
                        <li><i class="ri-mail-line"></i> infocontact256@gmail.com</li>
                        <li><i class="ri-time-line"></i> Mon - Sun: 9:00 - 17:00</li>
                    </ul>
                </div>
            </div>

            
            <div class="footer-bottom">
                <p class="mb-0">&copy; 2025 Unity Volunteers Trust. All rights reserved. <i class="ri-heart-fill" style="color: #ff6b6b;"></i></p>
            </div>
        </div>
    </footer>






    <!-- scroll to top button -->
    <div class="scroll-to-top">
         <i class="ri-arrow-up-s-line"></i>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    // scroll animation observer
    const observerOptions = {
        threshold: 0.2,
        rootMargin: '0px 0px -100px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, observerOptions);

    document.addEventListener('DOMContentLoaded', () => {
        const aboutSections = document.querySelectorAll('.animate-on-scroll');
        aboutSections.forEach(section => {
            observer.observe(section);
        });
    });



    //scroll to top
    document.addEventListener('DOMContentLoaded', () => {
    const scrollToTopBtn = document.querySelector('.scroll-to-top');
    
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            scrollToTopBtn.classList.add('show');
        } else {
            scrollToTopBtn.classList.remove('show');
        }
    });
    
    scrollToTopBtn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
    
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                window.scrollTo({ top: targetElement.offsetTop - 80, behavior: 'smooth' });
            }
        });
    });
});





// Email validations 
const emailInput = document.getElementById('email');
const emailHints = document.getElementById('emailHints');

emailInput.addEventListener('input', function() {
    const value = this.value.trim();
    emailHints.innerHTML = '';

    if (value.length > 0) {
        const messages = [];
        
        // checking for @ symbol
        if (!value.includes('@')) {
            messages.push('Please add @');
        } else {
            // checking for domain after @
            const parts = value.split('@');
            if (parts.length === 2 && parts[1].length > 0) {
                // checking for dot
                if (!parts[1].includes('.')) {
                    messages.push('Please add . (dot)');
                } else {
                    // checking for domain extension
                    const afterDot = parts[1].split('.').pop();
                    if (afterDot.length < 2) {
                        messages.push('Please add domain extension (com, net, org, etc.)');
                    }
                }
            } else if (parts.length === 2 && parts[1].length === 0) {
                messages.push('Please add domain name');
            }
        }

        if (messages.length > 0) {
            messages.forEach(msg => {
                const span = document.createElement('span');
                span.className = 'email-hint';
                span.textContent = msg;
                emailHints.appendChild(span);
            });
        }
    }
});

// Form validation
const form = document.getElementById('contactForm');
const submitBtn = form.querySelector('.submit-btn');
const btnText = submitBtn.querySelector('.btn-text');
const btnLoading = submitBtn.querySelector('.btn-loading');

form.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const inputs = form.querySelectorAll('.form-control');
    inputs.forEach(input => {
        input.classList.remove('is-invalid');
    });

    let isValid = true;

    // Validate name
    const name = document.getElementById('name');
    if (name.value.trim() === '') {
        name.classList.add('is-invalid');
        isValid = false;
    }

    // Validate email
    const email = document.getElementById('email');
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email.value.trim())) {
        email.classList.add('is-invalid');
        isValid = false;
    }

    // Validate message
    const message = document.getElementById('message');
    if (message.value.trim() === '') {
        message.classList.add('is-invalid');
        isValid = false;
    }

    if (isValid) {
        // Show loading state
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;
        btnText.style.display = 'none';
        btnLoading.style.display = 'inline-block';

        // Submit form
        form.submit();
    }
});

// Remove invalid class on input
const inputs = form.querySelectorAll('.form-control');
inputs.forEach(input => {
    input.addEventListener('input', function() {
        if (this.classList.contains('is-invalid')) {
            this.classList.remove('is-invalid');
        }
    });
});

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
});




        // Search function for the main search bar
        function searchEvents() {
            const searchInput = document.getElementById('searchInput').value;
            const url = new URL(window.location.href);
            url.searchParams.set('search', searchInput);
            window.location.href = url.toString();
        }
        
        // Enter key support for search
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchEvents();
            }
        });
        
        // Auto-submit filters when changed (optional)
        document.getElementById('filterForm').addEventListener('change', function() {
            this.submit();
        });

        


        
</script>
</body>
</html>