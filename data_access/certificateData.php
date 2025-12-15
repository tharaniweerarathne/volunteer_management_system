<?php
require_once 'db.php';

class CertificateData {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    // Get volunteers eligible for certificate (Present and no certificate)
    public function getEligibleVolunteers($eventId) {
        $sql = "SELECT DISTINCT u.userId, u.name, u.email, a.attendanceDate
                FROM attendance a
                JOIN users u ON a.userId = u.userId
                LEFT JOIN certificates c ON a.eventId = c.eventId AND a.userId = c.userId
                WHERE a.eventId = ? 
                AND a.status = 'Present'
                AND c.certificateId IS NULL
                AND u.role = 'Volunteer'
                ORDER BY u.name";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    // Issue certificate
    public function issueCertificate($eventId, $userId, $certificateNumber, $filePath, $issuedBy) {
        $sql = "INSERT INTO certificates (eventId, userId, certificateNumber, issueDate, filePath, issuedBy) 
                VALUES (?, ?, ?, CURDATE(), ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iissi", $eventId, $userId, $certificateNumber, $filePath, $issuedBy);
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }
    
    // Check if certificate already issued
    public function isCertificateIssued($eventId, $userId) {
        $sql = "SELECT certificateId FROM certificates 
                WHERE eventId = ? AND userId = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $eventId, $userId);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }
    
    // Get certificate details
    public function getCertificate($certificateId) {
        $sql = "SELECT c.*, u.name as volunteerName, u.email, 
                       e.eventName, e.category, e.startDate,
                       s.skillName, iss.name as issuedByName
                FROM certificates c
                JOIN users u ON c.userId = u.userId
                JOIN events e ON c.eventId = e.eventId
                LEFT JOIN skills s ON e.requiredSkillId = s.skillId
                JOIN users iss ON c.issuedBy = iss.userId
                WHERE c.certificateId = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $certificateId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    // Get all certificates for admin
    public function getAllCertificates() {
        $sql = "SELECT c.*, u.name as volunteerName, e.eventName, 
                       e.startDate, iss.name as issuedByName
                FROM certificates c
                JOIN users u ON c.userId = u.userId
                JOIN events e ON c.eventId = e.eventId
                JOIN users iss ON c.issuedBy = iss.userId
                ORDER BY c.issuedAt DESC";
        
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // Generate unique certificate number
    public function generateCertificateNumber($eventId, $userId) {
        $prefix = "CERT-" . date('Ymd');
        $unique = substr(md5($eventId . $userId . time()), 0, 8);
        return $prefix . "-" . strtoupper($unique);
    }
    
    // Get events with eligible volunteers for certificates
// Get events with eligible volunteers for certificates
public function getEventsWithEligibleVolunteers() {
    $sql = "SELECT DISTINCT e.eventId, e.eventName, e.startDate,
                   COUNT(DISTINCT CASE WHEN a.status = 'Present' 
                                       AND c.certificateId IS NULL 
                                       THEN a.userId END) AS eligibleCount
            FROM events e
            JOIN attendance a ON e.eventId = a.eventId
            LEFT JOIN certificates c 
                ON e.eventId = c.eventId AND a.userId = c.userId
            WHERE a.status = 'Present'
              AND e.endDate <= CURDATE()
            GROUP BY e.eventId
            HAVING eligibleCount > 0
            ORDER BY e.endDate DESC";

    $result = $this->conn->query($sql);

    if (!$result) {
        // Optional: debug SQL errors
        die("Database query failed: " . $this->conn->error);
    }

    return $result->fetch_all(MYSQLI_ASSOC);
}


// Get certificates by volunteer
public function getCertificatesByVolunteer($userId) {
    $sql = "SELECT c.*, e.eventName, e.category, e.startDate,
                   s.skillName, iss.name as issuedByName
            FROM certificates c
            JOIN events e ON c.eventId = e.eventId
            LEFT JOIN skills s ON e.requiredSkillId = s.skillId
            JOIN users iss ON c.issuedBy = iss.userId
            WHERE c.userId = ?
            ORDER BY c.issueDate DESC";
    
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

}
?>