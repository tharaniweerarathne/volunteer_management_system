<?php

require_once 'db.php';

class EventRegistrationData {
    
    // Check if volunteer already joined

public function isAlreadyJoined($eventId, $userId) {
    global $conn;
    
    $sql = "SELECT registrationId FROM event_registrations 
            WHERE eventId = ? AND userId = ? AND status = 'registered'";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $eventId, $userId);
    $stmt->execute();
    
    return $stmt->get_result()->num_rows > 0;
}
    
    
    public function checkTimeConflict($userId, $newEventId) {
        global $conn;
        
        // Get new event details
        $sql = "SELECT startDate, endDate, startTime, endTime, eventName 
                FROM events WHERE eventId = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $newEventId);
        $stmt->execute();
        $newEvent = $stmt->get_result()->fetch_assoc();
        
        if (!$newEvent) return [];
        
        $newStart = $newEvent['startDate'] . ' ' . $newEvent['startTime'];
        $newEnd = $newEvent['endDate'] . ' ' . $newEvent['endTime'];
        
        // Get all registered events for the user
        $sql = "SELECT e.*, er.registrationId
                FROM event_registrations er
                JOIN events e ON er.eventId = e.eventId
                WHERE er.userId = ? AND er.status = 'registered' 
                AND er.eventId != ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $userId, $newEventId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $conflicts = [];
        
        while ($existingEvent = $result->fetch_assoc()) {
            $existingStart = $existingEvent['startDate'] . ' ' . $existingEvent['startTime'];
            $existingEnd = $existingEvent['endDate'] . ' ' . $existingEvent['endTime'];
            
            // Check for time overlap
            if (strtotime($newStart) < strtotime($existingEnd) && 
                strtotime($newEnd) > strtotime($existingStart)) {
                
                $conflicts[] = [
                    'eventName' => $existingEvent['eventName'],
                    'startDate' => $existingEvent['startDate'],
                    'endDate' => $existingEvent['endDate'],
                    'startTime' => $existingEvent['startTime'],
                    'endTime' => $existingEvent['endTime']
                ];
            }
        }
        
        return $conflicts;
    }
    


public function insertRegistration($eventId, $userId, $isRejoining = false) {
    global $conn;
    
    // Check if user has a CANCELLED registration for this event
    $checkSql = "SELECT registrationId FROM event_registrations 
                 WHERE eventId = ? AND userId = ? AND status = 'cancelled'";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("ii", $eventId, $userId);
    $checkStmt->execute();
    $cancelledRegistration = $checkStmt->get_result()->fetch_assoc();
    
    $isRejoin = false;
    $registrationId = null;
    
    if ($cancelledRegistration) {
        // RE-activate cancelled registration
        $sql = "UPDATE event_registrations 
                SET status = 'registered', 
                    registrationDate = CURRENT_TIMESTAMP,
                    cancellationReason = NULL
                WHERE registrationId = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $cancelledRegistration['registrationId']);
        
        if ($stmt->execute()) {
           
            $this->incrementJoinedCount($eventId);
            $isRejoin = true;
            $registrationId = $cancelledRegistration['registrationId'];
            return ['success' => true, 'isRejoin' => true, 'registrationId' => $registrationId];
        }
        return ['success' => false, 'isRejoin' => false];
    } else {
        // Create new registration
        $sql = "INSERT INTO event_registrations (eventId, userId, status) 
                VALUES (?, ?, 'registered')";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $eventId, $userId);
        
        if ($stmt->execute()) {
            $this->incrementJoinedCount($eventId);
            $registrationId = $conn->insert_id;
            return ['success' => true, 'isRejoin' => false, 'registrationId' => $registrationId];
        }
        return ['success' => false, 'isRejoin' => false];
    }
}
    
    
    public function incrementJoinedCount($eventId) {
        global $conn;
        
        // Check if record exists
        $checkSql = "SELECT eventId FROM event_stats WHERE eventId = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("i", $eventId);
        $checkStmt->execute();
        
        if ($checkStmt->get_result()->num_rows > 0) {
            $sql = "UPDATE event_stats SET joinedCount = joinedCount + 1 WHERE eventId = ?";
        } else {
            $sql = "INSERT INTO event_stats (eventId, joinedCount) VALUES (?, 1)";
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $eventId);
        
        return $stmt->execute();
    }
    
   
public function getVolunteerEvents($userId) {
    global $conn;

    $sql = "SELECT e.*, er.registrationId, er.registrationDate, er.status,
                   es.joinedCount, s.skillName,
                   organizer.name AS organizerName,
                   GROUP_CONCAT(DISTINCT u.name SEPARATOR ', ') AS coordinators
            FROM event_registrations er
            JOIN events e ON er.eventId = e.eventId
            LEFT JOIN event_stats es ON e.eventId = es.eventId
            LEFT JOIN skills s ON e.requiredSkillId = s.skillId
            LEFT JOIN users organizer ON e.createdBy = organizer.userId
            LEFT JOIN event_coordinators ec ON e.eventId = ec.eventId
            LEFT JOIN users u ON ec.coordinatorId = u.userId
            WHERE er.userId = ?
            GROUP BY e.eventId
            ORDER BY e.startDate DESC, er.registrationDate DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

    
    // Cancel registration 
    public function cancelRegistration($registrationId, $reason = null) {
        global $conn;
        
        
        $getSql = "SELECT eventId FROM event_registrations WHERE registrationId = ?";
        $getStmt = $conn->prepare($getSql);
        $getStmt->bind_param("i", $registrationId);
        $getStmt->execute();
        $result = $getStmt->get_result()->fetch_assoc();
        
        if (!$result) return false;
        
        $eventId = $result['eventId'];
        
        
        $sql = "UPDATE event_registrations 
                SET status = 'cancelled', cancellationReason = ?
                WHERE registrationId = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $reason, $registrationId);
        $success = $stmt->execute();
        
        if ($success) {
            
            $this->decrementJoinedCount($eventId);
        }
        
        return $success;
    }
    
    
    private function decrementJoinedCount($eventId) {
        global $conn;
        
        $sql = "UPDATE event_stats SET joinedCount = GREATEST(0, joinedCount - 1) 
                WHERE eventId = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $eventId);
        
        return $stmt->execute();
    }
    
    // Get event details with joined count
public function getEventDetails($eventId) {
    global $conn;

    $sql = "SELECT e.*, s.skillName, 
                   GROUP_CONCAT(u.name SEPARATOR ', ') AS coordinators,
                   es.joinedCount,
                   (e.maxVolunteers - COALESCE(es.joinedCount, 0)) AS availableSlots,
                   org.name AS organizerName,
                   org.userId AS organizerId
            FROM events e
            LEFT JOIN skills s ON e.requiredSkillId = s.skillId
            LEFT JOIN event_coordinators ec ON e.eventId = ec.eventId
            LEFT JOIN users u ON ec.coordinatorId = u.userId
            LEFT JOIN users org ON e.createdBy = org.userId
            LEFT JOIN event_stats es ON e.eventId = es.eventId
            WHERE e.eventId = ?
            GROUP BY e.eventId";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $eventId);
    $stmt->execute();

    return $stmt->get_result()->fetch_assoc();
}

    
    // Get registration by ID
    public function getRegistrationById($registrationId) {
        global $conn;
        
        $sql = "SELECT er.*, e.eventName, e.startDate, e.endDate, 
                       e.startTime, e.endTime, e.location
                FROM event_registrations er
                JOIN events e ON er.eventId = e.eventId
                WHERE er.registrationId = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $registrationId);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_assoc();
    }


    // Edit registration 
public function updateRegistration($registrationId, $newEventId, $userId) {
    global $conn;
    
    
    $oldEventSql = "SELECT eventId FROM event_registrations WHERE registrationId = ?";
    $oldEventStmt = $conn->prepare($oldEventSql);
    $oldEventStmt->bind_param("i", $registrationId);
    $oldEventStmt->execute();
    $oldEventResult = $oldEventStmt->get_result()->fetch_assoc();
    
    if (!$oldEventResult) return false;
    
    $oldEventId = $oldEventResult['eventId'];
    
    
    $conn->begin_transaction();
    
    try {
        
        $cancelSql = "UPDATE event_registrations 
                     SET status = 'cancelled', 
                         cancellationReason = 'Changed to another event'
                     WHERE registrationId = ?";
        $cancelStmt = $conn->prepare($cancelSql);
        $cancelStmt->bind_param("i", $registrationId);
        
        if (!$cancelStmt->execute()) {
            throw new Exception("Failed to cancel old registration");
        }
        
        
        if (!$this->decrementJoinedCount($oldEventId)) {
            throw new Exception("Failed to decrement old event count");
        }
        
       
        $newSql = "INSERT INTO event_registrations (eventId, userId, status) 
                  VALUES (?, ?, 'registered')";
        $newStmt = $conn->prepare($newSql);
        $newStmt->bind_param("ii", $newEventId, $userId);
        
        if (!$newStmt->execute()) {
            throw new Exception("Failed to create new registration");
        }
        
      
        if (!$this->incrementJoinedCount($newEventId)) {
            throw new Exception("Failed to increment new event count");
        }
        
        $conn->commit();
        return ['success' => true, 'newRegistrationId' => $conn->insert_id];
        
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Update registration failed: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Get available events for editing 
public function getAvailableEventsForEdit($userId, $currentEventId) {
    global $conn;
    
    $sql = "SELECT e.*, 
            (e.maxVolunteers - COALESCE(es.joinedCount, 0)) as availableSlots
            FROM events e
            LEFT JOIN event_stats es ON e.eventId = es.eventId
            WHERE e.eventId != ?
            AND (e.endDate > CURDATE() OR (e.endDate = CURDATE() AND e.endTime > CURTIME()))
            AND (e.maxVolunteers = 0 OR COALESCE(es.joinedCount, 0) < e.maxVolunteers)
            AND e.eventId NOT IN (
                SELECT eventId FROM event_registrations 
                WHERE userId = ? AND status = 'registered'
            )
            ORDER BY e.startDate ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $currentEventId, $userId);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
}
?>