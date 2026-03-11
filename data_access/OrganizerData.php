<?php


class OrganizerData {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // Check if user already has a pending or approved request
    public function hasExistingRequest($userId) {
        $stmt = $this->conn->prepare("SELECT requestId, requestStatus FROM organizer_requests WHERE userId = ? AND requestStatus IN ('Pending', 'Approved')");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    // Submit organizer request
    public function submitOrganizerRequest($userId, $organizationName, $organizationType, $organizationDescription, $yearsOfExperience, $previousEvents, $motivation) {
        $stmt = $this->conn->prepare("INSERT INTO organizer_requests (userId, organizationName, organizationType, organizationDescription, yearsOfExperience, previousEvents, motivation) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $userId, $organizationName, $organizationType, $organizationDescription, $yearsOfExperience, $previousEvents, $motivation);
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }
    
    // Get all organizer requests
    public function getAllOrganizerRequests($status = null) {
        $query = "SELECT 
                    or_req.requestId,
                    or_req.userId,
                    or_req.organizationName,
                    or_req.organizationType,
                    or_req.organizationDescription,
                    or_req.yearsOfExperience,
                    or_req.previousEvents,
                    or_req.motivation,
                    or_req.requestStatus,
                    or_req.requestDate,
                    or_req.reviewDate,
                    or_req.reviewNotes,
                    u.name as userName,
                    u.email as userEmail,
                    u.telephoneNo,
                    u.location,
                    reviewer.name as reviewerName
                  FROM organizer_requests or_req
                  JOIN users u ON or_req.userId = u.userId
                  LEFT JOIN users reviewer ON or_req.reviewedBy = reviewer.userId";
        
        if ($status) {
            $query .= " WHERE or_req.requestStatus = ?";
            $stmt = $this->conn->prepare($query . " ORDER BY or_req.requestDate DESC");
            $stmt->bind_param("s", $status);
        } else {
            $stmt = $this->conn->prepare($query . " ORDER BY or_req.requestDate DESC");
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // Get organizer request by ID
    public function getOrganizerRequestById($requestId) {
        $stmt = $this->conn->prepare("SELECT 
                    or_req.*,
                    u.name as userName,
                    u.email as userEmail,
                    u.telephoneNo,
                    u.location,
                    u.gender,
                    reviewer.name as reviewerName
                  FROM organizer_requests or_req
                  JOIN users u ON or_req.userId = u.userId
                  LEFT JOIN users reviewer ON or_req.reviewedBy = reviewer.userId
                  WHERE or_req.requestId = ?");
        $stmt->bind_param("i", $requestId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    
    public function getUserOrganizerRequests($userId) {
        $stmt = $this->conn->prepare("SELECT * FROM organizer_requests WHERE userId = ? ORDER BY requestDate DESC");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // Approve organizer request
    public function approveOrganizerRequest($requestId, $reviewerId, $reviewNotes = '') {
        
        $this->conn->begin_transaction();
        
        try {
            
            $stmt = $this->conn->prepare("SELECT userId FROM organizer_requests WHERE requestId = ?");
            $stmt->bind_param("i", $requestId);
            $stmt->execute();
            $result = $stmt->get_result();
            $request = $result->fetch_assoc();
            
            if (!$request) {
                throw new Exception("Request not found");
            }
            
            
            $stmt = $this->conn->prepare("UPDATE organizer_requests SET requestStatus = 'Approved', reviewedBy = ?, reviewDate = NOW(), reviewNotes = ? WHERE requestId = ?");
            $stmt->bind_param("isi", $reviewerId, $reviewNotes, $requestId);
            $stmt->execute();
            
            // Update user role to Organizer
            $stmt = $this->conn->prepare("UPDATE users SET role = 'Organizer' WHERE userId = ?");
            $stmt->bind_param("i", $request['userId']);
            $stmt->execute();
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }
    
    // Reject organizer request
    public function rejectOrganizerRequest($requestId, $reviewerId, $reviewNotes = '') {
        $stmt = $this->conn->prepare("UPDATE organizer_requests SET requestStatus = 'Rejected', reviewedBy = ?, reviewDate = NOW(), reviewNotes = ? WHERE requestId = ?");
        $stmt->bind_param("isi", $reviewerId, $reviewNotes, $requestId);
        return $stmt->execute();
    }
    
    
    public function getAllOrganizers() {
        $stmt = $this->conn->prepare("SELECT userId, name, email, telephoneNo, location, gender FROM users WHERE role = 'Organizer' ORDER BY name ASC");
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // Get organizer details with request info
    public function getOrganizerDetails($userId) {
        $stmt = $this->conn->prepare("SELECT 
                    u.*,
                    or_req.organizationName,
                    or_req.organizationType,
                    or_req.organizationDescription,
                    or_req.yearsOfExperience,
                    or_req.previousEvents,
                    or_req.requestDate
                  FROM users u
                  LEFT JOIN organizer_requests or_req ON u.userId = or_req.userId AND or_req.requestStatus = 'Approved'
                  WHERE u.userId = ? AND u.role = 'Organizer'");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    
    public function getRequestStatistics() {
        $stmt = $this->conn->prepare("SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN requestStatus = 'Pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN requestStatus = 'Approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN requestStatus = 'Rejected' THEN 1 ELSE 0 END) as rejected
                  FROM organizer_requests");
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
   
public function deleteOrganizerRequest($requestId) {
    
    $stmt = $this->conn->prepare(
        "SELECT userId FROM organizer_requests WHERE requestId = ?"
    );
    $stmt->bind_param("i", $requestId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $userId = $row['userId'];

        
        $stmtUser = $this->conn->prepare(
            "SELECT email FROM users WHERE userId = ?"
        );
        $stmtUser->bind_param("i", $userId);
        $stmtUser->execute();
        $userResult = $stmtUser->get_result();

        if ($userRow = $userResult->fetch_assoc()) {
            $email = $userRow['email'];

            
            $stmtDel = $this->conn->prepare(
                "DELETE FROM organizer_requests WHERE requestId = ?"
            );
            $stmtDel->bind_param("i", $requestId);
            $stmtDel->execute();

         
            $stmtDelUser = $this->conn->prepare(
                "DELETE FROM users WHERE userId = ?"
            );
            $stmtDelUser->bind_param("i", $userId);
            return $stmtDelUser->execute();
        }
    }

    return false;
}


    
    // Get organizers for CSV export
    public function getAllOrganizersForExport() {
        $query = "SELECT 
                    u.userId,
                    u.name,
                    u.email,
                    u.telephoneNo,
                    u.location,
                    u.gender,
                    or_req.organizationName,
                    or_req.organizationType,
                    or_req.yearsOfExperience,
                    or_req.requestDate as approvedDate
                  FROM users u
                  LEFT JOIN organizer_requests or_req ON u.userId = or_req.userId AND or_req.requestStatus = 'Approved'
                  WHERE u.role = 'Organizer'
                  ORDER BY u.name ASC";
        
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>