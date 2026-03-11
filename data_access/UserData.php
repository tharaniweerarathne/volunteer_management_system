<?php
class UserData {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    
    public function getUserByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc(); 
    }

    public function getUserById($userId) {
        $sql = "SELECT u.*, 
                GROUP_CONCAT(s.skillName SEPARATOR ', ') as skillNames,
                GROUP_CONCAT(s.skillId SEPARATOR ',') as skillIds
                FROM users u
                LEFT JOIN volunteer_skills vs ON u.userId = vs.userId
                LEFT JOIN skills s ON vs.skillId = s.skillId
                WHERE u.userId = ?
                GROUP BY u.userId";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Get all skills for a user
    public function getUserSkills($userId) {
        $sql = "SELECT s.* 
                FROM skills s
                JOIN volunteer_skills vs ON s.skillId = vs.skillId
                WHERE vs.userId = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>
