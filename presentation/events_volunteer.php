<?php
require_once '../business_logic/eventLogic.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$eventLogic = new EventLogic();

// get filters from query parameters
$filters = [
    'search' => $_GET['search'] ?? '',
    'skillId' => $_GET['skillId'] ?? '',
    'category' => $_GET['category'] ?? '',
    'location' => $_GET['location'] ?? '',
    'date' => $_GET['date'] ?? ''
];

// get upcoming events using EventLogic
$result = $eventLogic->getUpcomingEvents($filters);

if ($result['success']) {
    $events = $result['events'];
} else {
    $events = [];
    $error = $result['message'] ?? 'Error loading events';
}

// get categories and skills for filter dropdowns
$eventData = new EventData();
$categories = $eventData->getCategories();
$skills = $eventData->getAllSkills();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management</title>
    <link rel="icon" type="image/png" href="../assets/images/title.png">
    <link rel="stylesheet" href="../assets/css/join_event_main.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">

    <style>
        .card-img-top { height: 200px; object-fit: cover; }
        .required:after { content: " *"; color: red; }
        .logo-img { height: 60px; width: auto; margin-right: 10px; vertical-align: middle;}
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="#">
            <img src="../assets/images/logo.png" alt="Logo" class="logo-img">
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
            aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        
        <div class="collapse navbar-collapse" id="navbarContent">
            <div class="navbar-nav ms-auto">
                    <a class="nav-link active" href="volunteer_dashboard.php">Back</a>
                    <a class="nav-link" href="my_events.php">My Events</a>
            </div>
        </div>
    </div>
</nav>

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
                        
                        <!-- NEW: Organizer Info Section -->
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
                        // Check if user is logged in
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>


        // search function 
        function searchEvents() {
            const searchInput = document.getElementById('searchInput').value;
            const url = new URL(window.location.href);
            url.searchParams.set('search', searchInput);
            window.location.href = url.toString();
        }
        
        
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchEvents();
            }
        });
        
        
        document.getElementById('filterForm').addEventListener('change', function() {
            this.submit();
        });
</script>
</body>
</html>