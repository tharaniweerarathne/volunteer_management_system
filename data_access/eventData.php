<?php
require_once 'db.php';

class eventData {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
 public function getAllEvents($filters = []) {
    global $conn;
    
    $sql = "SELECT e.*, s.skillName, 
            GROUP_CONCAT(u.name SEPARATOR ', ') as coordinators,
            GROUP_CONCAT(ec.coordinatorId) as coordinatorIds
            FROM events e
            LEFT JOIN skills s ON e.requiredSkillId = s.skillId
            LEFT JOIN event_coordinators ec ON e.eventId = ec.eventId
            LEFT JOIN users u ON ec.coordinatorId = u.userId
            WHERE 1=1";
    
    $params = [];
    $types = "";
    
    // Search filter
    if (!empty($filters['search'])) {
        $sql .= " AND (e.eventName LIKE ? OR e.location LIKE ? OR e.category LIKE ? OR e.eventDescription LIKE ?)";
        $searchTerm = "%{$filters['search']}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "ssss";
    }
    
    // Skill filter
    if (!empty($filters['skillId'])) {
        $sql .= " AND e.requiredSkillId = ?";
        $params[] = $filters['skillId'];
        $types .= "i";
    }
    
    // Category filter
    if (!empty($filters['category'])) {
        $sql .= " AND e.category = ?";
        $params[] = $filters['category'];
        $types .= "s";
    }
    
    // Location filter - ADDED THIS
    if (!empty($filters['location'])) {
        $sql .= " AND e.location LIKE ?";
        $params[] = "%{$filters['location']}%";
        $types .= "s";
    }
    
    // Date filter - ADDED THIS
    if (!empty($filters['date'])) {
        // This finds events that are happening on a specific date
        // The date filter should match events where the selected date is within the event's date range
        $sql .= " AND ? BETWEEN e.startDate AND e.endDate";
        $params[] = $filters['date'];
        $types .= "s";
    }
    
    $sql .= " GROUP BY e.eventId ORDER BY e.startDate DESC";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
    
    // Create new event
    public function createEvent($data) {
        global $conn;
        
        $sql = "INSERT INTO events (eventName, eventDescription, category, location, 
                googleMapLink, startDate, endDate, startTime, endTime, maxVolunteers, 
                requiredSkillId, eventImage, createdBy) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssiisi", 
            $data['eventName'],
            $data['eventDescription'],
            $data['category'],
            $data['location'],
            $data['googleMapLink'],
            $data['startDate'],
            $data['endDate'],
            $data['startTime'],
            $data['endTime'],
            $data['maxVolunteers'],
            $data['requiredSkillId'],
            $data['eventImage'],
            $data['createdBy']
        );
        
        return $stmt->execute() ? $conn->insert_id : false;
    }
    
    // Get event by ID
    public function getEventById($eventId) {
        global $conn;
        
        $sql = "SELECT e.*, s.skillName, 
                GROUP_CONCAT(ec.coordinatorId) as coordinatorIds
                FROM events e
                LEFT JOIN skills s ON e.requiredSkillId = s.skillId
                LEFT JOIN event_coordinators ec ON e.eventId = ec.eventId
                WHERE e.eventId = ?
                GROUP BY e.eventId";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_assoc();
    }
    
    // Update event
    public function updateEvent($eventId, $data) {
        global $conn;
        
        $sql = "UPDATE events SET 
                eventName = ?, eventDescription = ?, category = ?, 
                location = ?, googleMapLink = ?, startDate = ?, 
                endDate = ?, startTime = ?, endTime = ?, 
                maxVolunteers = ?, requiredSkillId = ?, eventImage = ?
                WHERE eventId = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssiisi", 
            $data['eventName'],
            $data['eventDescription'],
            $data['category'],
            $data['location'],
            $data['googleMapLink'],
            $data['startDate'],
            $data['endDate'],
            $data['startTime'],
            $data['endTime'],
            $data['maxVolunteers'],
            $data['requiredSkillId'],
            $data['eventImage'],
            $eventId
        );
        
        return $stmt->execute();
    }
    
    // Delete event
    public function deleteEvent($eventId) {
        global $conn;
        
        // Disable autocommit for pseudo-transaction
        $conn->autocommit(FALSE);
        
        try {
            // Delete from event_coordinators first
            $sql1 = "DELETE FROM event_coordinators WHERE eventId = ?";
            $stmt1 = $conn->prepare($sql1);
            $stmt1->bind_param("i", $eventId);
            
            if (!$stmt1->execute()) {
                throw new Exception("Failed to delete from event_coordinators");
            }
            
            // Delete from events table
            $sql2 = "DELETE FROM events WHERE eventId = ?";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bind_param("i", $eventId);
            
            if (!$stmt2->execute()) {
                throw new Exception("Failed to delete from events");
            }
            
            // Commit pseudo-transaction
            $conn->commit();
            return true;
            
        } catch (Exception $e) {
            // Rollback on error
            $conn->rollback();
            error_log("Delete event failed: " . $e->getMessage());
            return false;
        } finally {
            // Restore autocommit
            $conn->autocommit(TRUE);
        }
    }
    
    // Assign coordinators to event
    public function assignCoordinators($eventId, $coordinatorIds) {
        global $conn;
        
        // Check for scheduling conflicts
        $conflicts = $this->checkSchedulingConflicts($eventId, $coordinatorIds);
        if (!empty($conflicts)) {
            return ['success' => false, 'conflicts' => $conflicts];
        }
        
        // Remove existing assignments
        $sql = "DELETE FROM event_coordinators WHERE eventId = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        
        // Add new assignments
        $sql = "INSERT INTO event_coordinators (eventId, coordinatorId) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        
        foreach ($coordinatorIds as $coordinatorId) {
            $stmt->bind_param("ii", $eventId, $coordinatorId);
            $stmt->execute();
        }
        
        return ['success' => true];
    }
    
    // Most accurate version using PHP DateTime
    public function checkSchedulingConflicts($eventId, $coordinatorIds) {
        global $conn;
        $conflicts = [];
        
        // Get the event we're trying to assign
        $event = $this->getEventById($eventId);
        if (!$event) return $conflicts;
        
        // Create DateTime objects for new event
        $newEventStart = new DateTime($event['startDate'] . ' ' . $event['startTime']);
        $newEventEnd = new DateTime($event['endDate'] . ' ' . $event['endTime']);
        
        $sql = "SELECT e.eventId, e.eventName, e.startDate, e.endDate, 
                       e.startTime, e.endTime, u.name as coordinatorName, u.userId
                FROM event_coordinators ec
                JOIN events e ON ec.eventId = e.eventId
                JOIN users u ON ec.coordinatorId = u.userId
                WHERE ec.coordinatorId = ? 
                AND ec.eventId != ?";
        
        $stmt = $conn->prepare($sql);
        
        foreach ($coordinatorIds as $coordinatorId) {
            $stmt->bind_param("ii", $coordinatorId, $eventId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($existingEvent = $result->fetch_assoc()) {
                // Create DateTime objects for existing event
                $existingStart = new DateTime($existingEvent['startDate'] . ' ' . $existingEvent['startTime']);
                $existingEnd = new DateTime($existingEvent['endDate'] . ' ' . $existingEvent['endTime']);
                
                // Check if events overlap
                // Events overlap if one starts before the other ends and ends after the other starts
                if ($newEventStart < $existingEnd && $newEventEnd > $existingStart) {
                    $conflicts[] = [
                        'coordinatorId' => $coordinatorId,
                        'coordinatorName' => $existingEvent['coordinatorName'],
                        'eventName' => $existingEvent['eventName'],
                        'existingEvent' => $existingEvent,
                        'conflictType' => 'Scheduling conflict',
                        'newEvent' => [
                            'start' => $newEventStart->format('Y-m-d H:i'),
                            'end' => $newEventEnd->format('Y-m-d H:i')
                        ],
                        'conflictEvent' => [
                            'start' => $existingStart->format('Y-m-d H:i'),
                            'end' => $existingEnd->format('Y-m-d H:i')
                        ]
                    ];
                }
            }
        }
        
        return $conflicts;
    }
    
    // Get all skills
    public function getAllSkills() {
        global $conn;
        
        $sql = "SELECT * FROM skills ORDER BY skillName";
        $result = $conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // Get all coordinators
    public function getAllCoordinators() {
        global $conn;
        
        $sql = "SELECT userId, name, email FROM users WHERE role = 'Coordinator' ORDER BY name";
        $result = $conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // Get events by coordinator
    public function getEventsByCoordinator($coordinatorId) {
        global $conn;
        
        $sql = "SELECT e.*, s.skillName
                FROM events e
                JOIN event_coordinators ec ON e.eventId = ec.eventId
                LEFT JOIN skills s ON e.requiredSkillId = s.skillId
                WHERE ec.coordinatorId = ?
                ORDER BY e.startDate DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $coordinatorId);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    // Get event categories
    public function getCategories() {
        global $conn;
        
        $sql = "SELECT DISTINCT category FROM events WHERE category IS NOT NULL AND category != '' ORDER BY category";
        $result = $conn->query($sql);
        
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        
        // If no categories exist in database, return some defaults
        if (empty($categories)) {
            $defaultCategories = [
                ['category' => 'Charity'],
                ['category' => 'Education'],
                ['category' => 'Environment'],
                ['category' => 'Health'],
                ['category' => 'Community'],
                ['category' => 'Sports'],
                ['category' => 'Arts & Culture'],
                ['category' => 'Disaster Relief'],
                ['category' => 'Animal Welfare'],
                ['category' => 'Technology']
            ];
            return $defaultCategories;
        }
        
        return $categories;
    }
    
    // Insert sample categories into existing events
    public function addCategoriesToExistingEvents() {
        global $conn;
        
        $categories = ['Charity', 'Education', 'Environment', 'Health', 'Community'];
        
        // Update events that don't have categories
        $sql = "UPDATE events SET category = ? WHERE (category IS NULL OR category = '') LIMIT 1";
        $stmt = $conn->prepare($sql);
        
        foreach ($categories as $category) {
            $stmt->bind_param("s", $category);
            $stmt->execute();
        }
    }




    // Get only upcoming events for public index page
public function getUpcomingEvents($filters = []) {
    global $conn;
    
    $sql = "SELECT e.*, s.skillName, 
            GROUP_CONCAT(u.name SEPARATOR ', ') as coordinators,
            GROUP_CONCAT(ec.coordinatorId) as coordinatorIds
            FROM events e
            LEFT JOIN skills s ON e.requiredSkillId = s.skillId
            LEFT JOIN event_coordinators ec ON e.eventId = ec.eventId
            LEFT JOIN users u ON ec.coordinatorId = u.userId
            WHERE (e.endDate > CURDATE() OR (e.endDate = CURDATE() AND e.endTime > CURTIME()))";
    
    $params = [];
    $types = "";
    
    // Search filter
    if (!empty($filters['search'])) {
        $sql .= " AND (e.eventName LIKE ? OR e.location LIKE ? OR e.category LIKE ?)";
        $params[] = "%{$filters['search']}%";
        $params[] = "%{$filters['search']}%";
        $params[] = "%{$filters['search']}%";
        $types .= "sss";
    }
    
    // Skill filter
    if (!empty($filters['skillId'])) {
        $sql .= " AND e.requiredSkillId = ?";
        $params[] = $filters['skillId'];
        $types .= "i";
    }
    
    // Category filter
    if (!empty($filters['category'])) {
        $sql .= " AND e.category = ?";
        $params[] = $filters['category'];
        $types .= "s";
    }
    
    // Location filter
    if (!empty($filters['location'])) {
        $sql .= " AND e.location LIKE ?";
        $params[] = "%{$filters['location']}%";
        $types .= "s";
    }
    
    // Date filter
    if (!empty($filters['date'])) {
        $sql .= " AND ? BETWEEN e.startDate AND e.endDate";
        $params[] = $filters['date'];
        $types .= "s";
    }
    
    $sql .= " GROUP BY e.eventId ORDER BY e.startDate ASC";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}


    
    // Get user role
    public function getUserRole($userId) {
        global $conn;
        
        $sql = "SELECT role FROM users WHERE userId = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        
        $result = $stmt->get_result()->fetch_assoc();
        return $result ? $result['role'] : null;
    }
    
    // Check if user can edit event
    public function canUserEditEvent($eventId, $userId) {
        $event = $this->getEventById($eventId);
        if (!$event) return false;
        
        $userRole = $this->getUserRole($userId);
        return $event['createdBy'] == $userId || $userRole == 'Admin';
    }
    
    // Get user by ID
    public function getUserById($userId) {
        global $conn;
        
        $sql = "SELECT name, email FROM users WHERE userId = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    // Get all events for export (with more details)
    public function getAllEventsForExport($filters = []) {
        global $conn;
        
        $sql = "SELECT 
                    e.eventId,
                    e.eventName,
                    e.eventDescription as description,
                    e.category,
                    e.location,
                    e.googleMapLink,
                    e.startDate,
                    e.endDate,
                    e.startTime,
                    e.endTime,
                    e.maxVolunteers,
                    s.skillName as requiredSkill,
                    e.eventImage,
                    e.createdAt,
                    u.name as createdByName,
                    u.email as createdByEmail,
                    GROUP_CONCAT(DISTINCT uc.name SEPARATOR ', ') as assignedCoordinators,
                    GROUP_CONCAT(DISTINCT uc.email SEPARATOR ', ') as coordinatorEmails,
                    CASE 
                        WHEN e.endDate < CURDATE() THEN 'Completed'
                        WHEN e.endDate = CURDATE() AND e.endTime < CURTIME() THEN 'Completed'
                        ELSE 'Upcoming'
                    END as status
                FROM events e
                LEFT JOIN skills s ON e.requiredSkillId = s.skillId
                LEFT JOIN users u ON e.createdBy = u.userId
                LEFT JOIN event_coordinators ec ON e.eventId = ec.eventId
                LEFT JOIN users uc ON ec.coordinatorId = uc.userId
                WHERE 1=1";
        
        $params = [];
        $types = "";
        
        // Add filters if provided
        if (!empty($filters['startDate'])) {
            $sql .= " AND e.startDate >= ?";
            $params[] = $filters['startDate'];
            $types .= "s";
        }
        
        if (!empty($filters['endDate'])) {
            $sql .= " AND e.endDate <= ?";
            $params[] = $filters['endDate'];
            $types .= "s";
        }
        
        if (!empty($filters['category'])) {
            $sql .= " AND e.category = ?";
            $params[] = $filters['category'];
            $types .= "s";
        }
        
        $sql .= " GROUP BY e.eventId ORDER BY e.startDate DESC, e.startTime DESC";
        
        $stmt = $conn->prepare($sql);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    // Additional export functions
    public function getAllUsersForExport() {
        global $conn;
        
        $sql = "SELECT 
                    userId,
                    name,
                    email,
                    telephoneNo,
                    location,
                    gender,
                    role
                FROM users
                ORDER BY role, name";
        
        $result = $conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getAllCoordinatorsForExport() {
        global $conn;
        
        $sql = "SELECT 
                    userId,
                    name,
                    email,
                    telephoneNo,
                    location,
                    gender,
                    (SELECT COUNT(*) FROM event_coordinators WHERE coordinatorId = users.userId) as eventsAssigned
                FROM users
                WHERE role = 'Coordinator'
                ORDER BY name";
        
        $result = $conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getAllVolunteersForExport() {
        global $conn;
        
        $sql = "SELECT 
                    userId,
                    name,
                    email,
                    telephoneNo,
                    location,
                    gender
                FROM users
                WHERE role = 'Volunteer'
                ORDER BY name";
        
        $result = $conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}




?>