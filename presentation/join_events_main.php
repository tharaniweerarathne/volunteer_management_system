<?php
require_once '../business_logic/eventLogic.php';

// Check if session is already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Create instance of EventLogic
$eventLogic = new EventLogic();

// Get filters from query parameters
$filters = [
    'search' => $_GET['search'] ?? '',
    'skillId' => $_GET['skillId'] ?? '',
    'category' => $_GET['category'] ?? '',
    'location' => $_GET['location'] ?? '',
    'date' => $_GET['date'] ?? ''
];

// Get upcoming events using EventLogic
$result = $eventLogic->getUpcomingEvents($filters);

if ($result['success']) {
    $events = $result['events'];
} else {
    $events = [];
    $error = $result['message'] ?? 'Error loading events';
}

// Get categories and skills for filter dropdowns
$eventData = new EventData();
$categories = $eventData->getCategories();
$skills = $eventData->getAllSkills();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Events</title>
    <link rel="stylesheet" href="../assets/css/join_event_main.css">
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
                            <a class="nav-link" href="index.html#home">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.html#about">About</a>
                        </li>
                       <li class="nav-item">
                            <a class="nav-link" href="index.html#events">Events</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.html#leadership_board">Top Volunteers</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.html#past_events">Past Events</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.html#contact">Contact Us</a>
                        </li>
                        <li class="nav-item">
                            <button class="nav-button" onclick="window.location.href='sign_in.php'">Sign Up</button>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>


        <!-- hero section -->
        <div class="hero-content">
            <div class="hero-text">
                <h1>Be Part of Our Latest Events</h1>
                <p>Find an event that inspires you to make a change.</p>
            </div>
        </div>
    </section>


<section class="joinEventsSection animate-on-scroll">
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
                        // Check if user is logged in (REMOVED session_start() from here)
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
      
    </div>
</section>



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
                        <li><a href="index.html#home"><i class="ri-arrow-right-s-line"></i> Home</a></li>
                        <li><a href="index.html#about"><i class="ri-arrow-right-s-line"></i> About Us</a></li>
                        <li><a href="index.html#events"><i class="ri-arrow-right-s-line"></i> Events</a></li>
                        <li><a href="index.html#leadership_board"><i class="ri-arrow-right-s-line"></i> Top Volunteers</a></li>
                        <li><a href="index.html#past_events"><i class="ri-arrow-right-s-line"></i> Past Events</a></li>
                        <li><a href="index.html#contact"><i class="ri-arrow-right-s-line"></i> Contact Us</a></li>
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