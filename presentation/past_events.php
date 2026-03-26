<?php
require_once '../business_logic/eventLogic.php';
require_once '../business_logic/resultsLogic.php';


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

// Get approved events
$resultsData = $resultsLogic->getPublicResults(20, $filters); 
if ($resultsData['success']) {
    $results = $resultsData['results'];
} else {
    $results = [];
    $resultsError = $resultsData['message'] ?? 'Error loading event results';
}


$eventData = new EventData();
$categories = $eventData->getCategories();
$skills = $eventData->getAllSkills();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Past Events</title>
    <link rel="stylesheet" href="../assets/css/past_events_main.css">
    <link rel="icon" type="image/png" href="../assets/images/title.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.css" rel="stylesheet">

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
                            <a class="nav-link" href="index.php#home">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php#about">About</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php#events">Events</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php#leadership_board">Top Volunteers</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="past_events_main.php">Past Events</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php#contact">Contact Us</a>
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
                <h1>Highlights from Our Recent Activities</h1>
                <p>See what our volunteers have accomplished and get inspired to join the next one!</p>
            </div>
        </div>
    </section>

    <section class="past_events py-5">
        <div class="container">
            <!-- Search Box -->
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="joinEventsSearchContainer">
                        <input type="text" id="searchInput" class="joinEventsSearchInput" 
                               placeholder="Search event results..." 
                               value="<?php echo htmlspecialchars($filters['search']); ?>"
                               onkeypress="handleKeyPress(event)">
                        <button class="joinEventsSearchBtn" onclick="searchEvents()">
                            <i class="ri-search-line"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Advanced Filters -->
            <div class="filter_details">
                <form method="GET" id="filterForm">
                    <div class="search-row">
                        <input type="date" id="date" name="date" placeholder="Result Date"
                               value="<?php echo htmlspecialchars($filters['date']); ?>">
                        
                        <select id="category" name="category">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat['category']); ?>"
                                    <?php echo ($filters['category'] == $cat['category']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['category']); ?>
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
                        
                        <input type="text" id="location" name="location" placeholder="Event Location"
                               value="<?php echo htmlspecialchars($filters['location']); ?>"
                               onkeypress="handleKeyPress(event)">
                        
                        <button type="submit" class="search-btn">
                            <i class="ri-search-line"></i> Search
                        </button>
                    </div>
                </form>
            </div>

            <!-- Results Count -->
            <div class="row mb-4">
                <div class="col-12">
                    <p class="text-center text-muted">
                        <?php echo count($results); ?> result<?php echo count($results) != 1 ? 's' : ''; ?> found
                    </p>
                </div>
            </div>

            <!-- Event Results Grid -->
            <div class="events-container">
                <?php if (isset($resultsError)): ?>
                    <div class="alert alert-danger text-center">
                        <?php echo htmlspecialchars($resultsError); ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($results)): ?>
                    <div class="no-results">
                        <i class="ri-file-search-line"></i>
                        <h3 class="mt-3">No event results found</h3>
                        <p class="text-muted">Try adjusting your search criteria or check back later for more event highlights.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($results as $result): ?>
                        <div class="row justify-content-center mb-4">
                            <div class="col-md-10">
                                <div class="card shadow-lg result-card">
                                    <div class="card-body p-4">
                                        <div class="row align-items-center">
                                            <!-- Result Image -->
                                            <div class="col-md-5">
                                                <?php if (!empty($result['resultImage'])): ?>
                                                    <?php 
                                                    $imagePath = $result['resultImage'];
                                                    if (!str_starts_with($imagePath, '../') && !str_starts_with($imagePath, 'http')) {
                                                        $imagePath = '../' . $imagePath;
                                                    }
                                                    ?>
                                                    <img src="<?php echo htmlspecialchars($imagePath); ?>" 
                                                         alt="<?php echo htmlspecialchars($result['resultTitle']); ?>" 
                                                         class="img-fluid rounded">
                                                <?php else: ?>
                                                    <div class="d-flex align-items-center justify-content-center bg-light rounded" 
                                                         style="height: 300px;">
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
                                                    <strong>Event:</strong> <?php echo htmlspecialchars($result['eventName'] ?? 'N/A'); ?>
                                                </p>
                                                
                                                <!-- Category and Skill -->
                                                <div class="mb-3">
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
                                                <div class="event-meta">
                                                    <i class="ri-calendar-line"></i>
                                                    <span class="fw-semibold">
                                                        <?php echo date('F j, Y', strtotime($result['resultDate'])); ?>
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
                                                    if (strlen($description) > 250) {
                                                        echo substr($description, 0, 250) . '...';
                                                    } else {
                                                        echo $description;
                                                    }
                                                    ?>
                                                </p>
                                                
                                                <!-- View Details Button -->
<a href="view_result.php?resultId=<?php echo $result['resultId']; ?>" 
   class="btn btn-primary"
   style="background-color: #F67D31; border: 1px solid orange; color: white;">
    View Details
</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
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
                    </p>
                    <div class="social-links mt-4">
                        <a href="#"><i class="ri-facebook-fill"></i></a>
                        <a href="#"><i class="ri-instagram-line"></i></a>
                        <a href="#"><i class="ri-linkedin-fill"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6 mb-4">
                    <h5 class="footer-title">Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="index.php#home"><i class="ri-arrow-right-s-line"></i> Home</a></li>
                        <li><a href="index.php#about"><i class="ri-arrow-right-s-line"></i> About Us</a></li>
                        <li><a href="index.php#events"><i class="ri-arrow-right-s-line"></i> Events</a></li>
                        <li><a href="index.php#leadership_board"><i class="ri-arrow-right-s-line"></i> Top Volunteers</a></li>
                        <li><a href="past_events_main.php"><i class="ri-arrow-right-s-line"></i> Past Events</a></li>
                        <li><a href="index.php#contact"><i class="ri-arrow-right-s-line"></i> Contact Us</a></li>
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
    // Search function
    function searchEvents() {
        const search = document.getElementById('searchInput').value;
        const date = document.getElementById('date').value;
        const category = document.getElementById('category').value;
        const skill = document.getElementById('skill').value;
        const location = document.getElementById('location').value;
        
        // Build query string
        const params = new URLSearchParams();
        if (search) params.append('search', search);
        if (date) params.append('date', date);
        if (category) params.append('category', category);
        if (skill) params.append('skillId', skill);
        if (location) params.append('location', location);
        
        // Reload page with filters
        window.location.href = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
    }

    // Clear all filters
    function clearFilters() {
        window.location.href = window.location.pathname;
    }

   
    function handleKeyPress(e) {
        if (e.key === 'Enter') {
            searchEvents();
        }
    }


    document.getElementById('filterForm')?.addEventListener('change', function() {

    });

    // Scroll animation observer
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

    // Scroll to top
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
    </script>
</body>
</html>