<?php
class RegistrationData {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // checking if email already exists
    public function emailExists($email) {
        $stmt = $this->conn->prepare("SELECT userId FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
    
    // insertion of new user
    public function createUser($name, $email, $hashedPassword, $telephoneNo, $location, $gender) {
        $role = 'Volunteer'; 
        $stmt = $this->conn->prepare("INSERT INTO users (name, email, password, telephoneNo, location, gender, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $name, $email, $hashedPassword, $telephoneNo, $location, $gender, $role);
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }
    
    
    public function getSkillIdsByNames($skillNames) {
        if (empty($skillNames)) {
            return [];
        }
        
        $placeholders = str_repeat('?,', count($skillNames) - 1) . '?';
        $stmt = $this->conn->prepare("SELECT skillId, skillName FROM skills WHERE skillName IN ($placeholders)");
        
        $types = str_repeat('s', count($skillNames));
        $stmt->bind_param($types, ...$skillNames);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $skillIds = [];
        while ($row = $result->fetch_assoc()) {
            $skillIds[] = $row['skillId'];
        }
        return $skillIds;
    }
    
    // insertion of volunteer skills
    public function addVolunteerSkills($userId, $skillIds) {
        if (empty($skillIds)) {
            return true;
        }
        
        $stmt = $this->conn->prepare("INSERT INTO volunteer_skills (userId, skillId) VALUES (?, ?)");
        
        foreach ($skillIds as $skillId) {
            $stmt->bind_param("ii", $userId, $skillId);
            if (!$stmt->execute()) {
                return false;
            }
        }
        return true;
    }


       // ==================== coordinators methods ====================
    
    // create coordinator 
    public function createCoordinator($name, $email, $hashedPassword, $telephoneNo, $location, $gender) {
        $role = 'Coordinator'; 
        $stmt = $this->conn->prepare("INSERT INTO users (name, email, password, telephoneNo, location, gender, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $name, $email, $hashedPassword, $telephoneNo, $location, $gender, $role);
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }
    
    // get all coordinators
    public function getAllCoordinators() {
        $stmt = $this->conn->prepare("SELECT userId, name, email, telephoneNo, location, gender FROM users WHERE role = 'Coordinator' ORDER BY name ASC");
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    
    public function getCoordinatorById($userId) {
        $stmt = $this->conn->prepare("SELECT userId, name, email, telephoneNo, location, gender FROM users WHERE userId = ? AND role = 'Coordinator'");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    // update coordinator
    public function updateCoordinator($userId, $name, $email, $telephoneNo, $location, $gender) {
        $stmt = $this->conn->prepare("UPDATE users SET name = ?, email = ?, telephoneNo = ?, location = ?, gender = ? WHERE userId = ? AND role = 'Coordinator'");
        $stmt->bind_param("sssssi", $name, $email, $telephoneNo, $location, $gender, $userId);
        return $stmt->execute();
    }
    
    // update coordinator password
    public function updateCoordinatorPassword($userId, $hashedPassword) {
        $stmt = $this->conn->prepare("UPDATE users SET password = ? WHERE userId = ? AND role = 'Coordinator'");
        $stmt->bind_param("si", $hashedPassword, $userId);
        return $stmt->execute();
    }
    
    // delete coordinator
    public function deleteCoordinator($userId) {
        $stmt = $this->conn->prepare("DELETE FROM users WHERE userId = ? AND role = 'Coordinator'");
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }
    
    // check if email exists for another user (for update validation)
    public function emailExistsForOtherUser($email, $userId) {
        $stmt = $this->conn->prepare("SELECT userId FROM users WHERE email = ? AND userId != ?");
        $stmt->bind_param("si", $email, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }


      // ==================== volunteers methods ====================
    
    // get all volunteers with their skills
    public function getAllVolunteers() {
        $query = "SELECT u.userId, u.name, u.email, u.telephoneNo, u.location, u.gender,
                  GROUP_CONCAT(s.skillName SEPARATOR ',') as skills
                  FROM users u
                  LEFT JOIN volunteer_skills vs ON u.userId = vs.userId
                  LEFT JOIN skills s ON vs.skillId = s.skillId
                  WHERE u.role = 'Volunteer'
                  GROUP BY u.userId
                  ORDER BY u.userId DESC";
        
        $result = $this->conn->query($query);
        $volunteers = [];
        
        while ($row = $result->fetch_assoc()) {
            $row['skills'] = $row['skills'] ? explode(',', $row['skills']) : [];
            $volunteers[] = $row;
        }
        
        return $volunteers;
    }
    
    // get volunteer by ID with skills
    public function getVolunteerById($userId) {
        $stmt = $this->conn->prepare("SELECT userId, name, email, telephoneNo, location, gender FROM users WHERE userId = ? AND role = 'Volunteer'");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $volunteer = $result->fetch_assoc();
        
        if ($volunteer) {
            // Get skills
            $skillStmt = $this->conn->prepare("SELECT s.skillName FROM volunteer_skills vs 
                                              JOIN skills s ON vs.skillId = s.skillId 
                                              WHERE vs.userId = ?");
            $skillStmt->bind_param("i", $userId);
            $skillStmt->execute();
            $skillResult = $skillStmt->get_result();
            
            $skills = [];
            while ($skillRow = $skillResult->fetch_assoc()) {
                $skills[] = $skillRow['skillName'];
            }
            $volunteer['skills'] = $skills;
        }
        
        return $volunteer;
    }
    
    // update volunteer
    public function updateVolunteer($userId, $name, $email, $telephoneNo, $location, $gender) {
        $stmt = $this->conn->prepare("UPDATE users SET name = ?, email = ?, telephoneNo = ?, location = ?, gender = ? WHERE userId = ? AND role = 'Volunteer'");
        $stmt->bind_param("sssssi", $name, $email, $telephoneNo, $location, $gender, $userId);
        return $stmt->execute();
    }
    
    // update volunteer password
    public function updateVolunteerPassword($userId, $hashedPassword) {
        $stmt = $this->conn->prepare("UPDATE users SET password = ? WHERE userId = ? AND role = 'Volunteer'");
        $stmt->bind_param("si", $hashedPassword, $userId);
        return $stmt->execute();
    }
    
    // delete volunteer skills
    public function deleteVolunteerSkills($userId) {
        $stmt = $this->conn->prepare("DELETE FROM volunteer_skills WHERE userId = ?");
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }
    
    // delete volunteer
public function deleteVolunteer($userId) {
    // delete from volunteer_skills 
    $stmt1 = $this->conn->prepare("DELETE FROM volunteer_skills WHERE userId = ?");
    $stmt1->bind_param("i", $userId);
    $stmt1->execute();

    // delete from users table
    $stmt2 = $this->conn->prepare("DELETE FROM users WHERE userId = ?");
    $stmt2->bind_param("i", $userId);

    return $stmt2->execute();
}


// ==================== CSV export methods ====================

// get all volunteers for CSV export 
public function getAllVolunteersForExport() {
    $query = "SELECT u.userId, u.name, u.email, u.telephoneNo, u.location, u.gender,
              GROUP_CONCAT(s.skillName SEPARATOR ', ') as skills
              FROM users u
              LEFT JOIN volunteer_skills vs ON u.userId = vs.userId
              LEFT JOIN skills s ON vs.skillId = s.skillId
              WHERE u.role = 'Volunteer'
              GROUP BY u.userId
              ORDER BY u.userId DESC";
    
    $result = $this->conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// get all coordinators for CSV export 
public function getAllCoordinatorsForExport() {
    $stmt = $this->conn->prepare("SELECT userId, name, email, telephoneNo, location, gender 
                                   FROM users 
                                   WHERE role = 'Coordinator' 
                                   ORDER BY name ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// get all users for CSV export 
public function getAllUsersForExport() {
    $query = "SELECT u.userId, u.name, u.email, u.telephoneNo, u.location, u.gender, u.role,
              GROUP_CONCAT(s.skillName SEPARATOR ', ') as skills
              FROM users u
              LEFT JOIN volunteer_skills vs ON u.userId = vs.userId
              LEFT JOIN skills s ON vs.skillId = s.skillId
              GROUP BY u.userId
              ORDER BY u.role, u.name ASC";
    
    $result = $this->conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
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

// generic method for custom queries
public function getDataForExport($query) {
    $result = $this->conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}
}
?>