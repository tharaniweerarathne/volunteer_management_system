<?php
require_once 'db.php';

class EventVolunteerData {
    
    // Get event details
public function getEventDetails($eventId) {
    global $conn;
    
    $sql = "SELECT e.*, 
            GROUP_CONCAT(DISTINCT u.name SEPARATOR ', ') as coordinators,
            s.skillName as requiredSkill,
            o.name AS organizerName
            FROM events e
            LEFT JOIN event_coordinators ec ON e.eventId = ec.eventId
            LEFT JOIN users u ON ec.coordinatorId = u.userId
            LEFT JOIN skills s ON e.requiredSkillId = s.skillId
            LEFT JOIN users o ON e.createdBy = o.userId
            WHERE e.eventId = ?
            GROUP BY e.eventId";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $eventId);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_assoc();
}

    
    // Check if user is coordinator for this event
    public function isUserCoordinatorForEvent($eventId, $userId) {
        global $conn;
        
        $sql = "SELECT 1 FROM event_coordinators 
               WHERE eventId = ? AND coordinatorId = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $eventId, $userId);
        $stmt->execute();
        
        return $stmt->get_result()->num_rows > 0;
    }
    
    // Get volunteers for a specific event
    public function getVolunteersByEvent($eventId) {
        global $conn;
        
        $sql = "SELECT u.userId, u.name, u.email, u.telephoneNo, u.gender, u.location,
                er.registrationDate, er.status,
                GROUP_CONCAT(DISTINCT s.skillName SEPARATOR ', ') as skills
                FROM event_registrations er
                JOIN users u ON er.userId = u.userId
                LEFT JOIN volunteer_skills vs ON u.userId = vs.userId
                LEFT JOIN skills s ON vs.skillId = s.skillId
                WHERE er.eventId = ? AND er.status = 'registered'
                GROUP BY u.userId
                ORDER BY er.registrationDate DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    // Get event statistics
    public function getEventStatistics($eventId) {
        global $conn;
        
        $sql = "SELECT 
                COUNT(DISTINCT er.userId) as total_volunteers,
                COUNT(DISTINCT CASE WHEN u.gender = 'Male' THEN u.userId END) as male_count,
                COUNT(DISTINCT CASE WHEN u.gender = 'Female' THEN u.userId END) as female_count,
                COUNT(DISTINCT CASE WHEN u.gender = 'Other' THEN u.userId END) as other_count,
                COUNT(DISTINCT CASE WHEN vs.skillId IS NOT NULL THEN u.userId END) as skilled_count
                FROM event_registrations er
                JOIN users u ON er.userId = u.userId
                LEFT JOIN volunteer_skills vs ON u.userId = vs.userId
                WHERE er.eventId = ? AND er.status = 'registered'";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_assoc();
    }
    
    // Export volunteers to CSV
    public function exportVolunteersToCSV($eventId) {
        global $conn;
        
        $sql = "SELECT u.name, u.email, u.telephoneNo, u.gender, u.location,
                er.registrationDate,
                GROUP_CONCAT(DISTINCT s.skillName SEPARATOR ', ') as skills
                FROM event_registrations er
                JOIN users u ON er.userId = u.userId
                LEFT JOIN volunteer_skills vs ON u.userId = vs.userId
                LEFT JOIN skills s ON vs.skillId = s.skillId
                WHERE er.eventId = ? AND er.status = 'registered'
                GROUP BY u.userId
                ORDER BY er.registrationDate DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }


    
public function searchVolunteersByEvent($eventId, $searchParams = []) {
    global $conn;
    
    $sql = "SELECT u.userId, u.name, u.email, u.telephoneNo as phone, u.gender, u.location,
            er.registrationDate, er.status,
            GROUP_CONCAT(DISTINCT s.skillName SEPARATOR ', ') as skills
            FROM event_registrations er
            JOIN users u ON er.userId = u.userId
            LEFT JOIN volunteer_skills vs ON u.userId = vs.userId
            LEFT JOIN skills s ON vs.skillId = s.skillId
            WHERE er.eventId = ? AND er.status = 'registered'";
    
    $params = [$eventId];
    $types = "i";
    
    
    if (!empty($searchParams['search_name'])) {
        $sql .= " AND u.name LIKE ?";
        $params[] = '%' . $searchParams['search_name'] . '%';
        $types .= "s";
    }
    
    if (!empty($searchParams['search_email'])) {
        $sql .= " AND u.email LIKE ?";
        $params[] = '%' . $searchParams['search_email'] . '%';
        $types .= "s";
    }
    
    if (!empty($searchParams['search_phone'])) {
        $sql .= " AND u.telephoneNo LIKE ?";
        $params[] = '%' . $searchParams['search_phone'] . '%';
        $types .= "s";
    }
    
    if (!empty($searchParams['search_date_from']) && !empty($searchParams['search_date_to'])) {
        $sql .= " AND DATE(er.registrationDate) BETWEEN ? AND ?";
        $params[] = $searchParams['search_date_from'];
        $params[] = $searchParams['search_date_to'];
        $types .= "ss";
    } elseif (!empty($searchParams['search_date_from'])) {
        $sql .= " AND DATE(er.registrationDate) >= ?";
        $params[] = $searchParams['search_date_from'];
        $types .= "s";
    } elseif (!empty($searchParams['search_date_to'])) {
        $sql .= " AND DATE(er.registrationDate) <= ?";
        $params[] = $searchParams['search_date_to'];
        $types .= "s";
    }
    
   
    if (!empty($searchParams['search_specific_date'])) {
        $sql .= " AND DATE(er.registrationDate) = ?";
        $params[] = $searchParams['search_specific_date'];
        $types .= "s";
    }
    
    $sql .= " GROUP BY u.userId ORDER BY er.registrationDate DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
}
?>