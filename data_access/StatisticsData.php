<?php
// data_access/StatisticsData.php
require_once 'db.php';

class StatisticsData {
    
    // Get total volunteers count
    public function getTotalVolunteers() {
        global $conn;
        
        $sql = "SELECT COUNT(*) as total FROM users WHERE role = 'Volunteer'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        
        return $row['total'];
    }
    
    // Get active volunteers (those who have attended at least one event)
    public function getActiveVolunteers() {
        global $conn;
        
        $sql = "SELECT COUNT(DISTINCT userId) as total 
                FROM attendance 
                WHERE status = 'Present'";
        
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        
        return $row['total'];
    }
    
    // Get completed events count
    public function getCompletedEvents() {
        global $conn;
        
        // Events that have passed (endDate < today)
        $sql = "SELECT COUNT(*) as total 
                FROM events 
                WHERE status = 'Active' AND endDate < CURDATE()";
        
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        
        return $row['total'];
    }
    
    // Get upcoming events count
    public function getUpcomingEvents() {
        global $conn;
        
        $sql = "SELECT COUNT(*) as total 
                FROM events 
                WHERE status = 'Active' AND startDate >= CURDATE()";
        
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        
        return $row['total'];
    }
    
    // Get total events count
    public function getTotalEvents() {
        global $conn;
        
        $sql = "SELECT COUNT(*) as total FROM events WHERE status = 'Active'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        
        return $row['total'];
    }
    
    // Get unique locations/cities served
    public function getUniqueLocations() {
        global $conn;
        
        $sql = "SELECT COUNT(DISTINCT location) as total FROM events WHERE status = 'Active'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        
        return $row['total'];
    }
    
    // Get total volunteer hours (from attendance * event duration)
    public function getTotalVolunteerHours() {
        global $conn;
        
        $sql = "SELECT SUM(TIMESTAMPDIFF(HOUR, e.startTime, e.endTime)) as total_hours
                FROM attendance a
                JOIN events e ON a.eventId = e.eventId
                WHERE a.status = 'Present'";
        
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        
        return round($row['total_hours'] ?? 0);
    }
    
    // Get events by category
    public function getEventsByCategory() {
        global $conn;
        
        $sql = "SELECT category, COUNT(*) as count 
                FROM events 
                WHERE status = 'Active'
                GROUP BY category
                ORDER BY count DESC";
        
        $result = $conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>