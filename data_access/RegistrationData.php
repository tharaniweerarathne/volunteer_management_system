<?php
class RegistrationData {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // Check if email already exists
    public function emailExists($email) {
        $stmt = $this->conn->prepare("SELECT userId FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
    
    // Insert new user
    public function createUser($name, $email, $hashedPassword, $telephoneNo, $location, $gender) {
        $role = 'Volunteer'; // Always set role as Volunteer
        $stmt = $this->conn->prepare("INSERT INTO users (name, email, password, telephoneNo, location, gender, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $name, $email, $hashedPassword, $telephoneNo, $location, $gender, $role);
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }
    
    // Get skill IDs by skill names
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
    
    // Insert volunteer skills
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
}
?>