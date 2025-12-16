<?php
require_once 'db.php';

class AttendanceData {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    // Get events assigned to coordinator for a specific date
    public function getCoordinatorEvents($coordinatorId, $date) {
        $sql = "SELECT DISTINCT e.* 
                FROM events e
                JOIN event_coordinators ec ON e.eventId = ec.eventId
                WHERE ec.coordinatorId = ?
                AND ? BETWEEN e.startDate AND e.endDate
                AND (e.endDate > CURDATE() OR (e.endDate = CURDATE() AND e.endTime > CURTIME()))
                ORDER BY e.startDate ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $coordinatorId, $date);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    // Get volunteers registered for an event (FIXED - Added table alias to status column)
// Get volunteers registered for an event with optional search
public function getEventVolunteers($eventId, $search = '') {
    $sql = "SELECT u.userId, u.name, u.email, u.telephoneNo,
                   IFNULL(a.status, 'Not Marked') as attendanceStatus
            FROM event_registrations er
            JOIN users u ON er.userId = u.userId
            LEFT JOIN attendance a ON er.userId = a.userId 
                AND er.eventId = a.eventId 
                AND a.attendanceDate = CURDATE()
            WHERE er.eventId = ? 
            AND er.status = 'registered'
            AND u.role = 'Volunteer'";
    
    // Add search condition if provided
    if (!empty($search)) {
        $sql .= " AND (LOWER(u.name) LIKE LOWER(?) OR 
                      LOWER(u.email) LIKE LOWER(?) OR 
                      u.telephoneNo LIKE ?)";
    }
    
    $sql .= " ORDER BY u.name";
    
    $stmt = $this->conn->prepare($sql);
    
    if (!empty($search)) {
        $searchTerm = "%{$search}%";
        $stmt->bind_param("isss", $eventId, $searchTerm, $searchTerm, $searchTerm);
    } else {
        $stmt->bind_param("i", $eventId);
    }
    
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
    
    // Mark attendance
    public function markAttendance($eventId, $userId, $date, $status, $coordinatorId, $remarks = '') {
        // Check if attendance already marked for today
        $checkSql = "SELECT attendanceId FROM attendance 
                    WHERE eventId = ? AND userId = ? AND attendanceDate = ?";
        $checkStmt = $this->conn->prepare($checkSql);
        $checkStmt->bind_param("iis", $eventId, $userId, $date);
        $checkStmt->execute();
        $existing = $checkStmt->get_result()->fetch_assoc();
        
        if ($existing) {
            // Update existing record
            $sql = "UPDATE attendance SET 
                    status = ?, markedBy = ?, remarks = ?
                    WHERE attendanceId = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sisi", $status, $coordinatorId, $remarks, $existing['attendanceId']);
        } else {
            // Insert new record
            $sql = "INSERT INTO attendance (eventId, userId, attendanceDate, status, markedBy, remarks) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("iissis", $eventId, $userId, $date, $status, $coordinatorId, $remarks);
        }
        
        return $stmt->execute();
    }
    
    // Bulk mark attendance
    public function bulkMarkAttendance($eventId, $date, $coordinatorId, $attendances) {
        $this->conn->begin_transaction();
        
        try {
            foreach ($attendances as $userId => $data) {
                $this->markAttendance(
                    $eventId, 
                    $userId, 
                    $date, 
                    $data['status'], 
                    $coordinatorId, 
                    $data['remarks'] ?? ''
                );
            }
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Bulk attendance failed: " . $e->getMessage());
            return false;
        }
    }
    
    // Check if coordinator is assigned to event
    public function isCoordinatorAssigned($coordinatorId, $eventId) {
        $sql = "SELECT id FROM event_coordinators 
                WHERE coordinatorId = ? AND eventId = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $coordinatorId, $eventId);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }
    
    // Mark absent for unmarked volunteers after event ends
    public function markAbsentForUnmarked($eventId, $date) {
        $sql = "INSERT INTO attendance (eventId, userId, attendanceDate, status, markedBy, remarks)
                SELECT er.eventId, er.userId, ?, 'Absent', 
                       (SELECT coordinatorId FROM event_coordinators WHERE eventId = er.eventId LIMIT 1),
                       'Auto-marked as absent'
                FROM event_registrations er
                LEFT JOIN attendance a ON er.eventId = a.eventId 
                    AND er.userId = a.userId 
                    AND a.attendanceDate = ?
                WHERE er.eventId = ? 
                AND er.status = 'registered'
                AND a.attendanceId IS NULL
                ON DUPLICATE KEY UPDATE status = 'Absent'";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssi", $date, $date, $eventId);
        return $stmt->execute();
    }
    
    // Get attendance summary for event (FIXED - Added table alias to status column)
    public function getAttendanceSummary($eventId, $date = null) {
        if (!$date) $date = date('Y-m-d');
        
        $sql = "SELECT 
                COUNT(CASE WHEN a.status = 'Present' THEN 1 END) as presentCount,
                COUNT(CASE WHEN a.status = 'Absent' THEN 1 END) as absentCount,
                COUNT(CASE WHEN a.status IS NULL THEN 1 END) as notMarkedCount
                FROM event_registrations er
                LEFT JOIN attendance a ON er.userId = a.userId 
                    AND er.eventId = a.eventId 
                    AND a.attendanceDate = ?
                WHERE er.eventId = ? 
                AND er.status = 'registered'";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $date, $eventId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }


    
}
?>