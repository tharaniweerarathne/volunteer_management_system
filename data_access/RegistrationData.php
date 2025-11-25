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
}
?>