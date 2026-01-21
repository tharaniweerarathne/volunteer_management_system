<?php
// resultsData.php ----> data_access folder (UPDATED VERSION WITH MULTIPLE IMAGES)
require_once 'db.php';

class ResultsData {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    public function getAllResults($filters = []) {
        global $conn;
        
        $sql = "SELECT er.*, e.eventName, e.category, e.startDate as eventStartDate, e.location, s.skillName,
                u.name as addedByName, u.role as addedByRole,
                org.name as organizerName, org.email as organizerEmail,
                er.approvalStatus,
                CASE 
                    WHEN er.approvalStatus = 'Approved' THEN 1
                    WHEN er.approvalStatus = 'Pending' THEN 2
                    WHEN er.approvalStatus = 'Rejected' THEN 3
                    ELSE 4
                END as status_order
                FROM event_results er
                LEFT JOIN events e ON er.eventId = e.eventId
                LEFT JOIN skills s ON er.skillId = s.skillId
                LEFT JOIN users u ON er.addedBy = u.userId
                LEFT JOIN users org ON er.organizerId = org.userId
                WHERE 1=1";
        
        $params = [];
        $types = "";
        
        // search filter
        if (!empty($filters['search'])) {
            $sql .= " AND (er.resultTitle LIKE ? OR er.description LIKE ? OR e.eventName LIKE ? OR org.name LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "ssss";
        }
        
        // filter by event
        if (!empty($filters['eventId'])) {
            $sql .= " AND er.eventId = ?";
            $params[] = $filters['eventId'];
            $types .= "i";
        }
        
        // filter by skill
        if (!empty($filters['skillId'])) {
            $sql .= " AND er.skillId = ?";
            $params[] = $filters['skillId'];
            $types .= "i";
        }
        
        // filter by category
        if (!empty($filters['category'])) {
            $sql .= " AND e.category = ?";
            $params[] = $filters['category'];
            $types .= "s";
        }
        
        // filter by organizer
        if (!empty($filters['organizerId'])) {
            $sql .= " AND er.organizerId = ?";
            $params[] = $filters['organizerId'];
            $types .= "i";
        }
        
        // date filters
        if (!empty($filters['fromDate'])) {
            $sql .= " AND (e.startDate >= ? OR er.resultDate >= ?)";
            $params[] = $filters['fromDate'];
            $params[] = $filters['fromDate'];
            $types .= "ss";
        }
        
        if (!empty($filters['toDate'])) {
            $sql .= " AND (e.startDate <= ? OR er.resultDate <= ?)";
            $params[] = $filters['toDate'];
            $params[] = $filters['toDate'];
            $types .= "ss";
        }
        
        if (!empty($filters['date'])) {
            $sql .= " AND (e.startDate = ? OR er.resultDate = ?)";
            $params[] = $filters['date'];
            $params[] = $filters['date'];
            $types .= "ss";
        }
        
        // filter by approval status
        if (!empty($filters['approvalStatus'])) {
            $sql .= " AND er.approvalStatus = ?";
            $params[] = $filters['approvalStatus'];
            $types .= "s";
        }
        
        // filter by added by user
        if (!empty($filters['addedBy'])) {
            $sql .= " AND er.addedBy = ?";
            $params[] = $filters['addedBy'];
            $types .= "i";
        }
        
        // location filter
        if (!empty($filters['location'])) {
            $sql .= " AND e.location LIKE ?";
            $params[] = "%{$filters['location']}%";
            $types .= "s";
        }
        
        $sql .= " ORDER BY status_order, er.resultDate DESC";
        
        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    // Create new result with multiple images
// Create new result with multiple images (FIXED VERSION)
public function createResult($data) {
    global $conn;
    
    $sql = "INSERT INTO event_results (eventId, organizerId, resultTitle, description, resultDate, 
            skillId, resultImage, resultImage2, resultImage3, resultImage4, resultImage5, addedBy, approvalStatus) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $defaultStatus = 'Pending';
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }
    
    // Extract values with proper null handling
    $eventId = $data['eventId'];
    $organizerId = $data['organizerId'];
    $resultTitle = $data['resultTitle'];
    $description = $data['description'];
    $resultDate = $data['resultDate'];
    $skillId = $data['skillId'] ?? null;
    $resultImage = $data['resultImage'] ?? null;
    $resultImage2 = $data['resultImage2'] ?? null;
    $resultImage3 = $data['resultImage3'] ?? null;
    $resultImage4 = $data['resultImage4'] ?? null;
    $resultImage5 = $data['resultImage5'] ?? null;
    $addedBy = $data['addedBy'];
    
    // Bind parameters properly - don't pass null directly by reference
    $stmt->bind_param("iisssisssssss", 
        $eventId,
        $organizerId,
        $resultTitle,
        $description,
        $resultDate,
        $skillId,
        $resultImage,
        $resultImage2,
        $resultImage3,
        $resultImage4,
        $resultImage5,
        $addedBy,
        $defaultStatus
    );
    
    $result = $stmt->execute();
    
    if (!$result) {
        error_log("Execute failed: " . $stmt->error);
        return false;
    }
    
    $insertId = $conn->insert_id;
    $stmt->close();
    
    return $insertId;
}
    
    // Get result by ID (UPDATED with multiple images)
    public function getResultById($resultId) {
        global $conn;
        
        $sql = "SELECT er.*, e.eventName, e.category, s.skillName,
                u.name as addedByName, u.userId as addedById, u.role as addedByRole,
                org.name as organizerName, org.email as organizerEmail, org.userId as organizerId,
                approver.name as approvedByName
                FROM event_results er
                LEFT JOIN events e ON er.eventId = e.eventId
                LEFT JOIN skills s ON er.skillId = s.skillId
                LEFT JOIN users u ON er.addedBy = u.userId
                LEFT JOIN users org ON er.organizerId = org.userId
                LEFT JOIN users approver ON er.approvedBy = approver.userId
                WHERE er.resultId = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $resultId);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_assoc();
    }
    
    // Update result with multiple images
// Update result with multiple images (FIXED VERSION)
public function updateResult($resultId, $data) {
    global $conn;
    
    $sql = "UPDATE event_results SET 
            eventId = ?, organizerId = ?, resultTitle = ?, description = ?, resultDate = ?, 
            skillId = ?, resultImage = ?, resultImage2 = ?, resultImage3 = ?, resultImage4 = ?, resultImage5 = ?
            WHERE resultId = ?";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }
    
    // Extract values with proper null handling
    $eventId = $data['eventId'];
    $organizerId = $data['organizerId'];
    $resultTitle = $data['resultTitle'];
    $description = $data['description'];
    $resultDate = $data['resultDate'];
    $skillId = $data['skillId'] ?? null;
    $resultImage = $data['resultImage'] ?? null;
    $resultImage2 = $data['resultImage2'] ?? null;
    $resultImage3 = $data['resultImage3'] ?? null;
    $resultImage4 = $data['resultImage4'] ?? null;
    $resultImage5 = $data['resultImage5'] ?? null;
    
    $stmt->bind_param("iisssisssssi", 
        $eventId,
        $organizerId,
        $resultTitle,
        $description,
        $resultDate,
        $skillId,
        $resultImage,
        $resultImage2,
        $resultImage3,
        $resultImage4,
        $resultImage5,
        $resultId
    );
    
    $result = $stmt->execute();
    
    if (!$result) {
        error_log("Execute failed: " . $stmt->error);
        return false;
    }
    
    $stmt->close();
    return true;
}
    
    // Get all organizers for dropdown
    public function getAllOrganizers() {
        global $conn;
        
        $sql = "SELECT DISTINCT u.userId, u.name, u.email, u.role
                FROM users u
                WHERE u.role = 'Organizer'
                ORDER BY u.name";
        
        $result = $conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // Get organizers for a specific event
    public function getEventOrganizers($eventId) {
        global $conn;
        
        $sql = "SELECT u.userId, u.name, u.email
                FROM events e
                JOIN users u ON e.createdBy = u.userId
                WHERE e.eventId = ?
                AND u.role = 'Organizer'
                AND u.status = 'Active'
                
                UNION
                
                SELECT DISTINCT u.userId, u.name, u.email
                FROM event_coordinators ec
                JOIN users u ON ec.coordinatorId = u.userId
                WHERE ec.eventId = ?
                AND u.role = 'Organizer'
                AND u.status = 'Active'";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $eventId, $eventId);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    // Get events with their organizers for dropdown
    public function getEventsWithOrganizers() {
        global $conn;
        
        $sql = "SELECT e.eventId, e.eventName, e.startDate, e.category,
                u.userId as organizerId, u.name as organizerName
                FROM events e
                LEFT JOIN users u ON e.createdBy = u.userId
                WHERE e.status = 'Active' 
                AND e.endDate <= CURDATE()
                ORDER BY e.eventName";
        
        $result = $conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // Get approved results for public display
    public function getApprovedResults($limit = 10, $filters = []) {
        global $conn;
        
        $sql = "SELECT er.*, e.eventName, e.category, e.startDate as eventStartDate, s.skillName,
                u.name as addedByName,
                org.name as organizerName, org.email as organizerEmail
                FROM event_results er
                LEFT JOIN events e ON er.eventId = e.eventId
                LEFT JOIN skills s ON er.skillId = s.skillId
                LEFT JOIN users u ON er.addedBy = u.userId
                LEFT JOIN users org ON er.organizerId = org.userId
                WHERE er.approvalStatus = 'Approved'";
        
        $params = [];
        $types = "";
        
        // filter by event
        if (!empty($filters['eventId'])) {
            $sql .= " AND er.eventId = ?";
            $params[] = $filters['eventId'];
            $types .= "i";
        }
        
        // filter by skill
        if (!empty($filters['skillId'])) {
            $sql .= " AND er.skillId = ?";
            $params[] = $filters['skillId'];
            $types .= "i";
        }
        
        // filter by category
        if (!empty($filters['category'])) {
            $sql .= " AND e.category = ?";
            $params[] = $filters['category'];
            $types .= "s";
        }
        
        // filter by organizer
        if (!empty($filters['organizerId'])) {
            $sql .= " AND er.organizerId = ?";
            $params[] = $filters['organizerId'];
            $types .= "i";
        }
        
        // search filter
        if (!empty($filters['search'])) {
            $sql .= " AND (er.resultTitle LIKE ? OR er.description LIKE ? OR e.eventName LIKE ? OR org.name LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "ssss";
        }
        
        // date filter
        if (!empty($filters['date'])) {
            $sql .= " AND (e.startDate = ? OR er.resultDate = ?)";
            $params[] = $filters['date'];
            $params[] = $filters['date'];
            $types .= "ss";
        }
        
        // location filter
        if (!empty($filters['location'])) {
            $sql .= " AND e.location LIKE ?";
            $params[] = "%{$filters['location']}%";
            $types .= "s";
        }
        
        $sql .= " ORDER BY er.resultDate DESC LIMIT ?";
        $params[] = $limit;
        $types .= "i";
        
        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    // Approve result
    public function approveResult($resultId, $adminId, $notes = '') {
        global $conn;
        
        $sql = "UPDATE event_results SET 
                approvalStatus = 'Approved',
                approvedBy = ?,
                approvalNotes = ?,
                approvedDate = NOW()
                WHERE resultId = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isi", $adminId, $notes, $resultId);
        
        return $stmt->execute();
    }
    
    // Reject result
    public function rejectResult($resultId, $adminId, $notes = '') {
        global $conn;
        
        $sql = "UPDATE event_results SET 
                approvalStatus = 'Rejected',
                approvedBy = ?,
                approvalNotes = ?,
                approvedDate = NOW()
                WHERE resultId = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isi", $adminId, $notes, $resultId);
        
        return $stmt->execute();
    }
    
    // Delete result with multiple images
    public function deleteResult($resultId) {
        global $conn;
        
        // Get image paths first
        $result = $this->getResultById($resultId);
        if (!$result) {
            return false;
        }
        
        // Delete from database
        $sql = "DELETE FROM event_results WHERE resultId = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $resultId);
        $success = $stmt->execute();
        
        // Delete all image files if they exist
        if ($success) {
            $imageFields = ['resultImage', 'resultImage2', 'resultImage3', 'resultImage4', 'resultImage5'];
            
            foreach ($imageFields as $field) {
                $imagePath = $result[$field] ?? null;
                if ($imagePath && file_exists('../' . $imagePath)) {
                    unlink('../' . $imagePath);
                }
            }
        }
        
        return $success;
    }
    
    public function getAllEventsForDropdown() {
        global $conn;
        
        $sql = "SELECT eventId, eventName, startDate, category 
                FROM events 
                WHERE status = 'Active' 
                AND endDate <= CURDATE()
                ORDER BY eventName";
        
        $result = $conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getResultsStatistics($userId = null) {
        global $conn;
        
        $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN approvalStatus = 'Pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN approvalStatus = 'Approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN approvalStatus = 'Rejected' THEN 1 ELSE 0 END) as rejected
                FROM event_results";
        
        if ($userId) {
            $sql .= " WHERE addedBy = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        }
        
        $result = $conn->query($sql);
        return $result->fetch_assoc();
    }
    
    public function canUserModifyResult($resultId, $userId) {
        $result = $this->getResultById($resultId);
        if (!$result) return false;
        
        // Admin can modify any result
        if ($this->getUserRole($userId) == 'Admin') {
            return true;
        }
        
        // Organizers/Coordinators can only modify their own results
        // unless result is already approved (then only admin can modify)
        return ($result['addedById'] == $userId && $result['approvalStatus'] == 'Pending');
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
    
    public function getEventsForResultSubmission($userId = null) {
        global $conn;
        
        $sql = "SELECT e.*, 
                (SELECT COUNT(*) FROM event_results WHERE eventId = e.eventId) as resultCount
                FROM events e
                WHERE e.status = 'Active' 
                AND e.endDate <= CURDATE()";
        
        if ($userId) {
            $userRole = $this->getUserRole($userId);
            
            if ($userRole == 'Coordinator') {
                $sql .= " AND EXISTS (
                    SELECT 1 FROM event_coordinators ec 
                    WHERE ec.eventId = e.eventId AND ec.coordinatorId = ?
                )";
            } elseif ($userRole == 'Organizer') {
                $sql .= " AND (e.createdBy = ? OR EXISTS (
                    SELECT 1 FROM event_coordinators ec 
                    WHERE ec.eventId = e.eventId AND ec.coordinatorId = ?
                ))";
            }
            
            $stmt = $conn->prepare($sql);
            if ($userRole == 'Organizer') {
                $stmt->bind_param("ii", $userId, $userId);
            } else {
                $stmt->bind_param("i", $userId);
            }
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }
        
        $result = $conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // Get all images for a result as an array
    public function getResultImages($resultId) {
        global $conn;
        
        $sql = "SELECT resultImage, resultImage2, resultImage3, resultImage4, resultImage5 
                FROM event_results WHERE resultId = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $resultId);
        $stmt->execute();
        
        $result = $stmt->get_result()->fetch_assoc();
        $images = [];
        
        if ($result) {
            for ($i = 1; $i <= 5; $i++) {
                $fieldName = $i == 1 ? 'resultImage' : 'resultImage' . $i;
                if (!empty($result[$fieldName])) {
                    $images[] = $result[$fieldName];
                }
            }
        }
        
        return $images;
    }
}
?>