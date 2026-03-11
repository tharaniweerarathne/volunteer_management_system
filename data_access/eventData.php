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
                GROUP_CONCAT(DISTINCT u.name SEPARATOR ', ') as coordinators,
                GROUP_CONCAT(DISTINCT ec.coordinatorId) as coordinatorIds,
                organizer.name as organizerName,
                organizer.userId as organizerId
                FROM events e
                LEFT JOIN skills s ON e.requiredSkillId = s.skillId
                LEFT JOIN event_coordinators ec ON e.eventId = ec.eventId
                LEFT JOIN users u ON ec.coordinatorId = u.userId
                LEFT JOIN users organizer ON e.createdBy = organizer.userId
                WHERE 1=1";
        
        $params = [];
        $types = "";
        
        // search filter
        if (!empty($filters['search'])) {
            $sql .= " AND (e.eventName LIKE ? OR e.location LIKE ? OR e.category LIKE ? OR e.eventDescription LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "ssss";
        }
        
        // search using skill 
        if (!empty($filters['skillId'])) {
            $sql .= " AND e.requiredSkillId = ?";
            $params[] = $filters['skillId'];
            $types .= "i";
        }
        
        // search using category 
        if (!empty($filters['category'])) {
            $sql .= " AND e.category = ?";
            $params[] = $filters['category'];
            $types .= "s";
        }
        
        // search using location 
        if (!empty($filters['location'])) {
            $sql .= " AND e.location LIKE ?";
            $params[] = "%{$filters['location']}%";
            $types .= "s";
        }
        
        // search using date 
        if (!empty($filters['date'])) {
            $sql .= " AND ? BETWEEN e.startDate AND e.endDate";
            $params[] = $filters['date'];
            $types .= "s";
        }
        
        // Filter by organizer if provided
        if (!empty($filters['organizerId'])) {
            $sql .= " AND e.createdBy = ?";
            $params[] = $filters['organizerId'];
            $types .= "i";
        }
        
        $sql .= " GROUP BY e.eventId ORDER BY e.startDate DESC";
        
        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    // create new event
    public function createEvent($data) {
        global $conn;
        
        $sql = "INSERT INTO events (eventName, eventDescription, category, location, 
                googleMapLink, startDate, endDate, startTime, endTime, maxVolunteers, 
                requiredSkillId, eventImage, createdBy, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active')";
        
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
    
    // get event by ID
    public function getEventById($eventId) {
        global $conn;
        
        $sql = "SELECT e.*, s.skillName, 
                GROUP_CONCAT(ec.coordinatorId) as coordinatorIds,
                organizer.name as organizerName,
                organizer.userId as organizerId,
                organizer.email as organizerEmail
                FROM events e
                LEFT JOIN skills s ON e.requiredSkillId = s.skillId
                LEFT JOIN event_coordinators ec ON e.eventId = ec.eventId
                LEFT JOIN users organizer ON e.createdBy = organizer.userId
                WHERE e.eventId = ?
                GROUP BY e.eventId";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_assoc();
    }
    
    // update event
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
    
    // cancel event 
    public function cancelEvent($eventId) {
        global $conn;
        
        $sql = "UPDATE events SET status = 'Cancelled' WHERE eventId = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $eventId);
        
        return $stmt->execute();
    }
    
    // delete event 
    public function deleteEvent($eventId) {
        global $conn;
        
        $conn->autocommit(FALSE);
        
        try {
            // delete related records first
            $sql1 = "DELETE FROM event_coordinators WHERE eventId = ?";
            $stmt1 = $conn->prepare($sql1);
            $stmt1->bind_param("i", $eventId);
            
            if (!$stmt1->execute()) {
                throw new Exception("Failed to delete event coordinators");
            }
            
            // delete event registrations
            $sql2 = "DELETE FROM event_registrations WHERE eventId = ?";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bind_param("i", $eventId);
            $stmt2->execute();
            
            // delete the event
            $sql3 = "DELETE FROM events WHERE eventId = ?";
            $stmt3 = $conn->prepare($sql3);
            $stmt3->bind_param("i", $eventId);
            
            if (!$stmt3->execute()) {
                throw new Exception("Failed to delete event");
            }
            
            $conn->commit();
            return true;
            
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Delete event failed: " . $e->getMessage());
            return false;
        } finally {
            $conn->autocommit(TRUE);
        }
    }
    
    // assign coordinators to event
    public function assignCoordinators($eventId, $coordinatorIds) {
        global $conn;
        
        // check for scheduling conflicts
        $conflicts = $this->checkSchedulingConflicts($eventId, $coordinatorIds);
        if (!empty($conflicts)) {
            return ['success' => false, 'conflicts' => $conflicts];
        }
        
        // remove existing assignments
        $sql = "DELETE FROM event_coordinators WHERE eventId = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        
        // add new assignments
        $sql = "INSERT INTO event_coordinators (eventId, coordinatorId) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        
        foreach ($coordinatorIds as $coordinatorId) {
            $stmt->bind_param("ii", $eventId, $coordinatorId);
            if (!$stmt->execute()) {
                return ['success' => false, 'message' => 'Failed to assign coordinators'];
            }
        }
        
        return ['success' => true, 'coordinatorIds' => $coordinatorIds];
    }
    
    // check scheduling conflicts
    public function checkSchedulingConflicts($eventId, $coordinatorIds) {
        global $conn;
        $conflicts = [];
        
        $event = $this->getEventById($eventId);
        if (!$event) return $conflicts;
        
        $newEventStart = new DateTime($event['startDate'] . ' ' . $event['startTime']);
        $newEventEnd = new DateTime($event['endDate'] . ' ' . $event['endTime']);
        
        $sql = "SELECT e.eventId, e.eventName, e.startDate, e.endDate, 
                       e.startTime, e.endTime, u.name as coordinatorName, u.userId
                FROM event_coordinators ec
                JOIN events e ON ec.eventId = e.eventId
                JOIN users u ON ec.coordinatorId = u.userId
                WHERE ec.coordinatorId = ? 
                AND ec.eventId != ?
                AND e.status = 'Active'";
        
        $stmt = $conn->prepare($sql);
        
        foreach ($coordinatorIds as $coordinatorId) {
            $stmt->bind_param("ii", $coordinatorId, $eventId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($existingEvent = $result->fetch_assoc()) {
                $existingStart = new DateTime($existingEvent['startDate'] . ' ' . $existingEvent['startTime']);
                $existingEnd = new DateTime($existingEvent['endDate'] . ' ' . $existingEvent['endTime']);
                
                if ($newEventStart < $existingEnd && $newEventEnd > $existingStart) {
                    $conflicts[] = [
                        'coordinatorId' => $coordinatorId,
                        'coordinatorName' => $existingEvent['coordinatorName'],
                        'eventName' => $existingEvent['eventName'],
                        'existingEvent' => $existingEvent,
                        'conflictType' => 'Scheduling conflict'
                    ];
                }
            }
        }
        
        return $conflicts;
    }
    
    
    public function getAllSkills() {
        global $conn;
        
        $sql = "SELECT * FROM skills ORDER BY skillName";
        $result = $conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    
    public function getAllCoordinators() {
        global $conn;
        
        $sql = "SELECT userId, name, email FROM users WHERE role = 'Coordinator' ORDER BY name";
        $result = $conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    
    public function getAllOrganizers() {
        global $conn;
        
        $sql = "SELECT userId, name, email FROM users WHERE role = 'Organizer' ORDER BY name";
        $result = $conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    
    public function getEventsByCoordinator($coordinatorId) {
        global $conn;
        
        $oneDayAgo = date('Y-m-d H:i:s', strtotime('-1 day'));
        
        $sql = "SELECT e.*, s.skillName, organizer.name as organizerName
                FROM events e
                JOIN event_coordinators ec ON e.eventId = ec.eventId
                LEFT JOIN skills s ON e.requiredSkillId = s.skillId
                LEFT JOIN users organizer ON e.createdBy = organizer.userId
                WHERE ec.coordinatorId = ?
                AND CONCAT(e.endDate, ' ', e.endTime) >= ?
                AND e.status = 'Active'
                ORDER BY e.startDate ASC, e.eventId ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $coordinatorId, $oneDayAgo);
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
    
    
    public function getUpcomingEvents($filters = []) {
        global $conn;
        
        $sql = "SELECT e.*, s.skillName, 
                GROUP_CONCAT(u.name SEPARATOR ', ') as coordinators,
                GROUP_CONCAT(ec.coordinatorId) as coordinatorIds,
                organizer.name as organizerName
                FROM events e
                LEFT JOIN skills s ON e.requiredSkillId = s.skillId
                LEFT JOIN event_coordinators ec ON e.eventId = ec.eventId
                LEFT JOIN users u ON ec.coordinatorId = u.userId
                LEFT JOIN users organizer ON e.createdBy = organizer.userId
                WHERE (e.endDate > CURDATE() OR (e.endDate = CURDATE() AND e.endTime > CURTIME()))
                AND e.status = 'Active'";
        
        $params = [];
        $types = "";
        
        if (!empty($filters['search'])) {
            $sql .= " AND (e.eventName LIKE ? OR e.location LIKE ? OR e.category LIKE ?)";
            $params[] = "%{$filters['search']}%";
            $params[] = "%{$filters['search']}%";
            $params[] = "%{$filters['search']}%";
            $types .= "sss";
        }
        
        if (!empty($filters['skillId'])) {
            $sql .= " AND e.requiredSkillId = ?";
            $params[] = $filters['skillId'];
            $types .= "i";
        }
        
        if (!empty($filters['category'])) {
            $sql .= " AND e.category = ?";
            $params[] = $filters['category'];
            $types .= "s";
        }
        
        if (!empty($filters['location'])) {
            $sql .= " AND e.location LIKE ?";
            $params[] = "%{$filters['location']}%";
            $types .= "s";
        }
        
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
    
    
    public function getUserRole($userId) {
        global $conn;
        
        $sql = "SELECT role FROM users WHERE userId = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        
        $result = $stmt->get_result()->fetch_assoc();
        return $result ? $result['role'] : null;
    }
    
   
    public function canUserEditEvent($eventId, $userId) {
        $event = $this->getEventById($eventId);
        if (!$event) return false;
        
        $userRole = $this->getUserRole($userId);
        
        return $userRole == 'Admin' || $event['createdBy'] == $userId;
    }
    
    // Check if user can cancel event
    public function canUserCancelEvent($eventId, $userId) {
        $event = $this->getEventById($eventId);
        if (!$event) return false;
        
        $userRole = $this->getUserRole($userId);
        
        return $userRole == 'Admin' || ($userRole == 'Organizer' && $event['createdBy'] == $userId);
    }
    
    
    public function getUserById($userId) {
        global $conn;
        
        $sql = "SELECT name, email, role FROM users WHERE userId = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    // Get all participants of an event 
    public function getEventParticipants($eventId) {
        global $conn;
        
        $sql = "SELECT DISTINCT u.userId, u.name, u.email, u.role
                FROM users u
                LEFT JOIN event_registrations er ON u.userId = er.userId
                LEFT JOIN event_coordinators ec ON u.userId = ec.coordinatorId
                WHERE (er.eventId = ? OR ec.eventId = ?)
                AND u.role IN ('Volunteer', 'Coordinator')";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $eventId, $eventId);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }



    // ai functions

    public function getEventAttendanceCount($eventId){
    global $conn;

    $sql = "SELECT COUNT(*) as total 
            FROM attendance 
            WHERE eventId = ? AND status = 'Present'";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $eventId);
    $stmt->execute();

    $result = $stmt->get_result()->fetch_assoc();

    return $result['total'] ?? 0;
}


    public function getAllUpcomingEventsRaw() {
        $sql = "SELECT e.*, s.skillName 
                FROM events e
                LEFT JOIN skills s ON e.requiredSkillId = s.skillId
                WHERE e.endDate >= CURDATE() 
                AND e.status = 'Active'
                ORDER BY e.startDate ASC";
        
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
}

?>