<?php

require_once 'db.php';

class CalendarData {
    
    // Get all events for admin 
    public function getAllEvents() {
        global $conn;
        
        $sql = "SELECT e.*, 
                s.skillName,
                organizer.name AS organizerName,
                organizer.userId AS organizerId,
                GROUP_CONCAT(DISTINCT u.name SEPARATOR ', ') AS coordinators,
                GROUP_CONCAT(DISTINCT u.userId) AS coordinatorIds,
                es.joinedCount,
                (e.maxVolunteers - COALESCE(es.joinedCount, 0)) AS availableSlots,
                (SELECT COUNT(*) FROM event_registrations er2 
                 WHERE er2.eventId = e.eventId AND er2.status = 'registered') as total_registrations
                FROM events e
                LEFT JOIN skills s ON e.requiredSkillId = s.skillId
                LEFT JOIN users organizer ON e.createdBy = organizer.userId
                LEFT JOIN event_stats es ON e.eventId = es.eventId
                LEFT JOIN event_coordinators ec ON e.eventId = ec.eventId
                LEFT JOIN users u ON ec.coordinatorId = u.userId
                GROUP BY e.eventId
                ORDER BY e.startDate ASC";
        
        $result = $conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // Get coordinator's assigned events
    public function getCoordinatorEvents($coordinatorId) {
        global $conn;
        
        $sql = "SELECT e.*, 
                s.skillName,
                organizer.name AS organizerName,
                organizer.userId AS organizerId,
                GROUP_CONCAT(DISTINCT u.name SEPARATOR ', ') AS coordinators,
                GROUP_CONCAT(DISTINCT u.userId) AS coordinatorIds,
                es.joinedCount,
                (e.maxVolunteers - COALESCE(es.joinedCount, 0)) AS availableSlots
                FROM events e
                LEFT JOIN skills s ON e.requiredSkillId = s.skillId
                LEFT JOIN users organizer ON e.createdBy = organizer.userId
                LEFT JOIN event_stats es ON e.eventId = es.eventId
                LEFT JOIN event_coordinators ec ON e.eventId = ec.eventId
                LEFT JOIN users u ON ec.coordinatorId = u.userId
                WHERE e.status = 'Active'
                AND ec.coordinatorId = ?
                GROUP BY e.eventId
                ORDER BY e.startDate ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $coordinatorId);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    // Get organizer's created events
    public function getOrganizerEvents($organizerId) {
        global $conn;
        
        $sql = "SELECT e.*, 
                s.skillName,
                organizer.name AS organizerName,
                organizer.userId AS organizerId,
                GROUP_CONCAT(DISTINCT u.name SEPARATOR ', ') AS coordinators,
                GROUP_CONCAT(DISTINCT u.userId) AS coordinatorIds,
                es.joinedCount,
                (e.maxVolunteers - COALESCE(es.joinedCount, 0)) AS availableSlots
                FROM events e
                LEFT JOIN skills s ON e.requiredSkillId = s.skillId
                LEFT JOIN users organizer ON e.createdBy = organizer.userId
                LEFT JOIN event_stats es ON e.eventId = es.eventId
                LEFT JOIN event_coordinators ec ON e.eventId = ec.eventId
                LEFT JOIN users u ON ec.coordinatorId = u.userId
                WHERE e.status = 'Active'
                AND e.createdBy = ?
                GROUP BY e.eventId
                ORDER BY e.startDate ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $organizerId);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    // Get volunteer's joined events
    public function getVolunteerJoinedEvents($volunteerId) {
        global $conn;
        
        $sql = "SELECT e.*, 
                s.skillName,
                organizer.name AS organizerName,
                organizer.userId AS organizerId,
                er.registrationId,
                er.status AS registrationStatus,
                GROUP_CONCAT(DISTINCT u.name SEPARATOR ', ') AS coordinators,
                GROUP_CONCAT(DISTINCT u.userId) AS coordinatorIds,
                es.joinedCount,
                (e.maxVolunteers - COALESCE(es.joinedCount, 0)) AS availableSlots
                FROM events e
                LEFT JOIN skills s ON e.requiredSkillId = s.skillId
                LEFT JOIN users organizer ON e.createdBy = organizer.userId
                LEFT JOIN event_stats es ON e.eventId = es.eventId
                LEFT JOIN event_coordinators ec ON e.eventId = ec.eventId
                LEFT JOIN users u ON ec.coordinatorId = u.userId
                INNER JOIN event_registrations er ON e.eventId = er.eventId
                WHERE e.status = 'Active'
                AND er.userId = ?
                AND er.status = 'registered'
                GROUP BY e.eventId
                ORDER BY e.startDate ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $volunteerId);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    // Get event status (over/cancelled/active)
    public function getEventStatus($event) {
        date_default_timezone_set('Asia/Colombo');
        
        // Check if cancelled
        if (isset($event['status']) && $event['status'] === 'Cancelled') {
            return 'cancelled';
        }
        
        
        if (!isset($event['endDate']) || empty($event['endDate'])) {
            return 'active';
        }
        
        $endTime = !empty($event['endTime']) ? $event['endTime'] : '23:59:59';
        if (strlen($endTime) <= 5) {
            $endTime .= ':00';
        }
        
        $eventDateTime = $event['endDate'] . ' ' . $endTime;
        
        // Check if event is over
        if (strtotime($eventDateTime) < time()) {
            return 'over';
        }
        
        return 'active';
    }
    
    // Get event statistics for admin dashboard
    public function getAdminStatistics() {
        global $conn;
        
        $sql = "SELECT 
                COUNT(*) as total_events,
                COUNT(CASE WHEN status = 'Active' THEN 1 END) as active_events,
                COUNT(CASE WHEN status = 'Cancelled' THEN 1 END) as cancelled_events,
                COUNT(CASE WHEN startDate > CURDATE() THEN 1 END) as upcoming_events,
                COUNT(CASE WHEN endDate < CURDATE() THEN 1 END) as past_events
                FROM events";
        
        $result = $conn->query($sql);
        return $result->fetch_assoc();
    }
    
    // Get user statistics for admin
    public function getUserStatistics() {
        global $conn;
        
        $sql = "SELECT 
                COUNT(*) as total_users,
                COUNT(CASE WHEN role = 'Volunteer' THEN 1 END) as volunteers,
                COUNT(CASE WHEN role = 'Coordinator' THEN 1 END) as coordinators,
                COUNT(CASE WHEN role = 'Organizer' THEN 1 END) as organizers,
                COUNT(CASE WHEN role = 'Admin' THEN 1 END) as admins
                FROM users";
        
        $result = $conn->query($sql);
        return $result->fetch_assoc();
    }
    
    // Get recent activities for admin
    public function getAdminRecentActivities($limit = 10) {
        global $conn;
        
        $sql = "SELECT 
                'event_created' as type,
                eventName as title,
                'Event created: ' as description,
                createdBy as userId,
                createdAt as date,
                NULL as targetId
                FROM events
                
                UNION ALL
                
                SELECT 
                'registration' as type,
                '' as title,
                'Registered for event' as description,
                userId,
                registrationDate as date,
                eventId as targetId
                FROM event_registrations
                WHERE status = 'registered'
                
                UNION ALL
                
                SELECT 
                'user_registered' as type,
                name as title,
                'New user registered' as description,
                userId,
                createdAt as date,
                NULL as targetId
                FROM users
                
                ORDER BY date DESC
                LIMIT ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>